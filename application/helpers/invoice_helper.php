<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('amount_to_words_indian')) {
    /**
     * Convert numeric amount into Indian Rupee words
     *
     * @param float $amount
     * @return string
     */
    function amount_to_words_indian($amount)
    {
        $amount = round(floatval($amount), 2);
        $parts = explode('.', number_format($amount, 2, '.', ''));
        $rupees = intval($parts[0]);
        $paise = isset($parts[1]) ? intval($parts[1]) : 0;

        $words = [
            0 => '',
            1 => 'One',
            2 => 'Two',
            3 => 'Three',
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
            8 => 'Eight',
            9 => 'Nine',
            10 => 'Ten',
            11 => 'Eleven',
            12 => 'Twelve',
            13 => 'Thirteen',
            14 => 'Fourteen',
            15 => 'Fifteen',
            16 => 'Sixteen',
            17 => 'Seventeen',
            18 => 'Eighteen',
            19 => 'Nineteen',
            20 => 'Twenty',
            30 => 'Thirty',
            40 => 'Forty',
            50 => 'Fifty',
            60 => 'Sixty',
            70 => 'Seventy',
            80 => 'Eighty',
            90 => 'Ninety'
        ];

        $convert_chunk = function ($n) use ($words, &$convert_chunk) {
            $str = '';
            if ($n >= 100) {
                $str .= $words[intval($n / 100)] . ' Hundred ';
                $n = $n % 100;
            }
            if ($n > 0) {
                if ($n < 20) {
                    $str .= $words[$n] . ' ';
                } else {
                    $str .= $words[intval($n / 10) * 10] . ' ';
                    if ($n % 10 > 0) {
                        $str .= $words[$n % 10] . ' ';
                    }
                }
            }
            return trim($str);
        };

        if ($rupees === 0) {
            $res = 'Zero Rupees';
        } else {
            $crore = intval($rupees / 10000000);
            $rupees %= 10000000;
            $lakh = intval($rupees / 100000);
            $rupees %= 100000;
            $thousand = intval($rupees / 1000);
            $rupees %= 1000;
            $hundred = $rupees;

            $res_arr = [];
            if ($crore > 0) $res_arr[] = $convert_chunk($crore) . ' Crore';
            if ($lakh > 0) $res_arr[] = $convert_chunk($lakh) . ' Lakh';
            if ($thousand > 0) $res_arr[] = $convert_chunk($thousand) . ' Thousand';
            if ($hundred > 0) $res_arr[] = $convert_chunk($hundred);

            $res = implode(' ', $res_arr) . ' Rupees';
        }

        if ($paise > 0) {
            $res .= ' and ' . $convert_chunk($paise) . ' Paise';
        }

        return trim($res) . ' Only';
    }
}

if (!function_exists('render_order_invoice_html')) {
    /**
     * Render the professional ReComm Tax Invoice HTML.
     *
     * Layout note: card "boxes" are styled directly on <td> elements (not on
     * inner <div>s with min-height). Table cells in the same row always match
     * height automatically in every HTML renderer, including the PDF engines
     * commonly used from PHP (mPDF, Dompdf, TCPDF, wkhtmltopdf) — so the
     * Sold-By/Meta and Bill-To/Ship-To cards line up perfectly even when one
     * side has more text than the other. Corner rounding is kept only on
     * small, low-risk elements (pill badges); the larger content blocks use
     * clean square corners with a border/left accent bar instead of
     * border-radius + overflow:hidden, which is the combination most PDF
     * engines render inconsistently.
     *
     * @param int $order_id
     * @param int|null $vendor_id
     * @return string|null
     */
    function render_order_invoice_html($order_id, $vendor_id = null)
    {
        $CI = &get_instance();
        $CI->load->database();

        $order = $CI->db->get_where('orders', ['id' => $order_id])->row();
        if (!$order) {
            return null;
        }

        $customer = $CI->db->get_where('users', ['id' => $order->user_id])->row();
        $address = $CI->db->get_where('user_addresses', ['id' => $order->address_id])->row();

        $CI->db->where('order_id', $order_id);
        if ($vendor_id) {
            $CI->db->where('vendor_id', $vendor_id);
        }
        $items = $CI->db->get('order_items')->result();
        if (empty($items)) {
            $items = $CI->db->where('order_id', $order_id)->get('order_items')->result();
        }
        if (empty($items)) {
            return null;
        }

        $actual_vendor_id = $vendor_id ?: ($items[0]->vendor_id ?? 0);
        $vendor = $CI->db->get_where('users', ['id' => $actual_vendor_id])->row();

        // 1. Calculate Items & Taxes
        $subtotal = 0.00;
        $total_qty = 0;
        $calculated_gst = 0.00;
        $items_rows = '';
        $idx = 1;

        foreach ($items as $item) {
            $qty = (int)$item->quantity;
            $total_qty += $qty;
            $unit_price = floatval($item->price);
            $line_taxable = ($unit_price * $qty);
            $subtotal += $line_taxable;

            $gst_pct = floatval($item->gst_percent ?? 0.00);
            $gst_amt = floatval($item->gst_amount ?? 0.00);
            if ($gst_amt <= 0 && $gst_pct > 0) {
                $gst_amt = ($line_taxable * $gst_pct) / 100;
            }
            $calculated_gst += $gst_amt;
            $line_total = $line_taxable + $gst_amt;

            $p_name = htmlspecialchars($item->product_name);
            $p_qty = $qty;
            $p_price = number_format($unit_price, 2);
            $p_taxable = number_format($line_taxable, 2);
            $p_gst_pct = $gst_pct > 0 ? (number_format($gst_pct, 1) . '%') : '0%';
            $p_line = number_format($line_total, 2);
            $bg = ($idx % 2 === 0) ? '#f7f9fc' : '#ffffff';

            $store_subtext = '';
            if (!empty($item->vendor_id)) {
                $v_info = $CI->db->select('store_name, name')->get_where('users', ['id' => $item->vendor_id])->row();
                if ($v_info) {
                    $sname = !empty($v_info->store_name) ? $v_info->store_name : $v_info->name;
                    $store_subtext = "<br><span style='font-size: 8.5px; color: #5a6b82; font-weight: normal;'>Store: " . htmlspecialchars($sname) . "</span>";
                }
            }

            $items_rows .= "
                <tr style='background-color: {$bg};'>
                    <td style='padding: 8px; border-bottom: 1px solid #e4e9f0; text-align: center; color: #8a94a6; font-size: 9.5px;'>{$idx}</td>
                    <td style='padding: 8px; border-bottom: 1px solid #e4e9f0; font-weight: bold; color: #0b1f45; font-size: 10.5px;'>{$p_name}{$store_subtext}</td>
                    <td style='padding: 8px; border-bottom: 1px solid #e4e9f0; text-align: center; color: #3a4256; font-size: 10px;'>{$p_qty}</td>
                    <td style='padding: 8px; border-bottom: 1px solid #e4e9f0; text-align: right; color: #3a4256; font-size: 10px;'>&#8377; {$p_price}</td>
                    <td style='padding: 8px; border-bottom: 1px solid #e4e9f0; text-align: right; color: #3a4256; font-size: 10px;'>&#8377; {$p_taxable}</td>
                    <td style='padding: 8px; border-bottom: 1px solid #e4e9f0; text-align: center; color: #8a94a6; font-size: 10px;'>{$p_gst_pct}</td>
                    <td style='padding: 8px; border-bottom: 1px solid #e4e9f0; text-align: right; font-weight: bold; color: #0b1f45; font-size: 10.5px;'>&#8377; {$p_line}</td>
                </tr>
            ";
            $idx++;
        }

        // 2. Order Delivery & Totals
        $is_urgent = (isset($order->delivery_type) && strtolower($order->delivery_type) === 'urgent');
        $urgent_charge = $is_urgent ? 50.00 : 0.00;
        $delivery_charge = floatval($order->delivery_charge ?? 0.00);
        $base_delivery_charge = ($is_urgent && $delivery_charge >= 50) ? ($delivery_charge - 50.00) : $delivery_charge;

        $discount = floatval($order->discount ?? 0.00);
        $taxable_value = max(0.00, $subtotal - $discount);

        $total_gst = floatval($order->gst_amount ?? 0.00);
        if ($total_gst <= 0 && $calculated_gst > 0) {
            $total_gst = $calculated_gst;
        }

        $cgst = round($total_gst / 2, 2);
        $sgst = round($total_gst / 2, 2);
        $igst = 0.00;

        $grand_total = floatval($order->total_amount ?? ($subtotal + $total_gst + $delivery_charge - $discount));

        // 3. Metadata & Formatting
        $order_number = htmlspecialchars($order->order_number ?? ('ORD-' . $order->id));
        $date_str = date('d M Y, h:i A', strtotime($order->created_at ?: ($order->created_on ?: 'now')));
        $order_status = strtoupper(htmlspecialchars($order->status ?? 'CONFIRMED'));

        // 4. Vendor Details
        $vendor_name = htmlspecialchars($vendor->store_name ?? ($vendor->name ?? 'Recomm Vendor Store'));
        $vendor_owner = htmlspecialchars($vendor->owner_name ?? ($vendor->name ?? 'Store Owner'));
        $vendor_phone = htmlspecialchars($vendor->mobile ?? ($vendor->contact_number ?? ''));
        $vendor_email = htmlspecialchars($vendor->email ?? '');
        $vendor_addr = htmlspecialchars($vendor->address ?? 'Store Address');
        $vendor_pin = htmlspecialchars($vendor->pincode ?? '');
        $vendor_gst = htmlspecialchars($vendor->gst_number ?? 'Exempt / Unregistered');

        // 5. Customer & Delivery Address Details
        $cust_name = htmlspecialchars($customer->name ?? ($address->full_name ?? 'Valued Customer'));
        $cust_phone = htmlspecialchars($customer->mobile ?? ($address->mobile ?? ''));
        $cust_email = htmlspecialchars($customer->email ?? '');

        $ship_name = htmlspecialchars($address->full_name ?? $cust_name);
        $ship_phone = htmlspecialchars($address->mobile ?? $cust_phone);
        $ship_addr = $address ? htmlspecialchars("{$address->address_line1}" . ($address->address_line2 ? ", {$address->address_line2}" : "") . ($address->landmark ? ", Near {$address->landmark}" : "") . ", {$address->city}, {$address->state} - {$address->pincode}") : 'Customer Delivery Address';
        $place_of_supply = htmlspecialchars(($address->state ?? ($vendor->state ?? 'Gujarat')) . ' (24)');

        // 6. Payment & Delivery Slot Info
        $pay_method = strtoupper(htmlspecialchars($order->payment_method ?? 'COD'));
        $pay_status = strtoupper(htmlspecialchars($order->payment_status ?? 'PENDING'));
        $txn_id = htmlspecialchars($order->razorpay_payment_id ?? 'N/A');

        $del_mode = ($order->delivery_option === 'self') ? 'By Self (Store Pickup / Delivery)' : 'Delivery Partner';
        $distance_km = $order->distance ? floatval($order->distance) . ' KM' : ($order->distance_km ? floatval($order->distance_km) . ' KM' : '');

        // Badges: plain background + a solid left accent bar instead of an emoji glyph.
        // Emoji characters (⚡ etc.) are frequently missing from the subset fonts PHP PDF
        // libraries embed, so they print as an empty box. A colour + left-bar reads just
        // as clearly and renders identically on every engine.
        $urgent_badge = $is_urgent
            ? '<span style="background-color:#fef2f2; color:#b91c1c; border:1px solid #f3b9b9; border-left:3px solid #dc2626; padding:3px 8px; font-weight:bold; font-size:9px; letter-spacing:0.3px;">URGENT DELIVERY</span>'
            : '<span style="background-color:#f0fdf4; color:#166534; border:1px solid #bfe6c7; border-left:3px solid #2f9e4f; padding:3px 8px; font-weight:bold; font-size:9px; letter-spacing:0.3px;">STANDARD DELIVERY</span>';

        $pay_ok = in_array($pay_status, ['PAID', 'COMPLETED', 'SUCCESS'], true);
        $pay_badge = $pay_ok
            ? '<span style="background-color:#eafaf0; color:#146c43; border:1px solid #bfe6c7; border-left:3px solid #2f9e4f; padding:3px 8px; font-weight:bold; font-size:9px; letter-spacing:0.3px;">' . $pay_status . '</span>'
            : '<span style="background-color:#fef8e8; color:#8a5a00; border:1px solid #f2dca0; border-left:3px solid #d69e00; padding:3px 8px; font-weight:bold; font-size:9px; letter-spacing:0.3px;">' . $pay_status . '</span>';

        $status_badge = '<span style="background-color:#eaf0fb; color:#0b1f45; border:1px solid #c9d7f2; border-left:3px solid #17356e; padding:2px 7px; font-weight:bold; font-size:8.5px; letter-spacing:0.3px;">' . $order_status . '</span>';

        $chosen_slot_opt = $order->chosen_time_option ?? 'immediately';
        $slot_label = ucfirst($chosen_slot_opt);
        if ($chosen_slot_opt === 'later') $slot_label = 'Later (3–4 hrs)';
        if ($chosen_slot_opt === 'immediately') $slot_label = 'Immediately (ASAP)';
        if ($chosen_slot_opt === 'lunch') $slot_label = 'Lunch Delivery Window';
        if ($chosen_slot_opt === 'dinner') $slot_label = 'Dinner Delivery Window';
        if ($chosen_slot_opt === 'custom') $slot_label = 'Custom Scheduled';

        $window_display = '';
        if (!empty($order->estimated_window_start) && !empty($order->estimated_window_end)) {
            $window_display = function_exists('format_slot_window_display')
                ? format_slot_window_display($order->estimated_window_start, $order->estimated_window_end, strtotime($order->created_at ?: 'now'))
                : (date('d M, h:i A', strtotime($order->estimated_window_start)) . ' - ' . date('h:i A', strtotime($order->estimated_window_end)));
        }

        // 7. Formatted Currency Values
        $subtotal_fmt = number_format($subtotal, 2);
        $discount_fmt = number_format($discount, 2);
        $taxable_fmt = number_format($taxable_value, 2);
        $total_tax_fmt = number_format($total_gst, 2);
        $cgst_fmt = number_format($cgst, 2);
        $sgst_fmt = number_format($sgst, 2);
        $igst_fmt = number_format($igst, 2);
        $base_delivery_fmt = number_format($base_delivery_charge, 2);
        $urgent_fmt = number_format($urgent_charge, 2);
        $grand_total_fmt = number_format($grand_total, 2);

        $amount_in_words = amount_to_words_indian($grand_total);

        // 8. Logo Base64 embedding
        $logo_tag = '<span style="font-size: 25px; font-weight: 800; color: #0b1f45; letter-spacing: -0.5px;">RECOMM</span>';
        $logo_path = FCPATH . 'assets/recomm-logo.png';
        if (!file_exists($logo_path)) {
            $logo_path = FCPATH . 'assets/recomm-logo.png';
        }
        if (file_exists($logo_path)) {
            $logo_data = base64_encode(@file_get_contents($logo_path));
            if (!empty($logo_data)) {
                $logo_tag = '<img src="data:image/png;base64,' . $logo_data . '" style="height: 46px; width: auto; max-width: 170px;" alt="ReComm Logo"/>';
            }
        }

        // 9. Construct Clean, Professional HTML
        return '<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Tax Invoice - ' . $order_number . '</title>
<style>
    @page {
        margin: 12mm 12mm 14mm;
        size: A4 portrait;
    }
    * { box-sizing: border-box; }
    body {
        font-family: "DejaVu Sans", Arial, Helvetica, sans-serif;
        font-size: 10px;
        color: #2a3142;
        margin: 0;
        padding: 0;
        line-height: 1.5;
        background-color: #ffffff;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    /* Tri-tone brand bar: navy / emerald / gold hairline */
    .brand-bar-table { margin-bottom: 14px; }
    .brand-bar-table td { height: 6px; padding: 0; font-size: 0; line-height: 0; }
    .bar-navy { background-color: #0b1f45; }
    .bar-green { background-color: #2f9e4f; }
    .bar-gold { background-color: #c9972e; }

    .header-table td { vertical-align: middle; padding: 0; }
    .brand-sub {
        font-size: 8px;
        font-weight: bold;
        color: #2f9e4f;
        text-transform: uppercase;
        letter-spacing: 1.4px;
        margin-top: 3px;
    }
    .invoice-title {
        font-size: 19px;
        font-weight: bold;
        color: #0b1f45;
        text-align: right;
        letter-spacing: 0.6px;
    }
    .invoice-title-rule {
        border-bottom: 2px solid #c9972e;
        width: 70px;
        margin: 3px 0 5px auto;
    }
    .invoice-num {
        font-size: 12.5px;
        font-weight: bold;
        color: #2f9e4f;
        text-align: right;
    }
    .invoice-meta-sub {
        font-size: 9px;
        color: #6b7385;
        text-align: right;
        margin-top: 2px;
    }
    .section-divider {
        border-bottom: 1px solid #e4e9f0;
        margin: 12px 0 14px;
        font-size: 0;
        line-height: 0;
    }

    /* Section labels shown above a table-based card row */
    .section-label {
        font-size: 8px;
        font-weight: bold;
        color: #6b7385;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        padding: 0 0 4px 2px;
    }

    /* Card table: two matched-height cells styled directly (not on an inner
       div), so left/right columns always align regardless of content length
       or PDF-engine min-height support. */
    .card-table { width: 100%; margin-bottom: 10px; }
    .card-table td.card-cell {
        vertical-align: top;
        width: 49%;
        background-color: #f7f9fc;
        border: 1px solid #e4e9f0;
        border-top: 2px solid #0b1f45;
        padding: 10px 12px;
    }
    .card-table td.card-cell.alt-border { border-top: 2px solid #2f9e4f; }
    .card-table td.card-gap { width: 2%; padding: 0; }

    .card-heading {
        font-size: 8px;
        font-weight: bold;
        color: #2f9e4f;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        margin-bottom: 5px;
        padding-bottom: 4px;
        border-bottom: 1px solid #e4e9f0;
    }
    .card-heading-navy { color: #0b1f45; }
    .party-title {
        font-size: 12px;
        font-weight: bold;
        color: #0b1f45;
        margin-bottom: 3px;
    }
    .party-row {
        font-size: 9.5px;
        color: #565f74;
        line-height: 1.45;
        margin-bottom: 1px;
    }
    .party-row strong { color: #2a3142; }

    /* Product Line Items Table */
    .items-table {
        width: 100%;
        margin-top: 4px;
        margin-bottom: 12px;
        border: 1px solid #e4e9f0;
    }
    .items-table th {
        background-color: #0b1f45;
        color: #ffffff;
        font-size: 8.5px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 9px 8px;
        border-bottom: 2px solid #c9972e;
    }
    .items-table td { font-size: 9.5px; }
    .items-totals-row td {
        background-color: #eef1f7;
        border-top: 2px solid #c3ccdb;
        font-size: 10px;
        font-weight: bold;
        color: #0b1f45;
        padding: 8px;
    }

    /* Tax Summary Strip */
    .tax-strip-table {
        border: 1px solid #e4e9f0;
        border-left: 3px solid #0b1f45;
        margin-bottom: 14px;
    }
    .tax-strip-table td {
        padding: 8px;
        text-align: center;
        border-right: 1px solid #e4e9f0;
    }
    .tax-strip-table td.last { border-right: none; }
    .tax-strip-label {
        color: #6b7385;
        text-transform: uppercase;
        font-weight: bold;
        font-size: 7.5px;
        letter-spacing: 0.4px;
    }
    .tax-strip-val { font-size: 10.5px; font-weight: bold; color: #0b1f45; }

    /* Bottom Layout */
    .bottom-layout-table { width: 100%; margin-bottom: 14px; }
    .bottom-layout-table td { vertical-align: top; padding: 0; }

    .info-block {
        background-color: #f7f9fc;
        border: 1px solid #e4e9f0;
        padding: 10px 12px;
        margin-bottom: 8px;
    }
    .info-block table td { font-size: 9.5px; padding: 3px 0; }
    .info-block .lbl { width: 42%; color: #6b7385; }
    .info-block .val { font-weight: bold; color: #0b1f45; }

    .receipt-box {
        background-color: #ffffff;
        border: 1px solid #e4e9f0;
        border-top: 3px solid #0b1f45;
        padding: 12px 14px;
    }
    .receipt-heading {
        font-size: 10px;
        font-weight: bold;
        color: #0b1f45;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        border-bottom: 1px solid #e4e9f0;
        padding-bottom: 5px;
        margin-bottom: 7px;
    }
    .receipt-row td { padding: 3.5px 0; font-size: 9.5px; }
    .receipt-label { color: #565f74; }
    .receipt-val { text-align: right; font-weight: bold; color: #2a3142; }

    .grand-total-box {
        background-color: #0b1f45;
        color: #ffffff;
        border-top: 3px solid #c9972e;
        padding: 9px 12px;
        margin-top: 8px;
    }
    .grand-total-box table td { color: #ffffff; padding: 0; }
    .grand-total-title { font-size: 10.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.6px; }
    .grand-total-amount { font-size: 15px; font-weight: bold; color: #6fe08a; text-align: right; }

    /* Words & Signature */
    .sign-table { width: 100%; margin-top: 4px; border-top: 1px solid #e4e9f0; padding-top: 10px; }
    .sign-table td { vertical-align: top; }
    .words-label { font-size: 9px; color: #6b7385; text-transform: uppercase; letter-spacing: 0.4px; }
    .words-val { font-size: 11px; font-weight: bold; color: #0b1f45; margin-top: 3px; margin-bottom: 6px; }
    .declaration { color: #6b7385; font-size: 8.5px; line-height: 1.5; }
    .sign-box { text-align: center; font-size: 9.5px; color: #565f74; }
    .sign-vendor { font-weight: bold; color: #0b1f45; }
    .sign-space { height: 34px; }
    .sign-line { border-top: 1px solid #b9c1d1; padding-top: 4px; }

    .invoice-footer {
        margin-top: 16px;
        border-top: 1px solid #e4e9f0;
        padding-top: 10px;
        text-align: center;
        font-size: 8.5px;
        color: #8a94a6;
    }
    .invoice-footer .thanks { font-weight: bold; color: #0b1f45; font-size: 10px; margin: 0 0 4px; }
</style>
</head>
<body>

    <!-- Tri-tone Brand Bar -->
    <table class="brand-bar-table"><tr>
        <td class="bar-navy" style="width:70%;"></td>
        <td class="bar-green" style="width:22%;"></td>
        <td class="bar-gold" style="width:8%;"></td>
    </tr></table>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="width: 50%;">
                ' . $logo_tag . '
                <div class="brand-sub">Hyperlocal Delivery Marketplace</div>
            </td>
            <td style="width: 50%;">
                <div class="invoice-title">TAX INVOICE</div>
                <div class="invoice-title-rule"></div>
                <div class="invoice-num">#' . $order_number . '</div>
                <div class="invoice-meta-sub">Date &amp; Time: ' . $date_str . '</div>
                <div class="invoice-meta-sub">Place of Supply: ' . $place_of_supply . '</div>
            </td>
        </tr>
    </table>

    <div class="section-divider"></div>

    <!-- Row 1: Seller Details & Invoice Meta -->
    <table class="card-table">
        <tr>
            <td class="card-cell">
                <div class="card-heading">Sold By (Vendor / Seller)</div>
                <div class="party-title">' . $vendor_name . '</div>'
            . ($vendor_owner ? '<div class="party-row"><strong>Proprietor:</strong> ' . $vendor_owner . '</div>' : '')
            . '<div class="party-row"><strong>Address:</strong> ' . $vendor_addr . ($vendor_pin ? ' - ' . $vendor_pin : '') . '</div>'
            . ($vendor_phone ? '<div class="party-row"><strong>Mobile:</strong> ' . $vendor_phone . '</div>' : '')
            . ($vendor_email ? '<div class="party-row"><strong>Email:</strong> ' . $vendor_email . '</div>' : '')
            . '<div class="party-row"><strong>GSTIN:</strong> ' . $vendor_gst . '</div>
            </td>
            <td class="card-gap"></td>
            <td class="card-cell alt-border">
                <div class="card-heading card-heading-navy">Invoice &amp; Order Meta</div>
                <div class="party-row"><strong>Invoice No:</strong> INV-' . htmlspecialchars(str_replace('ORD-', '', $order_number)) . '</div>
                <div class="party-row"><strong>Order ID:</strong> #' . $order_number . '</div>
                <div class="party-row"><strong>Order Date:</strong> ' . $date_str . '</div>
                <div class="party-row"><strong>Order Status:</strong> ' . $status_badge . '</div>
                <div class="party-row"><strong>Channel:</strong> ReComm Customer Platform</div>
                <div class="party-row"><strong>State Code:</strong> 24 (Gujarat)</div>
            </td>
        </tr>
    </table>

    <!-- Row 2: Bill-To & Ship-To -->
    <table class="card-table">
        <tr>
            <td class="card-cell">
                <div class="card-heading card-heading-navy">Bill To (Customer)</div>
                <div class="party-title">' . $cust_name . '</div>
                <div class="party-row"><strong>Mobile:</strong> ' . $cust_phone . '</div>'
            . ($cust_email ? '<div class="party-row"><strong>Email:</strong> ' . $cust_email . '</div>' : '')
            . '<div class="party-row"><strong>Billing Country:</strong> India</div>
            </td>
            <td class="card-gap"></td>
            <td class="card-cell">
                <div class="card-heading card-heading-navy">Ship To (Delivery Address)</div>
                <div class="party-title">' . $ship_name . '</div>
                <div class="party-row">' . $ship_addr . '</div>
                <div class="party-row"><strong>Contact:</strong> ' . $ship_phone . '</div>
            </td>
        </tr>
    </table>

    <!-- Product Line Items -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">#</th>
                <th style="width: 41%; text-align: left;">Product Description</th>
                <th style="width: 8%; text-align: center;">Qty</th>
                <th style="width: 14%; text-align: right;">Unit Rate</th>
                <th style="width: 14%; text-align: right;">Taxable Val</th>
                <th style="width: 8%; text-align: center;">GST</th>
                <th style="width: 10%; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            ' . $items_rows . '
            <tr class="items-totals-row">
                <td colspan="2" style="text-align: right; padding-right: 12px;">ITEM TOTALS:</td>
                <td style="text-align: center;">' . $total_qty . '</td>
                <td></td>
                <td style="text-align: right;">&#8377; ' . $subtotal_fmt . '</td>
                <td style="text-align: center;">' . ($total_gst > 0 ? '&#8377; ' . $total_tax_fmt : '-') . '</td>
                <td style="text-align: right;">&#8377; ' . number_format($subtotal + $total_gst, 2) . '</td>
            </tr>
        </tbody>
    </table>

    <!-- Tax Summary Strip -->
    <table class="tax-strip-table">
        <tr>
            <td style="width: 24%;">
                <div class="tax-strip-label">Taxable Value</div>
                <div class="tax-strip-val">&#8377; ' . $taxable_fmt . '</div>
            </td>
            <td style="width: 19%;">
                <div class="tax-strip-label">CGST (Central)</div>
                <div class="tax-strip-val">&#8377; ' . $cgst_fmt . '</div>
            </td>
            <td style="width: 19%;">
                <div class="tax-strip-label">SGST (State)</div>
                <div class="tax-strip-val">&#8377; ' . $sgst_fmt . '</div>
            </td>
            <td style="width: 19%;">
                <div class="tax-strip-label">IGST (Integrated)</div>
                <div class="tax-strip-val">&#8377; ' . $igst_fmt . '</div>
            </td>
            <td class="last" style="width: 19%;">
                <div class="tax-strip-label">Total Tax Amount</div>
                <div class="tax-strip-val" style="color: #2f9e4f;">&#8377; ' . $total_tax_fmt . '</div>
            </td>
        </tr>
    </table>

    <!-- Payment & Delivery (left) + Order Summary (right) -->
    <table class="bottom-layout-table">
        <tr>
            <td style="width: 53%;">
                <div class="info-block">
                    <div class="card-heading" style="margin-bottom:6px;">Payment Information</div>
                    <table>
                        <tr>
                            <td class="lbl">Payment Method:</td>
                            <td class="val">' . $pay_method . '</td>
                        </tr>
                        <tr>
                            <td class="lbl">Payment Status:</td>
                            <td>' . $pay_badge . '</td>
                        </tr>'
            . ($txn_id !== 'N/A' ? '<tr><td class="lbl">Transaction ID:</td><td style="color:#3a4256;">' . $txn_id . '</td></tr>' : '') . '
                    </table>
                </div>

                <div class="info-block" style="margin-bottom:0;">
                    <div class="card-heading" style="margin-bottom:6px;">Delivery &amp; Fulfillment Info</div>
                    <table>
                        <tr>
                            <td class="lbl">Fulfillment Mode:</td>
                            <td class="val">' . $del_mode . '</td>
                        </tr>
                        <tr>
                            <td class="lbl">Delivery Speed:</td>
                            <td>' . $urgent_badge . ($distance_km ? ' &nbsp;<span style="color:#6b7385;">(' . $distance_km . ')</span>' : '') . '</td>
                        </tr>
                        <tr>
                            <td class="lbl">Time Slot Option:</td>
                            <td class="val">' . htmlspecialchars($slot_label) . '</td>
                        </tr>'
            . ($window_display ? '<tr><td class="lbl">Est. Delivery Window:</td><td class="val" style="color:#2f9e4f;">' . htmlspecialchars($window_display) . '</td></tr>' : '') . '
                    </table>
                </div>
            </td>

            <td style="width: 3%;"></td>

            <td style="width: 44%;">
                <div class="receipt-box">
                    <div class="receipt-heading">Invoice Summary</div>
                    <table>
                        <tr class="receipt-row">
                            <td class="receipt-label">Items Subtotal:</td>
                            <td class="receipt-val">&#8377; ' . $subtotal_fmt . '</td>
                        </tr>'
            . ($discount > 0 ? '
                        <tr class="receipt-row">
                            <td class="receipt-label" style="color: #2f9e4f;">Coupon Discount:</td>
                            <td class="receipt-val" style="color: #2f9e4f;">- &#8377; ' . $discount_fmt . '</td>
                        </tr>' : '') . '
                        <tr class="receipt-row">
                            <td class="receipt-label">Taxable Value:</td>
                            <td class="receipt-val">&#8377; ' . $taxable_fmt . '</td>
                        </tr>
                        <tr class="receipt-row">
                            <td class="receipt-label">Goods &amp; Services Tax (GST):</td>
                            <td class="receipt-val">&#8377; ' . $total_tax_fmt . '</td>
                        </tr>
                        <tr class="receipt-row">
                            <td class="receipt-label">Delivery Fee' . ($distance_km ? ' (' . $distance_km . ')' : '') . ':</td>
                            <td class="receipt-val">' . ($base_delivery_charge > 0 ? '&#8377; ' . $base_delivery_fmt : '<span style="color:#2f9e4f;">FREE</span>') . '</td>
                        </tr>'
            . ($is_urgent ? '
                        <tr class="receipt-row">
                            <td class="receipt-label" style="color: #b91c1c;">Urgent Delivery Fee:</td>
                            <td class="receipt-val" style="color: #b91c1c;">+ &#8377; ' . $urgent_fmt . '</td>
                        </tr>' : '') . '
                        <tr class="receipt-row">
                            <td class="receipt-label">Platform Fee:</td>
                            <td class="receipt-val">&#8377; 0.00</td>
                        </tr>
                    </table>

                    <div class="grand-total-box">
                        <table>
                            <tr>
                                <td class="grand-total-title">Grand Total</td>
                                <td class="grand-total-amount">&#8377; ' . $grand_total_fmt . '</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Amount in Words & Signatory -->
    <table class="sign-table">
        <tr>
            <td style="width: 62%;">
                <div class="words-label">Amount Chargeable (in words)</div>
                <div class="words-val">' . $amount_in_words . '</div>
                <div class="declaration">
                    <strong>Declarations &amp; Policy:</strong> We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct. Returns and replacements are governed by the ReComm Platform Refund &amp; Cancellation Policy.
                </div>
            </td>
            <td style="width: 38%;" class="sign-box">
                <div>For <span class="sign-vendor">' . $vendor_name . '</span></div>
                <div class="sign-space"></div>
                <div class="sign-line"><strong>Authorized Signatory</strong></div>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="invoice-footer">
        <p class="thanks">Thank you for shopping with ReComm!</p>
        <p style="margin: 0;">This is a computer-generated tax invoice and does not require a physical signature. Issued under applicable provisions of the Information Technology Act &amp; GST Rules.</p>
    </div>
</body>
</html>';
    }
}
