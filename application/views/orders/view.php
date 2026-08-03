<style>
    /* Styling variables and custom elements for order details */
    :root {
        --rc-forest-900: #07271b;
        --rc-forest-800: #0b3d29;
        --rc-forest-700: #0f5c3e;
        --rc-green-600: #16794f;
        --rc-green-500: #1f9d63;
        --rc-emerald-400: #2ecc71;
        --rc-lime-300: #7ee8a8;
        --rc-gold-500: #f2a93b;
        --rc-gold-400: #f7c469;

        --rc-ink: #0e2a1f;
        --rc-muted: #5f7c70;
        --rc-line: #e2ede7;
        --rc-bg: #f3f8f6;
        --rc-white: #ffffff;

        --rc-red-600: #c0293a;
        --rc-blue-600: #2f6fb3;

        --rc-radius-lg: 20px;
        --rc-radius-md: 14px;
        --rc-radius-sm: 10px;
        --rc-shadow: 0 6px 24px rgba(11, 61, 41, 0.08);
        --rc-shadow-hover: 0 12px 32px rgba(11, 61, 41, 0.14);
    }

    .rc-order-details {
        font-family: 'Inter', 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
        color: var(--rc-ink);
        padding-bottom: 2rem;
    }

    .rc-order-details h1,
    .rc-order-details h2,
    .rc-order-details h3,
    .rc-order-details h4,
    .rc-order-details h5,
    .rc-order-details h6 {
        font-family: 'Poppins', 'Inter', sans-serif;
        color: var(--rc-forest-900);
    }

    /* ---------- Header ---------- */
    .rc-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        background: var(--rc-white);
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-lg);
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--rc-shadow);
        flex-wrap: wrap;
    }

    .rc-page-header .rc-header-left {
        display: flex;
        align-items: center;
        gap: .9rem;
    }

    .rc-header-badge {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--rc-forest-800), var(--rc-emerald-400));
        color: #fff;
        font-size: 1.15rem;
        flex-shrink: 0;
        box-shadow: 0 6px 14px rgba(15, 92, 62, 0.35);
    }

    .rc-page-header h1 {
        font-size: 1.4rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.2;
    }

    .rc-page-header .rc-subtitle {
        font-size: .85rem;
        color: var(--rc-muted);
        margin-top: 2px;
    }

    .rc-breadcrumb {
        display: flex;
        align-items: center;
        gap: .4rem;
        list-style: none;
        margin: 0;
        padding: 0;
        font-size: .8rem;
        color: var(--rc-green-600);
        background: rgba(46, 204, 113, 0.08);
        padding: .4rem .9rem;
        border-radius: 999px;
        font-weight: 600;
    }

    .rc-breadcrumb a {
        color: var(--rc-green-600);
        text-decoration: none;
        transition: color 0.15s ease;
    }

    .rc-breadcrumb a:hover {
        color: var(--rc-forest-900);
    }

    /* ---------- Columns Layout ---------- */
    .rc-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
    }

    .rc-col-main {
        flex: 2 1 600px;
        min-width: 0;
    }

    .rc-col-side {
        flex: 1 1 350px;
        min-width: 0;
    }

    /* ---------- Cards ---------- */
    .rc-card {
        background: var(--rc-white);
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-lg);
        box-shadow: var(--rc-shadow);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .rc-card-header {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        margin-bottom: 1.25rem;
        border-bottom: 1px solid var(--rc-line);
        padding-bottom: 0.9rem;
    }

    .rc-card-header h3 {
        font-size: 1.05rem;
        font-weight: 700;
        margin: 0;
    }

    .rc-card-header i {
        color: var(--rc-green-600);
        font-size: 1.15rem;
    }

    /* ---------- Order Summary Card ---------- */
    .rc-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.25rem;
    }

    .rc-summary-item {
        background: #f8faf8;
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-md);
        padding: 0.9rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .rc-summary-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(31, 157, 99, 0.08);
        color: var(--rc-green-600);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        flex-shrink: 0;
    }

    .rc-summary-content {
        display: flex;
        flex-direction: column;
    }

    .rc-summary-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        color: var(--rc-muted);
        font-weight: 700;
        letter-spacing: 0.04em;
    }

    .rc-summary-value {
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--rc-forest-900);
    }

    /* ---------- Items Table ---------- */
    .rc-table-wrap {
        overflow-x: auto;
        border-radius: var(--rc-radius-md);
        border: 1px solid var(--rc-line);
        margin-bottom: 1.5rem;
    }

    .rc-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .rc-table thead th {
        background: #f8faf8;
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--rc-muted);
        font-weight: 700;
        border-bottom: 2px solid var(--rc-line);
        padding: 0.9rem 1rem;
    }

    .rc-table tbody td {
        padding: 1rem;
        border-bottom: 1px solid var(--rc-line);
        font-size: .88rem;
        vertical-align: middle;
    }

    .rc-table tbody tr:last-child td {
        border-bottom: none;
    }

    .rc-prod-thumb {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        object-fit: cover;
        border: 1px solid var(--rc-line);
    }

    .rc-prod-fallback {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        background: var(--rc-bg);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--rc-muted);
        font-size: 1.15rem;
        border: 1px solid var(--rc-line);
    }

    .rc-vendor-badge {
        display: inline-block;
        background: #f0faf4;
        color: var(--rc-green-600);
        font-size: 0.74rem;
        font-weight: 600;
        padding: 0.15rem 0.45rem;
        border-radius: 6px;
        border: 1px solid #dcf6e6;
    }

    /* ---------- Bill breakdown ---------- */
    .rc-bill-breakdown {
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
        padding: 1rem;
        background: #f8faf8;
        border-radius: var(--rc-radius-md);
        border: 1px solid var(--rc-line);
    }

    .rc-bill-row {
        display: flex;
        justify-content: space-between;
        font-size: 0.88rem;
        color: var(--rc-muted);
    }

    .rc-bill-row.total {
        border-top: 1px dashed var(--rc-line);
        padding-top: 0.75rem;
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--rc-forest-900);
    }

    /* ---------- Form Status Updater ---------- */
    .rc-status-form {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }

    .rc-form-group {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .rc-form-label {
        font-size: 0.74rem;
        text-transform: uppercase;
        color: var(--rc-muted);
        font-weight: 700;
        letter-spacing: 0.04em;
    }

    .rc-select {
        padding: 0.72rem 1rem;
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-sm);
        background: var(--rc-bg);
        color: var(--rc-ink);
        font-size: 0.88rem;
        font-weight: 600;
        outline: none;
        cursor: pointer;
    }

    .rc-select:focus {
        border-color: var(--rc-green-500);
        background-color: #fff;
    }

    .rc-textarea {
        padding: 0.72rem 1rem;
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-sm);
        background: var(--rc-bg);
        color: var(--rc-ink);
        font-size: 0.88rem;
        outline: none;
        resize: vertical;
        min-height: 70px;
    }

    .rc-textarea:focus {
        border-color: var(--rc-green-500);
        background-color: #fff;
    }

    .rc-btn-submit {
        background: linear-gradient(135deg, var(--rc-green-600), var(--rc-green-500));
        color: #fff;
        border: none;
        border-radius: var(--rc-radius-sm);
        padding: 0.75rem;
        font-weight: 700;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        box-shadow: 0 4px 12px rgba(22, 121, 79, 0.15);
    }

    .rc-btn-submit:hover {
        background: linear-gradient(135deg, var(--rc-forest-800), var(--rc-green-600));
        box-shadow: 0 6px 16px rgba(22, 121, 79, 0.25);
    }

    /* ---------- Timeline Details ---------- */
    .rc-timeline {
        position: relative;
        padding-left: 1.5rem;
        margin-top: 1rem;
    }

    .rc-timeline::before {
        content: "";
        position: absolute;
        left: 4px;
        top: 6px;
        bottom: 6px;
        width: 2px;
        background: var(--rc-line);
    }

    .rc-timeline-item {
        position: relative;
        padding-bottom: 1.25rem;
    }

    .rc-timeline-item:last-child {
        padding-bottom: 0;
    }

    .rc-timeline-badge {
        position: absolute;
        left: -24px;
        top: 2px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--rc-muted);
        border: 2px solid var(--rc-white);
        box-shadow: 0 0 0 2px var(--rc-line);
    }

    .rc-timeline-item.active .rc-timeline-badge {
        background: var(--rc-green-500);
        box-shadow: 0 0 0 2px rgba(46, 204, 113, 0.2);
    }

    .rc-timeline-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.2rem;
    }

    .rc-timeline-status {
        font-weight: 700;
        font-size: 0.84rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: var(--rc-forest-900);
    }

    .rc-timeline-time {
        font-size: 0.72rem;
        color: var(--rc-muted);
    }

    .rc-timeline-remarks {
        font-size: 0.82rem;
        color: var(--rc-muted);
        background: var(--rc-bg);
        padding: 0.4rem 0.6rem;
        border-radius: 6px;
        margin-top: 0.25rem;
        border: 1px solid var(--rc-line);
    }

    /* ---------- Customer & Address Details ---------- */
    .rc-meta-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.85rem;
        background: #f8faf8;
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-md);
        margin-bottom: 0.75rem;
    }

    .rc-meta-item:last-child {
        margin-bottom: 0;
    }

    .rc-meta-icon {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: rgba(31, 157, 99, 0.08);
        color: var(--rc-green-600);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    .rc-meta-content {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .rc-meta-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        color: var(--rc-muted);
        font-weight: 700;
        letter-spacing: 0.04em;
        margin-bottom: 0.15rem;
    }

    .rc-meta-value {
        font-size: 0.88rem;
        font-weight: 600;
        color: var(--rc-forest-900);
        word-break: break-word;
    }

    /* Status Pill Details */
    .rc-badge-status {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .35rem .75rem;
        border-radius: 999px;
        font-size: .74rem;
        font-weight: 700;
        letter-spacing: .01em;
        text-transform: capitalize;
    }

    .rc-badge-status.pending { background: #fffbeb; color: var(--rc-gold-500); border: 1px solid #fde68a; }
    .rc-badge-status.confirmed { background: #eff6ff; color: var(--rc-blue-600); border: 1px solid #bfdbfe; }
    .rc-badge-status.packed { background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe; }
    .rc-badge-status.out_for_delivery { background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; }
    .rc-badge-status.delivered { background: #ecfdf5; color: var(--rc-green-600); border: 1px solid #a7f3d0; }
    .rc-badge-status.cancelled { background: #fef2f2; color: var(--rc-red-600); border: 1px solid #fecaca; }

    /* Button Back */
    .rc-btn-back {
        background: transparent;
        color: var(--rc-muted);
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-md);
        padding: 0.6rem 1.15rem;
        font-weight: 600;
        font-size: 0.88rem;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .rc-btn-back:hover {
        background: var(--rc-bg);
        color: var(--rc-forest-900);
        border-color: var(--rc-muted);
    }
</style>

<div class="rc-order-details">

    <!-- Page Header -->
    <div class="rc-page-header">
        <div class="rc-header-left">
            <div class="rc-header-badge"><i class="fas fa-receipt"></i></div>
            <div>
                <h1>Order details: <?= htmlspecialchars($order->order_number) ?></h1>
                <div class="rc-subtitle">Placed on <?= date('d M Y \a\t h:i A', strtotime($order->created_on)) ?></div>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <ul class="rc-breadcrumb d-none d-sm-flex">
                <li><a href="<?= base_url('dashboard') ?>"><i class="fas fa-home"></i> Dashboard</a></li>
                <li>/</li>
                <li><a href="<?= base_url('orders') ?>">Orders</a></li>
                <li>/</li>
                <li>View</li>
            </ul>
            <a href="<?= base_url('orders') ?>" class="rc-btn-back">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
        </div>
    </div>

    <!-- Alert Message block -->
    <?php if ($this->session->flashdata('message')): ?>
        <div class="alert alert-<?= $this->session->flashdata('message_type') ?: 'info' ?> alert-dismissible fade show" role="alert" style="border-radius: var(--rc-radius-sm); margin-bottom: 1.5rem;">
            <?= $this->session->flashdata('message') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="rc-row">
        <!-- Left: Main order details -->
        <div class="rc-col-main">

            <!-- Overview grid -->
            <div class="rc-card">
                <div class="rc-card-header">
                    <i class="fas fa-eye"></i>
                    <h3>Order Overview</h3>
                </div>
                <div class="rc-summary-grid">
                    <div class="rc-summary-item">
                        <div class="rc-summary-icon"><i class="fas fa-circle-notch"></i></div>
                        <div class="rc-summary-content">
                            <span class="rc-summary-label">Status</span>
                            <span class="rc-summary-value">
                                <span class="rc-badge-status <?= htmlspecialchars($order->status) ?>" style="padding: 0.2rem 0.5rem; font-size: 0.7rem;">
                                    <?= htmlspecialchars(str_replace('_', ' ', $order->status)) ?>
                                </span>
                            </span>
                        </div>
                    </div>
                    <div class="rc-summary-item">
                        <div class="rc-summary-icon"><i class="fas fa-money-bill-wave"></i></div>
                        <div class="rc-summary-content">
                            <span class="rc-summary-label">Payment Method</span>
                            <span class="rc-summary-value"><?= htmlspecialchars(strtoupper($order->payment_method ?: 'ONLINE')) ?></span>
                        </div>
                    </div>
                    <div class="rc-summary-item">
                        <div class="rc-summary-icon"><i class="fas fa-credit-card"></i></div>
                        <div class="rc-summary-content">
                            <span class="rc-summary-label">Payment Status</span>
                            <span class="rc-summary-value">
                                <span class="badge bg-<?= ($order->payment_status == 'paid') ? 'success' : 'warning' ?>">
                                    <?= htmlspecialchars(strtoupper($order->payment_status ?: 'pending')) ?>
                                </span>
                            </span>
                        </div>
                    </div>
                    <div class="rc-summary-item">
                        <div class="rc-summary-icon"><i class="fas fa-shopping-basket"></i></div>
                        <div class="rc-summary-content">
                            <span class="rc-summary-label">Total Items</span>
                            <span class="rc-summary-value"><?= $order->total_items ?> Items</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="rc-card">
                <div class="rc-card-header">
                    <i class="fas fa-box-open"></i>
                    <h3>Order Items</h3>
                </div>
                <div class="rc-table-wrap">
                    <table class="rc-table">
                        <thead>
                            <tr>
                                <th style="width: 70px;">Image</th>
                                <th>Product</th>
                                <th>Vendor</th>
                                <th>Price</th>
                                <th style="width: 80px; text-align: center;">Qty</th>
                                <th>GST</th>
                                <th style="text-align: right;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($item->product_image)): ?>
                                            <img src="<?= base_url($item->product_image) ?>" alt="" class="rc-prod-thumb">
                                        <?php else: ?>
                                            <div class="rc-prod-fallback"><i class="fas fa-image"></i></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($item->product_name) ?></strong>
                                    </td>
                                    <td>
                                        <span class="rc-vendor-badge" title="<?= htmlspecialchars($item->vendor_store ?: $item->vendor_name) ?>">
                                            <i class="fas fa-store mr-1" style="font-size: 10px;"></i>
                                            <?= htmlspecialchars($item->vendor_name ?: 'System') ?>
                                        </span>
                                    </td>
                                    <td>₹<?= number_format($item->price, 2) ?></td>
                                    <td style="text-align: center;"><strong><?= $item->quantity ?></strong></td>
                                    <td>
                                        <small class="text-muted"><?= number_format($item->gst_percent, 1) ?>%</small><br>
                                        ₹<?= number_format($item->gst_amount, 2) ?>
                                    </td>
                                    <td style="text-align: right; font-weight: 700; color: var(--rc-forest-900);">₹<?= number_format($item->total, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Bill summary breakdown -->
                <div class="rc-bill-breakdown">
                    <div class="rc-bill-row">
                        <span>Items Subtotal</span>
                        <span>₹<?= number_format($order->subtotal ?: ($order->total_amount - $order->gst_amount - $order->delivery_charge + $order->discount), 2) ?></span>
                    </div>
                    <div class="rc-bill-row">
                        <span>Tax / GST</span>
                        <span>₹<?= number_format($order->gst_amount ?: 0, 2) ?></span>
                    </div>
                    <div class="rc-bill-row">
                        <span>Delivery / Shipping Charge</span>
                        <span>₹<?= number_format($order->delivery_charge ?: 0, 2) ?></span>
                    </div>
                    <?php if ($order->discount > 0): ?>
                        <div class="rc-bill-row text-danger">
                            <span>Discount / Coupon Applied</span>
                            <span>-₹<?= number_format($order->discount, 2) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="rc-bill-row total">
                        <span>Grand Total</span>
                        <span>₹<?= number_format($order->total_amount, 2) ?></span>
                    </div>
                </div>
            </div>

            <!-- Notes card if available -->
            <?php if (!empty($order->notes)): ?>
                <div class="rc-card">
                    <div class="rc-card-header">
                        <i class="fas fa-comment-dots"></i>
                        <h3>Order Notes</h3>
                    </div>
                    <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: var(--rc-radius-md); padding: 1rem; font-size: 0.88rem; color: #78350f;">
                        <?= nl2br(htmlspecialchars($order->notes)) ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <!-- Right: Side details (Customer info, address, status updates) -->
        <div class="rc-col-side">

            <!-- Customer Card -->
            <div class="rc-card">
                <div class="rc-card-header">
                    <i class="fas fa-user-circle"></i>
                    <h3>Customer details</h3>
                </div>
                <div class="rc-meta-item">
                    <div class="rc-meta-icon"><i class="fas fa-user"></i></div>
                    <div class="rc-meta-content">
                        <span class="rc-meta-label">Full Name</span>
                        <span class="rc-meta-value"><?= htmlspecialchars($order->customer_name ?: '-') ?></span>
                    </div>
                </div>
                <div class="rc-meta-item">
                    <div class="rc-meta-icon"><i class="fas fa-phone"></i></div>
                    <div class="rc-meta-content">
                        <span class="rc-meta-label">Mobile</span>
                        <span class="rc-meta-value"><?= htmlspecialchars($order->customer_mobile ?: '-') ?></span>
                    </div>
                </div>
                <div class="rc-meta-item">
                    <div class="rc-meta-icon"><i class="fas fa-envelope"></i></div>
                    <div class="rc-meta-content">
                        <span class="rc-meta-label">Email</span>
                        <span class="rc-meta-value"><?= htmlspecialchars($order->customer_email ?: '-') ?></span>
                    </div>
                </div>

                <?php if ($address): ?>
                    <div class="rc-meta-item" style="align-items: flex-start;">
                        <div class="rc-meta-icon" style="margin-top: 2px;"><i class="fas fa-map-marker-alt"></i></div>
                        <div class="rc-meta-content">
                            <span class="rc-meta-label">Delivery Address</span>
                            <span class="rc-meta-value" style="font-weight: 500; font-size: 0.84rem; line-height: 1.45; color: var(--rc-forest-800);">
                                <strong><?= htmlspecialchars($address->full_name) ?></strong><br>
                                <?= htmlspecialchars($address->address_line1) ?><br>
                                <?php if ($address->address_line2) echo htmlspecialchars($address->address_line2) . '<br>'; ?>
                                <?php if ($address->landmark) echo 'Landmark: ' . htmlspecialchars($address->landmark) . '<br>'; ?>
                                <?= htmlspecialchars($address->city) ?>, <?= htmlspecialchars($address->state) ?> - <strong><?= htmlspecialchars($address->pincode) ?></strong><br>
                                Phone: <?= htmlspecialchars($address->mobile) ?>
                            </span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Status Form Card -->
            <div class="rc-card">
                <div class="rc-card-header">
                    <i class="fas fa-tasks"></i>
                    <h3>Update Status</h3>
                </div>
                <form action="<?= base_url('orders/update-status') ?>" method="POST" class="rc-status-form">
                    <input type="hidden" name="order_id" value="<?= $order->id ?>">
                    <div class="rc-form-group">
                        <label class="rc-form-label" for="status-select">Order Status</label>
                        <select name="status" id="status-select" class="rc-select" required>
                            <option value="pending" <?= ($order->status == 'pending') ? 'selected' : '' ?>>Pending</option>
                            <option value="confirmed" <?= ($order->status == 'confirmed') ? 'selected' : '' ?>>Confirmed</option>
                            <option value="packed" <?= ($order->status == 'packed') ? 'selected' : '' ?>>Packed</option>
                            <option value="out_for_delivery" <?= ($order->status == 'out_for_delivery') ? 'selected' : '' ?>>Out for Delivery</option>
                            <option value="delivered" <?= ($order->status == 'delivered') ? 'selected' : '' ?>>Delivered</option>
                            <option value="cancelled" <?= ($order->status == 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="rc-form-group">
                        <label class="rc-form-label" for="remarks-textarea">Remarks / Update Notes</label>
                        <textarea name="remarks" id="remarks-textarea" class="rc-textarea" placeholder="Provide tracking notes or details..."></textarea>
                    </div>
                    <button type="submit" class="rc-btn-submit">
                        <i class="fas fa-check-circle"></i> Save Update
                    </button>
                </form>
            </div>

            <!-- Status timeline history -->
            <div class="rc-card">
                <div class="rc-card-header">
                    <i class="fas fa-history"></i>
                    <h3>Status Timeline</h3>
                </div>
                <?php if (!empty($history)): ?>
                    <div class="rc-timeline">
                        <?php foreach ($history as $idx => $step): ?>
                            <div class="rc-timeline-item <?= ($idx === 0) ? 'active' : '' ?>">
                                <div class="rc-timeline-badge"></div>
                                <div class="rc-timeline-header">
                                    <span class="rc-timeline-status"><?= htmlspecialchars($step->status) ?></span>
                                    <span class="rc-timeline-time"><?= date('d M Y, h:i A', strtotime($step->created_at)) ?></span>
                                </div>
                                <div class="rc-timeline-remarks">
                                    <?= htmlspecialchars($step->remarks ?: 'No remarks provided') ?>
                                    <br><small class="text-muted" style="font-size: 10px;">By: <?= htmlspecialchars(ucfirst($step->changed_by)) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-2" style="font-size: 0.84rem;">
                        <i class="fas fa-info-circle mr-1"></i> No status history timeline recorded yet.
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

</div>
