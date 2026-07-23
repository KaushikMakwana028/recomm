<!-- Page Header -->
<div class="page-header">
    <div>
        <h1><i class="fas fa-plus me-2"></i>Add Product</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('products') ?>">Products</a></li>
                <li class="breadcrumb-item active">Add</li>
            </ol>
        </nav>
    </div>
</div>

<?= form_open_multipart('products/add') ?>
<div class="row">
    <!-- Left Column -->
    <div class="col-lg-8">
        <!-- Basic Information -->
        <div class="card mb-4">
            <div class="card-header">
                <span><i class="fas fa-info-circle me-2"></i>Basic Information</span>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Product Name *</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="<?= set_value('name') ?>" required>
                    <?= form_error('name', '<small class="text-danger">', '</small>') ?>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" 
                              rows="5"><?= set_value('description') ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="category_id" class="form-label">Category *</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category->id ?>" <?= set_select('category_id', $category->id) ?>>
                                    <?= $category->name ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?= form_error('category_id', '<small class="text-danger">', '</small>') ?>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="sku" class="form-label">SKU</label>
                        <input type="text" class="form-control" id="sku" name="sku" 
                               value="<?= set_value('sku') ?>" placeholder="e.g., PROD-001">
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing -->
        <div class="card mb-4">
            <div class="card-header">
                <span><i class="fas fa-rupee-sign me-2"></i>Pricing</span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="price" class="form-label">Regular Price *</label>
                        <input type="number" class="form-control" id="price" name="price" 
                               value="<?= set_value('price') ?>" step="0.01" min="0" required>
                        <?= form_error('price', '<small class="text-danger">', '</small>') ?>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="sale_price" class="form-label">Sale Price</label>
                        <input type="number" class="form-control" id="sale_price" name="sale_price" 
                               value="<?= set_value('sale_price') ?>" step="0.01" min="0">
                        <small class="text-muted">Leave empty if no sale</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Images -->
        <div class="card">
            <div class="card-header">
                <span><i class="fas fa-images me-2"></i>Product Images</span>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="image" class="form-label">Main Image</label>
                    <input type="file" class="form-control" id="image" name="image" 
                           accept="image/*" onchange="previewMainImage(this)">
                    <small class="text-muted">Recommended size: 800x800px</small>
                    
                    <div id="mainImagePreview" class="mt-3" style="display: none;">
                        <img id="mainPreview" src="" alt="Preview" class="img-thumbnail" 
                             style="max-width: 200px;">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="gallery" class="form-label">Gallery Images</label>
                    <input type="file" class="form-control" id="gallery" name="gallery[]" 
                           accept="image/*" multiple onchange="previewGalleryImages(this)">
                    <small class="text-muted">You can select multiple images</small>
                    
                    <div id="galleryPreview" class="mt-3 d-flex flex-wrap gap-2"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-lg-4">
        <!-- Publish -->
        <div class="card mb-4">
            <div class="card-header">
                <span><i class="fas fa-cog me-2"></i>Publish</span>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" 
                               name="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Product
                    </button>
                    <a href="<?= base_url('products') ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </div>
        </div>

        <!-- Tips -->
        <div class="card">
            <div class="card-header">
                <span><i class="fas fa-lightbulb me-2"></i>Tips</span>
            </div>
            <div class="card-body">
                <ul class="list-unstyled small">
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Use clear product names
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Add detailed descriptions
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Upload high-quality images
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Set competitive prices
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-info-circle text-info me-2"></i>
                        Vendors can add this product later
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?= form_close() ?>

<script>
function previewMainImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#mainPreview').attr('src', e.target.result);
            $('#mainImagePreview').show();
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function previewGalleryImages(input) {
    $('#galleryPreview').empty();
    
    if (input.files) {
        for (let i = 0; i < input.files.length; i++) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#galleryPreview').append(`
                    <img src="${e.target.result}" class="img-thumbnail" 
                         style="width: 100px; height: 100px; object-fit: cover;">
                `);
            }
            reader.readAsDataURL(input.files[i]);
        }
    }
}
</script>