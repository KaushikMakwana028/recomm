<!-- Page Header -->
<div class="page-header">
    <div>
        <h1><i class="fas fa-box me-2"></i>Products</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?= base_url('dashboard') ?>">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Products</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#bulkAddModal">
            <i class="fas fa-file-upload me-2"></i>Bulk Add
        </button>
        <a href="<?= base_url('products/add') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add Product
        </a>
    </div>
</div>

<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?= $this->session->flashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?= $this->session->flashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-4">
                <span>
                    <i class="fas fa-table me-2"></i>All Products
                    <span id="recordCount" class="badge bg-secondary ms-2">0</span>
                </span>
            </div>
            <div class="col-md-8">
                <div class="d-flex gap-2 justify-content-end">

                    <!-- Category Filter -->
                    <select id="categoryFilter" class="form-select form-select-sm" style="width: 180px;">
                        <option value="">All Categories</option>
                        <?php
                        $CI = &get_instance();
                        $categories = $CI->gm->getAll('categories', ['is_active' => 1], 'id, name', 'name ASC');
                        foreach ($categories as $cat):
                        ?>
                            <option value="<?= $cat->id ?>"><?= htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Search Input -->
                    <div class="input-group input-group-sm" style="width: 280px;">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text"
                            id="searchInput"
                            class="form-control"
                            placeholder="Search by name or SKU..."
                            autocomplete="off">
                    </div>

                    <!-- Clear Button -->
                    <button type="button" id="clearFilters" class="btn btn-sm btn-secondary" style="display: none;">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body">

        <!-- Loading Spinner -->
        <div id="loadingSpinner" class="text-center py-5" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-2">Loading products...</p>
        </div>

        <!-- Table -->
        <div class="table-responsive" id="productsTableContainer">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="60">#</th>
                        <th width="100">Image</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th width="80">Status</th>
                        <th width="150" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="productsTableBody">
                    <!-- Loaded via AJAX -->
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div id="paginationContainer" class="mt-3"></div>

        <!-- Results Info -->
        <div id="resultsInfo" class="text-center text-muted mt-2" style="font-size: 0.875rem;"></div>

    </div>
</div>

<!-- Delete Form -->
<form id="deleteForm" method="POST" style="display: none;">
</form>


<!-- Bulk Add Modal -->
<div class="modal fade" id="bulkAddModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-upload me-2"></i>Bulk Add Products</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <div class="alert alert-info d-flex align-items-start gap-2">
                    <i class="fas fa-circle-info mt-1"></i>
                    <div>
                        <strong>Required columns:</strong> Product Name, Category, Regular Price, Sale Price, Main Image (HTTPS URL).<br>
                        <strong>Optional columns:</strong> SKU, Description, Gallery Images.<br>
                        Main Image and Gallery Images must be public <code>https://</code> links, max <strong>1MB</strong> each.
                        If <u>any</u> row has an error, no products are added.
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small">Download a ready-to-fill template:</span>
                    <a href="<?= base_url('products/download_template') ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-download me-1"></i>Download Template
                    </a>
                </div>

                <div id="bulkDropZone" class="border border-2 rounded p-4 text-center bg-light">
                    <i class="fas fa-cloud-arrow-up fa-2x text-primary mb-2 d-block"></i>
                    <p class="mb-2">Drag & drop your CSV, XLSX or XLS file here</p>
                    <p class="text-muted small mb-2">or</p>
                    <input type="file" id="bulkFileInput" accept=".csv,.xlsx,.xls" class="form-control" style="max-width: 320px; margin: 0 auto;">
                    <div id="bulkFileName" class="mt-2 fw-bold text-primary"></div>
                </div>

                <div id="bulkUploadResult" class="mt-3" style="display:none;"></div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" id="bulkUploadBtn" class="btn btn-primary" disabled>
                    <i class="fas fa-upload me-2"></i>Upload & Add Products
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    #productsTableContainer {
        min-height: 400px;
        transition: opacity 0.3s;
    }

    .pagination .page-link {
        cursor: pointer;
    }

    .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .page-item.disabled .page-link {
        cursor: not-allowed;
    }

    #bulkDropZone {
        border-style: dashed !important;
        transition: border-color 0.2s, background-color 0.2s;
    }

    #bulkDropZone.border-primary {
        background-color: #eef4ff;
    }
</style>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function() {
        let currentPage = 1;
        let searchQuery = '';
        let categoryId = '';
        let searchTimeout = null;

        // Initial load
        loadProducts();

        // Search with debounce
        $('#searchInput').on('keyup', function() {
            clearTimeout(searchTimeout);
            const value = $(this).val().trim();
            searchTimeout = setTimeout(function() {
                searchQuery = value;
                currentPage = 1;
                loadProducts();
                updateClearButton();
            }, 500);
        });

        // Category filter
        $('#categoryFilter').on('change', function() {
            categoryId = $(this).val();
            currentPage = 1;
            loadProducts();
            updateClearButton();
        });

        // Clear filters
        $('#clearFilters').on('click', function() {
            $('#searchInput').val('');
            $('#categoryFilter').val('');
            searchQuery = '';
            categoryId = '';
            currentPage = 1;
            $(this).hide();
            loadProducts();
        });

        // Pagination click
        $(document).on('click', '.pagination a.page-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            if (page) {
                currentPage = page;
                loadProducts();
                $('html, body').animate({
                    scrollTop: $('#productsTableContainer').offset().top - 100
                }, 300);
            }
        });

        // Load products
        function loadProducts() {
            showLoading();

            $.ajax({
                url: '<?= base_url("products/get_products") ?>',
                type: 'POST',
                data: {
                    page: currentPage,
                    search: searchQuery,
                    category_id: categoryId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        $('#productsTableBody').html(response.html);
                        $('#paginationContainer').html(response.pagination);
                        $('#recordCount').text(response.total_records);

                        // Results info
                        if (response.total_records > 0) {
                            const start = ((response.current_page - 1) * 10) + 1;
                            const end = Math.min(response.current_page * 10, response.total_records);
                            let info = `Showing ${start} to ${end} of ${response.total_records} entries`;

                            let filters = [];
                            if (searchQuery) filters.push(`search: "${searchQuery}"`);
                            if (categoryId) filters.push(`category filter applied`);
                            if (filters.length) info += ` (filtered by ${filters.join(', ')})`;

                            $('#resultsInfo').text(info);
                        } else {
                            $('#resultsInfo').text('No records found');
                        }
                    } else {
                        showError('Failed to load products');
                    }
                },
                error: function(xhr) {
                    console.error('AJAX Error:', xhr);
                    showError('Something went wrong. Please refresh the page.');
                },
                complete: function() {
                    hideLoading();
                }
            });
        }

        // Toggle status
        $(document).on('change', '.status-toggle', function() {
            const toggle = $(this);
            const id = toggle.data('id');
            const status = toggle.is(':checked') ? 1 : 0;

            toggle.prop('disabled', true);

            $.ajax({
                url: '<?= base_url("products/toggle_status") ?>',
                type: 'POST',
                data: {
                    id: id,
                    status: status
                },
                dataType: 'json',
                success: function(response) {
                    if (!response.status) {
                        alert(response.message || 'Failed to update status');
                        toggle.prop('checked', !status);
                    }
                },
                error: function() {
                    alert('Something went wrong. Please try again.');
                    toggle.prop('checked', !status);
                },
                complete: function() {
                    toggle.prop('disabled', false);
                }
            });
        });

        // Delete product
        $(document).on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');

            if (confirm(`Are you sure you want to delete "${name}"?\n\nThis action cannot be undone.`)) {
                $('#deleteForm').attr('action', '<?= base_url("products/delete/") ?>' + id).submit();
            }
        });

        // Helpers
        function updateClearButton() {
            (searchQuery || categoryId) ? $('#clearFilters').show(): $('#clearFilters').hide();
        }

        function showLoading() {
            $('#loadingSpinner').show();
            $('#productsTableContainer').css('opacity', '0.5');
        }

        function hideLoading() {
            $('#loadingSpinner').hide();
            $('#productsTableContainer').css('opacity', '1');
        }

        function showError(message) {
            $('#productsTableBody').html(`
            <tr>
                <td colspan="7" class="text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3 d-block"></i>
                    <p class="text-danger">${message}</p>
                    <button class="btn btn-primary" onclick="location.reload()">
                        <i class="fas fa-sync me-2"></i>Reload Page
                    </button>
                </td>
            </tr>
        `);
            $('#paginationContainer').html('');
            $('#resultsInfo').text('');
            $('#recordCount').text('0');
        }
    });
</script>

<script>
    $(document).ready(function() {
        let bulkFile = null;

        function setBulkFile(file) {
            bulkFile = file;
            $('#bulkFileName').text(file ? file.name : '');
            $('#bulkUploadBtn').prop('disabled', !file);
            $('#bulkUploadResult').hide().empty();
        }

        $('#bulkFileInput').on('change', function() {
            setBulkFile(this.files[0] || null);
        });

        const dropZone = document.getElementById('bulkDropZone');
        ['dragenter', 'dragover'].forEach(evt => {
            dropZone.addEventListener(evt, function(e) {
                e.preventDefault();
                dropZone.classList.add('border-primary');
            });
        });
        ['dragleave', 'drop'].forEach(evt => {
            dropZone.addEventListener(evt, function(e) {
                e.preventDefault();
                dropZone.classList.remove('border-primary');
            });
        });
        dropZone.addEventListener('drop', function(e) {
            const file = e.dataTransfer.files[0];
            if (file) {
                $('#bulkFileInput')[0].files = e.dataTransfer.files;
                setBulkFile(file);
            }
        });

        $('#bulkAddModal').on('hidden.bs.modal', function() {
            setBulkFile(null);
            $('#bulkFileInput').val('');
        });

        $('#bulkUploadBtn').on('click', function() {
            if (!bulkFile) return;

            const btn = $(this);
            const formData = new FormData();
            formData.append('bulk_file', bulkFile);

            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Uploading...');
            $('#bulkUploadResult').hide().empty();

            $.ajax({
                url: '<?= base_url("products/bulk_upload") ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        $('#bulkUploadResult').html(
                            '<div class="alert alert-success mb-0"><i class="fas fa-check-circle me-2"></i>' + response.message + '</div>'
                        ).show();
                        setTimeout(function() {
                            $('#bulkAddModal').modal('hide');
                            location.reload();
                        }, 1200);
                    } else if (response.errors && response.errors.length) {
                        let list = response.errors.map(function(e) {
                            return '<li><strong>Row ' + e.row + '</strong> (' + e.product + '): ' + e.errors.join(', ') + '</li>';
                        }).join('');
                        $('#bulkUploadResult').html(
                            '<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-triangle me-2"></i>' +
                            response.message + '<ul class="mb-0 mt-2">' + list + '</ul></div>'
                        ).show();
                    } else {
                        $('#bulkUploadResult').html(
                            '<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-triangle me-2"></i>' + response.message + '</div>'
                        ).show();
                    }
                },
                error: function() {
                    $('#bulkUploadResult').html(
                        '<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Something went wrong. Please try again.</div>'
                    ).show();
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-upload me-2"></i>Upload & Add Products');
                }
            });
        });
    });
</script>