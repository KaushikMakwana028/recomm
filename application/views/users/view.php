<!-- Page Header -->
<div class="page-header">
    <div>
        <h1><i class="fas fa-user me-2"></i>User Details</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('users') ?>">Users</a></li>
                <li class="breadcrumb-item active">View</li>
            </ol>
        </nav>
    </div>
    <a href="<?= base_url('users/edit/' . $user->id) ?>" class="btn btn-primary">
        <i class="fas fa-edit me-2"></i>Edit User
    </a>
</div>

<?php
$role_color = ['admin' => 'danger', 'vendor' => 'success', 'user' => 'primary'];
$role_icon  = ['admin' => 'user-shield', 'vendor' => 'store', 'user' => 'user'];
?>

<div class="row">

    <!-- Left: Profile Card -->
    <div class="col-lg-4 mb-4">
        <div class="card text-center">
            <div class="card-body py-4">

                <!-- Profile Image -->
                <?php if ($user->profile_image): ?>
                    <img src="<?= base_url($user->profile_image) ?>" alt="<?= $user->name ?>"
                         class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                <?php else: ?>
                    <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                         style="width: 120px; height: 120px; background: linear-gradient(135deg, #4CAF50, #0A2A4D); font-size: 48px; color: white; font-weight: 700;">
                        <?= strtoupper(substr($user->name, 0, 1)) ?>
                    </div>
                <?php endif; ?>

                <h4 class="mb-1"><?= $user->name ?></h4>

                <span class="badge bg-<?= $role_color[$user->role] ?> mb-2">
                    <i class="fas fa-<?= $role_icon[$user->role] ?> me-1"></i>
                    <?= ucfirst($user->role) ?>
                </span>

                <br>

                <span class="badge bg-<?= $user->is_active ? 'success' : 'danger' ?>">
                    <?= $user->is_active ? 'Active' : 'Inactive' ?>
                </span>

                <hr>

                <table class="table table-sm table-borderless text-start mb-0">
                    <tr>
                        <td class="text-muted"><i class="fas fa-phone me-2"></i></td>
                        <td><?= $user->mobile ?></td>
                    </tr>
                    <?php if ($user->email): ?>
                        <tr>
                            <td class="text-muted"><i class="fas fa-envelope me-2"></i></td>
                            <td><?= $user->email ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="text-muted"><i class="fas fa-calendar me-2"></i></td>
                        <td><?= date('d M Y', strtotime($user->created_on)) ?></td>
                    </tr>
                </table>

            </div>
        </div>
    </div>

    <!-- Right: Details -->
    <div class="col-lg-8">

        <!-- ADMIN: Basic Info -->
        <?php if ($user->role == 'admin'): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-shield me-2"></i>Admin Information
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="200" class="text-muted">Full Name</th>
                            <td><?= $user->name ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Mobile</th>
                            <td><?= $user->mobile ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Email</th>
                            <td><?= $user->email ?: '-' ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Joined On</th>
                            <td><?= date('d M Y, h:i A', strtotime($user->created_on)) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- USER: Basic Info -->
        <?php if ($user->role == 'user'): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user me-2"></i>User Information
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="200" class="text-muted">Full Name</th>
                            <td><?= $user->name ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Mobile</th>
                            <td><?= $user->mobile ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Email</th>
                            <td><?= $user->email ?: '-' ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Address</th>
                            <td><?= $user->address ?: '-' ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Joined On</th>
                            <td><?= date('d M Y, h:i A', strtotime($user->created_on)) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- VENDOR: Store Info -->
        <?php if ($user->role == 'vendor'): ?>

            <!-- Store Photo -->
            <?php if (!empty($user->store_photo)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-store me-2"></i>Store Photo
                    </div>
                    <div class="card-body">
                        <img src="<?= base_url($user->store_photo) ?>" alt="Store Photo"
                             class="img-fluid rounded" style="max-height: 250px; object-fit: cover;">
                    </div>
                </div>
            <?php endif; ?>

            <!-- Store Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-2"></i>Store Information
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="200" class="text-muted">Store Name</th>
                            <td><?= $user->store_name ?: '-' ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Owner Name</th>
                            <td><?= $user->owner_name ?: '-' ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Mobile</th>
                            <td><?= $user->mobile ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Contact Number</th>
                            <td><?= $user->contact_number ?: '-' ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Email</th>
                            <td><?= $user->email ?: '-' ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">GST Number</th>
                            <td><?= $user->gst_number ?: '-' ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Address</th>
                            <td><?= $user->address ?: '-' ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Opening Time</th>
                            <td><?= $user->opening_time ? date('h:i A', strtotime($user->opening_time)) : '-' ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Closing Time</th>
                            <td><?= $user->closing_time ? date('h:i A', strtotime($user->closing_time)) : '-' ?></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Joined On</th>
                            <td><?= date('d M Y, h:i A', strtotime($user->created_on)) ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Vendor Products -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-box me-2"></i>Products (<?= count($vendor_products ?? []) ?>)</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
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
                            <tbody>
                                <?php if (!empty($vendor_products)): ?>
                                    <?php foreach ($vendor_products as $vp): ?>
                                        <?php
                                        // Use admin image if admin product, else vendor image
                                        $img = $vp->product_id ? $vp->admin_image : $vp->image;
                                        ?>
                                        <tr>
                                            <td>
                                                <?php if ($img): ?>
                                                    <img src="<?= base_url($img) ?>" alt=""
                                                         style="width: 45px; height: 45px; object-fit: cover; border-radius: 8px;">
                                                <?php else: ?>
                                                    <div style="width: 45px; height: 45px; background: #e9ecef; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?= $vp->product_name ?></strong>
                                                <?php if ($vp->brand): ?>
                                                    <br><small class="text-muted"><?= $vp->brand ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?= $vp->category_name ?: '-' ?></span>
                                            </td>
                                            <td>₹<?= number_format($vp->mrp, 2) ?></td>
                                            <td>₹<?= number_format($vp->selling_price, 2) ?></td>
                                            <td><?= $vp->stock ?></td>
                                            <td>
                                                <span class="badge bg-<?= $vp->is_active ? 'success' : 'danger' ?>">
                                                    <?= $vp->is_active ? 'Active' : 'Inactive' ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-3 text-muted">
                                            <i class="fas fa-inbox me-2"></i>No products added yet
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php endif; ?>

    </div>
</div>