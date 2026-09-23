<style>
    /* Styling variables and custom elements for users */
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

    .rc-form-container {
        font-family: 'Inter', 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
        color: var(--rc-ink);
        padding-bottom: 6rem;
        /* room for mobile sticky action bar */
    }

    .rc-form-container h1,
    .rc-form-container h2,
    .rc-form-container h3,
    .rc-form-container h4,
    .rc-form-container h5,
    .rc-form-container h6 {
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
        min-width: 0;
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
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
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
        flex-shrink: 0;
    }

    .rc-breadcrumb a {
        color: var(--rc-green-600);
        text-decoration: none;
        transition: color 0.15s ease;
    }

    .rc-breadcrumb a:hover {
        color: var(--rc-forest-900);
    }

    /* ---------- Cards ---------- */
    .rc-card {
        background: var(--rc-white);
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-lg);
        box-shadow: var(--rc-shadow);
        padding: 1.75rem;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .rc-card-header {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid var(--rc-line);
        padding-bottom: 1.1rem;
    }

    .rc-card-icon-box {
        width: 38px;
        height: 38px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(31, 157, 99, 0.1);
        color: var(--rc-green-600);
        font-size: 1rem;
        flex-shrink: 0;
    }

    .rc-card-header h3 {
        font-size: 1.05rem;
        font-weight: 700;
        margin: 0;
    }

    .rc-card-header .rc-card-sub {
        font-size: 0.76rem;
        color: var(--rc-muted);
        font-weight: 500;
        margin-top: 1px;
    }

    /* ---------- Form Styles ---------- */
    .rc-form-group {
        margin-bottom: 1.35rem;
    }

    .rc-form-group:last-child {
        margin-bottom: 0;
    }

    .rc-form-label {
        font-weight: 600;
        color: var(--rc-forest-900);
        font-size: 0.86rem;
        margin-bottom: 0.45rem;
        display: flex;
        align-items: center;
        gap: .3rem;
    }

    .rc-form-label .req {
        color: #c0293a;
    }

    .rc-form-control {
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-md);
        padding: 0.75rem 1rem;
        font-size: 0.92rem;
        color: var(--rc-ink);
        background-color: var(--rc-bg-tint, #f9fbfb);
        transition: all 0.2s ease;
        width: 100%;
    }

    .rc-form-control:focus {
        border-color: var(--rc-green-500);
        box-shadow: 0 0 0 4px rgba(31, 157, 99, 0.1);
        outline: 0;
        background-color: #fff;
    }

    .rc-form-control:disabled {
        background-color: var(--rc-bg);
        color: var(--rc-muted);
        cursor: not-allowed;
    }

    .rc-btn-primary {
        background: linear-gradient(135deg, var(--rc-green-600), var(--rc-green-500));
        color: #fff;
        border: none;
        border-radius: var(--rc-radius-md);
        padding: 0.8rem 1.75rem;
        font-weight: 600;
        font-size: 0.95rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        box-shadow: 0 4px 12px rgba(22, 121, 79, 0.2);
        transition: all 0.2s ease;
        cursor: pointer;
        text-decoration: none;
        width: 100%;
    }

    .rc-btn-primary:hover {
        background: linear-gradient(135deg, var(--rc-forest-800), var(--rc-green-600));
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(22, 121, 79, 0.3);
        color: #fff;
    }

    .rc-btn-info {
        background: rgba(46, 204, 113, 0.12);
        color: var(--rc-green-600);
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-md);
        padding: 0.8rem 1.75rem;
        font-weight: 600;
        font-size: 0.95rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
        cursor: pointer;
        text-decoration: none;
        width: 100%;
    }

    .rc-btn-info:hover {
        background: var(--rc-green-600);
        color: #fff;
        border-color: transparent;
    }

    .rc-btn-secondary {
        background: var(--rc-bg);
        color: var(--rc-forest-800);
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-md);
        padding: 0.8rem 1.75rem;
        font-weight: 600;
        font-size: 0.95rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
        cursor: pointer;
        text-decoration: none;
        width: 100%;
    }

    .rc-btn-secondary:hover {
        background: var(--rc-line);
        color: var(--rc-forest-900);
    }

    /* Switch checked color */
    .form-check-input:checked {
        background-color: var(--rc-green-500);
        border-color: var(--rc-green-500);
    }

    .form-switch .form-check-input {
        cursor: pointer;
        width: 2.4em;
        height: 1.35em;
    }

    /* ---------- Role badge ---------- */
    .rc-role-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.85rem;
        font-weight: 700;
        padding: 0.5rem 1rem;
        border-radius: 999px;
    }

    .rc-role-pill.role-admin {
        background: rgba(220, 53, 69, .1);
        color: #c0293a;
    }

    .rc-role-pill.role-vendor {
        background: rgba(31, 157, 99, .12);
        color: var(--rc-green-600);
    }

    .rc-role-pill.role-user {
        background: rgba(13, 110, 253, .1);
        color: #0d6efd;
    }

    /* ---------- Locked field hint ---------- */
    .rc-locked-hint {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.74rem;
        color: var(--rc-muted);
        margin-top: 0.4rem;
        background: var(--rc-bg);
        border: 1px solid var(--rc-line);
        padding: 0.3rem 0.6rem;
        border-radius: 999px;
    }

    /* ---------- Avatar upload ---------- */
    .rc-avatar-upload {
        display: flex;
        align-items: center;
        gap: 1.1rem;
        flex-wrap: wrap;
    }

    .rc-avatar-box {
        position: relative;
        width: 92px;
        height: 92px;
        border-radius: 50%;
        flex-shrink: 0;
        cursor: pointer;
        overflow: hidden;
        border: 3px solid #fff;
        box-shadow: 0 4px 14px rgba(11, 61, 41, 0.15);
        background: var(--rc-bg-tint, #f9fbfb);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .rc-avatar-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .rc-avatar-box .rc-avatar-placeholder {
        color: var(--rc-muted);
        font-size: 1.6rem;
    }

    .rc-avatar-box .rc-avatar-overlay {
        position: absolute;
        inset: 0;
        background: rgba(7, 39, 27, 0.55);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.2s ease;
        font-size: 1rem;
    }

    .rc-avatar-box:hover .rc-avatar-overlay {
        opacity: 1;
    }

    .rc-avatar-box.has-new-image {
        border-color: var(--rc-green-500);
    }

    .rc-avatar-meta small {
        display: block;
        color: var(--rc-muted);
        font-size: 0.78rem;
        margin-top: 0.2rem;
    }

    .rc-avatar-meta .rc-new-tag {
        display: none;
        color: var(--rc-green-600);
        font-weight: 700;
        font-size: 0.76rem;
        align-items: center;
        gap: 0.3rem;
        margin-top: 0.3rem;
    }

    .rc-avatar-box.has-new-image~.rc-avatar-meta .rc-new-tag {
        display: flex;
    }

    /* ---------- Status pill ---------- */
    .rc-status-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--rc-bg);
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-md);
        padding: 0.9rem 1.1rem;
        margin-bottom: 1.25rem;
    }

    .rc-status-row .rc-status-text strong {
        display: block;
        font-size: 0.88rem;
        color: var(--rc-forest-900);
    }

    .rc-status-row .rc-status-text span {
        font-size: 0.74rem;
        color: var(--rc-muted);
    }

    /* ---------- Notice cards ---------- */
    .rc-notice-card {
        border-radius: var(--rc-radius-lg);
        padding: 1.25rem 1.4rem;
        margin-bottom: 1.5rem;
        border: 1px solid var(--rc-line);
        border-left: 4px solid var(--rc-line);
    }

    .rc-notice-card.notice-vendor {
        background: rgba(242, 169, 59, 0.05);
        border-left-color: var(--rc-gold-500);
    }

    .rc-notice-card.notice-user {
        background: rgba(46, 204, 113, 0.05);
        border-left-color: var(--rc-emerald-400);
    }

    .rc-notice-header {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin-bottom: 0.75rem;
    }

    .rc-notice-header .rc-notice-icon {
        width: 30px;
        height: 30px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        flex-shrink: 0;
    }

    .notice-vendor .rc-notice-icon {
        background: rgba(242, 169, 59, 0.18);
        color: var(--rc-gold-500);
    }

    .notice-user .rc-notice-icon {
        background: rgba(46, 204, 113, 0.15);
        color: var(--rc-green-600);
    }

    .rc-notice-header h4 {
        margin: 0;
        font-size: 0.9rem;
        font-weight: 700;
    }

    .notice-vendor h4 {
        color: #a4680f;
    }

    .notice-user h4 {
        color: var(--rc-forest-800);
    }

    .rc-notice-card p {
        font-size: 0.82rem;
        color: var(--rc-muted);
        margin-bottom: 0.5rem;
    }

    .rc-notice-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .rc-notice-list li {
        font-size: 0.8rem;
        color: var(--rc-muted);
        padding: 0.3rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .rc-notice-list li::before {
        content: '';
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: var(--rc-gold-500);
        flex-shrink: 0;
    }

    /* ---------- Account info ---------- */
    .rc-info-list {
        display: flex;
        flex-direction: column;
    }

    .rc-info-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.7rem 0;
        border-bottom: 1px dashed var(--rc-line);
        font-size: 0.86rem;
    }

    .rc-info-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .rc-info-row:first-child {
        padding-top: 0;
    }

    .rc-info-row .label {
        color: var(--rc-muted);
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .rc-info-row .value {
        font-weight: 700;
        color: var(--rc-forest-900);
    }

    /* ---------- Sticky sidebar (desktop only) ---------- */
    @media (min-width: 992px) {
        .rc-sticky-sidebar {
            position: sticky;
            top: 1.5rem;
        }
    }

    /* ---------- Mobile sticky action bar ---------- */
    .rc-mobile-action-bar {
        display: none;
    }

    @media (max-width: 991.98px) {
        .rc-desktop-actions {
            display: none;
        }

        .rc-mobile-action-bar {
            display: flex;
            gap: 0.6rem;
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1030;
            background: #fff;
            border-top: 1px solid var(--rc-line);
            padding: 0.75rem 1rem calc(0.75rem + env(safe-area-inset-bottom));
            box-shadow: 0 -6px 20px rgba(11, 61, 41, 0.08);
        }

        .rc-mobile-action-bar .rc-btn-primary,
        .rc-mobile-action-bar .rc-btn-secondary {
            padding: 0.7rem 1rem;
            font-size: 0.88rem;
            flex: 1;
        }
    }

    @media (max-width: 767.98px) {
        .rc-page-header {
            padding: 1rem;
        }

        .rc-page-header h1 {
            font-size: 1.15rem;
        }

        .rc-breadcrumb {
            display: none;
        }

        .rc-card {
            padding: 1.25rem;
            border-radius: var(--rc-radius-md);
        }
    }
</style>

<div class="rc-form-container">

    <!-- Page Header -->
    <div class="rc-page-header">
        <div class="rc-header-left">
            <div class="rc-header-badge"><i class="fas fa-edit"></i></div>
            <div style="min-width: 0;">
                <h1>Edit User</h1>
                <div class="rc-subtitle"><?= htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
        <ul class="rc-breadcrumb">
            <li><a href="<?= base_url('dashboard') ?>"><i class="fas fa-home"></i> Dashboard</a></li>
            <li>&nbsp;/&nbsp;</li>
            <li><a href="<?= base_url('users') ?>">Users</a></li>
            <li>&nbsp;/&nbsp;Edit</li>
        </ul>
    </div>

    <?php
    $role_color = ['admin' => 'danger', 'vendor' => 'success', 'user' => 'primary'];
    $role_icon = ['admin' => 'user-shield', 'vendor' => 'store', 'user' => 'user'];
    ?>

    <?= form_open_multipart('users/edit/' . $user->id) ?>
    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <div class="rc-card mb-lg-0">
                <div class="rc-card-header">
                    <div class="rc-card-icon-box"><i class="fas fa-info-circle"></i></div>
                    <div>
                        <h3>Basic Information</h3>
                        <div class="rc-card-sub">Profile, contact and role</div>
                    </div>
                </div>

                <!-- Avatar + Role -->
                <div class="rc-form-group">
                    <label class="rc-form-label">Profile Image</label>
                    <div class="rc-avatar-upload">
                        <label for="profile_image" class="rc-avatar-box" id="avatarBox">
                            <?php if ($user->profile_image): ?>
                                <img id="preview" src="<?= base_url($user->profile_image) ?>" alt="Avatar">
                            <?php else: ?>
                                <i class="fas fa-user rc-avatar-placeholder" id="avatarPlaceholder"></i>
                            <?php endif; ?>
                            <div class="rc-avatar-overlay"><i class="fas fa-camera"></i></div>
                        </label>
                        <input type="file" id="profile_image" name="profile_image" accept="image/*"
                            onchange="previewImage(this)" style="display:none;">
                        <div class="rc-avatar-meta">
                            <small>Click the avatar to change. Max 2MB.</small>
                            <span class="rc-new-tag"><i class="fas fa-check-circle"></i> New photo selected</span>
                        </div>
                    </div>
                </div>

                <div class="rc-form-group">
                    <label class="rc-form-label">Role</label>
                    <div>
                        <span class="rc-role-pill role-<?= $user->role ?>">
                            <i class="fas fa-<?= $role_icon[$user->role] ?>"></i>
                            <?= ucfirst($user->role) ?>
                        </span>
                        <input type="hidden" name="role" value="<?= $user->role ?>">
                        <span class="rc-locked-hint"><i class="fas fa-lock"></i> Role cannot be updated</span>
                    </div>
                </div>

                <!-- Fields -->
                <div class="row">
                    <div class="col-md-6 rc-form-group">
                        <label for="name" class="rc-form-label">Full Name <span class="req">*</span></label>
                        <input type="text" class="rc-form-control" id="name" name="name"
                            value="<?= set_value('name', $user->name) ?>" required>
                        <?= form_error('name', '<small class="text-danger mt-1 d-block">', '</small>') ?>
                    </div>

                    <div class="col-md-6 rc-form-group">
                        <label class="rc-form-label">Mobile Number</label>
                        <input type="text" class="rc-form-control" value="<?= $user->mobile ?>" disabled>
                        <span class="rc-locked-hint"><i class="fas fa-lock"></i> Secure and locked</span>
                    </div>
                </div>

                <div class="rc-form-group mb-0">
                    <label for="email" class="rc-form-label">Email Address</label>
                    <input type="email" class="rc-form-control" id="email" name="email"
                        value="<?= set_value('email', $user->email) ?>">
                    <?= form_error('email', '<small class="text-danger mt-1 d-block">', '</small>') ?>
                </div>
            </div>

            <?php if ($user->role == 'vendor'): ?>
                <!-- Store Information Card -->
                <div class="rc-card">
                    <div class="rc-card-header">
                        <div class="rc-card-icon-box"><i class="fas fa-store"></i></div>
                        <div>
                            <h3>Store Information</h3>
                            <div class="rc-card-sub">Business profile, physical address and location</div>
                        </div>
                    </div>

                    <div class="rc-form-group">
                        <label class="rc-form-label">Store Banner Photo</label>
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <?php if (!empty($user->store_photo)): ?>
                                <img id="current_store_photo" src="<?= base_url($user->store_photo) ?>" alt="Store Photo"
                                    style="height: 80px; width: 140px; object-fit: cover; border-radius: 8px; border: 1px solid var(--rc-line);">
                            <?php endif; ?>
                            <div id="storePhotoPreview" style="display: none;">
                                <img id="preview_store" src="" alt="New Store Photo Preview"
                                    style="height: 80px; width: 140px; object-fit: cover; border-radius: 8px; border: 2px solid var(--rc-green-500);">
                            </div>
                        </div>
                        <input type="file" class="rc-form-control" id="store_photo" name="store_photo" accept="image/*" onchange="previewStorePhoto(this)">
                        <small class="text-muted mt-1 d-block">Allowed: JPG, PNG, WEBP. Max: 5MB</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 rc-form-group">
                            <label for="store_name" class="rc-form-label">Store Name <span class="req">*</span></label>
                            <input type="text" class="rc-form-control" id="store_name" name="store_name"
                                value="<?= set_value('store_name', $user->store_name) ?>" required>
                            <?= form_error('store_name', '<small class="text-danger mt-1 d-block">', '</small>') ?>
                        </div>
                        <div class="col-md-6 rc-form-group">
                            <label for="owner_name" class="rc-form-label">Owner Name</label>
                            <input type="text" class="rc-form-control" id="owner_name" name="owner_name"
                                value="<?= set_value('owner_name', $user->owner_name) ?>" placeholder="e.g. John Doe">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 rc-form-group">
                            <label for="contact_number" class="rc-form-label">Store Contact Number</label>
                            <input type="text" class="rc-form-control" id="contact_number" name="contact_number"
                                value="<?= set_value('contact_number', $user->contact_number) ?>" placeholder="e.g. 9876543210">
                        </div>
                        <div class="col-md-6 rc-form-group">
                            <label for="gst_number" class="rc-form-label">GST Number</label>
                            <input type="text" class="rc-form-control" id="gst_number" name="gst_number"
                                value="<?= set_value('gst_number', $user->gst_number) ?>" placeholder="e.g. 27AAPFU0939F1ZV">
                        </div>
                    </div>

                    <div class="rc-form-group">
                        <label for="address" class="rc-form-label">Store Address</label>
                        <textarea class="rc-form-control" id="address" name="address" rows="2"
                            placeholder="Full physical shop address..."><?= set_value('address', $user->address) ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 rc-form-group">
                            <label for="pincode" class="rc-form-label">Pincode</label>
                            <input type="text" class="rc-form-control" id="pincode" name="pincode"
                                value="<?= set_value('pincode', $user->pincode) ?>" placeholder="e.g. 380001">
                        </div>
                        <div class="col-md-4 rc-form-group">
                            <label for="latitude" class="rc-form-label">Latitude</label>
                            <input type="text" class="rc-form-control" id="latitude" name="latitude"
                                value="<?= set_value('latitude', $user->latitude) ?>" placeholder="e.g. 23.079496">
                        </div>
                        <div class="col-md-4 rc-form-group">
                            <label for="longitude" class="rc-form-label">Longitude</label>
                            <input type="text" class="rc-form-control" id="longitude" name="longitude"
                                value="<?= set_value('longitude', $user->longitude) ?>" placeholder="e.g. 72.672547">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 rc-form-group">
                            <label for="opening_time" class="rc-form-label">Opening Time</label>
                            <input type="time" class="rc-form-control" id="opening_time" name="opening_time"
                                value="<?= set_value('opening_time', $user->opening_time ? substr($user->opening_time, 0, 5) : '') ?>">
                        </div>
                        <div class="col-md-6 rc-form-group">
                            <label for="closing_time" class="rc-form-label">Closing Time</label>
                            <input type="time" class="rc-form-control" id="closing_time" name="closing_time"
                                value="<?= set_value('closing_time', $user->closing_time ? substr($user->closing_time, 0, 5) : '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Bank & Settlement Details Card -->
                <div class="rc-card">
                    <div class="rc-card-header">
                        <div class="rc-card-icon-box"><i class="fas fa-university"></i></div>
                        <div>
                            <h3>Bank & Payout Details</h3>
                            <div class="rc-card-sub">Bank accounts for order settlement transfers</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 rc-form-group">
                            <label for="account_holder_name" class="rc-form-label">Account Holder Name</label>
                            <input type="text" class="rc-form-control" id="account_holder_name" name="account_holder_name"
                                value="<?= set_value('account_holder_name', $user->account_holder_name) ?>" placeholder="e.g. Makwana Kaushik">
                        </div>
                        <div class="col-md-6 rc-form-group">
                            <label for="bank_name" class="rc-form-label">Bank Name</label>
                            <input type="text" class="rc-form-control" id="bank_name" name="bank_name"
                                value="<?= set_value('bank_name', $user->bank_name) ?>" placeholder="e.g. State Bank of India">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 rc-form-group">
                            <label for="account_number" class="rc-form-label">Account Number</label>
                            <input type="text" class="rc-form-control" id="account_number" name="account_number"
                                value="<?= set_value('account_number', $user->account_number) ?>" placeholder="e.g. 123456789012">
                        </div>
                        <div class="col-md-6 rc-form-group">
                            <label for="ifsc_code" class="rc-form-label">IFSC Code</label>
                            <input type="text" class="rc-form-control" id="ifsc_code" name="ifsc_code"
                                value="<?= set_value('ifsc_code', $user->ifsc_code) ?>" placeholder="e.g. SBIN0001234">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 rc-form-group">
                            <label for="account_type" class="rc-form-label">Account Type</label>
                            <select class="rc-form-control" id="account_type" name="account_type">
                                <option value="">Select Account Type</option>
                                <?php $curr_acc = set_value('account_type', $user->account_type); ?>
                                <option value="savings" <?= $curr_acc == 'savings' ? 'selected' : '' ?>>Savings Account</option>
                                <option value="current" <?= $curr_acc == 'current' ? 'selected' : '' ?>>Current Account</option>
                                <option value="business" <?= $curr_acc == 'business' ? 'selected' : '' ?>>Business Account</option>
                                <option value="cash_credit" <?= $curr_acc == 'cash_credit' ? 'selected' : '' ?>>Cash Credit</option>
                                <option value="overdraft" <?= $curr_acc == 'overdraft' ? 'selected' : '' ?>>Overdraft</option>
                                <option value="joint" <?= $curr_acc == 'joint' ? 'selected' : '' ?>>Joint Account</option>
                            </select>
                        </div>
                        <div class="col-md-6 rc-form-group">
                            <label for="branch_name" class="rc-form-label">Branch Name</label>
                            <input type="text" class="rc-form-control" id="branch_name" name="branch_name"
                                value="<?= set_value('branch_name', $user->branch_name) ?>" placeholder="e.g. Navrangpura Branch">
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($user->role == 'user'): ?>
                <div class="rc-card">
                    <div class="rc-card-header">
                        <div class="rc-card-icon-box"><i class="fas fa-map-marker-alt"></i></div>
                        <div>
                            <h3>Delivery Address</h3>
                            <div class="rc-card-sub">Default customer shipping address</div>
                        </div>
                    </div>
                    <div class="rc-form-group mb-0">
                        <label for="user_address" class="rc-form-label">Address</label>
                        <textarea class="rc-form-control" id="user_address" name="address" rows="3"
                            placeholder="Enter user delivery address..."><?= set_value('address', $user->address) ?></textarea>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <!-- /Left Column -->

        <!-- Right Column -->
        <div class="col-lg-4">
            <div class="rc-sticky-sidebar">

                <!-- Save Actions Card -->
                <div class="rc-card">
                    <div class="rc-card-header">
                        <div class="rc-card-icon-box"><i class="fas fa-save"></i></div>
                        <div>
                            <h3>Save Actions</h3>
                            <div class="rc-card-sub">Status and quick links</div>
                        </div>
                    </div>

                    <div class="rc-status-row">
                        <div class="rc-status-text">
                            <strong>Active Status</strong>
                            <span>Allow this user to sign in</span>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="is_active"
                                name="is_active" value="1"
                                <?= $user->is_active == 1 ? 'checked' : '' ?>>
                        </div>
                    </div>

                    <!-- Desktop actions (hidden on mobile, replaced by sticky bar) -->
                    <div class="d-flex flex-column gap-2 rc-desktop-actions">
                        <button type="submit" class="rc-btn-primary">
                            <i class="fas fa-save"></i> Update User
                        </button>
                        <a href="<?= base_url('users/view/' . $user->id) ?>" class="rc-btn-info">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                        <a href="<?= base_url('users') ?>" class="rc-btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </div>

                <!-- Account Info -->
                <div class="rc-card">
                    <div class="rc-card-header">
                        <div class="rc-card-icon-box"><i class="fas fa-clock"></i></div>
                        <div>
                            <h3>Account Info</h3>
                            <div class="rc-card-sub">Read-only summary</div>
                        </div>
                    </div>
                    <div class="rc-info-list">
                        <div class="rc-info-row">
                            <span class="label"><i class="fas fa-calendar-alt"></i> Joined</span>
                            <span class="value"><?= date('d M Y', strtotime($user->created_on)) ?></span>
                        </div>
                        <div class="rc-info-row">
                            <span class="label"><i class="fas fa-toggle-on"></i> Status</span>
                            <span class="badge bg-<?= $user->is_active ? 'success' : 'danger' ?>" style="font-size: 0.75rem;">
                                <?= $user->is_active ? 'Active' : 'Inactive' ?>
                            </span>
                        </div>
                        <div class="rc-info-row">
                            <span class="label"><i class="fas fa-id-badge"></i> Role</span>
                            <span class="badge bg-<?= $role_color[$user->role] ?>" style="font-size: 0.75rem;">
                                <?= ucfirst($user->role) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Vendor note -->
                <?php if ($user->role == 'vendor'): ?>
                    <div class="rc-notice-card notice-vendor">
                        <div class="rc-notice-header">
                            <div class="rc-notice-icon"><i class="fas fa-info-circle"></i></div>
                            <h4>Vendor Notice</h4>
                        </div>
                        <p>These fields are managed independently by the vendor from their store settings panel:</p>
                        <ul class="rc-notice-list">
                            <li>Store Name &amp; Owner details</li>
                            <li>GST &amp; Contact Information</li>
                            <li>Warehouse Address</li>
                            <li>Store Hours &amp; Photos</li>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- User note -->
                <?php if ($user->role == 'user'): ?>
                    <div class="rc-notice-card notice-user">
                        <div class="rc-notice-header">
                            <div class="rc-notice-icon"><i class="fas fa-info-circle"></i></div>
                            <h4>User Notice</h4>
                        </div>
                        <p class="mb-0">Shipping details, home address, and cart configurations are managed securely by the customer from their mobile client dashboard.</p>
                    </div>
                <?php endif; ?>

            </div>
        </div>
        <!-- /Right Column -->
    </div>

    <!-- Mobile sticky action bar -->
    <div class="rc-mobile-action-bar">
        <a href="<?= base_url('users') ?>" class="rc-btn-secondary">
            <i class="fas fa-times"></i> Cancel
        </a>
        <button type="submit" class="rc-btn-primary">
            <i class="fas fa-save"></i> Update
        </button>
    </div>

    <?= form_close() ?>

</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const box = document.getElementById('avatarBox');
                let img = document.getElementById('preview');
                const placeholder = document.getElementById('avatarPlaceholder');
                if (placeholder) placeholder.remove();
                if (!img) {
                    img = document.createElement('img');
                    img.id = 'preview';
                    img.alt = 'Avatar';
                    box.prepend(img);
                }
                img.src = e.target.result;
                box.classList.add('has-new-image');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewStorePhoto(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview_store').src = e.target.result;
                document.getElementById('storePhotoPreview').style.display = 'block';
                const cur = document.getElementById('current_store_photo');
                if (cur) cur.style.opacity = '0.5';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>