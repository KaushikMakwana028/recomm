<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('esc')) {
    function esc($data = '', $context = 'html')
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = esc($value, $context);
            }
            return $data;
        }
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
}