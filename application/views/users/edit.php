<!-- Page Header -->
<div class="page-header">
    <div>
        <h1><i class="fas fa-edit me-2"></i>Edit User</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('users') ?>">Users</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<?= form_open_multipart('users/edit/' . $user->id) ?>
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <span><i class="fas fa-info-circle me-2"></i>Basic Information</span>
            </div>
            <div class="card-body">

                <!-- Role Badge -->
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <div>
                        <?php
                        $role_color = ['admin' => 'danger', 'vendor' => 'success', 'user' => 'primary'];
                        $role_icon = ['admin' => 'user-shield', 'vendor' => 'store', 'user' => 'user'];
                        ?>
                        <span class="badge bg-<?= $role_color[$user->role] ?> fs-6">
                            <i class="fas fa-<?= $role_icon[$user->role] ?> me-1"></i>
                            <?= ucfirst($user->role) ?>
                        </span>
                        <input type="hidden" name="role" value="<?= $user->role ?>">
                        <small class="text-muted ms-2">Role cannot be changed</small>
                    </div>
                </div>

                <!-- Common Fields -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= set_value('name', $user->name) ?>" required>
                        <?= form_error('name', '<small class="text-danger">', '</small>') ?>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Mobile Number</label>
                        <input type="text" class="form-control" value="<?= $user->mobile ?>" disabled>
                        <small class="text-muted">Cannot be changed</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?= set_value('email', $user->email) ?>">
                    <?= form_error('email', '<small class="text-danger">', '</small>') ?>
                </div>

                <!-- Profile Image -->
                <div class="mb-3">
                    <label class="form-label">Profile Image</label>
                    <?php if ($user->profile_image): ?>
                        <div class="mb-2">
                            <img src="<?= base_url($user->profile_image) ?>" alt="Current"
                                 class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" name="profile_image" 
                           accept="image/*" onchange="previewImage(this)">
                    <small class="text-muted">Leave empty to keep current image</small>
                    <div id="imagePreview" class="mt-2" style="display: none;">
                        <img id="preview" src="" alt="Preview" class="rounded-circle" 
                             style="width: 100px; height: 100px; object-fit: cover;">
                    </div>
                </div>

                <!-- Status -->
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" 
                               name="is_active" value="1" 
                               <?= $user->is_active == 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update User
                    </button>
                    <a href="<?= base_url('users/view/' . $user->id) ?>" class="btn btn-info">
                        <i class="fas fa-eye me-2"></i>View Details
                    </a>
                    <a href="<?= base_url('users') ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>

            </div>
        </div>
    </div>

    <!-- Info Card -->
    <div class="col-lg-4">

        <!-- Vendor Info Note -->
        <?php if ($user->role == 'vendor'): ?>
            <div class="card border-warning mb-3">
                <div class="card-body">
                    <h6 class="text-warning">
                        <i class="fas fa-info-circle me-2"></i>Vendor Details
                    </h6>
                    <p class="small text-muted mb-2">
                        Following fields are managed by vendor from their panel:
                    </p>
                    <ul class="small text-muted ps-3 mb-0">
                        <li>Store Name</li>
                        <li>Owner Name</li>
                        <li>GST Number</li>
                        <li>Contact Number</li>
                        <li>Address</li>
                        <li>Opening / Closing Time</li>
                        <li>Store Photo</li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <!-- User Info Note -->
        <?php if ($user->role == 'user'): ?>
            <div class="card border-info mb-3">
                <div class="card-body">
                    <h6 class="text-info">
                        <i class="fas fa-info-circle me-2"></i>User Details
                    </h6>
                    <p class="small text-muted mb-0">
                        Address and other details are managed by user from their profile.
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Quick Info -->
        <div class="card">
            <div class="card-body">
                <h6><i class="fas fa-clock me-2"></i>Account Info</h6>
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Joined:</td>
                        <td><?= date('d M Y', strtotime($user->created_on)) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status:</td>
                        <td>
                            <span class="badge bg-<?= $user->is_active ? 'success' : 'danger' ?>">
                                <?= $user->is_active ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Role:</td>
                        <td>
                            <span class="badge bg-<?= $role_color[$user->role] ?>">
                                <?= ucfirst($user->role) ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

    </div>
</div>
<?= form_close() ?>

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
</script>