<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Dashboard Home
     */
    public function index()
    {
        $this->setPageTitle('Dashboard');

        // Get statistics
        $data['total_users'] = $this->gm->countRows('users', ['role' => 'user']);
        $data['total_vendors'] = $this->gm->countRows('users', ['role' => 'vendor']);
        $data['total_products'] = $this->gm->countRows('products');
        $data['total_categories'] = $this->gm->countRows('categories');
        $data['total_orders'] = $this->gm->countRows('orders');
        $data['total_revenue'] = $this->gm->getSum('orders', 'total_amount', ['payment_status' => 'paid']);

        // Get latest orders
        $data['latest_orders'] = $this->gm->getWithJoin('orders', [
            [
                'table' => 'users',
                'condition' => 'users.id = orders.user_id',
                'type' => 'left'
            ]
        ], [], 'orders.*, users.name as user_name');

        // Get latest users
        $data['latest_users'] = $this->gm->getAll('users', [], '*', 'id DESC', 5);

        // Get order statistics for chart
        $data['order_stats'] = $this->getOrderStatistics();

        // Get vendor statistics for chart
        $data['vendor_stats'] = $this->getVendorStatistics();

        $this->loadView('dashboard/index', $data);
    }

    /**
     * Get order statistics for the last 7 days
     */
    private function getOrderStatistics()
    {
        $stats = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $count = $this->gm->countRows('orders', ['DATE(created_on)' => $date]);

            $stats[] = [
                'date' => date('M d', strtotime($date)),
                'count' => $count
            ];
        }

        return $stats;
    }

    /**
     * Get vendor statistics for chart
     */
    private function getVendorStatistics()
    {
        $vendors = $this->db
            ->select('id, name, store_name')
            ->where('role', 'vendor')
            ->get('users')
            ->result_array();

        $stats = [];
        foreach ($vendors as $v) {
            // Calculate revenue
            $revenue = $this->db
                ->select('SUM(oi.subtotal + oi.gst_amount) as total')
                ->from('order_items oi')
                ->join('orders o', 'o.id = oi.order_id')
                ->where('oi.vendor_id', $v['id'])
                ->where('o.status !=', 'cancelled')
                ->get()
                ->row()
                ->total ?: 0.00;

            // Calculate sold products
            $products_sold = $this->db
                ->select_sum('quantity')
                ->from('order_items oi')
                ->join('orders o', 'o.id = oi.order_id')
                ->where('oi.vendor_id', $v['id'])
                ->where('o.status !=', 'cancelled')
                ->get()
                ->row()
                ->quantity ?: 0;

            // Calculate orders count
            $orders_count = $this->db
                ->select('COUNT(DISTINCT oi.order_id) as count')
                ->from('order_items oi')
                ->join('orders o', 'o.id = oi.order_id')
                ->where('oi.vendor_id', $v['id'])
                ->where('o.status !=', 'cancelled')
                ->get()
                ->row()
                ->count ?: 0;

            $stats[] = [
                'vendor_id' => (int)$v['id'],
                'name' => $v['name'] ?: $v['store_name'] ?: 'Vendor #' . $v['id'],
                'store_name' => $v['store_name'] ?: $v['name'],
                'revenue' => (float)$revenue,
                'products_sold' => (int)$products_sold,
                'orders_count' => (int)$orders_count
            ];
        }

        return $stats;
    }
}
