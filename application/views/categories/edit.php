<style>
    /* Styling variables and custom elements for categories */
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

    textarea.rc-form-control {
        resize: vertical;
        min-height: 110px;
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

    /* ---------- Status pill ---------- */
    .rc-status-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--rc-bg);
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-md);
        padding: 0.9rem 1.1rem;
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

    /* ---------- Image upload dropzone ---------- */
    .rc-upload-box {
        position: relative;
        width: 100%;
        aspect-ratio: 1 / 1;
        max-width: 200px;
        border: 2px dashed var(--rc-line);
        border-radius: var(--rc-radius-lg);
        background: var(--rc-bg-tint, #f9fbfb);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .rc-upload-box:hover {
        border-color: var(--rc-green-500);
        background: rgba(31, 157, 99, 0.04);
    }

    .rc-upload-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .rc-upload-box .rc-upload-placeholder {
        text-align: center;
        color: var(--rc-muted);
        padding: 1rem;
    }

    .rc-upload-box .rc-upload-placeholder i {
        font-size: 1.8rem;
        color: var(--rc-green-500);
        display: block;
        margin-bottom: 0.5rem;
    }

    .rc-upload-box .rc-upload-placeholder span {
        font-size: 0.78rem;
        font-weight: 600;
        display: block;
    }

    .rc-upload-box .rc-upload-overlay {
        position: absolute;
        inset: 0;
        background: rgba(7, 39, 27, 0.55);
        color: #fff;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        opacity: 0;
        transition: opacity 0.2s ease;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .rc-upload-box:hover .rc-upload-overlay {
        opacity: 1;
    }

    .rc-upload-box .rc-upload-overlay i {
        font-size: 1.2rem;
    }

    .rc-upload-box.has-new-image {
        border-style: solid;
        border-color: var(--rc-green-500);
    }

    .rc-upload-box .new-badge {
        position: absolute;
        top: 8px;
        left: 8px;
        background: var(--rc-green-600);
        color: #fff;
        font-size: 0.6rem;
        font-weight: 700;
        letter-spacing: .04em;
        padding: 3px 7px;
        border-radius: 5px;
        z-index: 1;
        display: none;
    }

    .rc-upload-box.has-new-image .new-badge {
        display: block;
    }

    .rc-upload-hint {
        font-size: 0.78rem;
        color: var(--rc-muted);
        margin-top: 0.6rem;
    }

    /* ---------- Tips card ---------- */
    @media (min-width: 992px) {
        .rc-sticky-sidebar {
            position: sticky;
            top: 1.5rem;
        }
    }

    .rc-tips-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .rc-tips-list li {
        display: flex;
        align-items: flex-start;
        gap: 0.7rem;
        padding: 0.7rem 0;
        border-bottom: 1px dashed var(--rc-line);
        font-size: 0.85rem;
        color: var(--rc-ink);
        line-height: 1.45;
    }

    .rc-tips-list li:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .rc-tips-list li:first-child {
        padding-top: 0;
    }

    .rc-tips-list li .tip-icon {
        width: 26px;
        height: 26px;
        border-radius: 8px;
        background: rgba(46, 204, 113, 0.12);
        color: var(--rc-green-600);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        flex-shrink: 0;
        margin-top: 1px;
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

        .rc-upload-box {
            max-width: 100%;
        }
    }
</style>

<div class="rc-form-container">

    <!-- Page Header -->
    <div class="rc-page-header">
        <div class="rc-header-left">
            <div class="rc-header-badge"><i class="fas fa-edit"></i></div>
            <div style="min-width: 0;">
                <h1>Edit Category</h1>
                <div class="rc-subtitle"><?= htmlspecialchars($category->name, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
        <ul class="rc-breadcrumb">
            <li><a href="<?= base_url('dashboard') ?>"><i class="fas fa-home"></i> Dashboard</a></li>
            <li>&nbsp;/&nbsp;</li>
            <li><a href="<?= base_url('categories') ?>">Categories</a></li>
            <li>&nbsp;/&nbsp;Edit</li>
        </ul>
    </div>

    <?= form_open_multipart('categories/edit/' . $category->id) ?>
    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <div class="rc-card mb-lg-0">
                <div class="rc-card-header">
                    <div class="rc-card-icon-box"><i class="fas fa-info-circle"></i></div>
                    <div>
                        <h3>Category Information</h3>
                        <div class="rc-card-sub">Name, description and image</div>
                    </div>
                </div>

                <div class="rc-form-group">
                    <label for="name" class="rc-form-label">Category Name <span class="req">*</span></label>
                    <input type="text" class="rc-form-control" id="name" name="name"
                        value="<?= set_value('name', $category->name) ?>" required>
                    <?= form_error('name', '<small class="text-danger mt-1 d-block">', '</small>') ?>
                </div>

                <div class="rc-form-group">
                    <label for="description" class="rc-form-label">Description</label>
                    <textarea class="rc-form-control" id="description" name="description"
                        rows="4"><?= set_value('description', $category->description) ?></textarea>
                </div>

                <div class="rc-form-group">
                    <label class="rc-form-label">Category Image</label>

                    <label for="image" class="rc-upload-box" id="imageUploadBox">
                        <span class="new-badge" id="newImageBadge">NEW</span>
                        <?php if ($category->image): ?>
                            <img id="preview" src="<?= base_url($category->image) ?>" alt="Category image">
                        <?php else: ?>
                            <div class="rc-upload-placeholder" id="imagePlaceholder">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Click to upload</span>
                            </div>
                        <?php endif; ?>
                        <div class="rc-upload-overlay">
                            <i class="fas fa-camera"></i>
                            <span>Change Image</span>
                        </div>
                    </label>
                    <input type="file" id="image" name="image" accept="image/*"
                        onchange="previewImage(this)" style="display:none;">

                    <div class="rc-upload-hint">Leave empty to keep the current image. Allowed: JPG, JPEG, PNG, GIF (Max: 2MB).</div>
                </div>

                <div class="rc-form-group mb-0">
                    <div class="rc-status-row">
                        <div class="rc-status-text">
                            <strong>Active</strong>
                            <span>Visible in your store</span>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="is_active"
                                name="is_active" value="1"
                                <?= $category->is_active == 1 ? 'checked' : '' ?>>
                        </div>
                    </div>
                </div>

                <!-- Desktop actions (hidden on mobile, replaced by sticky bar) -->
                <div class="border-top pt-4 mt-4 d-flex gap-2 rc-desktop-actions" style="border-color: var(--rc-line) !important;">
                    <button type="submit" class="rc-btn-primary">
                        <i class="fas fa-save"></i> Update Category
                    </button>
                    <a href="<?= base_url('categories') ?>" class="rc-btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <div class="rc-sticky-sidebar">
                <div class="rc-card mb-lg-0">
                    <div class="rc-card-header">
                        <div class="rc-card-icon-box"><i class="fas fa-lightbulb"></i></div>
                        <div>
                            <h3>Tips</h3>
                            <div class="rc-card-sub">For a great category page</div>
                        </div>
                    </div>
                    <ul class="rc-tips-list">
                        <li>
                            <span class="tip-icon"><i class="fas fa-check"></i></span>
                            <span>Choose a clear, descriptive, and unique category name.</span>
                        </li>
                        <li>
                            <span class="tip-icon"><i class="fas fa-check"></i></span>
                            <span>Upload high-quality, high-contrast images so the category stands out.</span>
                        </li>
                        <li>
                            <span class="tip-icon"><i class="fas fa-check"></i></span>
                            <span>Recommended image resolution: 500x500px square format.</span>
                        </li>
                        <li>
                            <span class="tip-icon"><i class="fas fa-check"></i></span>
                            <span>Keep the description short and concise for quick reading.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile sticky action bar -->
    <div class="rc-mobile-action-bar">
        <a href="<?= base_url('categories') ?>" class="rc-btn-secondary">
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
                const box = document.getElementById('imageUploadBox');
                let img = document.getElementById('preview');
                const placeholder = document.getElementById('imagePlaceholder');
                if (placeholder) placeholder.remove();
                if (!img) {
                    img = document.createElement('img');
                    img.id = 'preview';
                    img.alt = 'Category image';
                    box.prepend(img);
                }
                img.src = e.target.result;
                box.classList.add('has-new-image');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>