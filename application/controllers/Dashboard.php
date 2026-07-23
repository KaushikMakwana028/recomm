<?php
defined('BASEPATH') OR exit('No direct script access allowed');

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
}