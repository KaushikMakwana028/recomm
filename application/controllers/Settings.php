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
            $this->form_validation->set_rules('immediate_delivery_charge', 'Immediate Delivery Extra Charge (₹)', 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[10000]');
            $this->form_validation->set_rules('low_stock_threshold', 'Low Stock Threshold (units)', 'required|is_natural_no_zero|less_than_equal_to[1000]');

            if ($this->form_validation->run()) {
                $radius = (float)$this->post('delivery_radius_km');
                $charge_per_km = (float)$this->post('delivery_charge_per_km');
                $immediate_charge = (float)$this->post('immediate_delivery_charge');
                $low_stock_th = (int)$this->post('low_stock_threshold');

                $r_success = set_delivery_radius_km($radius);
                $c_success = set_delivery_charge_per_km($charge_per_km);
                $i_success = set_immediate_delivery_charge($immediate_charge);
                $l_success = set_low_stock_threshold($low_stock_th);

                if ($r_success && $c_success && $i_success && $l_success) {
                    $this->setMessage('success', "Settings updated successfully: Radius = {$radius} km, Rate = ₹" . number_format($charge_per_km, 2) . "/km, Immediate Extra Charge = ₹" . number_format($immediate_charge, 2) . ", Low Stock Threshold = {$low_stock_th} units.");
                    redirect('settings/delivery');
                } else {
                    $this->setMessage('danger', 'Failed to update one or more settings.');
                }
            }
        }

        $data['delivery_radius_km'] = get_delivery_radius_km();
        $data['delivery_charge_per_km'] = get_delivery_charge_per_km();
        $data['immediate_delivery_charge'] = get_immediate_delivery_charge();
        $data['low_stock_threshold'] = get_low_stock_threshold();
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