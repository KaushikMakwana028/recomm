<style>
    /* Styling variables and custom elements for profile */
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

    .rc-profile-container {
        font-family: 'Inter', 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
        color: var(--rc-ink);
        padding-bottom: 2rem;
    }

    .rc-profile-container h1,
    .rc-profile-container h2,
    .rc-profile-container h3,
    .rc-profile-container h4,
    .rc-profile-container h5,
    .rc-profile-container h6 {
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

    /* ---------- Profile Avatar ---------- */
    .rc-avatar-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 1rem 0;
    }

    .rc-profile-avatar-container {
        position: relative;
        margin-bottom: 1.25rem;
    }

    .rc-profile-img {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid var(--rc-white);
        box-shadow: 0 8px 24px rgba(11, 61, 41, 0.15);
        background: linear-gradient(135deg, var(--rc-forest-800), var(--rc-emerald-400));
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 3.5rem;
        font-weight: 700;
    }

    .rc-avatar-upload-btn {
        position: absolute;
        bottom: 5px;
        right: 5px;
        background: var(--rc-green-600);
        color: #fff;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(22, 121, 79, 0.35);
        transition: all 0.2s ease;
        border: 3px solid var(--rc-white);
        z-index: 5;
    }

    .rc-avatar-upload-btn:hover {
        background: var(--rc-forest-800);
        transform: scale(1.1);
    }

    .rc-avatar-upload-btn input[type="file"] {
        display: none;
    }

    .rc-profile-name {
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
        color: var(--rc-forest-900);
    }

    .rc-role-badge {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        background: rgba(46, 204, 113, 0.12);
        color: var(--rc-green-600);
        padding: 0.35rem 0.85rem;
        border-radius: 50px;
        margin-bottom: 1.5rem;
        display: inline-block;
    }

    .rc-meta-item {
        display: flex;
        justify-content: space-between;
        width: 100%;
        padding: 0.75rem 0;
        border-top: 1px dashed var(--rc-line);
        font-size: 0.85rem;
    }

    .rc-meta-item .label {
        color: var(--rc-muted);
    }

    .rc-meta-item .val {
        font-weight: 600;
        color: var(--rc-ink);
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
        gap: 0.5rem;
        box-shadow: 0 4px 12px rgba(22, 121, 79, 0.2);
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .rc-btn-primary:hover {
        background: linear-gradient(135deg, var(--rc-forest-800), var(--rc-green-600));
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(22, 121, 79, 0.3);
    }
</style>

<div class="rc-profile-container">

    <!-- Page Header -->
    <div class="rc-page-header">
        <div class="rc-header-left">
            <div class="rc-header-badge"><i class="fas fa-user-circle"></i></div>
            <div>
                <h1>My Profile</h1>
                <div class="rc-subtitle">Manage your personal information and profile picture</div>
            </div>
        </div>
        <ul class="rc-breadcrumb">
            <li><a href="<?= base_url('dashboard') ?>"><i class="fas fa-home"></i> Dashboard</a></li>
            <li>&nbsp;/&nbsp;Profile</li>
        </ul>
    </div>

    <?= form_open_multipart('settings/profile') ?>
    <div class="row">
        <!-- Left Column: Avatar & Quick Info -->
        <div class="col-lg-4">
            <div class="rc-card">
                <div class="rc-avatar-wrapper">
                    <div class="rc-profile-avatar-container">
                        <?php if ($user->profile_image && file_exists($user->profile_image)): ?>
                            <img id="avatar-display" src="<?= base_url($user->profile_image) ?>" alt="<?= $user->name ?>" class="rc-profile-img">
                        <?php else: ?>
                            <div id="avatar-display" class="rc-profile-img">
                                <?= strtoupper(substr($user->name ?? 'A', 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        
                        <label class="rc-avatar-upload-btn" for="profile_image_input">
                            <i class="fas fa-camera"></i>
                            <input type="file" id="profile_image_input" name="profile_image" accept="image/*">
                        </label>
                    </div>

                    <div class="rc-profile-name"><?= html_escape($user->name) ?></div>
                    <div class="rc-role-badge">
                        <i class="fas fa-shield-alt me-1"></i><?= ucfirst($user->role) ?>
                    </div>

                    <div class="rc-meta-item">
                        <span class="label">Status</span>
                        <span class="val text-success">
                            <i class="fas fa-check-circle me-1"></i>Active
                        </span>
                    </div>
                    <div class="rc-meta-item">
                        <span class="label">Joined Date</span>
                        <span class="val"><?= date('M d, Y', strtotime($user->created_on)) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Edit Profile Details -->
        <div class="col-lg-8">
            <div class="rc-card">
                <div class="rc-card-header">
                    <i class="fas fa-id-card"></i>
                    <h3>Account Information</h3>
                </div>

                <div class="row">
                    <div class="col-md-6 rc-form-group">
                        <label for="name" class="rc-form-label">Full Name *</label>
                        <input type="text" class="rc-form-control" id="name" name="name" 
                               value="<?= set_value('name', $user->name) ?>" required>
                        <?= form_error('name', '<small class="text-danger mt-1 d-block">', '</small>') ?>
                    </div>

                    <div class="col-md-6 rc-form-group">
                        <label for="mobile" class="rc-form-label">Mobile Number *</label>
                        <input type="text" class="rc-form-control" id="mobile" name="mobile" 
                               value="<?= set_value('mobile', $user->mobile) ?>" maxlength="10" required>
                        <?= form_error('mobile', '<small class="text-danger mt-1 d-block">', '</small>') ?>
                    </div>
                </div>

                <div class="rc-form-group mb-4">
                    <label for="email" class="rc-form-label">Email Address</label>
                    <input type="email" class="rc-form-control" id="email" name="email" 
                           value="<?= set_value('email', $user->email) ?>">
                    <?= form_error('email', '<small class="text-danger mt-1 d-block">', '</small>') ?>
                </div>

                <div class="border-top var(--rc-line) pt-4 d-flex justify-content-end">
                    <button type="submit" class="rc-btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?= form_close() ?>

</div>

<script>
    document.getElementById('profile_image_input').addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var avatarEl = document.getElementById('avatar-display');
                if (avatarEl.tagName === 'IMG') {
                    avatarEl.src = e.target.result;
                } else {
                    // Create an img element to replace the initials text block
                    var img = document.createElement('img');
                    img.id = 'avatar-display';
                    img.className = 'rc-profile-img';
                    img.src = e.target.result;
                    avatarEl.parentNode.replaceChild(img, avatarEl);
                }
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
</script>
