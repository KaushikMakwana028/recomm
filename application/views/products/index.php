<style>
    /* Styling variables and custom elements for products */
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
        --rc-red-700: #842029;

        --rc-radius-lg: 20px;
        --rc-radius-md: 14px;
        --rc-radius-sm: 10px;
        --rc-shadow: 0 6px 24px rgba(11, 61, 41, 0.08);
        --rc-shadow-hover: 0 12px 32px rgba(11, 61, 41, 0.14);
    }

    .rc-products-container {
        font-family: 'Inter', 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
        color: var(--rc-ink);
        padding-bottom: 2rem;
    }

    .rc-products-container h1,
    .rc-products-container h2,
    .rc-products-container h3,
    .rc-products-container h4,
    .rc-products-container h5,
    .rc-products-container h6 {
        font-family: 'Poppins', 'Inter', sans-serif;
        color: var(--rc-forest-900);
    }

    /* ---------- Breadcrumbs & Header ---------- */
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

    /* ---------- Buttons ---------- */
    .rc-btn-primary {
        background: linear-gradient(135deg, var(--rc-green-600), var(--rc-green-500));
        color: #fff;
        border: none;
        border-radius: var(--rc-radius-md);
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        font-size: 0.95rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 12px rgba(22, 121, 79, 0.2);
        transition: all 0.2s ease;
        cursor: pointer;
        text-decoration: none;
    }

    .rc-btn-primary:hover {
        background: linear-gradient(135deg, var(--rc-forest-800), var(--rc-green-600));
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(22, 121, 79, 0.3);
        color: #fff;
    }

    .rc-btn-primary:disabled {
        opacity: .55;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
    }

    .rc-btn-outline-primary {
        border: 1.5px solid var(--rc-green-600);
        color: var(--rc-green-600);
        background: transparent;
        border-radius: var(--rc-radius-md);
        padding: 0.7rem 1.4rem;
        font-weight: 600;
        font-size: 0.95rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
        cursor: pointer;
        text-decoration: none;
    }

    .rc-btn-outline-primary:hover {
        background: rgba(22, 121, 79, 0.05);
        color: var(--rc-forest-800);
        border-color: var(--rc-forest-800);
    }

    /* ---------- Cards ---------- */
    .rc-card {
        background: var(--rc-white);
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-lg);
        box-shadow: var(--rc-shadow);
        padding: 2rem;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .rc-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 1.75rem;
        border-bottom: 1px solid var(--rc-line);
        padding-bottom: 1.25rem;
        flex-wrap: wrap;
    }

    .rc-card-title-group {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .rc-card-header h3 {
        font-size: 1.15rem;
        font-weight: 700;
        margin: 0;
    }

    .rc-card-header i.header-icon {
        color: var(--rc-green-600);
        font-size: 1.25rem;
    }

    /* ---------- Tables & Lists ---------- */
    .table-responsive {
        border-radius: var(--rc-radius-md);
        border: 1px solid var(--rc-line);
    }

    .table thead th {
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--rc-muted);
        font-weight: 700;
        border-bottom: 1px solid var(--rc-line);
        padding: 0.85rem 1rem;
        background-color: var(--rc-bg-tint, #f9fbfb);
    }

    .table tbody td {
        padding: 1rem;
        border-bottom: 1px solid var(--rc-line);
        color: var(--rc-ink);
        font-size: 0.9rem;
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    .table tbody tr {
        transition: background .15s ease;
    }

    .table tbody tr:hover {
        background: rgba(46, 204, 113, .04) !important;
    }

    /* Rounded images in table */
    .table img.img-thumbnail {
        border-radius: var(--rc-radius-sm);
        border: 1px solid var(--rc-line);
        padding: 0;
        box-shadow: 0 2px 6px rgba(11, 61, 41, 0.05);
    }

    /* Status switch colors */
    .form-check-input:checked {
        background-color: var(--rc-green-500);
        border-color: var(--rc-green-500);
    }

    .form-switch .form-check-input {
        cursor: pointer;
    }

    /* ---------- Table Action Buttons ---------- */
    .rc-btn-icon {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        border: 1px solid transparent;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .8rem;
        cursor: pointer;
        transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
        text-decoration: none;
        flex-shrink: 0;
        box-shadow: 0 1px 2px rgba(11, 61, 41, .06);
    }

    .rc-btn-icon:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px -4px rgba(11, 61, 41, .35);
    }

    .rc-btn-icon.edit {
        background: rgba(242, 169, 59, .15);
        color: #a4680f;
    }

    .rc-btn-icon.edit:hover {
        border-color: #a4680f;
    }

    .rc-btn-icon.delete {
        background: rgba(220, 53, 69, .1);
        color: #c0293a;
    }

    .rc-btn-icon.delete:hover {
        border-color: #c0293a;
    }

    /* ---------- Search & Filters ---------- */
    .rc-select-input {
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-md);
        padding: 0.5rem 1.8rem 0.5rem 1rem;
        font-size: 0.85rem;
        color: var(--rc-ink);
        background-color: var(--rc-bg-tint, #f9fbfb);
        transition: all 0.2s ease;
        outline: none;
        cursor: pointer;
    }

    .rc-select-input:focus {
        border-color: var(--rc-green-500);
        box-shadow: 0 0 0 4px rgba(31, 157, 99, 0.1);
        background-color: #fff;
    }

    .rc-search-input {
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-md);
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
        color: var(--rc-ink);
        background-color: var(--rc-bg-tint, #f9fbfb);
        transition: all 0.2s ease;
        outline: none;
    }

    .rc-search-input:focus {
        border-color: var(--rc-green-500);
        box-shadow: 0 0 0 4px rgba(31, 157, 99, 0.1);
        background-color: #fff;
    }

    /* ---------- Pagination ---------- */
    .pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .4rem;
        list-style: none;
        margin: 1.5rem 0 0;
        padding: 0;
        flex-wrap: wrap;
    }

    .pagination .page-link {
        min-width: 36px;
        height: 36px;
        padding: 0 .75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        border: 1px solid var(--rc-line) !important;
        background: var(--rc-white) !important;
        color: var(--rc-ink) !important;
        font-size: .82rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all .15s ease;
    }

    .pagination .page-link:hover {
        border-color: var(--rc-green-500) !important;
        color: var(--rc-green-600) !important;
    }

    .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, var(--rc-forest-800), var(--rc-green-500)) !important;
        border-color: transparent !important;
        color: #fff !important;
        box-shadow: 0 6px 14px -4px rgba(15, 92, 62, .5);
    }

    .pagination .page-item.disabled .page-link {
        opacity: .45;
        cursor: not-allowed;
        pointer-events: none;
    }

    /* ---------- Modal customization ---------- */
    .modal-content {
        border-radius: var(--rc-radius-lg);
        border: 1px solid var(--rc-line);
        box-shadow: 0 10px 40px rgba(11, 61, 41, 0.15);
    }

    .modal-header {
        border-bottom: 1px solid var(--rc-line);
        padding: 1.5rem;
    }

    .modal-header h5 {
        font-weight: 700;
        color: var(--rc-forest-900);
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        border-top: 1px solid var(--rc-line);
        padding: 1.25rem 1.5rem;
    }

    #bulkDropZone {
        border: 2px dashed var(--rc-line) !important;
        border-radius: var(--rc-radius-lg);
        background: var(--rc-bg-tint);
        transition: all 0.2s ease;
        cursor: pointer;
    }

    #bulkDropZone.border-primary {
        border-color: var(--rc-green-500) !important;
        background-color: rgba(46, 204, 113, 0.05);
    }

    /* ============================================================
       ENHANCED: Download Template card
       ============================================================ */
    .rc-template-card {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        background: linear-gradient(135deg, rgba(31, 157, 99, 0.05), rgba(242, 169, 59, 0.05));
        border: 1px solid rgba(31, 157, 99, 0.16);
        border-radius: var(--rc-radius-lg);
        padding: 1.1rem 1.3rem;
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .rc-template-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -8%;
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(46, 204, 113, .14), transparent 70%);
        pointer-events: none;
    }

    .rc-template-left {
        display: flex;
        align-items: center;
        gap: .9rem;
        position: relative;
        z-index: 1;
        min-width: 0;
    }

    .rc-template-icon-box {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--rc-green-600), var(--rc-emerald-400));
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
        box-shadow: 0 6px 14px rgba(22, 121, 79, 0.28);
    }

    .rc-template-card h6 {
        margin: 0;
        font-weight: 700;
        color: var(--rc-forest-900);
        font-size: 0.92rem;
    }

    .rc-template-card .rc-template-sub {
        font-size: 0.78rem;
        color: var(--rc-muted);
        margin-top: 1px;
    }

    .rc-btn-download {
        position: relative;
        z-index: 1;
        background: linear-gradient(135deg, var(--rc-green-600), var(--rc-green-500));
        color: #fff !important;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.82rem;
        padding: 0.6rem 1.1rem;
        box-shadow: 0 4px 10px rgba(22, 121, 79, 0.2);
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .rc-btn-download:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(22, 121, 79, 0.28) !important;
        background: linear-gradient(135deg, var(--rc-forest-800), var(--rc-green-600)) !important;
        color: #fff !important;
    }

    /* ============================================================
       ENHANCED: Selected file chip (replaces bulkFileNameContainer)
       ============================================================ */
    .rc-file-chip {
        display: flex;
        align-items: center;
        gap: .8rem;
        background: #fff;
        border: 1px solid rgba(31, 157, 99, 0.25);
        border-radius: var(--rc-radius-md);
        padding: .75rem .9rem;
        box-shadow: 0 4px 14px rgba(11, 61, 41, .06);
        text-align: left;
    }

    .rc-file-chip-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: rgba(31, 157, 99, .1);
        color: var(--rc-green-600);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        flex-shrink: 0;
    }

    .rc-file-chip-info {
        flex: 1;
        min-width: 0;
    }

    .rc-file-chip-name {
        display: block;
        font-weight: 600;
        font-size: 0.87rem;
        color: var(--rc-ink);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .rc-file-chip-meta {
        display: flex;
        align-items: center;
        gap: .4rem;
        font-size: 0.72rem;
        color: var(--rc-muted);
        margin-top: 2px;
    }

    .rc-file-chip-meta .dot {
        width: 3px;
        height: 3px;
        border-radius: 50%;
        background: var(--rc-muted);
        display: inline-block;
    }

    .rc-file-chip-meta .ready {
        color: var(--rc-green-600);
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: .3rem;
    }

    .rc-file-chip-remove {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: none;
        background: rgba(220, 53, 69, .08);
        color: var(--rc-red-600);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        font-size: 0.78rem;
        transition: all .18s ease;
        opacity: 1 !important;
    }

    .rc-file-chip-remove:hover {
        background: var(--rc-red-600);
        color: #fff;
        transform: rotate(90deg);
    }

    /* ============================================================
       ENHANCED: Bulk upload error panel (replaces plain alert-danger)
       ============================================================ */
    .rc-error-panel {
        border-radius: var(--rc-radius-lg);
        border: 1px solid rgba(220, 53, 69, .18);
        background: #fff;
        overflow: hidden;
        box-shadow: 0 6px 20px rgba(220, 53, 69, .06);
    }

    .rc-error-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .95rem 1.15rem;
        background: rgba(220, 53, 69, .05);
        border-bottom: 1px solid rgba(220, 53, 69, .12);
    }

    .rc-error-title {
        display: flex;
        align-items: center;
        gap: .65rem;
        min-width: 0;
    }

    .rc-error-icon-box {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        background: rgba(220, 53, 69, .12);
        color: var(--rc-red-600);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .95rem;
        flex-shrink: 0;
    }

    .rc-error-title strong {
        color: var(--rc-red-700);
        font-size: 0.9rem;
        display: block;
        line-height: 1.2;
    }

    .rc-error-title span.rc-error-sub {
        color: var(--rc-muted);
        font-size: 0.72rem;
        font-weight: 500;
    }

    .rc-error-badge-count {
        background: var(--rc-red-600);
        color: #fff;
        font-size: 0.72rem;
        font-weight: 700;
        padding: .35rem .75rem;
        border-radius: 999px;
        box-shadow: 0 2px 8px rgba(220, 53, 69, .25);
        white-space: nowrap;
        flex-shrink: 0;
    }

    .rc-error-body {
        max-height: 220px;
        overflow-y: auto;
        padding: .25rem 1.15rem;
    }

    .rc-error-row {
        display: flex;
        gap: .65rem;
        padding: .7rem 0;
        border-bottom: 1px dashed rgba(220, 53, 69, .14);
    }

    .rc-error-row:last-child {
        border-bottom: none;
    }

    .rc-error-row-icon {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: rgba(220, 53, 69, .12);
        color: var(--rc-red-600);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .6rem;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .rc-error-row-content {
        font-size: 0.82rem;
        color: #5f2229;
        line-height: 1.55;
    }

    .rc-error-row-content .rc-error-row-title {
        color: var(--rc-red-700);
        font-weight: 700;
    }

    .rc-error-row-content .rc-error-product {
        color: var(--rc-muted);
        font-weight: 500;
    }

    .rc-error-body::-webkit-scrollbar {
        width: 6px;
    }

    .rc-error-body::-webkit-scrollbar-track {
        background: rgba(220, 53, 69, 0.03);
        border-radius: 4px;
    }

    .rc-error-body::-webkit-scrollbar-thumb {
        background: rgba(220, 53, 69, 0.18);
        border-radius: 4px;
    }

    .rc-error-body::-webkit-scrollbar-thumb:hover {
        background: rgba(220, 53, 69, 0.32);
    }

    /* Generic success alert used inside the modal result area */
    .rc-success-panel {
        display: flex;
        align-items: center;
        gap: .75rem;
        border-radius: var(--rc-radius-md);
        border: 1px solid rgba(31, 157, 99, .25);
        background: rgba(31, 157, 99, .06);
        padding: .9rem 1.1rem;
        color: var(--rc-forest-900);
        font-size: .88rem;
        font-weight: 600;
    }

    .rc-success-panel i {
        color: var(--rc-green-600);
        font-size: 1.1rem;
        flex-shrink: 0;
    }
</style>

<div class="rc-products-container">

    <!-- Page Header -->
    <div class="rc-page-header">
        <div class="rc-header-left">
            <div class="rc-header-badge"><i class="fas fa-box"></i></div>
            <div>
                <h1>Products</h1>
                <div class="rc-subtitle">Manage store inventory, prices, and categories</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <ul class="rc-breadcrumb d-none d-lg-flex mb-0 me-2">
                <li><a href="<?= base_url('dashboard') ?>"><i class="fas fa-home"></i> Dashboard</a></li>
                <li>&nbsp;/&nbsp;Products</li>
            </ul>
            <button type="button" class="rc-btn-outline-primary" data-bs-toggle="modal" data-bs-target="#bulkAddModal">
                <i class="fas fa-file-upload"></i> Bulk Add
            </button>
            <a href="<?= base_url('products/add') ?>" class="rc-btn-primary text-decoration-none">
                <i class="fas fa-plus"></i> Add Product
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: var(--rc-radius-md);">
            <i class="fas fa-check-circle me-2"></i><?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: var(--rc-radius-md);">
            <i class="fas fa-exclamation-circle me-2"></i><?= $this->session->flashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Products List Card -->
    <div class="rc-card">
        <div class="rc-card-header">
            <div class="rc-card-title-group">
                <i class="fas fa-table header-icon"></i>
                <h3>All Products</h3>
                <span id="recordCount" class="badge bg-success ms-2" style="font-size: 0.75rem; border-radius: 50px; font-weight: 700;">0</span>
            </div>

            <div class="d-flex gap-2 align-items-center flex-wrap">
                <!-- Category Filter -->
                <select id="categoryFilter" class="rc-select-input form-select-sm">
                    <option value="">All Categories</option>
                    <?php
                    $CI = &get_instance();
                    $categories = $CI->db
                        ->select('categories.id, categories.name')
                        ->from('categories')
                        ->join('products', 'products.category_id = categories.id', 'inner')
                        ->where('categories.is_active', 1)
                        ->group_by('categories.id')
                        ->order_by('categories.name', 'ASC')
                        ->get()
                        ->result();
                    foreach ($categories as $cat):
                    ?>
                        <option value="<?= $cat->id ?>"><?= htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>

                <!-- Search Input -->
                <div class="input-group input-group-sm" style="width: 260px;">
                    <span class="input-group-text" style="background: var(--rc-bg-tint); border-color: var(--rc-line); border-radius: var(--rc-radius-md) 0 0 var(--rc-radius-md);">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text"
                        id="searchInput"
                        class="rc-search-input py-1"
                        placeholder="Search by name or SKU..."
                        autocomplete="off"
                        style="border-top-left-radius: 0; border-bottom-left-radius: 0; font-size: 0.85rem; width: auto; flex: 1;">
                </div>

                <!-- Clear Filters Button -->
                <button type="button" id="clearFilters" class="btn btn-sm btn-secondary" style="display: none; border-radius: var(--rc-radius-md);">
                    <i class="fas fa-times"></i> Clear
                </button>
            </div>
        </div>

        <!-- Loading Spinner -->
        <div id="loadingSpinner" class="text-center py-5" style="display: none;">
            <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-2">Loading products...</p>
        </div>

        <!-- Table -->
        <div class="table-responsive" id="productsTableContainer">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="60">#</th>
                        <th width="90">Image</th>
                        <th>Product Details</th>
                        <th>Category</th>
                        <th>Price Details</th>
                        <th width="100">Status</th>
                        <th width="140" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="productsTableBody">
                    <!-- Loaded via AJAX -->
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div id="paginationContainer" class="mt-3"></div>

        <!-- Results Info -->
        <div id="resultsInfo" class="text-center text-muted mt-3" style="font-size: 0.85rem;"></div>
    </div>

</div>

<!-- Delete Form -->
<form id="deleteForm" method="POST" style="display: none;">
</form>

<!-- Bulk Add Modal -->
<div class="modal fade" id="bulkAddModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-upload text-success me-2"></i>Bulk Add Products</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Guidelines Banner -->
                <div class="rc-info-banner p-3 mb-4" style="background: rgba(31, 157, 99, 0.04); border: 1px solid rgba(31, 157, 99, 0.12); border-radius: var(--rc-radius-md);">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fas fa-info-circle text-success" style="font-size: 1.1rem; color: var(--rc-green-600) !important;"></i>
                        <h6 class="mb-0 fw-bold" style="color: var(--rc-forest-900); font-size: 0.92rem;">Bulk Upload Guidelines</h6>
                    </div>
                    <div class="row g-2 mt-1" style="font-size: 0.82rem;">
                        <div class="col-12 col-md-6">
                            <span class="badge mb-1 px-2 py-1" style="font-size: 0.72rem; background: rgba(31,157,99,0.1); color: var(--rc-green-600); font-weight:600;">REQUIRED COLUMNS</span>
                            <div class="text-muted" style="line-height: 1.4; padding-left: 2px;">
                                Product Name, Category, Regular Price, Sale Price, Main Image (HTTPS URL)
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <span class="badge mb-1 px-2 py-1" style="font-size: 0.72rem; background: rgba(0,0,0,0.06); color: var(--rc-muted); font-weight:600;">OPTIONAL COLUMNS</span>
                            <div class="text-muted" style="line-height: 1.4; padding-left: 2px;">
                                SKU, Description, Gallery Images
                            </div>
                        </div>
                        <div class="col-12 mt-2 pt-2 border-top" style="border-color: rgba(31, 157, 99, 0.08) !important; font-size: 0.8rem; color: var(--rc-muted);">
                            <i class="fas fa-images me-1 text-success"></i> Images must be public HTTPS links (max 1MB each). If any row fails validation, no products will be added.
                        </div>
                    </div>
                </div>

                <!-- Download Template Card (ENHANCED) -->
                <div class="rc-template-card">
                    <div class="rc-template-left">
                        <div class="rc-template-icon-box"><i class="fas fa-file-csv"></i></div>
                        <div>
                            <h6>Ready-to-fill template</h6>
                            <div class="rc-template-sub">Download the standard template format.</div>
                        </div>
                    </div>
                    <a href="<?= base_url('products/download_template') ?>" class="rc-btn-download text-decoration-none">
                        <i class="fas fa-download"></i> Download Template
                    </a>
                </div>

                <!-- Dropzone -->
                <div id="bulkDropZone" class="p-4 text-center cursor-pointer" onclick="document.getElementById('bulkFileInput').click();">
                    <input type="file" id="bulkFileInput" accept=".csv,.xlsx,.xls" style="display: none;">
                    <div class="rc-drop-content py-3">
                        <i class="fas fa-cloud-upload-alt fa-3x text-success mb-3 d-block" style="opacity: 0.85;"></i>
                        <h6 class="mb-1 fw-bold" style="color: var(--rc-forest-900);">Drag & drop your file here</h6>
                        <p class="text-muted small mb-3">Supports CSV, XLSX or XLS up to 5MB</p>
                        <button type="button" class="btn btn-sm btn-outline-success px-3 py-1.5" style="border-radius: 8px; font-weight: 600; font-size: 0.82rem;">
                            <i class="fas fa-folder-open me-1"></i> Browse Files
                        </button>
                    </div>

                    <!-- Selected file chip (ENHANCED, replaces old plain bar) -->
                    <div id="bulkFileNameContainer" class="rc-file-chip mt-2" style="display: none;" onclick="event.stopPropagation();">
                        <div class="rc-file-chip-icon"><i class="fas fa-file-excel"></i></div>
                        <div class="rc-file-chip-info">
                            <span id="bulkFileName" class="rc-file-chip-name"></span>
                            <div class="rc-file-chip-meta">
                                <span id="bulkFileSize"></span>
                                <span class="dot"></span>
                                <span class="ready"><i class="fas fa-check-circle"></i> Ready to upload</span>
                            </div>
                        </div>
                        <button type="button" id="removeBulkFile" class="rc-file-chip-remove" title="Remove file" onclick="event.stopPropagation(); clearSelectedBulkFile();">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <div id="bulkUploadResult" class="mt-3" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="rc-btn-secondary" data-bs-dismiss="modal" style="padding: 0.55rem 1.3rem;">Close</button>
                <button type="button" id="bulkUploadBtn" class="rc-btn-primary" disabled style="padding: 0.55rem 1.3rem;">
                    <i class="fas fa-upload"></i> Upload & Add Products
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function() {
        let currentPage = 1;
        let searchQuery = '';
        let categoryId = '';
        let searchTimeout = null;

        // Initial load
        loadProducts();

        // Search with debounce
        $('#searchInput').on('keyup', function() {
            clearTimeout(searchTimeout);
            const value = $(this).val().trim();
            searchTimeout = setTimeout(function() {
                searchQuery = value;
                currentPage = 1;
                loadProducts();
                updateClearButton();
            }, 500);
        });

        // Category filter
        $('#categoryFilter').on('change', function() {
            categoryId = $(this).val();
            currentPage = 1;
            loadProducts();
            updateClearButton();
        });

        // Clear filters
        $('#clearFilters').on('click', function() {
            $('#searchInput').val('');
            $('#categoryFilter').val('');
            searchQuery = '';
            categoryId = '';
            currentPage = 1;
            $(this).hide();
            loadProducts();
        });

        // Pagination click
        $(document).on('click', '.pagination a.page-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            if (page) {
                currentPage = page;
                loadProducts();
                $('html, body').animate({
                    scrollTop: $('#productsTableContainer').offset().top - 120
                }, 200);
            }
        });

        // Load products
        function loadProducts() {
            showLoading();

            $.ajax({
                url: '<?= base_url("products/get_products") ?>',
                type: 'POST',
                data: {
                    page: currentPage,
                    search: searchQuery,
                    category_id: categoryId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        $('#productsTableBody').html(response.html);
                        $('#paginationContainer').html(response.pagination);
                        $('#recordCount').text(response.total_records);

                        // Results info
                        if (response.total_records > 0) {
                            const start = ((response.current_page - 1) * 10) + 1;
                            const end = Math.min(response.current_page * 10, response.total_records);
                            let info = `Showing ${start} to ${end} of ${response.total_records} entries`;

                            let filters = [];
                            if (searchQuery) filters.push(`search: "${searchQuery}"`);
                            if (categoryId) filters.push(`category filter applied`);
                            if (filters.length) info += ` (filtered by ${filters.join(', ')})`;

                            $('#resultsInfo').text(info);
                        } else {
                            $('#resultsInfo').text('No records found');
                        }
                    } else {
                        showError('Failed to load products');
                    }
                },
                error: function(xhr) {
                    console.error('AJAX Error:', xhr);
                    showError('Something went wrong. Please refresh the page.');
                },
                complete: function() {
                    hideLoading();
                }
            });
        }

        // Toggle status
        $(document).on('change', '.status-toggle', function() {
            const toggle = $(this);
            const id = toggle.data('id');
            const status = toggle.is(':checked') ? 1 : 0;

            toggle.prop('disabled', true);

            $.ajax({
                url: '<?= base_url("products/toggle_status") ?>',
                type: 'POST',
                data: {
                    id: id,
                    status: status
                },
                dataType: 'json',
                success: function(response) {
                    if (!response.status) {
                        alert(response.message || 'Failed to update status');
                        toggle.prop('checked', !status);
                    }
                },
                error: function() {
                    alert('Something went wrong. Please try again.');
                    toggle.prop('checked', !status);
                },
                complete: function() {
                    toggle.prop('disabled', false);
                }
            });
        });

        // Delete product
        $(document).on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');

            if (confirm(`Are you sure you want to delete "${name}"?\n\nThis action cannot be undone.`)) {
                $('#deleteForm').attr('action', '<?= base_url("products/delete/") ?>' + id).submit();
            }
        });

        // Helpers
        function updateClearButton() {
            (searchQuery || categoryId) ? $('#clearFilters').show(): $('#clearFilters').hide();
        }

        // Show/Hide Loading
        function showLoading() {
            $('#loadingSpinner').show();
            $('#productsTableContainer').css('opacity', '0.4');
        }

        function hideLoading() {
            $('#loadingSpinner').hide();
            $('#productsTableContainer').css('opacity', '1');
        }

        function showError(message) {
            $('#productsTableBody').html(`
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3 d-block"></i>
                        <p class="text-danger">${message}</p>
                        <button class="rc-btn-primary" onclick="location.reload()">
                            <i class="fas fa-sync"></i> Reload Page
                        </button>
                    </td>
                </tr>
            `);
            $('#paginationContainer').html('');
            $('#resultsInfo').text('');
            $('#recordCount').text('0');
        }
    });
</script>

<script>
    $(document).ready(function() {
        let bulkFile = null;

        // Make clearSelectedBulkFile global so inline onclick can use it
        window.clearSelectedBulkFile = function() {
            $('#bulkFileInput').val('');
            setBulkFile(null);
        };

        function formatFileSize(bytes) {
            if (!bytes && bytes !== 0) return '';
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
        }

        function setBulkFile(file) {
            bulkFile = file;
            if (file) {
                $('#bulkFileName').text(file.name).attr('title', file.name);
                $('#bulkFileSize').text(formatFileSize(file.size));
                $('#bulkFileNameContainer').css('display', 'flex');
                $('.rc-drop-content').hide();
                $('#bulkUploadBtn').prop('disabled', false);
            } else {
                $('#bulkFileName').text('');
                $('#bulkFileSize').text('');
                $('#bulkFileNameContainer').hide();
                $('.rc-drop-content').show();
                $('#bulkUploadBtn').prop('disabled', true);
            }
            $('#bulkUploadResult').hide().empty();
        }

        $('#bulkFileInput').on('change', function() {
            setBulkFile(this.files[0] || null);
        });

        const dropZone = document.getElementById('bulkDropZone');
        ['dragenter', 'dragover'].forEach(evt => {
            dropZone.addEventListener(evt, function(e) {
                e.preventDefault();
                dropZone.classList.add('border-primary');
            });
        });
        ['dragleave', 'drop'].forEach(evt => {
            dropZone.addEventListener(evt, function(e) {
                e.preventDefault();
                dropZone.classList.remove('border-primary');
            });
        });
        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            const file = e.dataTransfer.files[0];
            if (file) {
                $('#bulkFileInput')[0].files = e.dataTransfer.files;
                setBulkFile(file);
            }
        });

        $('#bulkAddModal').on('hidden.bs.modal', function() {
            clearSelectedBulkFile();
        });

        $('#bulkUploadBtn').on('click', function() {
            if (!bulkFile) return;

            const btn = $(this);
            const formData = new FormData();
            formData.append('bulk_file', bulkFile);

            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Uploading...');
            $('#bulkUploadResult').hide().empty();

            $.ajax({
                url: '<?= base_url("products/bulk_upload") ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        // ENHANCED success panel
                        $('#bulkUploadResult').html(
                            '<div class="rc-success-panel"><i class="fas fa-check-circle"></i><span>' + response.message + '</span></div>'
                        ).show();
                        setTimeout(function() {
                            $('#bulkAddModal').modal('hide');
                            location.reload();
                        }, 1200);
                    } else if (response.errors && response.errors.length) {
                        let errorCount = response.errors.length;

                        // ENHANCED error rows (icon + bolded row/product + message)
                        let rows = response.errors.map(function(e) {
                            return `
                                <div class="rc-error-row">
                                    <div class="rc-error-row-icon"><i class="fas fa-exclamation"></i></div>
                                    <div class="rc-error-row-content">
                                        <span class="rc-error-row-title">Row ${e.row}</span>
                                        <span class="rc-error-product">(${e.product})</span>
                                        &nbsp;&mdash;&nbsp;${e.errors.join(', ')}
                                    </div>
                                </div>
                            `;
                        }).join('');

                        // ENHANCED error panel (replaces plain alert-danger + <ul>)
                        let errorHtml = `
                            <div class="rc-error-panel">
                                <div class="rc-error-panel-header">
                                    <div class="rc-error-title">
                                        <div class="rc-error-icon-box"><i class="fas fa-exclamation-circle"></i></div>
                                        <div>
                                            <strong>Fix errors below and re-upload</strong><br>
                                            <span class="rc-error-sub">No products were added until these are resolved</span>
                                        </div>
                                    </div>
                                    <span class="rc-error-badge-count">${errorCount} row${errorCount > 1 ? 's' : ''} with errors</span>
                                </div>
                                <div class="rc-error-body">
                                    ${rows}
                                </div>
                            </div>
                        `;
                        $('#bulkUploadResult').html(errorHtml).show();
                    } else {
                        $('#bulkUploadResult').html(
                            '<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-triangle me-2"></i>' + response.message + '</div>'
                        ).show();
                    }
                },
                error: function() {
                    $('#bulkUploadResult').html(
                        '<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Something went wrong. Please try again.</div>'
                    ).show();
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-upload me-2"></i>Upload & Add Products');
                }
            });
        });
    });
</script>