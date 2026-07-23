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
    }

    /* Tip list styling */
    .rc-tips-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .rc-tips-list li {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        margin-bottom: 0.85rem;
        font-size: 0.9rem;
        color: var(--rc-ink);
        line-height: 1.4;
    }

    .rc-tips-list li i {
        color: var(--rc-emerald-400);
        margin-top: 3px;
        font-size: 0.95rem;
    }
</style>

<div class="rc-form-container">

    <!-- Page Header -->
    <div class="rc-page-header">
        <div class="rc-header-left">
            <div class="rc-header-badge"><i class="fas fa-plus"></i></div>
            <div>
                <h1>Add Product</h1>
                <div class="rc-subtitle">Create a new product in system inventory</div>
            </div>
        </div>
        <ul class="rc-breadcrumb">
            <li><a href="<?= base_url('dashboard') ?>"><i class="fas fa-home"></i> Dashboard</a></li>
            <li>&nbsp;/&nbsp;</li>
            <li><a href="<?= base_url('products') ?>">Products</a></li>
            <li>&nbsp;/&nbsp;Add</li>
        </ul>
    </div>

    <?= form_open_multipart('products/add') ?>
    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="rc-card">
                <div class="rc-card-header">
                    <i class="fas fa-info-circle"></i>
                    <h3>Basic Information</h3>
                </div>
                
                <div class="rc-form-group">
                    <label for="name" class="rc-form-label">Product Name *</label>
                    <input type="text" class="rc-form-control" id="name" name="name" 
                           value="<?= set_value('name') ?>" required placeholder="e.g. Fresh Red Apple">
                    <?= form_error('name', '<small class="text-danger mt-1 d-block">', '</small>') ?>
                </div>
                
                <div class="rc-form-group">
                    <label for="description" class="rc-form-label">Description</label>
                    <textarea class="rc-form-control" id="description" name="description" 
                              rows="5" placeholder="Add detailed product description, specifications, organic tags, etc..."><?= set_value('description') ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6 rc-form-group">
                        <label for="category_id" class="rc-form-label">Category *</label>
                        <select class="rc-form-control form-select" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category->id ?>" <?= set_select('category_id', $category->id) ?>>
                                    <?= $category->name ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?= form_error('category_id', '<small class="text-danger mt-1 d-block">', '</small>') ?>
                    </div>
                    
                    <div class="col-md-6 rc-form-group">
                        <label for="sku" class="rc-form-label">SKU (Stock Keeping Unit)</label>
                        <input type="text" class="rc-form-control" id="sku" name="sku" 
                               value="<?= set_value('sku') ?>" placeholder="e.g. FRU-APP-001">
                    </div>
                </div>
            </div>

            <!-- Pricing -->
            <div class="rc-card">
                <div class="rc-card-header">
                    <i class="fas fa-coins"></i>
                    <h3>Pricing</h3>
                </div>
                
                <div class="row">
                    <div class="col-md-6 rc-form-group">
                        <label for="price" class="rc-form-label">Regular Price (₹) *</label>
                        <input type="number" class="rc-form-control" id="price" name="price" 
                               value="<?= set_value('price') ?>" step="0.01" min="0" required placeholder="0.00">
                        <?= form_error('price', '<small class="text-danger mt-1 d-block">', '</small>') ?>
                    </div>
                    
                    <div class="col-md-6 rc-form-group">
                        <label for="sale_price" class="rc-form-label">Sale Price (₹)</label>
                        <input type="number" class="rc-form-control" id="sale_price" name="sale_price" 
                               value="<?= set_value('sale_price') ?>" step="0.01" min="0" placeholder="0.00">
                        <small class="text-muted mt-1 d-block">Leave empty if no special sale discount is active</small>
                    </div>
                </div>
            </div>

            <!-- Images -->
            <div class="rc-card">
                <div class="rc-card-header">
                    <i class="fas fa-images"></i>
                    <h3>Product Images</h3>
                </div>
                
                <div class="rc-form-group">
                    <label for="image" class="rc-form-label">Main Image</label>
                    <input type="file" class="rc-form-control" id="image" name="image" 
                           accept="image/*" onchange="previewMainImage(this)">
                    <small class="text-muted mt-1 d-block">Recommended resolution: 800x800px square format. Allowed: JPG, PNG, GIF</small>
                    
                    <div id="mainImagePreview" class="mt-3" style="display: none;">
                        <img id="mainPreview" src="" alt="Preview" class="img-thumbnail" 
                             style="max-width: 180px; border-radius: var(--rc-radius-sm); border: 1px solid var(--rc-line);">
                    </div>
                </div>
                
                <div class="rc-form-group mb-0">
                    <label for="gallery" class="rc-form-label">Gallery Images</label>
                    <input type="file" class="rc-form-control" id="gallery" name="gallery[]" 
                           accept="image/*" multiple onchange="previewGalleryImages(this)">
                    <small class="text-muted mt-1 d-block">You can select multiple supporting photos</small>
                    
                    <div id="galleryPreview" class="mt-3 d-flex flex-wrap gap-2"></div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Publish options -->
            <div class="rc-card">
                <div class="rc-card-header">
                    <i class="fas fa-cog"></i>
                    <h3>Publish Settings</h3>
                </div>
                
                <div class="rc-form-group mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" 
                               name="is_active" value="1" checked>
                        <label class="form-check-label rc-form-label ms-2 d-inline-block" for="is_active" style="margin-bottom: 0;">Active</label>
                    </div>
                </div>
                
                <div class="d-flex flex-column gap-2">
                    <button type="submit" class="rc-btn-primary">
                        <i class="fas fa-save"></i> Save Product
                    </button>
                    <a href="<?= base_url('products') ?>" class="rc-btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </div>

            <!-- Tips info -->
            <div class="rc-card">
                <div class="rc-card-header">
                    <i class="fas fa-lightbulb"></i>
                    <h3>Inventory Tips</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="rc-tips-list">
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span>Write precise and descriptive product titles.</span>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span>Provide list points or specs in the description.</span>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span>Include multiple gallery photos showing the product from different angles.</span>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span>Make sure prices are competitive compared to general marketplaces.</span>
                        </li>
                        <li>
                            <i class="fas fa-info-circle" style="color: var(--rc-gold-500);"></i>
                            <span>Vendors will be able to search and link this product to their stores.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?= form_close() ?>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
function previewMainImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('mainPreview').src = e.target.result;
            document.getElementById('mainImagePreview').style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function previewGalleryImages(input) {
    const previewContainer = document.getElementById('galleryPreview');
    previewContainer.innerHTML = '';
    
    if (input.files) {
        for (let i = 0; i < input.files.length; i++) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'img-thumbnail';
                img.style.width = '80px';
                img.style.height = '80px';
                img.style.objectFit = 'cover';
                img.style.borderRadius = 'var(--rc-radius-sm)';
                img.style.border = '1px solid var(--rc-line)';
                previewContainer.appendChild(img);
            }
            reader.readAsDataURL(input.files[i]);
        }
    }
}
</script>