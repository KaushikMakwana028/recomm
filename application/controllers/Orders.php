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
        if ($this->input->method() == 'post') {
            $order_id = $this->post('order_id');
            $status = $this->post('status');
            $remarks = $this->post('remarks') ?: 'Status updated by Admin';

            $order = $this->gm->getById('orders', $order_id);
            if ($order) {
                // Update order status
                $this->gm->update('orders', ['status' => $status], ['id' => $order_id]);

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
}
