<!-- Page Header -->
<div class="page-header">
    <div>
        <h1><i class="fas fa-plus me-2"></i>Add User</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('users') ?>">Users</a></li>
                <li class="breadcrumb-item active">Add</li>
            </ol>
        </nav>
    </div>
</div>

<?= form_open_multipart('users/add') ?>
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <span><i class="fas fa-info-circle me-2"></i>User Information</span>
            </div>
            <div class="card-body">

                <!-- Role Selection First -->
                <div class="mb-4">
                    <label class="form-label">Select Role *</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input role-radio" type="radio" name="role" 
                                   id="role_admin" value="admin" 
                                   <?= set_radio('role', 'admin') ?>>
                            <label class="form-check-label" for="role_admin">
                                <i class="fas fa-user-shield text-danger me-1"></i> Admin
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input role-radio" type="radio" name="role" 
                                   id="role_vendor" value="vendor" 
                                   <?= set_radio('role', 'vendor') ?>>
                            <label class="form-check-label" for="role_vendor">
                                <i class="fas fa-store text-success me-1"></i> Vendor
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input role-radio" type="radio" name="role" 
                                   id="role_user" value="user" 
                                   <?= set_radio('role', 'user') ?>>
                            <label class="form-check-label" for="role_user">
                                <i class="fas fa-user text-primary me-1"></i> User
                            </label>
                        </div>
                    </div>
                    <?= form_error('role', '<small class="text-danger">', '</small>') ?>
                </div>

                <!-- Common Fields -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= set_value('name') ?>" required>
                        <?= form_error('name', '<small class="text-danger">', '</small>') ?>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="mobile" class="form-label">Mobile Number *</label>
                        <input type="text" class="form-control" id="mobile" name="mobile" 
                               value="<?= set_value('mobile') ?>" maxlength="10" required>
                        <?= form_error('mobile', '<small class="text-danger">', '</small>') ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?= set_value('email') ?>">
                    <?= form_error('email', '<small class="text-danger">', '</small>') ?>
                </div>

                <!-- Vendor Only: Store Name -->
                <div class="mb-3 d-none" id="store_name_field">
                    <label for="store_name" class="form-label">Store Name *</label>
                    <input type="text" class="form-control" id="store_name" name="store_name" 
                           value="<?= set_value('store_name') ?>">
                    <?= form_error('store_name', '<small class="text-danger">', '</small>') ?>
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Vendor can update store details from their panel
                    </small>
                </div>

                <!-- User Only: Address -->
                <div class="mb-3 d-none" id="address_field">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" 
                              rows="3"><?= set_value('address') ?></textarea>
                </div>

                <!-- Profile Image -->
                <div class="mb-3">
                    <label for="profile_image" class="form-label">Profile Image</label>
                    <input type="file" class="form-control" id="profile_image" name="profile_image" 
                           accept="image/*" onchange="previewImage(this)">
                    <small class="text-muted">JPG, PNG, GIF - Max 2MB</small>
                    <div id="imagePreview" class="mt-2" style="display: none;">
                        <img id="preview" src="" alt="Preview" class="rounded-circle" 
                             style="width: 100px; height: 100px; object-fit: cover;">
                    </div>
                </div>

                <!-- Status -->
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" 
                               name="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save User
                    </button>
                    <a href="<?= base_url('users') ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>

            </div>
        </div>
    </div>

    <!-- Info Card -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <span><i class="fas fa-lightbulb me-2"></i>Role Info</span>
            </div>
            <div class="card-body">
                <div id="info_admin" class="role-info d-none">
                    <h6 class="text-danger"><i class="fas fa-user-shield me-2"></i>Admin</h6>
                    <ul class="small ps-3">
                        <li>Full panel access</li>
                        <li>Manage users, products, orders</li>
                        <li>Login via OTP</li>
                    </ul>
                </div>
                <div id="info_vendor" class="role-info d-none">
                    <h6 class="text-success"><i class="fas fa-store me-2"></i>Vendor</h6>
                    <ul class="small ps-3">
                        <li>Manage own products</li>
                        <li>Store details updated from vendor panel</li>
                        <li>Login via OTP</li>
                    </ul>
                </div>
                <div id="info_user" class="role-info d-none">
                    <h6 class="text-primary"><i class="fas fa-user me-2"></i>User</h6>
                    <ul class="small ps-3">
                        <li>Browse and order products</li>
                        <li>Manage own profile</li>
                        <li>Login via OTP</li>
                    </ul>
                </div>
                <p class="text-muted small mb-0" id="info_default">
                    Select a role to see information
                </p>
            </div>
        </div>
    </div>
</div>
<?= form_close() ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#preview').attr('src', e.target.result);
            $('#imagePreview').show();
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Handle role change
$('.role-radio').change(function() {
    const role = $(this).val();

    // Hide all conditional fields and info
    $('#store_name_field, #address_field').addClass('d-none');
    $('.role-info').addClass('d-none');
    $('#info_default').hide();

    // Show based on role
    if (role === 'vendor') {
        $('#store_name_field').removeClass('d-none');
        $('#info_vendor').removeClass('d-none');
    } else if (role === 'user') {
        $('#address_field').removeClass('d-none');
        $('#info_user').removeClass('d-none');
    } else if (role === 'admin') {
        $('#info_admin').removeClass('d-none');
    }
});

// Trigger on page load if role already selected (validation fail case)
$(document).ready(function() {
    const selected = $('input[name="role"]:checked');
    if (selected.length) {
        selected.trigger('change');
    }
});
</script>