<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends MY_Controller 
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('distance');
    }

    /**
     * Delivery radius settings view and update
     */
    public function index()
    {
        $this->setPageTitle('Delivery & Discovery Settings');

        if ($this->input->method() == 'post') {
            $this->form_validation->set_rules('delivery_radius_km', 'Delivery Radius (km)', 'required|numeric|greater_than[0]|less_than_equal_to[100]');

            if ($this->form_validation->run()) {
                $radius = (float)$this->post('delivery_radius_km');
                if (set_delivery_radius_km($radius)) {
                    $this->setMessage('success', "Delivery radius updated to {$radius} km successfully.");
                    redirect('settings/delivery');
                } else {
                    $this->setMessage('danger', 'Failed to update delivery radius setting.');
                }
            }
        }

        $data['delivery_radius_km'] = get_delivery_radius_km();
        $this->loadView('settings/index', $data);
    }
}