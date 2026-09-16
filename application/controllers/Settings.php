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
            $this->form_validation->set_rules('delivery_charge_per_km', 'Delivery Charge per KM (₹)', 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[500]');

            if ($this->form_validation->run()) {
                $radius = (float)$this->post('delivery_radius_km');
                $charge_per_km = (float)$this->post('delivery_charge_per_km');

                $r_success = set_delivery_radius_km($radius);
                $c_success = set_delivery_charge_per_km($charge_per_km);

                if ($r_success && $c_success) {
                    $this->setMessage('success', "Delivery settings updated successfully: Radius = {$radius} km, Rate = ₹" . number_format($charge_per_km, 2) . "/km.");
                    redirect('settings/delivery');
                } else {
                    $this->setMessage('danger', 'Failed to update one or more delivery settings.');
                }
            }
        }

        $data['delivery_radius_km'] = get_delivery_radius_km();
        $data['delivery_charge_per_km'] = get_delivery_charge_per_km();
        $this->loadView('settings/index', $data);
    }

    /**
     * Alias for settings/delivery route
     */
    public function delivery()
    {
        $this->index();
    }
}