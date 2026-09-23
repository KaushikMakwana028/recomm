<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Support_requests extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->setPageTitle('Customer Support Requests');

        $status_filter = trim($this->input->get('status') ?? '');
        $category_filter = trim($this->input->get('category') ?? '');
        $search = trim($this->input->get('search') ?? '');

        // Summary counts
        $total_count = $this->db->count_all('support_requests');
        $open_count = $this->db->where('status', 'Open')->count_all_results('support_requests');
        $in_progress_count = $this->db->where('status', 'In Progress')->count_all_results('support_requests');
        $resolved_count = $this->db->where('status', 'Resolved')->count_all_results('support_requests');

        // Query support requests with joins
        $this->db->select('sr.*, 
            o.order_number, o.total_amount as order_total, o.status as order_status, o.created_at as order_created_at,
            c.name as customer_name, c.email as customer_email, c.mobile as customer_mobile')
            ->from('support_requests sr')
            ->join('orders o', 'o.id = sr.order_id', 'left')
            ->join('users c', 'c.id = sr.customer_id', 'left');

        if (!empty($status_filter) && in_array($status_filter, ['Open', 'In Progress', 'Resolved'], true)) {
            $this->db->where('sr.status', $status_filter);
        }

        if (!empty($category_filter)) {
            $this->db->where('sr.category', $category_filter);
        }

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('sr.ticket_number', $search);
            $this->db->or_like('o.order_number', $search);
            $this->db->or_like('c.name', $search);
            $this->db->or_like('c.email', $search);
            $this->db->or_like('sr.description', $search);
            $this->db->group_end();
        }

        $this->db->order_by('CASE 
            WHEN sr.status = "Open" THEN 1 
            WHEN sr.status = "In Progress" THEN 2 
            ELSE 3 
        END', 'ASC', FALSE);
        $this->db->order_by('sr.id', 'DESC');

        $requests = $this->db->get()->result();

        $data = [
            'requests'          => $requests,
            'status_filter'     => $status_filter,
            'category_filter'   => $category_filter,
            'search'            => $search,
            'total_count'       => $total_count,
            'open_count'        => $open_count,
            'in_progress_count' => $in_progress_count,
            'resolved_count'    => $resolved_count,
        ];

        $this->loadView('support_requests/index', $data);
    }

    public function view($id)
    {
        $id = (int)$id;
        $request = $this->db->select('sr.*, 
            o.order_number, o.total_amount as order_total, o.status as order_status, o.payment_method, o.created_at as order_created_at,
            c.name as customer_name, c.email as customer_email, c.mobile as customer_mobile')
            ->from('support_requests sr')
            ->join('orders o', 'o.id = sr.order_id', 'left')
            ->join('users c', 'c.id = sr.customer_id', 'left')
            ->where('sr.id', $id)
            ->get()
            ->row();

        if (!$request) {
            $this->setFlashMessage('error', 'Support request not found.');
            redirect('support-requests');
            return;
        }

        $this->setPageTitle('Support Ticket #' . $request->ticket_number);

        // Fetch items for the linked order
        $order_items = $this->db->select('oi.*, u.store_name, u.name as vendor_name')
            ->from('order_items oi')
            ->join('users u', 'u.id = oi.vendor_id', 'left')
            ->where('oi.order_id', $request->order_id)
            ->get()
            ->result();

        // Fetch status history for this order
        $history = $this->db->where('order_id', $request->order_id)
            ->order_by('created_at', 'DESC')
            ->get('order_status_history')
            ->result();

        $data = [
            'ticket'      => $request,
            'order_items' => $order_items,
            'history'     => $history,
        ];

        $this->loadView('support_requests/view', $data);
    }

    public function update_status()
    {
        if ($this->input->method() !== 'post') {
            redirect('support-requests');
            return;
        }

        $id = (int)$this->input->post('id');
        $status = trim($this->input->post('status') ?? '');
        $admin_response = trim($this->input->post('admin_response') ?? '');

        $ticket = $this->db->get_where('support_requests', ['id' => $id])->row();
        if (!$ticket) {
            $this->setFlashMessage('error', 'Support request not found.');
            redirect('support-requests');
            return;
        }

        $valid_statuses = ['Open', 'In Progress', 'Resolved'];
        if (!in_array($status, $valid_statuses, true)) {
            $this->setFlashMessage('error', 'Invalid status selected.');
            redirect('support-requests/view/' . $id);
            return;
        }

        $update_data = [
            'status'         => $status,
            'admin_response' => $admin_response !== '' ? $admin_response : $ticket->admin_response,
            'updated_at'     => date('Y-m-d H:i:s'),
        ];

        if ($status === 'Resolved' && empty($ticket->resolved_at)) {
            $update_data['resolved_at'] = date('Y-m-d H:i:s');
        } elseif ($status !== 'Resolved') {
            $update_data['resolved_at'] = null;
        }

        $this->db->where('id', $id)->update('support_requests', $update_data);

        // Record history log on the order
        if ($this->db->table_exists('order_status_history')) {
            $log_msg = "Support ticket #{$ticket->ticket_number} marked as {$status}";
            if (!empty($admin_response)) {
                $log_msg .= ". Response: " . substr($admin_response, 0, 80);
            }
            $this->db->insert('order_status_history', [
                'order_id'   => $ticket->order_id,
                'status'     => 'support_' . strtolower(str_replace(' ', '_', $status)),
                'remarks'    => $log_msg,
                'changed_by' => 'admin',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->setFlashMessage('success', "Support ticket #{$ticket->ticket_number} updated to '{$status}' successfully.");
        redirect('support-requests/view/' . $id);
    }
}
