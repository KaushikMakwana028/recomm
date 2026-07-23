<div class="page-header">
    <div>
        <h1><i class="fas fa-list me-2"></i>Categories</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Categories</li>
            </ol>
        </nav>
    </div>
    <a href="<?= base_url('categories/add') ?>" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add Category
    </a>
</div>

<!-- Flash Messages -->
<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= $this->session->flashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?= $this->session->flashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Categories List -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-table me-2"></i>All Categories</span>
        <div class="d-flex gap-2">
            <div class="input-group input-group-sm" style="width: 250px;">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" 
                       id="searchInput" 
                       class="form-control" 
                       placeholder="Search by name..."
                       autocomplete="off">
            </div>
            <button type="button" id="clearSearch" class="btn btn-sm btn-secondary" style="display: none;">
                <i class="fas fa-times"></i> Clear
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Loading Spinner -->
        <div id="loadingSpinner" class="text-center py-5" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-2">Loading categories...</p>
        </div>

        <!-- Table -->
        <div class="table-responsive" id="categoriesTableContainer">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th width="80">ID</th>
                        <th width="100">Image</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th width="100">Status</th>
                        <th width="120">Created</th>
                        <th width="150" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="categoriesTableBody">
                    <!-- Data will be loaded via AJAX -->
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div id="paginationContainer" class="mt-3"></div>

        <!-- Results Info -->
        <div id="resultsInfo" class="text-center text-muted mt-2" style="font-size: 0.875rem;"></div>
    </div>
</div>

<!-- Delete Form (Hidden) -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="_method" value="DELETE">
</form>

<style>
/* Custom styles */
#categoriesTableContainer {
    min-height: 400px;
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

/* Smooth transitions */
#categoriesTableBody tr {
    transition: background-color 0.2s;
}

#categoriesTableBody tr:hover {
    background-color: rgba(0, 0, 0, 0.02);
}

.form-check-input:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    let currentPage = 1;
    let searchQuery = '';
    let searchTimeout = null;

    // Load categories on page load
    loadCategories();

    // Search functionality with debounce
    $('#searchInput').on('keyup', function() {
        const value = $(this).val().trim();
        
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            searchQuery = value;
            currentPage = 1;
            loadCategories();
            
            if (value) {
                $('#clearSearch').show();
            } else {
                $('#clearSearch').hide();
            }
        }, 500); // 500ms debounce
    });

    // Clear search
    $('#clearSearch').click(function() {
        $('#searchInput').val('');
        searchQuery = '';
        currentPage = 1;
        $(this).hide();
        loadCategories();
    });

    // Pagination click
    $(document).on('click', '.pagination a.page-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page) {
            currentPage = page;
            loadCategories();
        }
    });

    // Load categories function
    function loadCategories() {
        showLoading();

        $.ajax({
            url: '<?= base_url("categories/get_categories") ?>',
            type: 'POST',
            data: {
                page: currentPage,
                search: searchQuery
            },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    $('#categoriesTableBody').html(response.html);
                    $('#paginationContainer').html(response.pagination);
                    
                    // Update results info
                    let info = '';
                    if (response.total_records > 0) {
                        const start = ((response.current_page - 1) * 10) + 1;
                        const end = Math.min(response.current_page * 10, response.total_records);
                        info = `Showing ${start} to ${end} of ${response.total_records} entries`;
                        
                        if (searchQuery) {
                            info += ` (filtered from search: "${searchQuery}")`;
                        }
                    } else {
                        info = 'No records found';
                    }
                    $('#resultsInfo').text(info);
                    
                    // Scroll to top smoothly
                    $('html, body').animate({
                        scrollTop: $('#categoriesTableContainer').offset().top - 100
                    }, 300);
                } else {
                    showError('Failed to load categories');
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

    // Toggle status (delegated event)
    $(document).on('change', '.status-toggle', function() {
        const toggle = $(this);
        const id = toggle.data('id');
        const status = toggle.is(':checked') ? 1 : 0;
        
        // Disable toggle during request
        toggle.prop('disabled', true);
        
        $.ajax({
            url: '<?= base_url("categories/toggle_status") ?>',
            type: 'POST',
            data: { 
                id: id, 
                status: status            },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    // Optional: Show success toast
                    // showToast('Status updated successfully', 'success');
                } else {
                    alert(response.message || 'Failed to update status');
                    toggle.prop('checked', !status);
                }
            },
            error: function(xhr) {
                alert('Something went wrong. Please try again.');
                toggle.prop('checked', !status);
            },
            complete: function() {
                toggle.prop('disabled', false);
            }
        });
    });

    // Delete category (delegated event)
    $(document).on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        if (confirm(`Are you sure you want to delete "${name}"?\n\nThis action cannot be undone.`)) {
            const form = $('#deleteForm');
            form.attr('action', '<?= base_url("categories/delete/") ?>' + id);
            form.submit();
        }
    });

    // Helper functions
    function showLoading() {
        $('#loadingSpinner').show();
        $('#categoriesTableContainer').css('opacity', '0.5');
    }

    function hideLoading() {
        $('#loadingSpinner').hide();
        $('#categoriesTableContainer').css('opacity', '1');
    }

    function showError(message) {
        $('#categoriesTableBody').html(`
            <tr>
                <td colspan="7" class="text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <p class="text-danger">${message}</p>
                    <button class="btn btn-primary" onclick="location.reload()">
                        <i class="fas fa-sync me-2"></i>Reload Page
                    </button>
                </td>
            </tr>
        `);
        $('#paginationContainer').html('');
        $('#resultsInfo').text('');
    }
});
</script>