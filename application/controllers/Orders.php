<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Orders extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->setPageTitle('Orders Management');

        // Fetch all orders with customer name and vendor names
        $orders = $this->db
            ->select('o.*, c.name as customer_name, GROUP_CONCAT(DISTINCT v.name SEPARATOR ", ") as vendor_names')
            ->from('orders o')
            ->join('users c', 'c.id = o.user_id', 'left')
            ->join('order_items oi', 'oi.order_id = o.id', 'left')
            ->join('users v', 'v.id = oi.vendor_id', 'left')
            ->group_by('o.id')
            ->order_by('CASE 
                WHEN o.status = "cancelled" THEN 3 
                WHEN o.status = "delivered" OR o.status = "completed" THEN 2 
                ELSE 1 
            END', 'ASC', FALSE)
            ->order_by('o.id', 'DESC')
            ->get()
            ->result();

        $data['orders'] = $orders;

        $this->loadView('orders/index', $data);
    }

    public function view($id)
    {
        $this->ensure_orders_table();
        $this->setPageTitle('Order Details');

        // Fetch order with customer details
        $order = $this->db
            ->select('o.*, c.name as customer_name, c.mobile as customer_mobile, c.email as customer_email')
            ->from('orders o')
            ->join('users c', 'c.id = o.user_id', 'left')
            ->where('o.id', $id)
            ->get()
            ->row();

        if (!$order) {
            $this->setMessage('danger', 'Order not found');
            redirect('orders');
        }

        // Fetch order items with vendor details
        $items = $this->db
            ->select('oi.*, v.name as vendor_name, v.store_name as vendor_store')
            ->from('order_items oi')
            ->join('users v', 'v.id = oi.vendor_id', 'left')
            ->where('oi.order_id', $id)
            ->get()
            ->result();

        // Fetch customer address details (if address_id exists)
        $address = null;
        if (!empty($order->address_id)) {
            $address = $this->db->get_where('user_addresses', ['id' => $order->address_id])->row();
        }

        $this->load->helper('distance');

        // Check if distance or delivery charge is missing/zero and can be calculated automatically
        if (!empty($order->address_id) && !empty($items)) {
            $first_vendor_id = (int)($items[0]->vendor_id ?? 0);
            if ($first_vendor_id > 0) {
                $vendor = $this->db->select('id, address, pincode, latitude, longitude')->get_where('users', ['id' => $first_vendor_id])->row();
                if ($address) {
                    geocode_address_if_needed($address);
                }
                if ($vendor) {
                    geocode_vendor_if_needed($vendor);
                }

                // If order distance or delivery_charge is 0 or null
                if (($order->distance === null || (float)$order->distance == 0 || (float)$order->delivery_charge == 0) &&
                    $vendor && $address &&
                    !empty($vendor->latitude) && !empty($vendor->longitude) &&
                    !empty($address->latitude) && !empty($address->longitude) &&
                    (float)$vendor->latitude != 0 && (float)$vendor->longitude != 0 &&
                    (float)$address->latitude != 0 && (float)$address->longitude != 0) {

                    $resolved = resolve_order_distance($vendor->latitude, $vendor->longitude, $address->latitude, $address->longitude, true);
                    $new_dist = $resolved['distance_km'];
                    $new_method = $resolved['method'];
                    $chosen_slot = isset($order->chosen_time_option) ? $order->chosen_time_option : null;
                    $new_charge = calculate_delivery_charge_amount($new_dist, $order->delivery_type ?: 'normal', $chosen_slot);

                    $new_option = ($order->delivery_option === 'self' || empty($order->delivery_option)) ? 'delivery_partner' : $order->delivery_option;
                    $new_total = round((float)$order->subtotal + (float)$order->gst_amount + $new_charge - (float)$order->discount, 2);

                    $this->db->where('id', $id)->update('orders', [
                        'distance'        => $new_dist,
                        'distance_km'     => $new_dist,
                        'distance_method' => $new_method,
                        'delivery_charge' => $new_charge,
                        'delivery_option' => $new_option,
                        'total_amount'    => $new_total,
                        'updated_at'      => date('Y-m-d H:i:s')
                    ]);

                    $order->distance        = $new_dist;
                    $order->distance_km     = $new_dist;
                    $order->distance_method = $new_method;
                    $order->delivery_charge = $new_charge;
                    $order->delivery_option = $new_option;
                    $order->total_amount    = $new_total;
                }
            }
        }

        // Fetch order status history
        $history = $this->db
            ->order_by('created_at', 'DESC')
            ->get_where('order_status_history', ['order_id' => $id])
            ->result();

        $slot = !empty($order->chosen_time_option) ? $order->chosen_time_option : ($order->delivery_type === 'urgent' ? 'immediately' : 'immediately');
        $data['delivery_breakdown'] = get_delivery_charge_breakdown($order->distance ?: 0, $slot, $order->delivery_type ?: 'normal');

        $data['order'] = $order;
        $data['items'] = $items;
        $data['address'] = $address;
        $data['history'] = $history;

        $this->loadView('orders/view', $data);
    }

    public function update_status()
    {
        $this->ensure_orders_table();
        $this->load->helper('distance');
        if ($this->input->method() == 'post') {
            $order_id = $this->post('order_id');
            $status = $this->post('status');
            $remarks = $this->post('remarks') ?: 'Status updated by Admin';

            $delivery_option = $this->post('delivery_option');
            $distance_val = $this->post('distance');
            $delivery_type = $this->post('delivery_type') ?: 'normal';

            $order = $this->gm->getById('orders', $order_id);
            if ($order) {
                $update_fields = [
                    'status' => $status,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                if ($delivery_option !== null && $delivery_option !== '') {
                    $update_fields['delivery_option'] = $delivery_option;
                }

                $distance = null;
                if ($distance_val !== null && $distance_val !== '' && is_numeric($distance_val)) {
                    $distance = floatval($distance_val);
                    $update_fields['distance'] = $distance;
                    $update_fields['distance_km'] = $distance;
                } else {
                    $update_fields['distance'] = null;
                    $update_fields['distance_km'] = null;
                }

                $update_fields['delivery_type'] = $delivery_type;

                // Calculate delivery charge if distance is set
                if ($distance !== null && $distance >= 0) {
                    $chosen_slot = isset($order->chosen_time_option) ? $order->chosen_time_option : null;
                    $delivery_charge = calculate_delivery_charge_amount($distance, $delivery_type, $chosen_slot);
                    $update_fields['delivery_charge'] = $delivery_charge;
                    $update_fields['total_amount'] = floatval($order->subtotal) + floatval($order->gst_amount) + $delivery_charge - floatval($order->discount);
                } else {
                    $update_fields['delivery_charge'] = 0.00;
                    $update_fields['total_amount'] = floatval($order->subtotal) + floatval($order->gst_amount) - floatval($order->discount);
                }

                // Update order status
                $this->gm->update('orders', $update_fields, ['id' => $order_id]);

                // Insert into history
                $history_data = [
                    'order_id' => $order_id,
                    'status' => $status,
                    'remarks' => $remarks,
                    'changed_by' => 'admin',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $this->db->insert('order_status_history', $history_data);

                $this->setMessage('success', 'Order status updated successfully');
            } else {
                $this->setMessage('danger', 'Failed to update order status');
            }
        }
        redirect('orders/view/' . $order_id);
    }

    private function ensure_orders_table()
    {
        if (!$this->db->field_exists('delivery_option', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `delivery_option` VARCHAR(50) DEFAULT 'self'");
        }
        if (!$this->db->field_exists('distance', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `distance` DECIMAL(10,2) DEFAULT NULL");
        }
        if (!$this->db->field_exists('distance_km', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `distance_km` DECIMAL(10,2) DEFAULT NULL");
        }
        if (!$this->db->field_exists('distance_method', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `distance_method` VARCHAR(50) DEFAULT 'pure_math'");
        }
        if (!$this->db->field_exists('delivery_type', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `delivery_type` VARCHAR(50) DEFAULT 'normal'");
        }
        if (!$this->db->field_exists('hidden_by_customer', 'orders')) {
            $this->db->query("ALTER TABLE `orders` ADD COLUMN `hidden_by_customer` TINYINT(1) NOT NULL DEFAULT 0");
        }
    }
}
