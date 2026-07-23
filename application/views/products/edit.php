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

    /* Prefix wrapper for currency fields */
    .rc-input-prefix {
        position: relative;
    }

    .rc-input-prefix .prefix-symbol {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--rc-muted);
        font-weight: 600;
        font-size: 0.9rem;
        pointer-events: none;
    }

    .rc-input-prefix input {
        padding-left: 1.85rem;
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

    /* ---------- Status pill inside Publish card ---------- */
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

    /* ---------- Sticky sidebar (desktop only) ---------- */
    @media (min-width: 992px) {
        .rc-sticky-sidebar {
            position: sticky;
            top: 1.5rem;
        }
    }

    /* ---------- Image upload dropzone ---------- */
    .rc-upload-box {
        position: relative;
        width: 100%;
        aspect-ratio: 1 / 1;
        max-width: 220px;
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
        font-size: 0.8rem;
        font-weight: 600;
    }

    .rc-upload-box:hover .rc-upload-overlay {
        opacity: 1;
    }

    .rc-upload-box .rc-upload-overlay i {
        font-size: 1.2rem;
    }

    .rc-upload-hint {
        font-size: 0.78rem;
        color: var(--rc-muted);
        margin-top: 0.6rem;
    }

    /* ---------- Gallery grid ---------- */
    .rc-gallery-label-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.6rem;
    }

    .rc-gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(78px, 1fr));
        gap: 0.6rem;
    }

    .rc-gallery-tile {
        position: relative;
        width: 100%;
        aspect-ratio: 1 / 1;
        border-radius: var(--rc-radius-sm);
        overflow: hidden;
        border: 1px solid var(--rc-line);
        box-shadow: 0 2px 8px rgba(11, 61, 41, 0.05);
    }

    .rc-gallery-tile img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .rc-gallery-tile.new-tile::after {
        content: 'NEW';
        position: absolute;
        top: 4px;
        left: 4px;
        background: var(--rc-green-600);
        color: #fff;
        font-size: 0.55rem;
        font-weight: 700;
        letter-spacing: .04em;
        padding: 2px 5px;
        border-radius: 4px;
    }

    .rc-gallery-add-tile {
        width: 100%;
        aspect-ratio: 1 / 1;
        border-radius: var(--rc-radius-sm);
        border: 2px dashed var(--rc-line);
        background: var(--rc-bg-tint, #f9fbfb);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 0.2rem;
        cursor: pointer;
        color: var(--rc-green-600);
        transition: all 0.2s ease;
    }

    .rc-gallery-add-tile:hover {
        border-color: var(--rc-green-500);
        background: rgba(31, 157, 99, 0.05);
    }

    .rc-gallery-add-tile i {
        font-size: 1.1rem;
    }

    .rc-gallery-add-tile span {
        font-size: 0.6rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .03em;
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
        }

        .rc-mobile-action-bar .rc-btn-secondary {
            flex: 0 0 40%;
        }

        .rc-mobile-action-bar .rc-btn-primary {
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
                <h1>Edit Product</h1>
                <div class="rc-subtitle"><?= htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
        <ul class="rc-breadcrumb">
            <li><a href="<?= base_url('dashboard') ?>"><i class="fas fa-home"></i> Dashboard</a></li>
            <li>&nbsp;/&nbsp;</li>
            <li><a href="<?= base_url('products') ?>">Products</a></li>
            <li>&nbsp;/&nbsp;Edit</li>
        </ul>
    </div>

    <?= form_open_multipart('products/edit/' . $product->id) ?>
    <div class="row">
        <!-- Left Column: content flows in the order a user actually fills it in -->
        <div class="col-lg-8">

            <!-- 1. Basic Information -->
            <div class="rc-card">
                <div class="rc-card-header">
                    <div class="rc-card-icon-box"><i class="fas fa-info-circle"></i></div>
                    <div>
                        <h3>Basic Information</h3>
                        <div class="rc-card-sub">Name, description and category</div>
                    </div>
                </div>

                <div class="rc-form-group">
                    <label for="name" class="rc-form-label">Product Name <span class="req">*</span></label>
                    <input type="text" class="rc-form-control" id="name" name="name"
                        value="<?= set_value('name', $product->name) ?>" required>
                    <?= form_error('name', '<small class="text-danger mt-1 d-block">', '</small>') ?>
                </div>

                <div class="rc-form-group">
                    <label for="description" class="rc-form-label">Description</label>
                    <textarea class="rc-form-control" id="description" name="description"
                        rows="4"><?= set_value('description', $product->description) ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 rc-form-group">
                        <label for="category_id" class="rc-form-label">Category <span class="req">*</span></label>
                        <select class="rc-form-control form-select" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category->id ?>"
                                    <?= set_select('category_id', $category->id, $category->id == $product->category_id) ?>>
                                    <?= $category->name ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?= form_error('category_id', '<small class="text-danger mt-1 d-block">', '</small>') ?>
                    </div>

                    <div class="col-md-6 rc-form-group">
                        <label for="sku" class="rc-form-label">SKU</label>
                        <input type="text" class="rc-form-control" id="sku" name="sku"
                            value="<?= set_value('sku', $product->sku) ?>">
                    </div>
                </div>
            </div>

            <!-- 2. Product Images (moved up so it follows naturally after basic info) -->
            <div class="rc-card">
                <div class="rc-card-header">
                    <div class="rc-card-icon-box"><i class="fas fa-images"></i></div>
                    <div>
                        <h3>Product Images</h3>
                        <div class="rc-card-sub">Main photo and gallery</div>
                    </div>
                </div>

                <div class="rc-form-group">
                    <label class="rc-form-label">Main Image</label>

                    <label for="image" class="rc-upload-box" id="mainUploadBox">
                        <?php if ($product->image): ?>
                            <img id="mainPreview" src="<?= base_url($product->image) ?>" alt="Main image">
                        <?php else: ?>
                            <div class="rc-upload-placeholder" id="mainPlaceholder">
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
                        onchange="previewMainImage(this)" style="display:none;">

                    <div class="rc-upload-hint">Leave empty to keep the current main image. Allowed: JPG, PNG, GIF.</div>
                </div>

                <div class="rc-form-group mb-0">
                    <div class="rc-gallery-label-row">
                        <label class="rc-form-label mb-0">Gallery Images</label>
                    </div>

                    <div class="rc-gallery-grid mb-2" id="currentGalleryGrid">
                        <?php if (!empty($gallery_images)): ?>
                            <?php foreach ($gallery_images as $img): ?>
                                <div class="rc-gallery-tile">
                                    <img src="<?= base_url($img) ?>" alt="Gallery image">
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <div class="rc-gallery-add-tile" onclick="document.getElementById('gallery').click();">
                            <i class="fas fa-plus"></i>
                            <span>Add</span>
                        </div>
                    </div>
                    <input type="file" id="gallery" name="gallery[]" accept="image/*"
                        multiple onchange="previewGalleryImages(this)" style="display:none;">

                    <div class="rc-upload-hint">Selecting new files replaces the gallery. You can select multiple images.</div>

                    <div id="galleryPreview" class="rc-gallery-grid mt-3"></div>
                </div>
            </div>

            <!-- 3. Pricing -->
            <div class="rc-card mb-lg-0">
                <div class="rc-card-header">
                    <div class="rc-card-icon-box"><i class="fas fa-coins"></i></div>
                    <div>
                        <h3>Pricing</h3>
                        <div class="rc-card-sub">Regular and sale price</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 rc-form-group">
                        <label for="price" class="rc-form-label">Regular Price <span class="req">*</span></label>
                        <div class="rc-input-prefix">
                            <span class="prefix-symbol">₹</span>
                            <input type="number" class="rc-form-control" id="price" name="price"
                                value="<?= set_value('price', $product->price) ?>" step="0.01" min="0" required>
                        </div>
                        <?= form_error('price', '<small class="text-danger mt-1 d-block">', '</small>') ?>
                    </div>

                    <div class="col-md-6 rc-form-group mb-0">
                        <label for="sale_price" class="rc-form-label">Sale Price</label>
                        <div class="rc-input-prefix">
                            <span class="prefix-symbol">₹</span>
                            <input type="number" class="rc-form-control" id="sale_price" name="sale_price"
                                value="<?= set_value('sale_price', $product->sale_price) ?>" step="0.01" min="0">
                        </div>
                        <small class="text-muted mt-1 d-block">Leave empty if no sale discount is active</small>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column: Publish settings, sticky on desktop -->
        <div class="col-lg-4">
            <div class="rc-sticky-sidebar">
                <div class="rc-card mb-lg-0">
                    <div class="rc-card-header">
                        <div class="rc-card-icon-box"><i class="fas fa-cog"></i></div>
                        <div>
                            <h3>Publish Settings</h3>
                            <div class="rc-card-sub">Visibility and save</div>
                        </div>
                    </div>

                    <div class="rc-status-row">
                        <div class="rc-status-text">
                            <strong>Active</strong>
                            <span>Visible in your store</span>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="is_active"
                                name="is_active" value="1" <?= $product->is_active == 1 ? 'checked' : '' ?>>
                        </div>
                    </div>

                    <!-- Desktop actions (hidden on mobile, replaced by sticky bar) -->
                    <div class="d-flex flex-column gap-2 rc-desktop-actions">
                        <button type="submit" class="rc-btn-primary">
                            <i class="fas fa-save"></i> Update Product
                        </button>
                        <a href="<?= base_url('products') ?>" class="rc-btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile sticky action bar -->
    <div class="rc-mobile-action-bar">
        <a href="<?= base_url('products') ?>" class="rc-btn-secondary">
            <i class="fas fa-times"></i> Cancel
        </a>
        <button type="submit" class="rc-btn-primary">
            <i class="fas fa-save"></i> Update
        </button>
    </div>

    <?= form_close() ?>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    function previewMainImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const box = document.getElementById('mainUploadBox');
                let img = document.getElementById('mainPreview');
                const placeholder = document.getElementById('mainPlaceholder');
                if (placeholder) placeholder.remove();
                if (!img) {
                    img = document.createElement('img');
                    img.id = 'mainPreview';
                    img.alt = 'Main image';
                    box.prepend(img);
                }
                img.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewGalleryImages(input) {
        const previewContainer = document.getElementById('galleryPreview');
        previewContainer.innerHTML = '';

        if (input.files && input.files.length) {
            for (let i = 0; i < input.files.length; i++) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const tile = document.createElement('div');
                    tile.className = 'rc-gallery-tile new-tile';
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = 'New gallery image';
                    tile.appendChild(img);
                    previewContainer.appendChild(tile);
                }
                reader.readAsDataURL(input.files[i]);
            }
        }
    }
</script>