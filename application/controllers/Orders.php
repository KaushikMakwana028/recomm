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

        // Fetch order status history
        $history = $this->db
            ->order_by('created_at', 'DESC')
            ->get_where('order_status_history', ['order_id' => $id])
            ->result();

        $data['order'] = $order;
        $data['items'] = $items;
        $data['address'] = $address;
        $data['history'] = $history;

        $this->loadView('orders/view', $data);
    }

    public function update_status()
    {
        $this->ensure_orders_table();
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
                    $base_charge = $distance * 10.00;
                    if ($delivery_type === 'urgent') {
                        $delivery_charge = $base_charge + 50.00;
                    } else {
                        $delivery_charge = $base_charge;
                    }
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
    }
}
