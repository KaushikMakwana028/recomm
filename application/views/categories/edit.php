<!-- Page Header -->
<div class="page-header">
    <div>
        <h1><i class="fas fa-edit me-2"></i>Edit Category</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('categories') ?>">Categories</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Edit Category Form -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <span><i class="fas fa-info-circle me-2"></i>Category Information</span>
            </div>
            <div class="card-body">
                <?= form_open_multipart('categories/edit/' . $category->id) ?>
                
                    <div class="mb-3">
                        <label for="name" class="form-label">Category Name *</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= set_value('name', $category->name) ?>" required>
                        <?= form_error('name', '<small class="text-danger">', '</small>') ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" 
                                  rows="4"><?= set_value('description', $category->description) ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">Category Image</label>
                        
                        <?php if ($category->image): ?>
                            <div class="mb-2">
                                <img src="<?= base_url($category->image) ?>" alt="Current Image" 
                                     class="img-thumbnail" style="max-width: 200px;">
                            </div>
                        <?php endif; ?>
                        
                        <input type="file" class="form-control" id="image" name="image" 
                               accept="image/*" onchange="previewImage(this)">
                        <small class="text-muted">Leave empty to keep current image</small>
                        
                        <div id="imagePreview" class="mt-3" style="display: none;">
                            <img id="preview" src="" alt="Preview" class="img-thumbnail" 
                                 style="max-width: 200px;">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" 
                                   name="is_active" value="1" 
                                   <?= $category->is_active == 1 ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Category
                        </button>
                        <a href="<?= base_url('categories') ?>" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                    
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>

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