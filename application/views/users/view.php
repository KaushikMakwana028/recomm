<style>
    /* Styling variables and custom elements for user details */
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

        --rc-radius-lg: 18px;
        --rc-radius-md: 12px;
        --rc-radius-sm: 8px;
        --rc-shadow: 0 4px 18px rgba(11, 61, 41, 0.07);
        --rc-shadow-hover: 0 10px 26px rgba(11, 61, 41, 0.12);
    }

    * {
        box-sizing: border-box;
    }

    .rc-details-container {
        font-family: 'Inter', 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
        color: var(--rc-ink);
        padding-bottom: 1.5rem;
    }

    .rc-details-container h1,
    .rc-details-container h3 {
        font-family: 'Poppins', 'Inter', sans-serif;
        color: var(--rc-forest-900);
    }

    /* ---------- Row / column gutters ---------- */
    .rc-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1.25rem;
        margin: 0 0 1.25rem;
    }

    .rc-col-profile {
        flex: 1 1 300px;
        max-width: 340px;
    }

    .rc-col-detail {
        flex: 2 1 420px;
        min-width: 0;
    }

    @media (max-width: 991px) {
        .rc-col-profile {
            max-width: 100%;
        }
    }

    /* ---------- Header ---------- */
    .rc-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        background: var(--rc-white);
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-lg);
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
        box-shadow: var(--rc-shadow);
        flex-wrap: wrap;
    }

    .rc-header-left {
        display: flex;
        align-items: center;
        gap: .75rem;
        min-width: 0;
    }

    .rc-header-badge {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--rc-forest-800), var(--rc-emerald-400));
        color: #fff;
        font-size: 1.05rem;
        flex-shrink: 0;
        box-shadow: 0 5px 12px rgba(15, 92, 62, 0.32);
    }

    .rc-page-header h1 {
        font-size: 1.2rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.2;
        white-space: nowrap;
    }

    .rc-subtitle {
        font-size: .78rem;
        color: var(--rc-muted);
        margin-top: 1px;
    }

    .rc-breadcrumb {
        display: flex;
        align-items: center;
        gap: .35rem;
        list-style: none;
        margin: 0;
        padding: 0;
        font-size: .78rem;
        color: var(--rc-green-600);
        background: rgba(46, 204, 113, 0.08);
        padding: .35rem .8rem;
        border-radius: 999px;
        font-weight: 600;
        white-space: nowrap;
    }

    .rc-breadcrumb a {
        color: var(--rc-green-600);
        text-decoration: none;
    }

    .rc-breadcrumb a:hover {
        color: var(--rc-forest-900);
    }

    .rc-header-actions {
        display: flex;
        align-items: center;
        gap: .75rem;
        margin-left: auto;
    }

    @media (max-width: 575px) {
        .rc-breadcrumb {
            display: none;
        }

        .rc-page-header h1 {
            font-size: 1.05rem;
        }

        .rc-subtitle {
            display: none;
        }

        .rc-header-actions {
            width: 100%;
            margin-left: 0;
        }

        .rc-header-actions .rc-btn-primary {
            flex: 1 1 auto;
            justify-content: center;
        }
    }

    /* ---------- Buttons ---------- */
    .rc-btn-primary {
        background: linear-gradient(135deg, var(--rc-green-600), var(--rc-green-500));
        color: #fff;
        border: none;
        border-radius: var(--rc-radius-md);
        padding: 0.6rem 1.25rem;
        font-weight: 600;
        font-size: 0.88rem;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        box-shadow: 0 4px 12px rgba(22, 121, 79, 0.2);
        transition: all 0.2s ease;
        cursor: pointer;
        text-decoration: none;
        white-space: nowrap;
    }

    .rc-btn-primary:hover {
        background: linear-gradient(135deg, var(--rc-forest-800), var(--rc-green-600));
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(22, 121, 79, 0.3);
        color: #fff;
    }

    /* ---------- Cards ---------- */
    .rc-card {
        background: var(--rc-white);
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-lg);
        box-shadow: var(--rc-shadow);
        padding: 1.35rem 1.5rem;
        margin-bottom: 1.25rem;
        overflow: hidden;
    }

    @media (max-width: 575px) {
        .rc-card {
            padding: 1.1rem 1rem;
            border-radius: var(--rc-radius-md);
        }
    }

    .rc-card-header {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin-bottom: 1.1rem;
        border-bottom: 1px solid var(--rc-line);
        padding-bottom: 0.85rem;
    }

    .rc-card-header h3 {
        font-size: 1.02rem;
        font-weight: 700;
        margin: 0;
    }

    .rc-card-header i {
        color: var(--rc-green-600);
        font-size: 1.1rem;
    }

    /* ---------- Profile card ---------- */
    .rc-profile-card {
        text-align: center;
    }

    .rc-avatar-img,
    .rc-avatar-fallback {
        width: 92px;
        height: 92px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--rc-white);
        box-shadow: var(--rc-shadow);
        margin: 0 auto .75rem;
    }

    .rc-avatar-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--rc-forest-800), var(--rc-green-500));
        font-size: 36px;
        color: #fff;
        font-weight: 700;
    }

    .rc-profile-card h4 {
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0 0 .6rem;
    }

    .rc-badge-row {
        display: flex;
        gap: .4rem;
        justify-content: center;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }

    .rc-badge {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .32rem .7rem;
        border-radius: 999px;
        font-size: .74rem;
        font-weight: 700;
        color: #fff;
        letter-spacing: .01em;
    }

    .rc-badge.bg-danger {
        background: #e5484d;
    }

    .rc-badge.bg-success {
        background: var(--rc-green-500);
    }

    .rc-badge.bg-primary {
        background: #3b6bd6;
    }

    .rc-meta-list {
        text-align: left;
        border-top: 1px solid var(--rc-line);
        padding-top: .9rem;
        display: flex;
        flex-direction: column;
        gap: .65rem;
    }

    .rc-meta-item {
        display: flex;
        align-items: center;
        gap: .65rem;
        font-size: .88rem;
    }

    .rc-meta-item i {
        width: 20px;
        text-align: center;
        color: var(--rc-green-600);
        flex-shrink: 0;
    }

    .rc-meta-item span {
        font-weight: 600;
        word-break: break-word;
    }

    /* ---------- Info grid (replaces wide bootstrap tables) ---------- */
    .rc-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 1rem 1.5rem;
    }

    .rc-info-field {
        min-width: 0;
    }

    .rc-info-field--full {
        grid-column: 1 / -1;
    }

    .rc-info-label {
        display: block;
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--rc-muted);
        font-weight: 700;
        margin-bottom: .2rem;
    }

    .rc-info-value {
        display: block;
        font-size: .93rem;
        font-weight: 600;
        color: var(--rc-forest-900);
        word-break: break-word;
    }

    /* ---------- Store banner (merged into Store Information card) ---------- */
    .rc-store-banner {
        border-radius: var(--rc-radius-md);
        overflow: hidden;
        margin-bottom: 1.1rem;
        border: 1px solid var(--rc-line);
    }

    .rc-store-banner img {
        width: 100%;
        height: 160px;
        object-fit: cover;
        display: block;
    }

    @media (max-width: 575px) {
        .rc-store-banner img {
            height: 130px;
        }
    }

    /* ---------- Products ---------- */
    .rc-products-count {
        font-weight: 400;
        color: var(--rc-muted);
        font-size: .85rem;
    }

    .rc-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .rc-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 640px;
    }

    .rc-table thead th {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--rc-muted);
        font-weight: 700;
        border-bottom: 1px solid var(--rc-line);
        padding: .7rem .85rem;
        text-align: left;
        white-space: nowrap;
    }

    .rc-table tbody td {
        padding: .8rem .85rem;
        border-bottom: 1px solid var(--rc-line);
        font-size: .87rem;
        vertical-align: middle;
    }

    .rc-table tbody tr:last-child td {
        border-bottom: none;
    }

    .rc-table tbody tr:hover {
        background: rgba(46, 204, 113, .04);
    }

    .rc-prod-thumb,
    .rc-prod-thumb-fallback {
        width: 42px;
        height: 42px;
        border-radius: var(--rc-radius-sm);
        object-fit: cover;
        border: 1px solid var(--rc-line);
    }

    .rc-prod-thumb-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--rc-bg);
        color: var(--rc-muted);
    }

    .rc-chip {
        display: inline-block;
        font-size: .72rem;
        font-weight: 700;
        padding: .28rem .6rem;
        border-radius: 999px;
    }

    .rc-chip.category {
        background: #e6f2fd;
        color: #2f6fb3;
    }

    .rc-chip.stock {
        background: var(--rc-bg);
        color: var(--rc-forest-800);
    }

    .rc-chip.active {
        background: #e5f8ee;
        color: var(--rc-green-600);
    }

    .rc-chip.inactive {
        background: #fde8e8;
        color: #c93b3b;
    }

    .rc-price-mrp {
        font-weight: 700;
    }

    .rc-price-sell {
        font-weight: 700;
        color: var(--rc-green-600);
    }

    .rc-empty-state {
        text-align: center;
        padding: 2.5rem 1rem;
        color: var(--rc-muted);
    }

    .rc-empty-state i {
        font-size: 1.6rem;
        opacity: .5;
        display: block;
        margin-bottom: .5rem;
    }

    /* ---------- Products pagination ---------- */
    .rc-table-footer {
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        flex-wrap: wrap;
        margin-top: 1.1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--rc-line);
    }

    .rc-pg-info {
        font-size: .82rem;
        color: var(--rc-muted);
        font-weight: 500;
    }

    .rc-pagination {
        display: flex;
        align-items: center;
        gap: .3rem;
        flex-wrap: wrap;
    }

    .rc-pg-btn {
        min-width: 34px;
        height: 34px;
        padding: 0 .55rem;
        border: 1px solid var(--rc-line);
        background: var(--rc-white);
        color: var(--rc-forest-900);
        border-radius: var(--rc-radius-sm);
        font-size: .84rem;
        font-weight: 600;
        cursor: pointer;
        transition: all .15s ease;
    }

    .rc-pg-btn:hover:not(:disabled):not(.active) {
        border-color: var(--rc-green-500);
        color: var(--rc-green-600);
        background: rgba(46, 204, 113, .06);
    }

    .rc-pg-btn.active {
        background: linear-gradient(135deg, var(--rc-green-600), var(--rc-green-500));
        border-color: transparent;
        color: #fff;
        box-shadow: 0 4px 10px rgba(22, 121, 79, 0.25);
    }

    .rc-pg-btn:disabled {
        opacity: .4;
        cursor: not-allowed;
    }

    .rc-pg-ellipsis {
        color: var(--rc-muted);
        padding: 0 .2rem;
        font-size: .84rem;
    }

    @media (max-width: 575px) {
        .rc-table-footer {
            flex-direction: column;
            align-items: stretch;
            text-align: center;
        }

        .rc-pagination {
            justify-content: center;
        }
    }

    /* ---------- Mobile card-style transform for the products table ---------- */
    @media (max-width: 767px) {
        .rc-table-wrap {
            overflow-x: visible;
        }

        .rc-table {
            min-width: 0;
        }

        .rc-table thead {
            display: none;
        }

        .rc-table tbody tr {
            display: block;
            border: 1px solid var(--rc-line);
            border-radius: var(--rc-radius-md);
            padding: .85rem .9rem;
            margin-bottom: .75rem;
        }

        .rc-table tbody tr:last-child {
            margin-bottom: 0;
        }

        .rc-table tbody td {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: .4rem 0;
            border-bottom: 1px dashed var(--rc-line);
            font-size: .85rem;
        }

        .rc-table tbody td:last-child {
            border-bottom: none;
        }

        .rc-table tbody td::before {
            content: attr(data-label);
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .03em;
            font-weight: 700;
            color: var(--rc-muted);
            flex-shrink: 0;
        }

        .rc-table tbody td.rc-td-image {
            justify-content: center;
            padding-bottom: .75rem;
            border-bottom: 1px solid var(--rc-line);
            margin-bottom: .5rem;
        }

        .rc-table tbody td.rc-td-image::before {
            display: none;
        }

        .rc-table tbody td.rc-td-product {
            text-align: right;
        }
    }
</style>

<div class="rc-details-container">

    <!-- Page Header -->
    <div class="rc-page-header">
        <div class="rc-header-left">
            <div class="rc-header-badge"><i class="fas fa-user"></i></div>
            <div>
                <h1>User Details</h1>
                <div class="rc-subtitle">View profile metrics, roles, and linked details</div>
            </div>
        </div>
        <div class="rc-header-actions">
            <ul class="rc-breadcrumb">
                <li><a href="<?= base_url('dashboard') ?>"><i class="fas fa-home"></i> Dashboard</a></li>
                <li>/</li>
                <li><a href="<?= base_url('users') ?>">Users</a></li>
                <li>/ View</li>
            </ul>
            <a href="<?= base_url('users/edit/' . $user->id) ?>" class="rc-btn-primary text-decoration-none">
                <i class="fas fa-edit"></i> Edit User
            </a>
        </div>
    </div>

    <?php
    $role_color = ['admin' => 'danger', 'vendor' => 'success', 'user' => 'primary'];
    $role_icon  = ['admin' => 'user-shield', 'vendor' => 'store', 'user' => 'user'];
    ?>

    <div class="rc-row">
        <!-- Left: Profile Summary Card -->
        <div class="rc-col-profile">
            <div class="rc-card rc-profile-card">
                <?php if ($user->profile_image): ?>
                    <img src="<?= base_url($user->profile_image) ?>" alt="<?= $user->name ?>" class="rc-avatar-img">
                <?php else: ?>
                    <div class="rc-avatar-fallback"><?= strtoupper(substr($user->name, 0, 1)) ?></div>
                <?php endif; ?>

                <h4><?= $user->name ?></h4>

                <div class="rc-badge-row">
                    <span class="rc-badge bg-<?= $role_color[$user->role] ?>">
                        <i class="fas fa-<?= $role_icon[$user->role] ?>"></i> <?= ucfirst($user->role) ?>
                    </span>
                    <span class="rc-badge bg-<?= $user->is_active ? 'success' : 'danger' ?>">
                        <?= $user->is_active ? 'Active' : 'Inactive' ?>
                    </span>
                </div>

                <div class="rc-meta-list">
                    <div class="rc-meta-item">
                        <i class="fas fa-phone"></i><span><?= $user->mobile ?></span>
                    </div>
                    <?php if ($user->email): ?>
                        <div class="rc-meta-item">
                            <i class="fas fa-envelope"></i><span><?= $user->email ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="rc-meta-item">
                        <i class="fas fa-calendar"></i><span><?= date('d M Y', strtotime($user->created_on)) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Detail Sheets -->
        <div class="rc-col-detail">

            <!-- ADMIN Info Details -->
            <?php if ($user->role == 'admin'): ?>
                <div class="rc-card">
                    <div class="rc-card-header">
                        <i class="fas fa-user-shield"></i>
                        <h3>Admin Information</h3>
                    </div>
                    <div class="rc-info-grid">
                        <div class="rc-info-field">
                            <span class="rc-info-label">Full Name</span>
                            <span class="rc-info-value"><?= $user->name ?></span>
                        </div>
                        <div class="rc-info-field">
                            <span class="rc-info-label">Mobile</span>
                            <span class="rc-info-value"><?= $user->mobile ?></span>
                        </div>
                        <div class="rc-info-field">
                            <span class="rc-info-label">Email</span>
                            <span class="rc-info-value"><?= $user->email ?: '-' ?></span>
                        </div>
                        <div class="rc-info-field">
                            <span class="rc-info-label">Joined On</span>
                            <span class="rc-info-value"><?= date('d M Y, h:i A', strtotime($user->created_on)) ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- USER Info Details -->
            <?php if ($user->role == 'user'): ?>
                <div class="rc-card">
                    <div class="rc-card-header">
                        <i class="fas fa-user"></i>
                        <h3>User Information</h3>
                    </div>
                    <div class="rc-info-grid">
                        <div class="rc-info-field">
                            <span class="rc-info-label">Full Name</span>
                            <span class="rc-info-value"><?= $user->name ?></span>
                        </div>
                        <div class="rc-info-field">
                            <span class="rc-info-label">Mobile</span>
                            <span class="rc-info-value"><?= $user->mobile ?></span>
                        </div>
                        <div class="rc-info-field">
                            <span class="rc-info-label">Email</span>
                            <span class="rc-info-value"><?= $user->email ?: '-' ?></span>
                        </div>
                        <div class="rc-info-field rc-info-field--full">
                            <span class="rc-info-label">Address</span>
                            <span class="rc-info-value"><?= $user->address ?: '-' ?></span>
                        </div>
                        <div class="rc-info-field">
                            <span class="rc-info-label">Joined On</span>
                            <span class="rc-info-value"><?= date('d M Y, h:i A', strtotime($user->created_on)) ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- VENDOR Info Details (photo merged into the same card) -->
            <?php if ($user->role == 'vendor'): ?>
                <div class="rc-card rc-store-card">
                    <?php if (!empty($user->store_photo)): ?>
                        <div class="rc-store-banner">
                            <img src="<?= base_url($user->store_photo) ?>" alt="Store Photo">
                        </div>
                    <?php endif; ?>

                    <div class="rc-card-header">
                        <i class="fas fa-info-circle"></i>
                        <h3>Store Information</h3>
                    </div>

                    <div class="rc-info-grid">
                        <div class="rc-info-field">
                            <span class="rc-info-label">Store Name</span>
                            <span class="rc-info-value"><?= $user->store_name ?: '-' ?></span>
                        </div>
                        <div class="rc-info-field">
                            <span class="rc-info-label">Owner Name</span>
                            <span class="rc-info-value"><?= $user->owner_name ?: '-' ?></span>
                        </div>
                        <div class="rc-info-field">
                            <span class="rc-info-label">Mobile</span>
                            <span class="rc-info-value"><?= $user->mobile ?></span>
                        </div>
                        <div class="rc-info-field">
                            <span class="rc-info-label">Contact Number</span>
                            <span class="rc-info-value"><?= $user->contact_number ?: '-' ?></span>
                        </div>
                        <div class="rc-info-field">
                            <span class="rc-info-label">Email</span>
                            <span class="rc-info-value"><?= $user->email ?: '-' ?></span>
                        </div>
                        <div class="rc-info-field">
                            <span class="rc-info-label">GST Number</span>
                            <span class="rc-info-value"><?= $user->gst_number ?: '-' ?></span>
                        </div>
                        <div class="rc-info-field rc-info-field--full">
                            <span class="rc-info-label">Address</span>
                            <span class="rc-info-value"><?= $user->address ?: '-' ?></span>
                        </div>
                        <div class="rc-info-field">
                            <span class="rc-info-label">Opening Time</span>
                            <span class="rc-info-value"><?= $user->opening_time ? date('h:i A', strtotime($user->opening_time)) : '-' ?></span>
                        </div>
                        <div class="rc-info-field">
                            <span class="rc-info-label">Closing Time</span>
                            <span class="rc-info-value"><?= $user->closing_time ? date('h:i A', strtotime($user->closing_time)) : '-' ?></span>
                        </div>
                        <div class="rc-info-field">
                            <span class="rc-info-label">Joined On</span>
                            <span class="rc-info-value"><?= date('d M Y, h:i A', strtotime($user->created_on)) ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Vendor Products (Full Width Row, becomes stacked cards on mobile) -->
    <?php if ($user->role == 'vendor'): ?>
        <div class="rc-card">
            <div class="rc-card-header">
                <i class="fas fa-box"></i>
                <h3>Products <span class="rc-products-count">(<?= count($vendor_products ?? []) ?>)</span></h3>
            </div>

            <div class="rc-table-wrap">
                <table class="rc-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>MRP</th>
                            <th>Selling Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="rc-products-tbody">
                        <?php if (!empty($vendor_products)): ?>
                            <?php foreach ($vendor_products as $vp): ?>
                                <?php $img = $vp->product_id ? $vp->admin_image : $vp->image; ?>
                                <tr class="rc-product-row">
                                    <td class="rc-td-image" data-label="Image">
                                        <?php if ($img): ?>
                                            <img src="<?= base_url($img) ?>" alt="" class="rc-prod-thumb">
                                        <?php else: ?>
                                            <div class="rc-prod-thumb-fallback"><i class="fas fa-image"></i></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="rc-td-product" data-label="Product">
                                        <strong><?= htmlspecialchars($vp->product_name, ENT_QUOTES, 'UTF-8') ?></strong>
                                        <?php if ($vp->brand): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($vp->brand, ENT_QUOTES, 'UTF-8') ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Category">
                                        <span class="rc-chip category"><?= htmlspecialchars($vp->category_name ?: '-', ENT_QUOTES, 'UTF-8') ?></span>
                                    </td>
                                    <td class="rc-price-mrp" data-label="MRP">₹<?= number_format($vp->mrp, 2) ?></td>
                                    <td class="rc-price-sell" data-label="Selling Price">₹<?= number_format($vp->selling_price, 2) ?></td>
                                    <td data-label="Stock"><span class="rc-chip stock"><?= $vp->stock ?></span></td>
                                    <td data-label="Status">
                                        <span class="rc-chip <?= $vp->is_active ? 'active' : 'inactive' ?>">
                                            <?= $vp->is_active ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">
                                    <div class="rc-empty-state">
                                        <i class="fas fa-inbox"></i>
                                        No products added yet
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="rc-table-footer" id="rc-products-footer">
                <div class="rc-pg-info" id="rc-pg-info"></div>
                <div class="rc-pagination" id="rc-pagination"></div>
            </div>
        </div>
    <?php endif; ?>

</div>

<script>
    (function() {
        var perPage = 10;
        var tbody = document.getElementById('rc-products-tbody');
        if (!tbody) return;

        var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr.rc-product-row'));
        var total = rows.length;
        if (total <= perPage) return; // fits on one screen, no pagination needed

        var totalPages = Math.ceil(total / perPage);
        var currentPage = 1;

        var footer = document.getElementById('rc-products-footer');
        var infoEl = document.getElementById('rc-pg-info');
        var pagEl = document.getElementById('rc-pagination');
        footer.style.display = 'flex';

        function pageList(current, pages) {
            var delta = 1,
                range = [],
                withDots = [],
                last;
            for (var i = 1; i <= pages; i++) {
                if (i === 1 || i === pages || (i >= current - delta && i <= current + delta)) {
                    range.push(i);
                }
            }
            range.forEach(function(i) {
                if (last) {
                    if (i - last === 2) withDots.push(last + 1);
                    else if (i - last > 2) withDots.push('...');
                }
                withDots.push(i);
                last = i;
            });
            return withDots;
        }

        function renderRows() {
            rows.forEach(function(row, idx) {
                var page = Math.floor(idx / perPage) + 1;
                row.style.display = (page === currentPage) ? '' : 'none';
            });
            var start = (currentPage - 1) * perPage + 1;
            var end = Math.min(currentPage * perPage, total);
            infoEl.textContent = 'Showing ' + start + '\u2013' + end + ' of ' + total + ' products';
        }

        function renderPagination() {
            pagEl.innerHTML = '';

            var prev = document.createElement('button');
            prev.type = 'button';
            prev.className = 'rc-pg-btn rc-pg-arrow';
            prev.innerHTML = '<i class="fas fa-chevron-left"></i>';
            prev.disabled = currentPage === 1;
            prev.addEventListener('click', function() {
                goTo(currentPage - 1);
            });
            pagEl.appendChild(prev);

            pageList(currentPage, totalPages).forEach(function(p) {
                if (p === '...') {
                    var span = document.createElement('span');
                    span.className = 'rc-pg-ellipsis';
                    span.textContent = '...';
                    pagEl.appendChild(span);
                } else {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'rc-pg-btn' + (p === currentPage ? ' active' : '');
                    btn.textContent = p;
                    btn.addEventListener('click', function() {
                        goTo(p);
                    });
                    pagEl.appendChild(btn);
                }
            });

            var next = document.createElement('button');
            next.type = 'button';
            next.className = 'rc-pg-btn rc-pg-arrow';
            next.innerHTML = '<i class="fas fa-chevron-right"></i>';
            next.disabled = currentPage === totalPages;
            next.addEventListener('click', function() {
                goTo(currentPage + 1);
            });
            pagEl.appendChild(next);
        }

        function goTo(page) {
            if (page < 1 || page > totalPages || page === currentPage) return;
            currentPage = page;
            renderRows();
            renderPagination();
        }

        renderRows();
        renderPagination();
    })();
</script>