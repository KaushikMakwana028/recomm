<!-- Page Content -->
<div id="content">
    <!-- Top Navigation Bar -->
    <div class="topbar">
        <button type="button" id="sidebarCollapse" class="toggle-btn">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="user-info">
            <div class="avatar">
                <?= strtoupper(substr($admin_data->name ?? 'A', 0, 1)) ?>
            </div>
            <div class="user-details">
                <div class="name"><?= $admin_data->name ?? 'Admin' ?></div>
                <div class="role"><?= ucfirst($admin_data->role ?? 'Administrator') ?></div>
            </div>
            <div class="dropdown">
                <button class="dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-chevron-down"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li>
                        <a class="dropdown-item" href="<?= base_url('settings/profile') ?>">
                            <i class="fas fa-user me-2"></i> Profile
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?= base_url('settings/change-password') ?>">
                            <i class="fas fa-key me-2"></i> Change Password
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="<?= base_url('logout') ?>" onclick="return confirm('Are you sure you want to logout?')">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Main Content Area -->
    <div class="main-content">
        <?php if ($this->session->flashdata('message')): ?>
            <div class="alert alert-<?= $this->session->flashdata('message_type') ?> alert-dismissible fade show" role="alert">
                <strong>
                    <?php if($this->session->flashdata('message_type') == 'success'): ?>
                        <i class="fas fa-check-circle me-2"></i>
                    <?php elseif($this->session->flashdata('message_type') == 'danger'): ?>
                        <i class="fas fa-exclamation-circle me-2"></i>
                    <?php else: ?>
                        <i class="fas fa-info-circle me-2"></i>
                    <?php endif; ?>
                </strong>
                <?= $this->session->flashdata('message') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>