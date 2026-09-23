<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('calculate_distance_km')) {
    /**
     * Calculate geodesic distance between two GPS coordinate pairs in kilometers using Haversine formula.
     * Pure math, zero external API or network calls, works instantly and offline.
     *
     * @param float|numeric $lat1 Latitude of point 1 (in degrees)
     * @param float|numeric $lon1 Longitude of point 1 (in degrees)
     * @param float|numeric $lat2 Latitude of point 2 (in degrees)
     * @param float|numeric $lon2 Longitude of point 2 (in degrees)
     * @return float Distance in kilometers rounded to 2 decimal places (or 0.0 if invalid)
     */
    function calculate_distance_km($lat1, $lon1, $lat2, $lon2)
    {
        if ($lat1 === null || $lon1 === null || $lat2 === null || $lon2 === null) {
            return 0.0;
        }

        if (!is_numeric($lat1) || !is_numeric($lon1) || !is_numeric($lat2) || !is_numeric($lon2)) {
            return 0.0;
        }

        $lat1 = (float)$lat1;
        $lon1 = (float)$lon1;
        $lat2 = (float)$lat2;
        $lon2 = (float)$lon2;

        // Radius of the Earth in kilometers
        $earth_radius = 6371.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2.0) * sin($dLat / 2.0) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2.0) * sin($dLon / 2.0);

        $c = 2.0 * atan2(sqrt($a), sqrt(1.0 - $a));

        $distance = $earth_radius * $c;

        return round($distance, 2);
    }
}

if (!function_exists('calculate_road_distance')) {
    /**
     * Attempt to calculate real road distance using a free public road-routing service (OSRM).
     * Has a strict timeout (around 2 seconds). If slow, fails, or times out, returns null.
     *
     * @param float|numeric $lat1 Latitude of point 1
     * @param float|numeric $lon1 Longitude of point 1
     * @param float|numeric $lat2 Latitude of point 2
     * @param float|numeric $lon2 Longitude of point 2
     * @param float $timeout Maximum seconds to wait (default 2.0s)
     * @return array|null ['distance_km' => float, 'duration_mins' => float] or null on failure/timeout
     */
    function calculate_road_distance($lat1, $lon1, $lat2, $lon2, $timeout = 2.0)
    {
        if ($lat1 === null || $lon1 === null || $lat2 === null || $lon2 === null) {
            return null;
        }

        if (!is_numeric($lat1) || !is_numeric($lon1) || !is_numeric($lat2) || !is_numeric($lon2)) {
            return null;
        }

        $lat1 = (float)$lat1;
        $lon1 = (float)$lon1;
        $lat2 = (float)$lat2;
        $lon2 = (float)$lon2;

        // Note: OSRM uses {longitude},{latitude} parameter order
        $url = "http://router.project-osrm.org/route/v1/driving/{$lon1},{$lat1};{$lon2},{$lat2}?overview=false";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, (int)($timeout * 1000));
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, (int)($timeout * 1000));
        curl_setopt($ch, CURLOPT_USERAGENT, 'RecommApp/1.0 (LocationRouting)');
        $res = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200 && $res) {
            $data = json_decode($res, true);
            if (isset($data['routes'][0]['distance']) && is_numeric($data['routes'][0]['distance'])) {
                $distance_meters = (float)$data['routes'][0]['distance'];
                $distance_km = round($distance_meters / 1000.0, 2);
                $duration_seconds = (float)($data['routes'][0]['duration'] ?? 0);
                $duration_mins = round($duration_seconds / 60.0, 1);
                return [
                    'distance_km'   => $distance_km,
                    'duration_mins' => $duration_mins
                ];
            }
        }

        return null;
    }
}

if (!function_exists('resolve_order_distance')) {
    /**
     * Resolve delivery distance using fast pure-math as default, with an optional live road-routing attempt.
     * Silently falls back to pure-math if road routing fails or times out.
     *
     * @param float|numeric $lat1 Vendor Lat
     * @param float|numeric $lon1 Vendor Lon
     * @param float|numeric $lat2 Customer Lat
     * @param float|numeric $lon2 Customer Lon
     * @param bool $attempt_road_routing Whether to attempt live road routing
     * @return array ['distance_km' => float, 'method' => string ('road_routing'|'pure_math'), 'duration_mins' => float|null]
     */
    function resolve_order_distance($lat1, $lon1, $lat2, $lon2, $attempt_road_routing = true)
    {
        // 1. Calculate pure math distance first (always available, instant, zero latency)
        $pure_math_km = calculate_distance_km($lat1, $lon1, $lat2, $lon2);

        // Hyper-local distance (< 1.0 km): use direct pure math distance to stay consistent with product page & prevent road median looping
        if ($pure_math_km < 1.0) {
            return [
                'distance_km'   => round($pure_math_km, 2),
                'method'        => 'pure_math',
                'duration_mins' => max(1, (int)round($pure_math_km * 5)),
                'pure_math_km'  => round($pure_math_km, 2)
            ];
        }

        // 2. Extra accuracy layer: attempt live road routing if requested
        if ($attempt_road_routing) {
            $road = calculate_road_distance($lat1, $lon1, $lat2, $lon2, 2.0);
            if ($road && isset($road['distance_km']) && $road['distance_km'] > 0) {
                return [
                    'distance_km'   => $road['distance_km'],
                    'method'        => 'road_routing',
                    'duration_mins' => $road['duration_mins'],
                    'pure_math_km'  => $pure_math_km
                ];
            }
        }

        // 3. Silent fallback to pure math distance
        return [
            'distance_km'   => $pure_math_km,
            'method'        => 'pure_math',
            'duration_mins' => null,
            'pure_math_km'  => $pure_math_km
        ];
    }
}

if (!function_exists('get_delivery_radius_km')) {
    /**
     * Get the admin-configured maximum delivery radius in kilometers.
     * Reads from the settings table with a fallback default of 10.0 km.
     *
     * @return float Delivery radius in kilometers
     */
    function get_delivery_radius_km()
    {
        if (function_exists('get_instance')) {
            $CI =& get_instance();
            if (isset($CI->db) && $CI->db->table_exists('settings')) {
                $row = $CI->db->where('key', 'delivery_radius_km')->get('settings')->row();
                if ($row && is_numeric($row->value) && (float)$row->value > 0) {
                    return (float)$row->value;
                }
            }
        }
        return 10.0;
    }
}

if (!function_exists('set_delivery_radius_km')) {
    /**
     * Update the admin-configured maximum delivery radius in kilometers.
     *
     * @param float|numeric $radius_km
     * @return bool
     */
    function set_delivery_radius_km($radius_km)
    {
        if (function_exists('get_instance')) {
            $CI =& get_instance();
            if (isset($CI->db) && is_numeric($radius_km) && (float)$radius_km > 0) {
                $val = (string)(float)$radius_km;
                $existing = $CI->db->where('key', 'delivery_radius_km')->get('settings')->row();
                if ($existing) {
                    return $CI->db->where('key', 'delivery_radius_km')->update('settings', [
                        'value' => $val,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                } else {
                    return $CI->db->insert('settings', [
                        'key' => 'delivery_radius_km',
                        'value' => $val,
                        'description' => 'Maximum allowed distance (km) for nearby customer product discovery and order delivery',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }
        return false;
    }
}

if (!function_exists('get_delivery_charge_per_km')) {
    /**
     * Get the admin-configured delivery charge per kilometer in Rupees.
     * Reads from the settings table with a fallback default of 10.00 Rs/km.
     *
     * @return float Delivery charge per kilometer
     */
    function get_delivery_charge_per_km()
    {
        if (function_exists('get_instance')) {
            $CI =& get_instance();
            if (isset($CI->db) && $CI->db->table_exists('settings')) {
                $row = $CI->db->where('key', 'delivery_charge_per_km')->get('settings')->row();
                if ($row && is_numeric($row->value) && (float)$row->value >= 0) {
                    return (float)$row->value;
                }
            }
        }
        return 10.00;
    }
}

if (!function_exists('set_delivery_charge_per_km')) {
    /**
     * Update the admin-configured delivery charge per kilometer in Rupees.
     *
     * @param float|numeric $rate_per_km
     * @return bool
     */
    function set_delivery_charge_per_km($rate_per_km)
    {
        if (function_exists('get_instance')) {
            $CI =& get_instance();
            if (isset($CI->db) && is_numeric($rate_per_km) && (float)$rate_per_km >= 0) {
                $val = number_format((float)$rate_per_km, 2, '.', '');
                $existing = $CI->db->where('key', 'delivery_charge_per_km')->get('settings')->row();
                if ($existing) {
                    return $CI->db->where('key', 'delivery_charge_per_km')->update('settings', [
                        'value' => $val,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                } else {
                    return $CI->db->insert('settings', [
                        'key' => 'delivery_charge_per_km',
                        'value' => $val,
                        'description' => 'Delivery shipping charge per kilometer (in Rupees)',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }
        return false;
    }
}

if (!function_exists('get_immediate_delivery_charge')) {
    /**
     * Get the admin-configured extra charge for immediate delivery in Rupees.
     * Reads from the settings table with a fallback default of 50.00 Rs.
     *
     * @return float Immediate delivery extra charge
     */
    function get_immediate_delivery_charge()
    {
        if (function_exists('get_instance')) {
            $CI =& get_instance();
            if (isset($CI->db) && $CI->db->table_exists('settings')) {
                $row = $CI->db->where('key', 'immediate_delivery_charge')->get('settings')->row();
                if ($row && is_numeric($row->value) && (float)$row->value >= 0) {
                    return (float)$row->value;
                }
            }
        }
        return 50.00;
    }
}

if (!function_exists('set_immediate_delivery_charge')) {
    /**
     * Update the admin-configured extra charge for immediate delivery in Rupees.
     *
     * @param float|numeric $charge
     * @return bool
     */
    function set_immediate_delivery_charge($charge)
    {
        if (function_exists('get_instance')) {
            $CI =& get_instance();
            if (isset($CI->db) && is_numeric($charge) && (float)$charge >= 0) {
                $val = number_format((float)$charge, 2, '.', '');
                $existing = $CI->db->where('key', 'immediate_delivery_charge')->get('settings')->row();
                if ($existing) {
                    return $CI->db->where('key', 'immediate_delivery_charge')->update('settings', [
                        'value' => $val,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                } else {
                    return $CI->db->insert('settings', [
                        'key' => 'immediate_delivery_charge',
                        'value' => $val,
                        'description' => 'Extra surcharge for Immediate Delivery time slot (in Rupees)',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }
        return false;
    }
}

if (!function_exists('get_low_stock_threshold')) {
    /**
     * Get the admin-configured low stock warning threshold.
     * Reads from the settings table with a fallback default of 5.
     *
     * @return int Low stock threshold quantity
     */
    function get_low_stock_threshold()
    {
        if (function_exists('get_instance')) {
            $CI =& get_instance();
            if (isset($CI->db) && $CI->db->table_exists('settings')) {
                $row = $CI->db->where('key', 'low_stock_threshold')->get('settings')->row();
                if ($row && is_numeric($row->value) && (int)$row->value > 0) {
                    return (int)$row->value;
                }
            }
        }
        return 5;
    }
}

if (!function_exists('set_low_stock_threshold')) {
    /**
     * Update the admin-configured low stock warning threshold.
     *
     * @param int|numeric $threshold
     * @return bool
     */
    function set_low_stock_threshold($threshold)
    {
        if (function_exists('get_instance')) {
            $CI =& get_instance();
            if (isset($CI->db) && is_numeric($threshold) && (int)$threshold > 0) {
                $val = (string)(int)$threshold;
                $existing = $CI->db->where('key', 'low_stock_threshold')->get('settings')->row();
                if ($existing) {
                    return $CI->db->where('key', 'low_stock_threshold')->update('settings', [
                        'value' => $val,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                } else {
                    return $CI->db->insert('settings', [
                        'key' => 'low_stock_threshold',
                        'value' => $val,
                        'description' => 'Low stock quantity threshold for warning badge',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }
        return false;
    }
}

if (!function_exists('classify_stock_state')) {
    /**
     * Classifies a vendor product's stock into one of 4 states:
     * 1. 'temporarily_unavailable': Vendor toggled paused
     * 2. 'out_of_stock': stock <= 0
     * 3. 'low_stock': stock > 0 and stock < low_stock_threshold
     * 4. 'available': stock >= low_stock_threshold
     *
     * @param int|numeric $stock Current stock quantity
     * @param int|bool $temporarily_unavailable Vendor paused toggle
     * @param int|null $threshold Low stock threshold (defaults to settings value)
     * @return array Classified stock state dictionary
     */
    function classify_stock_state($stock, $temporarily_unavailable = 0, $threshold = null)
    {
        if ($threshold === null) {
            $threshold = get_low_stock_threshold();
        }
        $stock = (int)$stock;
        $is_temp_unavail = ((int)$temporarily_unavailable === 1 || $temporarily_unavailable === true || $temporarily_unavailable === '1');

        if ($is_temp_unavail) {
            return [
                'stock_status'        => 'temporarily_unavailable',
                'stock_status_label'  => 'Temporarily Unavailable',
                'is_purchasable'      => false,
                'can_add_to_cart'     => false,
                'low_stock_threshold' => (int)$threshold,
                'temporarily_unavailable' => 1
            ];
        }

        if ($stock <= 0) {
            return [
                'stock_status'        => 'out_of_stock',
                'stock_status_label'  => 'Unavailable',
                'is_purchasable'      => false,
                'can_add_to_cart'     => false,
                'low_stock_threshold' => (int)$threshold,
                'temporarily_unavailable' => 0
            ];
        }

        if ($stock < (int)$threshold) {
            return [
                'stock_status'        => 'low_stock',
                'stock_status_label'  => "Only {$stock} left",
                'is_purchasable'      => true,
                'can_add_to_cart'     => true,
                'low_stock_threshold' => (int)$threshold,
                'temporarily_unavailable' => 0
            ];
        }

        return [
            'stock_status'        => 'available',
            'stock_status_label'  => 'In Stock',
            'is_purchasable'      => true,
            'can_add_to_cart'     => true,
            'low_stock_threshold' => (int)$threshold,
            'temporarily_unavailable' => 0
        ];
    }
}


if (!function_exists('calculate_delivery_charge_amount')) {
    /**
     * Calculate delivery charge based on distance in KM, admin per-KM rate, and delivery time slot / type.
     * Distance rounding rules:
     * - Under 1 KM: counted as 1 KM (1 * rate_per_km, e.g. 10 Rs at 10/km).
     * - 1 KM and above: rounded mathematically (e.g. 2.4 km -> 2 km, 2.5 km -> 3 km, 2.6 km -> 3 km).
     * Surcharge:
     * - If immediate delivery is selected, adds admin-configured immediate_delivery_charge.
     *
     * @param float|numeric $distance_km Distance in kilometers
     * @param string $delivery_type 'normal' or 'urgent' / 'immediately' (legacy support)
     * @param string|null $chosen_time_option Delivery time slot ('immediately', 'later', 'lunch', 'dinner', 'custom')
     * @return float Calculated delivery charge rounded to 2 decimal places
     */
    function calculate_delivery_charge_amount($distance_km, $delivery_type = 'normal', $chosen_time_option = null)
    {
        $rate = get_delivery_charge_per_km();
        $dist = max(0.0, is_numeric($distance_km) ? (float)$distance_km : 0.0);
        $billed_km = ($dist < 1.0) ? 1 : max(1, (int)round($dist));
        $base = round($billed_km * $rate, 2);

        $is_immediate = ($chosen_time_option === 'immediately' || $delivery_type === 'urgent' || $delivery_type === 'immediately');
        $extra = $is_immediate ? get_immediate_delivery_charge() : 0.00;

        return round($base + $extra, 2);
    }
}

if (!function_exists('get_delivery_charge_breakdown')) {
    /**
     * Get detailed breakdown of delivery charge calculation for an order.
     *
     * @param float|numeric $distance_km Distance in kilometers
     * @param string|null $chosen_time_option Delivery time slot ('immediately', 'later', etc.)
     * @param string $delivery_type Legacy delivery type
     * @return array Breakdown details
     */
    function get_delivery_charge_breakdown($distance_km, $chosen_time_option = null, $delivery_type = 'normal')
    {
        $rate = get_delivery_charge_per_km();
        $dist = max(0.0, is_numeric($distance_km) ? (float)$distance_km : 0.0);
        $billed_km = ($dist < 1.0) ? 1 : max(1, (int)round($dist));
        $base = round($billed_km * $rate, 2);

        $is_immediate = ($chosen_time_option === 'immediately' || $delivery_type === 'urgent' || $delivery_type === 'immediately');
        $immediate_charge = get_immediate_delivery_charge();
        $extra = $is_immediate ? $immediate_charge : 0.00;

        return [
            'actual_distance_km'        => round($dist, 2),
            'billed_distance_km'        => (int)$billed_km,
            'rate_per_km'               => round($rate, 2),
            'base_charge'               => round($base, 2),
            'immediate_delivery_charge' => round($immediate_charge, 2),
            'is_immediate'              => $is_immediate,
            'extra_charge'              => round($extra, 2),
            'total_delivery_charge'     => round($base + $extra, 2),
        ];
    }
}

if (!function_exists('geocode_address_if_needed')) {
    /**
     * Ensure coordinates exist for a user address. If missing or 0, attempts geocoding and updates the DB.
     *
     * @param object|array $address Address record (passed by reference)
     * @return array|null ['latitude' => float, 'longitude' => float]
     */
    function geocode_address_if_needed(&$address)
    {
        if (empty($address)) return null;

        $is_array = is_array($address);
        $lat = $is_array ? ($address['latitude'] ?? null) : ($address->latitude ?? null);
        $lon = $is_array ? ($address['longitude'] ?? null) : ($address->longitude ?? null);
        $address_id = (int)($is_array ? ($address['id'] ?? 0) : ($address->id ?? 0));

        if ($lat !== null && $lon !== null && is_numeric($lat) && is_numeric($lon) && (float)$lat != 0 && (float)$lon != 0) {
            return [
                'latitude'  => (float)$lat,
                'longitude' => (float)$lon
            ];
        }

        $line1   = trim((string)($is_array ? ($address['address_line1'] ?? '') : ($address->address_line1 ?? '')));
        $city    = trim((string)($is_array ? ($address['city'] ?? '') : ($address->city ?? '')));
        $state   = trim((string)($is_array ? ($address['state'] ?? '') : ($address->state ?? '')));
        $pincode = trim((string)($is_array ? ($address['pincode'] ?? '') : ($address->pincode ?? '')));

        $queries = [];
        if (!empty($pincode)) {
            $queries[] = "{$pincode}, India";
        }
        if (!empty($city)) {
            $queries[] = "{$city}, {$state}, India";
        }
        if (!empty($line1) && !empty($city)) {
            $queries[] = "{$line1}, {$city}, {$pincode}, India";
        }

        // Try most specific query first
        $queries = array_reverse($queries);

        foreach ($queries as $q) {
            $url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($q) . "&limit=1";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 2500);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 2000);
            curl_setopt($ch, CURLOPT_USERAGENT, 'RecommApp/1.0 (DeliveryGeoLookup)');
            $res = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code === 200 && $res) {
                $data = json_decode($res, true);
                if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
                    $found_lat = round((float)$data[0]['lat'], 8);
                    $found_lon = round((float)$data[0]['lon'], 8);

                    if ($is_array) {
                        $address['latitude'] = $found_lat;
                        $address['longitude'] = $found_lon;
                    } else {
                        $address->latitude = $found_lat;
                        $address->longitude = $found_lon;
                    }

                    if ($address_id > 0 && function_exists('get_instance')) {
                        $CI =& get_instance();
                        if (isset($CI->db)) {
                            $CI->db->where('id', $address_id)->update('user_addresses', [
                                'latitude'   => $found_lat,
                                'longitude'  => $found_lon,
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                        }
                    }

                    return [
                        'latitude'  => $found_lat,
                        'longitude' => $found_lon
                    ];
                }
            }
        }

        return null;
    }
}

if (!function_exists('geocode_vendor_if_needed')) {
    /**
     * Ensure coordinates exist for a vendor. If missing or 0, attempts geocoding and updates the DB.
     *
     * @param object|array $vendor Vendor record (passed by reference)
     * @return array|null ['latitude' => float, 'longitude' => float]
     */
    function geocode_vendor_if_needed(&$vendor)
    {
        if (empty($vendor)) return null;

        $is_array = is_array($vendor);
        $lat = $is_array ? ($vendor['latitude'] ?? null) : ($vendor->latitude ?? null);
        $lon = $is_array ? ($vendor['longitude'] ?? null) : ($vendor->longitude ?? null);
        $vendor_id = (int)($is_array ? ($vendor['id'] ?? 0) : ($vendor->id ?? 0));

        if ($lat !== null && $lon !== null && is_numeric($lat) && is_numeric($lon) && (float)$lat != 0 && (float)$lon != 0) {
            return [
                'latitude'  => (float)$lat,
                'longitude' => (float)$lon
            ];
        }

        $addr    = trim((string)($is_array ? ($vendor['address'] ?? '') : ($vendor->address ?? '')));
        $pincode = trim((string)($is_array ? ($vendor['pincode'] ?? '') : ($vendor->pincode ?? '')));

        $queries = [];
        if (!empty($pincode)) {
            $queries[] = "{$pincode}, India";
        }
        if (!empty($addr)) {
            $queries[] = "{$addr}, India";
        }

        $queries = array_reverse($queries);

        foreach ($queries as $q) {
            $url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($q) . "&limit=1";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 2500);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 2000);
            curl_setopt($ch, CURLOPT_USERAGENT, 'RecommApp/1.0 (VendorGeoLookup)');
            $res = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code === 200 && $res) {
                $data = json_decode($res, true);
                if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
                    $found_lat = round((float)$data[0]['lat'], 8);
                    $found_lon = round((float)$data[0]['lon'], 8);

                    if ($is_array) {
                        $vendor['latitude'] = $found_lat;
                        $vendor['longitude'] = $found_lon;
                    } else {
                        $vendor->latitude = $found_lat;
                        $vendor->longitude = $found_lon;
                    }

                    if ($vendor_id > 0 && function_exists('get_instance')) {
                        $CI =& get_instance();
                        if (isset($CI->db)) {
                            $CI->db->where('id', $vendor_id)->update('users', [
                                'latitude'   => $found_lat,
                                'longitude'  => $found_lon
                            ]);
                        }
                    }

                    return [
                        'latitude'  => $found_lat,
                        'longitude' => $found_lon
                    ];
                }
            }
        }

        return null;
    }
}

if (!function_exists('format_slot_window_display')) {
    /**
     * Format start and end timestamps/datetimes into a human friendly string (e.g. 'Today, 12:00 PM – 02:00 PM').
     */
    function format_slot_window_display($start_or_opt, $end_or_start = null, $base_or_end = null)
    {
        $known_options = ['immediately', 'later', 'lunch', 'dinner', 'custom'];
        if (is_string($start_or_opt) && in_array(strtolower(trim($start_or_opt)), $known_options, true)) {
            $start = $end_or_start;
            $end = $base_or_end;
            $base = null;
        } else {
            $start = $start_or_opt;
            $end = $end_or_start;
            $base = $base_or_end;
        }

        if (empty($start) || empty($end)) return '';
        $start_ts = is_numeric($start) ? (int)$start : strtotime($start);
        $end_ts   = is_numeric($end)   ? (int)$end   : strtotime($end);
        $base_ts  = $base ? (is_numeric($base) ? (int)$base : strtotime($base)) : time();
        if (!$start_ts || !$end_ts) return '';

        $start_day = date('Y-m-d', $start_ts);
        $base_day = date('Y-m-d', $base_ts);
        $tomorrow_day = date('Y-m-d', strtotime('+1 day', $base_ts));

        if ($start_day === $base_day) {
            $day_prefix = 'Today';
        } elseif ($start_day === $tomorrow_day) {
            $day_prefix = 'Tomorrow';
        } else {
            $day_prefix = date('d M', $start_ts);
        }

        $time_range = date('h:i A', $start_ts) . ' – ' . date('h:i A', $end_ts);
        return "{$day_prefix}, {$time_range}";
    }
}

if (!function_exists('calculate_order_delivery_window')) {
    /**
     * Calculate realistic estimated delivery window for a chosen delivery time option.
     *
     * @param int|string|null $order_time Order placement timestamp or datetime string
     * @param object|array $vendor Vendor details including prep_time, operating hours, lunch/dinner windows
     * @param float $distance_km Distance in kilometers
     * @param string $chosen_option immediately, later, lunch, dinner, custom
     * @param string|null $custom_datetime For custom option
     * @return array
     */
    function calculate_order_delivery_window($order_time, $vendor, $distance_km, $chosen_option = 'immediately', $custom_datetime = null)
    {
        if (is_numeric($order_time) && (int)$order_time > 0) {
            $order_ts = (int)$order_time;
        } elseif (!empty($order_time) && is_string($order_time)) {
            $parsed = strtotime($order_time);
            $order_ts = ($parsed !== false) ? $parsed : time();
        } else {
            $order_ts = time();
        }

        $v = is_array($vendor) ? (object)$vendor : $vendor;
        $prep_min = isset($v->prep_time_minutes) && is_numeric($v->prep_time_minutes) && (int)$v->prep_time_minutes > 0
            ? (int)$v->prep_time_minutes
            : 30;

        $lunch_start = !empty($v->lunch_window_start) ? substr($v->lunch_window_start, 0, 5) : '12:00';
        $lunch_end   = !empty($v->lunch_window_end)   ? substr($v->lunch_window_end, 0, 5)   : '14:00';
        $dinner_start= !empty($v->dinner_window_start)? substr($v->dinner_window_start, 0, 5): '19:00';
        $dinner_end  = !empty($v->dinner_window_end)  ? substr($v->dinner_window_end, 0, 5)  : '21:00';
        $open_time   = !empty($v->opening_time)       ? substr($v->opening_time, 0, 5)       : '08:00';
        $close_time  = !empty($v->closing_time)       ? substr($v->closing_time, 0, 5)       : '22:00';

        // City travel speed 25 km/h -> travel time with 10 min minimum
        $dist = max(0.0, (float)$distance_km);
        $travel_min = max(10, (int)ceil(($dist / 25.0) * 60.0));
        $buffer_min = 10;
        $min_lead_min = $prep_min + $travel_min + $buffer_min;

        $option = strtolower(trim($chosen_option ?: 'immediately'));
        $shifted = false;
        $shift_reason = null;

        $order_date = date('Y-m-d', $order_ts);
        $close_today_ts = strtotime("{$order_date} {$close_time}:00");
        $open_today_ts  = strtotime("{$order_date} {$open_time}:00");

        $tomorrow_date = date('Y-m-d', strtotime('+1 day', $order_ts));
        $tomorrow_open_ts = strtotime("{$tomorrow_date} {$open_time}:00");

        switch ($option) {
            case 'later':
                // After 3-4 hours
                $start_ts = max($order_ts + (3 * 3600), $order_ts + ($min_lead_min * 60));
                $end_ts = $start_ts + 3600; // 1 hour window

                if ($start_ts > $close_today_ts) {
                    $start_ts = $tomorrow_open_ts + (3 * 3600);
                    $end_ts = $start_ts + 3600;
                    $shifted = true;
                    $shift_reason = "Store closes before later window. Shifted to tomorrow.";
                }
                $label = 'Later (after 3–4 hrs)';
                break;

            case 'lunch':
                $today_lunch_start_ts = strtotime("{$order_date} {$lunch_start}:00");
                $today_lunch_end_ts   = strtotime("{$order_date} {$lunch_end}:00");
                $earliest_delivery_ts = $order_ts + ($min_lead_min * 60);

                // Can lunch slot be met today?
                // Earliest delivery must be before lunch window ends, and order time must not be past lunch
                if ($earliest_delivery_ts <= $today_lunch_end_ts && $order_ts < $today_lunch_end_ts) {
                    $start_ts = max($today_lunch_start_ts, $earliest_delivery_ts);
                    $end_ts   = $today_lunch_end_ts;
                    if ($end_ts - $start_ts < 20 * 60) {
                        $end_ts = $start_ts + 20 * 60;
                    }
                    $shifted = false;
                } else {
                    // Cannot be met today (e.g. placed at 11:50 PM or late afternoon) -> Automatically shift to tomorrow's lunch window
                    $start_ts = strtotime("{$tomorrow_date} {$lunch_start}:00");
                    $end_ts   = strtotime("{$tomorrow_date} {$lunch_end}:00");
                    $shifted = true;
                    $shift_reason = "Today's lunch window cannot be met. Shifted to tomorrow's lunch window.";
                }
                $label = 'Lunch';
                break;

            case 'dinner':
                $today_dinner_start_ts = strtotime("{$order_date} {$dinner_start}:00");
                $today_dinner_end_ts   = strtotime("{$order_date} {$dinner_end}:00");
                $earliest_delivery_ts  = $order_ts + ($min_lead_min * 60);

                if ($earliest_delivery_ts <= $today_dinner_end_ts && $order_ts < $today_dinner_end_ts) {
                    $start_ts = max($today_dinner_start_ts, $earliest_delivery_ts);
                    $end_ts   = $today_dinner_end_ts;
                    if ($end_ts - $start_ts < 20 * 60) {
                        $end_ts = $start_ts + 20 * 60;
                    }
                    $shifted = false;
                } else {
                    // Cannot be met today -> Shift to tomorrow's dinner
                    $start_ts = strtotime("{$tomorrow_date} {$dinner_start}:00");
                    $end_ts   = strtotime("{$tomorrow_date} {$dinner_end}:00");
                    $shifted = true;
                    $shift_reason = "Today's dinner window cannot be met. Shifted to tomorrow's dinner window.";
                }
                $label = 'Dinner';
                break;

            case 'custom':
                $custom_ts = !empty($custom_datetime) ? strtotime($custom_datetime) : null;
                $earliest_ts = $order_ts + ($min_lead_min * 60);

                if (!$custom_ts || $custom_ts < $earliest_ts) {
                    $start_ts = $earliest_ts;
                    $end_ts   = $start_ts + 3600;
                    $shifted = true;
                    $shift_reason = "Selected custom time was earlier than minimum prep & transit time. Adjusted to earliest window.";
                } else {
                    $start_ts = $custom_ts;
                    $end_ts   = $start_ts + 3600;
                    $shifted = false;
                }
                $label = 'Custom';
                break;

            case 'immediately':
            default:
                $option = 'immediately';
                $start_ts = $order_ts + ($prep_min + $travel_min) * 60;
                $end_ts = $start_ts + 20 * 60; // 20-min window

                // If store is currently closed, shift to tomorrow morning
                if ($start_ts > $close_today_ts || $order_ts < $open_today_ts) {
                    $start_ts = $tomorrow_open_ts + ($prep_min + $travel_min) * 60;
                    $end_ts = $start_ts + 20 * 60;
                    $shifted = true;
                    $shift_reason = "Store is currently closed. Scheduled for tomorrow morning as soon as store opens.";
                }
                $label = 'Immediately';
                break;
        }

        $w_start = date('Y-m-d H:i:s', $start_ts);
        $w_end   = date('Y-m-d H:i:s', $end_ts);
        $w_fmt   = format_slot_window_display($start_ts, $end_ts, $order_ts);

        return [
            'option'               => $option,
            'label'                => $label,
            'window_start'         => $w_start,
            'window_end'           => $w_end,
            'window_start_dt'      => $w_start,
            'window_end_dt'        => $w_end,
            'formatted_window'     => $w_fmt,
            'window_display'       => $w_fmt,
            'shifted'              => $shifted,
            'shift_reason'         => $shift_reason,
            'prep_time_minutes'    => $prep_min,
            'travel_time_minutes'  => $travel_min
        ];
    }
}

if (!function_exists('get_vendor_available_delivery_slots')) {
    /**
     * Get all active delivery time slot options configured by the vendor, with calculated windows.
     *
     * @param object|array $vendor
     * @param float $distance_km
     * @param int|string|null $order_time
     * @return array
     */
    function get_vendor_available_delivery_slots($vendor, $distance_km, $order_time = null)
    {
        $v = is_array($vendor) ? (object)$vendor : $vendor;
        $order_ts = $order_time ? (is_numeric($order_time) ? (int)$order_time : strtotime($order_time)) : time();

        $slot_configs = [
            [
                'key' => 'immediately',
                'title' => 'Immediately',
                'subtitle' => 'Pack & deliver ASAP',
                'enabled' => isset($v->slot_immediately_enabled) ? (int)$v->slot_immediately_enabled === 1 : true
            ],
            [
                'key' => 'later',
                'title' => 'Later (3–4 hrs)',
                'subtitle' => 'A few hours out',
                'enabled' => isset($v->slot_later_enabled) ? (int)$v->slot_later_enabled === 1 : true
            ],
            [
                'key' => 'lunch',
                'title' => 'Lunch',
                'subtitle' => (!empty($v->lunch_window_start) ? date('h:i A', strtotime($v->lunch_window_start)) : '12:00 PM') . ' – ' . (!empty($v->lunch_window_end) ? date('h:i A', strtotime($v->lunch_window_end)) : '02:00 PM'),
                'enabled' => isset($v->slot_lunch_enabled) ? (int)$v->slot_lunch_enabled === 1 : true
            ],
            [
                'key' => 'dinner',
                'title' => 'Dinner',
                'subtitle' => (!empty($v->dinner_window_start) ? date('h:i A', strtotime($v->dinner_window_start)) : '07:00 PM') . ' – ' . (!empty($v->dinner_window_end) ? date('h:i A', strtotime($v->dinner_window_end)) : '09:00 PM'),
                'enabled' => isset($v->slot_dinner_enabled) ? (int)$v->slot_dinner_enabled === 1 : true
            ],
            [
                'key' => 'custom',
                'title' => 'Custom Time',
                'subtitle' => 'Choose your preferred date/time',
                'enabled' => isset($v->slot_custom_enabled) ? (int)$v->slot_custom_enabled === 1 : true
            ]
        ];

        $available_slots = [];
        foreach ($slot_configs as $cfg) {
            if ($cfg['enabled']) {
                $calc = calculate_order_delivery_window($order_ts, $v, $distance_km, $cfg['key']);
                $available_slots[] = [
                    'id'                       => $cfg['key'],
                    'option'                   => $cfg['key'],
                    'title'                    => $cfg['title'],
                    'subtitle'                 => $cfg['subtitle'],
                    'window_start'             => $calc['window_start'],
                    'window_end'               => $calc['window_end'],
                    'formatted_window'         => $calc['formatted_window'],
                    'estimated_window_display' => $calc['formatted_window'],
                    'shifted'                  => $calc['shifted'],
                    'shift_reason'             => $calc['shift_reason'],
                ];
            }
        }

        // Fallback: If vendor disabled all slots, at least provide immediately
        if (empty($available_slots)) {
            $calc = calculate_order_delivery_window($order_ts, $v, $distance_km, 'immediately');
            $available_slots[] = [
                'id'                       => 'immediately',
                'option'                   => 'immediately',
                'title'                    => 'Immediately',
                'subtitle'                 => 'Pack & deliver ASAP',
                'window_start'             => $calc['window_start'],
                'window_end'               => $calc['window_end'],
                'formatted_window'         => $calc['formatted_window'],
                'estimated_window_display' => $calc['formatted_window'],
                'shifted'                  => $calc['shifted'],
                'shift_reason'             => $calc['shift_reason'],
            ];
        }

        return $available_slots;
    }
}
