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
        padding-bottom: 2rem;
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
        gap: 0.75rem;
        margin-bottom: 1.75rem;
        border-bottom: 1px solid var(--rc-line);
        padding-bottom: 1rem;
    }

    .rc-card-header h3 {
        font-size: 1.15rem;
        font-weight: 700;
        margin: 0;
    }

    .rc-card-header i {
        color: var(--rc-green-600);
        font-size: 1.25rem;
    }

    /* ---------- Form Styles ---------- */
    .rc-form-group {
        margin-bottom: 1.25rem;
    }

    .rc-form-label {
        font-weight: 600;
        color: var(--rc-forest-900);
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
        display: block;
    }

    .rc-form-control {
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-md);
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
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

    /* Custom Role Selector Labels */
    .rc-role-options {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .rc-role-card {
        flex: 1 1 calc(33.33% - 1rem);
        min-width: 120px;
        border: 2px solid var(--rc-line);
        border-radius: var(--rc-radius-md);
        padding: 1rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        user-select: none;
    }

    .rc-role-card input[type="radio"] {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    .rc-role-card i {
        font-size: 1.8rem;
        margin-bottom: 0.5rem;
        display: block;
        transition: transform 0.2s;
    }

    .rc-role-card span {
        font-weight: 700;
        font-size: 0.9rem;
        display: block;
    }

    /* Role card selections colors */
    .rc-role-card.role-admin { color: #c0293a; }
    .rc-role-card.role-vendor { color: var(--rc-green-600); }
    .rc-role-card.role-user { color: #0d6efd; }

    .rc-role-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(11, 61, 41, 0.05);
    }

    .rc-role-card input[type="radio"]:checked + .role-content {
        transform: scale(1.05);
    }

    /* Active states */
    .rc-role-card.active-admin {
        border-color: #c0293a;
        background: rgba(220, 53, 69, 0.05);
    }
    .rc-role-card.active-vendor {
        border-color: var(--rc-green-600);
        background: rgba(46, 204, 113, 0.05);
    }
    .rc-role-card.active-user {
        border-color: #0d6efd;
        background: rgba(13, 110, 253, 0.05);
    }

    .rc-btn-primary {
        background: linear-gradient(135deg, var(--rc-green-600), var(--rc-green-500));
        color: #fff;
        border: none;
        border-radius: var(--rc-radius-md);
        padding: 0.75rem 1.75rem;
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
    }

    .rc-btn-primary:hover {
        background: linear-gradient(135deg, var(--rc-forest-800), var(--rc-green-600));
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(22, 121, 79, 0.3);
        color: #fff;
    }

    .rc-btn-secondary {
        background: var(--rc-bg);
        color: var(--rc-forest-800);
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-md);
        padding: 0.75rem 1.75rem;
        font-weight: 600;
        font-size: 0.95rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
        cursor: pointer;
        text-decoration: none;
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
    }
</style>

<div class="rc-form-container">

    <!-- Page Header -->
    <div class="rc-page-header">
        <div class="rc-header-left">
            <div class="rc-header-badge"><i class="fas fa-user-plus"></i></div>
            <div>
                <h1>Add User</h1>
                <div class="rc-subtitle">Create a new user, vendor, or administrator account</div>
            </div>
        </div>
        <ul class="rc-breadcrumb">
            <li><a href="<?= base_url('dashboard') ?>"><i class="fas fa-home"></i> Dashboard</a></li>
            <li>&nbsp;/&nbsp;</li>
            <li><a href="<?= base_url('users') ?>">Users</a></li>
            <li>&nbsp;/&nbsp;Add</li>
        </ul>
    </div>

    <?= form_open_multipart('users/add') ?>
    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <div class="rc-card">
                <div class="rc-card-header">
                    <i class="fas fa-info-circle"></i>
                    <h3>User Information</h3>
                </div>

                <!-- Role Selection -->
                <div class="rc-form-group mb-4">
                    <label class="rc-form-label">Select Role *</label>
                    <div class="rc-role-options">
                        <label class="rc-role-card role-admin" id="label_admin" for="role_admin">
                            <input class="role-radio" type="radio" name="role" 
                                   id="role_admin" value="admin" 
                                   <?= set_radio('role', 'admin') ?>>
                            <div class="role-content">
                                <i class="fas fa-user-shield"></i>
                                <span>Admin</span>
                            </div>
                        </label>
                        
                        <label class="rc-role-card role-vendor" id="label_vendor" for="role_vendor">
                            <input class="role-radio" type="radio" name="role" 
                                   id="role_vendor" value="vendor" 
                                   <?= set_radio('role', 'vendor') ?>>
                            <div class="role-content">
                                <i class="fas fa-store"></i>
                                <span>Vendor</span>
                            </div>
                        </label>
                        
                        <label class="rc-role-card role-user" id="label_user" for="role_user">
                            <input class="role-radio" type="radio" name="role" 
                                   id="role_user" value="user" 
                                   <?= set_radio('role', 'user') ?>>
                            <div class="role-content">
                                <i class="fas fa-user"></i>
                                <span>User</span>
                            </div>
                        </label>
                    </div>
                    <?= form_error('role', '<small class="text-danger mt-1 d-block">', '</small>') ?>
                </div>

                <!-- Common Input Fields -->
                <div class="row">
                    <div class="col-md-6 rc-form-group">
                        <label for="name" class="rc-form-label">Full Name *</label>
                        <input type="text" class="rc-form-control" id="name" name="name" 
                               value="<?= set_value('name') ?>" required placeholder="e.g. John Doe">
                        <?= form_error('name', '<small class="text-danger mt-1 d-block">', '</small>') ?>
                    </div>
                    
                    <div class="col-md-6 rc-form-group">
                        <label for="mobile" class="rc-form-label">Mobile Number *</label>
                        <input type="text" class="rc-form-control" id="mobile" name="mobile" 
                               value="<?= set_value('mobile') ?>" maxlength="10" required placeholder="10-digit number">
                        <?= form_error('mobile', '<small class="text-danger mt-1 d-block">', '</small>') ?>
                    </div>
                </div>

                <div class="rc-form-group">
                    <label for="email" class="rc-form-label">Email Address</label>
                    <input type="email" class="rc-form-control" id="email" name="email" 
                           value="<?= set_value('email') ?>" placeholder="e.g. john@example.com">
                    <?= form_error('email', '<small class="text-danger mt-1 d-block">', '</small>') ?>
                </div>

                <!-- Vendor Only: Store Name -->
                <div class="rc-form-group d-none" id="store_name_field">
                    <label for="store_name" class="rc-form-label">Store Name *</label>
                    <input type="text" class="rc-form-control" id="store_name" name="store_name" 
                           value="<?= set_value('store_name') ?>" placeholder="e.g. Green Valley Organics">
                    <?= form_error('store_name', '<small class="text-danger mt-1 d-block">', '</small>') ?>
                    <small class="text-muted mt-1 d-block">
                        <i class="fas fa-info-circle me-1"></i>
                        The vendor can update other store details from their vendor panel.
                    </small>
                </div>

                <!-- User Only: Address -->
                <div class="rc-form-group d-none" id="address_field">
                    <label for="address" class="rc-form-label">Delivery Address</label>
                    <textarea class="rc-form-control" id="address" name="address" 
                              rows="3" placeholder="Enter user address details..."><?= set_value('address') ?></textarea>
                </div>

                <!-- Profile Image -->
                <div class="rc-form-group">
                    <label for="profile_image" class="rc-form-label">Profile Image</label>
                    <input type="file" class="rc-form-control" id="profile_image" name="profile_image" 
                           accept="image/*" onchange="previewImage(this)">
                    <small class="text-muted mt-1 d-block">Allowed: JPG, PNG, GIF. Max: 2MB</small>
                    
                    <div id="imagePreview" class="mt-3" style="display: none;">
                        <img id="preview" src="" alt="Preview" class="rounded-circle" 
                             style="width: 100px; height: 100px; object-fit: cover; border: 3px solid var(--rc-white); box-shadow: 0 4px 10px rgba(11, 61, 41, 0.1);">
                    </div>
                </div>

                <!-- Status Checkbox -->
                <div class="rc-form-group mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" 
                               name="is_active" value="1" checked>
                        <label class="form-check-label rc-form-label ms-2 d-inline-block" for="is_active" style="margin-bottom: 0;">Active</label>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="border-top var(--rc-line) pt-4 d-flex gap-2">
                    <button type="submit" class="rc-btn-primary">
                        <i class="fas fa-save"></i> Save User
                    </button>
                    <a href="<?= base_url('users') ?>" class="rc-btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Column: Info Panel -->
        <div class="col-lg-4">
            <div class="rc-card">
                <div class="rc-card-header">
                    <i class="fas fa-lightbulb"></i>
                    <h3>Role Information</h3>
                </div>
                
                <div class="card-body p-0">
                    <div id="info_admin" class="role-info d-none">
                        <h4 style="font-size: 1.05rem; font-weight: 700; color: #c0293a; margin-bottom: 0.75rem;">
                            <i class="fas fa-user-shield me-2"></i>Admin Privileges:
                        </h4>
                        <ul style="padding-left: 1.25rem; font-size: 0.88rem; line-height: 1.5; color: var(--rc-ink);">
                            <li class="mb-2">Full administration access to the system.</li>
                            <li class="mb-2">Manage products, categories, orders, and system settings.</li>
                            <li>Authorized securely via mobile verification OTP.</li>
                        </ul>
                    </div>

                    <div id="info_vendor" class="role-info d-none">
                        <h4 style="font-size: 1.05rem; font-weight: 700; color: var(--rc-green-600); margin-bottom: 0.75rem;">
                            <i class="fas fa-store me-2"></i>Vendor Privileges:
                        </h4>
                        <ul style="padding-left: 1.25rem; font-size: 0.88rem; line-height: 1.5; color: var(--rc-ink);">
                            <li class="mb-2">Manage store listings, inventory, and linked products.</li>
                            <li class="mb-2">Store profile fields are manageable by the vendor via the vendor panel.</li>
                            <li>Authorized securely via mobile verification OTP.</li>
                        </ul>
                    </div>

                    <div id="info_user" class="role-info d-none">
                        <h4 style="font-size: 1.05rem; font-weight: 700; color: #0d6efd; margin-bottom: 0.75rem;">
                            <i class="fas fa-user me-2"></i>Customer/User Privileges:
                        </h4>
                        <ul style="padding-left: 1.25rem; font-size: 0.88rem; line-height: 1.5; color: var(--rc-ink);">
                            <li class="mb-2">Browse the marketplace and submit orders.</li>
                            <li class="mb-2">Manage shipping details and view order history from their mobile app.</li>
                            <li>Authorized securely via mobile verification OTP.</li>
                        </ul>
                    </div>

                    <p class="text-muted small mb-0" id="info_default">
                        Select a role card on the left to see descriptive details.
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?= form_close() ?>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

$(document).ready(function() {
    // Handle role card click & changes
    $('.role-radio').change(function() {
        const role = $(this).val();

        // Reset active labels styling
        $('.rc-role-card').removeClass('active-admin active-vendor active-user');
        
        // Hide conditional sections and info
        $('#store_name_field, #address_field').addClass('d-none');
        $('.role-info').addClass('d-none');
        $('#info_default').hide();

        // Apply new style and show relevant options
        if (role === 'admin') {
            $('#label_admin').addClass('active-admin');
            $('#info_admin').removeClass('d-none');
        } else if (role === 'vendor') {
            $('#label_vendor').addClass('active-vendor');
            $('#store_name_field').removeClass('d-none');
            $('#info_vendor').removeClass('d-none');
        } else if (role === 'user') {
            $('#label_user').addClass('active-user');
            $('#address_field').removeClass('d-none');
            $('#info_user').removeClass('d-none');
        }
    });

    // Trigger change on load if preset (validation failure recovery)
    const selected = $('input[name="role"]:checked');
    if (selected.length) {
        selected.trigger('change');
    }
});
</script>