<style>
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

        --rc-radius-lg: 20px;
        --rc-radius-md: 14px;
        --rc-radius-sm: 10px;
        --rc-shadow: 0 6px 24px rgba(11, 61, 41, 0.08);
        --rc-shadow-hover: 0 12px 32px rgba(11, 61, 41, 0.14);
    }

    /* ---------- Page shell ---------- */
    .rc-dash {
        font-family: 'Inter', 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
        color: var(--rc-ink);
        background: var(--rc-bg);
        padding-bottom: 2rem;
    }

    .rc-dash h1,
    .rc-dash h2,
    .rc-dash h3,
    .rc-dash h4,
    .rc-dash h5,
    .rc-dash h6 {
        font-family: 'Poppins', 'Inter', sans-serif;
        color: var(--rc-forest-900);
    }

    /* ---------- Page header ---------- */
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

    .rc-breadcrumb {
        display: flex;
        align-items: center;
        gap: .35rem;
        list-style: none;
        margin: 4px 0 0;
        padding: 0;
        font-size: .8rem;
    }

    .rc-breadcrumb li {
        color: var(--rc-muted);
    }

    .rc-breadcrumb li a {
        color: var(--rc-green-600);
        text-decoration: none;
        font-weight: 600;
    }

    .rc-breadcrumb li a:hover {
        text-decoration: underline;
    }

    .rc-breadcrumb li.active {
        color: var(--rc-ink);
        font-weight: 600;
    }

    .rc-breadcrumb li::after {
        content: "/";
        margin-left: .35rem;
        color: var(--rc-line);
    }

    .rc-breadcrumb li:last-child::after {
        content: "";
    }

    .rc-btn-primary {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        background: linear-gradient(135deg, var(--rc-forest-800), var(--rc-green-500));
        color: #fff;
        border: none;
        font-weight: 600;
        font-size: .88rem;
        padding: .65rem 1.2rem;
        border-radius: 999px;
        text-decoration: none;
        box-shadow: 0 8px 18px -6px rgba(15, 92, 62, .55);
        transition: transform .15s ease, box-shadow .15s ease;
        flex-shrink: 0;
    }

    .rc-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 22px -6px rgba(15, 92, 62, .6);
        color: #fff;
    }

    /* ---------- Stat cards ---------- */
    .rc-stat-card {
        position: relative;
        display: flex;
        align-items: center;
        gap: 1rem;
        background: var(--rc-white);
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-lg);
        padding: 1.1rem 1.25rem;
        height: 100%;
        box-shadow: var(--rc-shadow);
        transition: transform .18s ease, box-shadow .18s ease;
        overflow: hidden;
    }

    .rc-stat-card::after {
        content: "";
        position: absolute;
        inset: auto -30px -30px auto;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: var(--rc-tint, rgba(46, 204, 113, .08));
        z-index: 0;
    }

    .rc-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--rc-shadow-hover);
    }

    .rc-stat-card .rc-icon {
        position: relative;
        z-index: 1;
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.15rem;
        background: var(--rc-grad);
        box-shadow: 0 8px 16px -6px rgba(11, 61, 41, .45);
        flex-shrink: 0;
    }

    .rc-stat-card .rc-stat-info {
        position: relative;
        z-index: 1;
        flex-grow: 1;
        min-width: 0;
    }

    .rc-stat-card h3 {
        font-size: 1.55rem;
        font-weight: 700;
        margin: 0;
        letter-spacing: -.02em;
        line-height: 1.15;
    }

    .rc-stat-card p {
        margin: 0 0 .2rem;
        font-size: .78rem;
        font-weight: 600;
        color: var(--rc-muted);
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .rc-stat-view {
        position: relative;
        z-index: 1;
        border: 1px solid var(--rc-green-500);
        color: var(--rc-green-600);
        background: transparent;
        font-size: .74rem;
        font-weight: 700;
        padding: .3rem .75rem;
        border-radius: 999px;
        text-decoration: none;
        white-space: nowrap;
        transition: all .15s ease;
        flex-shrink: 0;
    }

    .rc-stat-view:hover {
        background: var(--rc-green-500);
        color: #fff;
    }

    .rc-stat-card.tint-admin {
        --rc-grad: linear-gradient(135deg, var(--rc-forest-900), var(--rc-gold-500));
        --rc-tint: rgba(242, 169, 59, .14);
    }

    .rc-stat-card.tint-vendor {
        --rc-grad: linear-gradient(135deg, var(--rc-green-600), var(--rc-lime-300));
        --rc-tint: rgba(46, 204, 113, .1);
    }

    .rc-stat-card.tint-user {
        --rc-grad: linear-gradient(135deg, var(--rc-forest-800), var(--rc-emerald-400));
        --rc-tint: rgba(15, 92, 62, .08);
    }

    /* ---------- Filter / toolbar card ---------- */
    .rc-toolbar {
        background: var(--rc-white);
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-lg);
        box-shadow: var(--rc-shadow);
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .rc-toolbar-title {
        display: flex;
        align-items: center;
        gap: .6rem;
        font-weight: 700;
        font-size: .98rem;
        color: var(--rc-forest-900);
        flex-shrink: 0;
    }

    .rc-toolbar-title i {
        color: var(--rc-green-500);
    }

    .rc-count-badge {
        background: var(--rc-forest-800);
        color: #fff;
        font-size: .72rem;
        font-weight: 700;
        padding: .18rem .55rem;
        border-radius: 999px;
        min-width: 24px;
        text-align: center;
    }

    .rc-toolbar-controls {
        display: flex;
        align-items: center;
        gap: .6rem;
        flex-wrap: wrap;
    }

    .rc-select {
        appearance: none;
        border: 1px solid var(--rc-line);
        background: var(--rc-bg) url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='10' height='6'><path d='M0 0l5 6 5-6z' fill='%235f7c70'/></svg>") no-repeat right 12px center;
        border-radius: 999px;
        padding: .55rem 2rem .55rem 1rem;
        font-size: .85rem;
        font-weight: 600;
        color: var(--rc-ink);
        min-width: 140px;
        transition: border-color .15s ease;
    }

    .rc-select:focus {
        outline: none;
        border-color: var(--rc-green-500);
    }

    .rc-search-wrap {
        position: relative;
        min-width: 240px;
    }

    .rc-search-wrap i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--rc-muted);
        font-size: .82rem;
    }

    .rc-search-input {
        width: 100%;
        border: 1px solid var(--rc-line);
        background: var(--rc-bg);
        border-radius: 999px;
        padding: .55rem 1rem .55rem 2.2rem;
        font-size: .85rem;
        color: var(--rc-ink);
        transition: border-color .15s ease;
    }

    .rc-search-input:focus {
        outline: none;
        border-color: var(--rc-green-500);
        background: #fff;
    }

    .rc-btn-clear {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border: 1px solid var(--rc-line);
        background: var(--rc-white);
        color: var(--rc-muted);
        font-size: .8rem;
        font-weight: 600;
        padding: .5rem .9rem;
        border-radius: 999px;
        cursor: pointer;
        transition: all .15s ease;
    }

    .rc-btn-clear:hover {
        border-color: #dc3545;
        color: #dc3545;
    }

    /* ---------- Table card ---------- */
    .rc-card {
        background: var(--rc-white);
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-lg);
        box-shadow: var(--rc-shadow);
        overflow: hidden;
    }

    .rc-card-body {
        padding: .5rem 1.25rem 1.25rem;
    }

    /* Loading state */
    #loadingSpinner {
        padding: 3rem 1rem;
    }

    #loadingSpinner .rc-spinner {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        border: 4px solid var(--rc-line);
        border-top-color: var(--rc-green-500);
        margin: 0 auto 1rem;
        animation: rc-spin .8s linear infinite;
    }

    @keyframes rc-spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* ---------- Avatars ---------- */
    .rc-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 700;
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        font-size: .9rem;
        background: linear-gradient(135deg, var(--rc-forest-800), var(--rc-emerald-400));
    }

    .rc-avatar.role-vendor {
        background: linear-gradient(135deg, var(--rc-green-600), var(--rc-lime-300));
    }

    .rc-avatar.role-admin {
        background: linear-gradient(135deg, var(--rc-forest-900), var(--rc-gold-500));
    }

    .rc-avatar.role-user {
        background: linear-gradient(135deg, var(--rc-forest-800), var(--rc-emerald-400));
    }

    .rc-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    /* ---------- Status badges (role + status) ---------- */
    .rc-badge {
        font-size: .7rem;
        font-weight: 700;
        padding: .32rem .7rem;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: .03em;
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        white-space: nowrap;
    }

    .rc-badge.role-admin {
        background: rgba(242, 169, 59, .15);
        color: #a4680f;
    }

    .rc-badge.role-vendor {
        background: rgba(15, 92, 62, .12);
        color: var(--rc-forest-700);
    }

    .rc-badge.role-user {
        background: rgba(95, 124, 112, .14);
        color: var(--rc-muted);
    }

    .rc-store-badge {
        background: rgba(46, 204, 113, .12);
        color: var(--rc-green-600);
        font-size: .72rem;
        font-weight: 700;
        padding: .3rem .65rem;
        border-radius: 999px;
        letter-spacing: .01em;
        white-space: nowrap;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .rc-no-store {
        color: var(--rc-muted);
        font-size: .8rem;
    }

    /* ---------- Toggle switch ---------- */
    .rc-toggle {
        position: relative;
        display: inline-block;
        width: 42px;
        height: 24px;
        flex-shrink: 0;
    }

    .rc-toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .rc-toggle .rc-slider {
        position: absolute;
        cursor: pointer;
        inset: 0;
        background: #d8e2dd;
        border-radius: 999px;
        transition: background .2s ease;
    }

    .rc-toggle .rc-slider::before {
        content: "";
        position: absolute;
        width: 18px;
        height: 18px;
        left: 3px;
        top: 3px;
        background: #fff;
        border-radius: 50%;
        transition: transform .2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .25);
    }

    .rc-toggle input:checked+.rc-slider {
        background: linear-gradient(135deg, var(--rc-forest-800), var(--rc-green-500));
    }

    .rc-toggle input:checked+.rc-slider::before {
        transform: translateX(18px);
    }

    .rc-toggle input:disabled+.rc-slider {
        opacity: .5;
        cursor: not-allowed;
    }

    /* ---------- Action buttons ---------- */
    .rc-actions {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        flex-wrap: wrap;
        padding: .3rem;
    }

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

    .rc-btn-icon.view {
        background: rgba(15, 92, 62, .1);
        color: var(--rc-forest-700);
    }

    .rc-btn-icon.view:hover {
        border-color: var(--rc-forest-700);
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

    .rc-btn-icon.store {
        background: rgba(46, 204, 113, .12);
        color: var(--rc-green-600);
    }

    .rc-btn-icon.store:hover {
        border-color: var(--rc-green-600);
    }

    /* ---------- Table (desktop) ---------- */
    .rc-table {
        width: 100%;
        table-layout: fixed;
        border-collapse: separate;
        border-spacing: 0;
    }

    .rc-table col.col-id {
        width: 6%;
    }

    .rc-table col.col-name {
        width: 15%;
    }

    .rc-table col.col-contact {
        width: 18%;
    }

    .rc-table col.col-store {
        width: 12%;
    }

    .rc-table col.col-role {
        width: 10%;
    }

    .rc-table col.col-status {
        width: 8%;
    }

    .rc-table col.col-joined {
        width: 11%;
    }

    .rc-table col.col-actions {
        width: 20%;
    }

    .rc-table thead th {
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--rc-muted);
        font-weight: 700;
        border-bottom: 1px solid var(--rc-line);
        padding: .7rem .5rem;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .rc-table thead th.text-center {
        text-align: center;
    }

    .rc-table tbody td {
        padding: .9rem .5rem;
        border-bottom: 1px solid var(--rc-line);
        vertical-align: middle;
        font-size: .87rem;
        overflow: hidden;
    }

    .rc-table tbody tr:last-child td {
        border-bottom: none;
    }

    .rc-table tbody tr {
        transition: background .15s ease;
    }

    .rc-table tbody tr:hover {
        background: rgba(46, 204, 113, .05);
    }

    .rc-user-cell {
        display: flex;
        align-items: center;
        gap: .65rem;
        min-width: 0;
    }

    .rc-user-cell h6 {
        margin: 0;
        font-size: .88rem;
        font-weight: 700;
        color: var(--rc-ink);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .rc-contact-cell {
        display: flex;
        flex-direction: column;
        gap: 2px;
        font-size: .8rem;
        color: var(--rc-muted);
        min-width: 0;
    }

    .rc-contact-cell span {
        display: flex;
        align-items: center;
        gap: .4rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .rc-contact-cell i {
        width: 14px;
        color: var(--rc-green-500);
        font-size: .75rem;
        flex-shrink: 0;
    }

    .rc-id-cell {
        color: var(--rc-green-600);
        font-weight: 700;
    }

    .rc-date-cell {
        color: var(--rc-muted);
        font-size: .8rem;
        display: flex;
        align-items: center;
        gap: .35rem;
        white-space: nowrap;
    }

    .rc-empty {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--rc-muted);
    }

    .rc-empty i {
        font-size: 2.4rem;
        color: var(--rc-lime-300);
        margin-bottom: .75rem;
        display: block;
    }

    /* ---------- Pagination ---------- */
    .rc-pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .4rem;
        list-style: none;
        margin: 1.25rem 0 0;
        padding: 0;
        flex-wrap: wrap;
    }

    .rc-pagination .page-link {
        min-width: 36px;
        height: 36px;
        padding: 0 .5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        border: 1px solid var(--rc-line);
        background: var(--rc-white);
        color: var(--rc-ink);
        font-size: .82rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all .15s ease;
    }

    .rc-pagination .page-link:hover {
        border-color: var(--rc-green-500);
        color: var(--rc-green-600);
    }

    .rc-pagination .page-item.active .page-link {
        background: linear-gradient(135deg, var(--rc-forest-800), var(--rc-green-500));
        border-color: transparent;
        color: #fff;
        box-shadow: 0 6px 14px -4px rgba(15, 92, 62, .5);
    }

    .rc-pagination .page-item.disabled .page-link {
        opacity: .45;
        cursor: not-allowed;
        pointer-events: none;
    }

    #resultsInfo {
        font-size: .8rem;
        color: var(--rc-muted);
        text-align: center;
        margin-top: .6rem;
    }

    /* ---------- Mobile: stacked "profile card" rows instead of a table ---------- */
    @media (max-width: 991.98px) {
        .rc-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .rc-toolbar-controls {
            flex-direction: column;
            align-items: stretch;
        }

        .rc-toolbar-controls .rc-select,
        .rc-toolbar-controls .rc-search-wrap,
        .rc-toolbar-controls .rc-btn-clear {
            width: 100%;
        }

        .rc-search-wrap {
            min-width: 0;
        }
    }

    @media (max-width: 767.98px) {
        .rc-page-header {
            padding: 1rem;
            border-radius: var(--rc-radius-md);
        }

        .rc-page-header h1 {
            font-size: 1.15rem;
        }

        .rc-btn-primary {
            width: 100%;
            justify-content: center;
        }

        .rc-card-body {
            padding: .5rem .9rem 1rem;
        }

        .rc-table thead {
            display: none;
        }

        .rc-table,
        .rc-table tbody,
        .rc-table tr,
        .rc-table td {
            display: block;
            width: 100% !important;
        }

        .rc-table tbody tr {
            background: var(--rc-white);
            border: 1px solid var(--rc-line);
            border-radius: var(--rc-radius-md);
            margin-bottom: .85rem;
            padding: 1rem;
            box-shadow: var(--rc-shadow);
            position: relative;
        }

        .rc-table tbody tr:hover {
            background: var(--rc-white);
        }

        .rc-table td {
            border-bottom: none !important;
            padding: .4rem 0 !important;
        }

        .rc-table td.rc-cell-id {
            display: none;
        }

        .rc-table td.rc-cell-user {
            padding-bottom: .75rem !important;
            border-bottom: 1px dashed var(--rc-line) !important;
            margin-bottom: .5rem;
        }

        .rc-table td.rc-cell-user .rc-badge {
            margin-left: auto;
        }

        .rc-table td.rc-cell-user .rc-user-cell {
            width: 100%;
            justify-content: space-between;
        }

        .rc-table td[data-label]:not(.rc-cell-user):not(.rc-cell-actions) {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
        }

        .rc-table td[data-label]:not(.rc-cell-user):not(.rc-cell-actions)::before {
            content: attr(data-label);
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: var(--rc-muted);
            flex-shrink: 0;
        }

        .rc-table td.rc-cell-actions {
            border-top: 1px dashed var(--rc-line);
            margin-top: .5rem;
            padding-top: .75rem !important;
        }

        .rc-actions {
            justify-content: flex-end;
            padding: 0;
        }
    }
</style>

<div class="rc-dash">

    <!-- Page Header -->
    <div class="rc-page-header">
        <div class="rc-header-left">
            <div class="rc-header-badge"><i class="fas fa-users"></i></div>
            <div>
                <h1>Users Management</h1>
                <ul class="rc-breadcrumb">
                    <li><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                    <li class="active">Users</li>
                </ul>
            </div>
        </div>
        <a href="<?= base_url('users/add') ?>" class="rc-btn-primary">
            <i class="fas fa-plus"></i>Add User
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="rc-stat-card tint-admin">
                <div class="rc-icon"><i class="fas fa-user-shield"></i></div>
                <div class="rc-stat-info">
                    <p>Admins</p>
                    <h3><?= number_format($total_admins) ?></h3>
                </div>
                <a href="<?= base_url('users?role=admin') ?>" class="rc-stat-view">View</a>
            </div>
        </div>

        <div class="col-6 col-md-4">
            <div class="rc-stat-card tint-vendor">
                <div class="rc-icon"><i class="fas fa-store"></i></div>
                <div class="rc-stat-info">
                    <p>Vendors</p>
                    <h3><?= number_format($total_vendors) ?></h3>
                </div>
                <a href="<?= base_url('users?role=vendor') ?>" class="rc-stat-view">View</a>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="rc-stat-card tint-user">
                <div class="rc-icon"><i class="fas fa-users"></i></div>
                <div class="rc-stat-info">
                    <p>Users</p>
                    <h3><?= number_format($total_users) ?></h3>
                </div>
                <a href="<?= base_url('users?role=user') ?>" class="rc-stat-view">View</a>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="rc-toolbar">
        <div class="rc-toolbar-title">
            <i class="fas fa-table"></i>
            <span id="tableTitle">All Users</span>
            <span id="recordCount" class="rc-count-badge">0</span>
        </div>

        <div class="rc-toolbar-controls">
            <select id="roleFilter" class="rc-select">
                <option value="">All Roles</option>
                <option value="admin">Admin</option>
                <option value="vendor">Vendor</option>
                <option value="user">User</option>
            </select>

            <div class="rc-search-wrap">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" class="rc-search-input"
                    placeholder="Search by name, email, mobile..." autocomplete="off">
            </div>

            <button type="button" id="clearFilters" class="rc-btn-clear" style="display:none;">
                <i class="fas fa-times"></i> Clear
            </button>
        </div>
    </div>

    <!-- Users List -->
    <div class="rc-card">
        <div class="rc-card-body">
            <!-- Loading Spinner -->
            <div id="loadingSpinner" class="text-center" style="display: none;">
                <div class="rc-spinner"></div>
                <p style="color:var(--rc-muted); margin:0;">Loading users...</p>
            </div>

            <!-- Table -->
            <div class="table-responsive" id="usersTableContainer">
                <table class="rc-table">
                    <colgroup>
                        <col class="col-id">
                        <col class="col-name">
                        <col class="col-contact">
                        <col class="col-store">
                        <col class="col-role">
                        <col class="col-status">
                        <col class="col-joined">
                        <col class="col-actions">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Store</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div id="paginationContainer"></div>

            <!-- Results Info -->
            <div id="resultsInfo"></div>
        </div>
    </div>

</div><!-- /.rc-dash -->

<!-- Delete Form (Hidden) -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="_method" value="DELETE">
</form>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function() {
        let currentPage = 1;
        let searchQuery = '';
        let roleFilter = '';
        let searchTimeout = null;
        const currentUserId = <?= $this->session->userdata('user_id') ?: 0 ?>;

        loadUsers();

        $('#searchInput').on('keyup', function() {
            const value = $(this).val().trim();
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                searchQuery = value;
                currentPage = 1;
                loadUsers();
                updateClearButton();
            }, 500);
        });

        $('#roleFilter').on('change', function() {
            roleFilter = $(this).val();
            currentPage = 1;
            loadUsers();
            updateClearButton();
            updateTableTitle();
        });

        $('#clearFilters').click(function() {
            $('#searchInput').val('');
            $('#roleFilter').val('');
            searchQuery = '';
            roleFilter = '';
            currentPage = 1;
            $(this).hide();
            loadUsers();
            updateTableTitle();
        });

        $(document).on('click', '.rc-pagination a.page-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            if (page) {
                currentPage = page;
                loadUsers();
                $('html, body').animate({
                    scrollTop: $('#usersTableContainer').offset().top - 100
                }, 300);
            }
        });

        function loadUsers() {
            showLoading();
            $.ajax({
                url: '<?= base_url("users/get_users") ?>',
                type: 'POST',
                data: {
                    page: currentPage,
                    search: searchQuery,
                    role: roleFilter
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        $('#usersTableBody').html(response.html);
                        $('#paginationContainer').html(response.pagination);
                        $('#recordCount').text(response.total_records);

                        let info = '';
                        if (response.total_records > 0) {
                            const start = ((response.current_page - 1) * 10) + 1;
                            const end = Math.min(response.current_page * 10, response.total_records);
                            info = `Showing ${start} to ${end} of ${response.total_records} entries`;
                            if (searchQuery || roleFilter) {
                                let filters = [];
                                if (searchQuery) filters.push(`search: "${searchQuery}"`);
                                if (roleFilter) filters.push(`role: ${roleFilter}`);
                                info += ` (filtered by ${filters.join(', ')})`;
                            }
                        } else {
                            info = 'No records found';
                        }
                        $('#resultsInfo').text(info);
                    } else {
                        showError('Failed to load users');
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

        $(document).on('change', '.status-toggle', function() {
            const toggle = $(this);
            const id = toggle.data('id');
            const status = toggle.is(':checked') ? 1 : 0;

            if (id == currentUserId) {
                alert('You cannot change your own account status');
                toggle.prop('checked', !status);
                return;
            }

            toggle.prop('disabled', true);

            $.ajax({
                url: '<?= base_url("users/toggle_status") ?>',
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
                error: function(xhr) {
                    alert('Something went wrong. Please try again.');
                    toggle.prop('checked', !status);
                },
                complete: function() {
                    toggle.prop('disabled', false);
                }
            });
        });

        $(document).on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');

            if (id == currentUserId) {
                alert('You cannot delete your own account');
                return;
            }

            if (confirm(`Are you sure you want to delete "${name}"?\n\nThis action cannot be undone.`)) {
                const form = $('#deleteForm');
                form.attr('action', '<?= base_url("users/delete/") ?>' + id);
                form.submit();
            }
        });

        function updateClearButton() {
            if (searchQuery || roleFilter) {
                $('#clearFilters').show();
            } else {
                $('#clearFilters').hide();
            }
        }

        function updateTableTitle() {
            let title = 'All Users';
            if (roleFilter) {
                const roleNames = {
                    'admin': 'Admins',
                    'vendor': 'Vendors',
                    'user': 'Users'
                };
                title = roleNames[roleFilter] || 'All Users';
            }
            $('#tableTitle').text(title);
        }

        function showLoading() {
            $('#loadingSpinner').show();
            $('#usersTableContainer').css('opacity', '0.5');
        }

        function hideLoading() {
            $('#loadingSpinner').hide();
            $('#usersTableContainer').css('opacity', '1');
        }

        function showError(message) {
            $('#usersTableBody').html(`
            <tr>
                <td colspan="8">
                    <div class="rc-empty">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p style="color:#c0293a;">${message}</p>
                        <button class="rc-btn-primary" style="border:none;" onclick="location.reload()">
                            <i class="fas fa-sync"></i>Reload Page
                        </button>
                    </div>
                </td>
            </tr>
        `);
            $('#paginationContainer').html('');
            $('#resultsInfo').text('');
            $('#recordCount').text('0');
        }
    });
</script>