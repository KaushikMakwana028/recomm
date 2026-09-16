<!-- Summernote Lite CSS for Rich-Text Editor -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">

<style>
    /* Clean container & form styles matching ReComm design system */
    .note-editor.note-frame {
        border: 1.5px solid #ced4da !important;
        border-radius: 12px !important;
        overflow: hidden !important;
        background: #ffffff !important;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03) !important;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .note-editor.note-frame.focus {
        border-color: #1f9d63 !important;
        box-shadow: 0 0 0 3px rgba(31, 157, 99, 0.12) !important;
    }
    .note-toolbar {
        background: #f8faf9 !important;
        border-bottom: 1px solid #e9ecef !important;
        padding: 8px 12px !important;
    }
    .note-btn {
        border-radius: 6px !important;
        border: 1px solid #dee2e6 !important;
        background: #ffffff !important;
        color: #495057 !important;
        padding: 5px 10px !important;
    }
    .note-btn:hover {
        background: #e9ecef !important;
        color: #212529 !important;
    }
    .note-editable {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
        line-height: 1.7 !important;
        font-size: 0.98rem !important;
        color: #1f2937 !important;
        padding: 24px 28px !important;
        min-height: 540px !important;
        background: #ffffff;
    }
    .note-editable h1, .note-editable h2 {
        font-size: 1.45rem !important;
        font-weight: 700 !important;
        margin-top: 1.75rem !important;
        margin-bottom: 0.85rem !important;
        color: #0b3d29 !important;
    }
    .note-editable h3, .note-editable h4 {
        font-size: 1.2rem !important;
        font-weight: 600 !important;
        margin-top: 1.4rem !important;
        margin-bottom: 0.65rem !important;
        color: #16794f !important;
    }
    .note-editable p {
        margin-bottom: 1.1rem !important;
    }
    .note-editable ul, .note-editable ol {
        margin-bottom: 1.2rem !important;
        padding-left: 1.6rem !important;
    }
    .note-editable li {
        margin-bottom: 0.4rem !important;
    }
    .note-editable table {
        width: 100% !important;
        margin: 1.25rem 0 !important;
        border-collapse: collapse !important;
    }
    .note-editable table td, .note-editable table th {
        border: 1px solid #dee2e6 !important;
        padding: 8px 12px !important;
    }
    .legal-guidance-card {
        position: sticky;
        top: 20px;
        background: #ffffff;
        border: 1px solid #e2ede7;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(11, 61, 41, 0.05);
    }
    .guidance-step {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 0.84rem;
        margin-bottom: 10px;
        line-height: 1.45;
        color: #374151;
    }
    .guidance-bullet {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: rgba(22, 121, 79, 0.12);
        color: #16794f;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.65rem;
        font-weight: 700;
        flex-shrink: 0;
        margin-top: 2px;
    }
</style>

<div class="container-fluid px-0">
    <!-- Top breadcrumb & navigation bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 pb-2 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="font-size: 0.85rem;">
                    <li class="breadcrumb-item"><a href="<?= base_url('legal-pages') ?>" class="text-decoration-none text-muted"><i class="fas fa-file-contract me-1"></i>Legal Pages</a></li>
                    <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page"><?= htmlspecialchars($page['title']) ?></li>
                </ol>
            </nav>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h4 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($page['title']) ?></h4>
                <span id="pageStatusPill" class="badge <?= ($page['status'] === 'custom') ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' ?> px-2 py-1 rounded-pill" style="font-size: 0.78rem;">
                    <?= ($page['status'] === 'custom') ? '<i class="fas fa-check-circle me-1"></i>Customized' : '<i class="fas fa-cube me-1"></i>Default (Shipped)' ?>
                </span>
            </div>
        </div>

        <!-- Single Save Button in Header -->
        <div class="d-flex align-items-center gap-2 mt-3 mt-md-0">
            <a href="<?= base_url('legal-pages') ?>" class="btn btn-outline-secondary btn-sm px-3 py-2 rounded-3">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            <button type="button" class="btn btn-primary btn-sm px-4 py-2 rounded-3 fw-semibold shadow-sm btn-save-action" style="background: var(--rc-forest-800, #0b3d29); border-color: var(--rc-forest-800, #0b3d29);">
                <i class="fas fa-save me-1"></i> Save Changes
            </button>
        </div>
    </div>

    <!-- Alert Box for notifications -->
    <div id="ajaxAlertContainer">
        <?php if ($this->session->flashdata('message')): ?>
            <div class="alert alert-<?= $this->session->flashdata('message_type') ?: 'info' ?> alert-dismissible fade show shadow-sm" role="alert">
                <?= $this->session->flashdata('message') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Main Two-Pane Layout -->
    <div class="row g-4">
        <!-- Left Pane: Editor Box -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-md-4">
                    <form id="legalPolicyForm" action="<?= base_url('legal-pages/save/' . $slug) ?>" method="POST">
                        
                        <!-- Header Row directly above editor (matching user reference mockup) -->
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <label class="form-label fw-bold text-dark text-uppercase mb-0" style="letter-spacing: 0.5px; font-size: 0.95rem;">
                                CONTENT <span class="text-danger">*</span>
                            </label>
                            
                            <!-- Fetch default data directly into editor button -->
                            <button type="button" class="btn btn-outline-primary btn-sm px-3 py-2 rounded-3 fw-semibold shadow-sm" id="btnInsertDefault">
                                <i class="fas fa-magic me-1"></i> Insert Default Template into Editor
                            </button>
                        </div>

                        <!-- Rich-Text Textarea (Summernote replaces this) -->
                        <textarea id="policyEditor" name="content"><?= htmlspecialchars($editor_content) ?></textarea>

                        <!-- Bottom metrics bar -->
                        <div class="d-flex flex-wrap align-items-center justify-content-between mt-3 pt-2 border-top text-muted" style="font-size: 0.8rem;">
                            <div class="d-flex align-items-center gap-3">
                                <span><i class="fas fa-align-left me-1"></i><strong id="wordCountDisplay" class="text-dark">0</strong> words</span>
                                <span><i class="fas fa-keyboard me-1"></i><strong id="charCountDisplay" class="text-dark">0</strong> chars</span>
                                <span><i class="fas fa-clock me-1"></i><strong id="readTimeDisplay" class="text-dark">1 min</strong> read</span>
                            </div>
                            <span id="saveStatusIndicator" class="text-success fw-semibold">
                                <i class="fas fa-check-circle me-1"></i>Ready
                            </span>
                        </div>

                        <!-- Bottom Form Actions: Single Save Button -->
                        <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="<?= base_url('legal-pages') ?>" class="btn btn-light border px-3 py-2 rounded-3 text-muted">
                                Cancel
                            </a>
                            <button type="button" class="btn btn-primary px-4 py-2 rounded-3 fw-semibold shadow-sm btn-save-action" style="background: var(--rc-forest-800, #0b3d29); border-color: var(--rc-forest-800, #0b3d29);">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Pane: Guidance & Checklist -->
        <div class="col-lg-4">
            <div class="legal-guidance-card p-4">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <div style="width: 34px; height: 34px; border-radius: 10px; background: rgba(22, 121, 79, 0.12); color: #16794f; display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">How to write this page</h6>
                        <span class="text-muted" style="font-size: 0.72rem;">Legal Guidance &amp; Tips</span>
                    </div>
                </div>

                <div class="mb-3">
                    <p class="text-muted small mb-0" style="line-height: 1.55;">
                        <?= htmlspecialchars($guidance['summary']) ?>
                    </p>
                </div>

                <h6 class="fw-bold text-dark mt-4 mb-2" style="font-size: 0.85rem;">
                    <i class="fas fa-tasks text-success me-1"></i>Essential Checklist
                </h6>

                <div class="guidance-checklist mb-4">
                    <?php foreach ($guidance['checklist'] as $idx => $item): ?>
                        <div class="guidance-step">
                            <span class="guidance-bullet"><?= $idx + 1 ?></span>
                            <span><?= htmlspecialchars($item) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="p-3 bg-light rounded-3 border">
                    <div class="d-flex align-items-center gap-2 text-dark fw-semibold mb-1" style="font-size: 0.8rem;">
                        <i class="fas fa-balance-scale text-primary"></i> Statutory Standard
                    </div>
                    <p class="text-muted small mb-0" style="font-size: 0.76rem;">
                        <?= htmlspecialchars($guidance['statutory']) ?>
                    </p>
                </div>

                <div class="p-3 mt-3 rounded-3" style="background: rgba(31, 157, 99, 0.08); border: 1px dashed #1f9d63;">
                    <small class="text-dark d-block fw-semibold mb-1"><i class="fas fa-magic text-success me-1"></i>One-Click Template</small>
                    <small class="text-muted d-block">Click the <strong>"Insert Default Template into Editor"</strong> button to load ReComm's compliant template, edit any specifics, then click <strong>"Save Changes"</strong>.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Summernote Lite JS -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<script>
$(document).ready(function() {
    const slug = <?= json_encode($slug) ?>;
    const saveUrl = "<?= base_url('legal-pages/save/' . $slug) ?>";
    const getDefaultUrl = "<?= base_url('legal-pages/get-default/' . $slug) ?>";
    const defaultContentFallback = <?= json_encode($default['content']) ?>;

    // Initialize Summernote Lite Editor
    $('#policyEditor').summernote({
        placeholder: 'Compose your policy content here...',
        tabsize: 2,
        height: 540,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'hr']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        callbacks: {
            onChange: function(contents) {
                updateMetrics(contents);
            },
            onInit: function() {
                updateMetrics($('#policyEditor').summernote('code'));
            }
        }
    });

    // Update live word, character, and read time metrics
    function updateMetrics(html) {
        const tmp = document.createElement('div');
        tmp.innerHTML = html || '';
        const text = (tmp.textContent || tmp.innerText || '').trim();
        
        const words = text ? text.split(/\s+/).filter(Boolean).length : 0;
        const chars = text.length;
        const readTime = Math.max(1, Math.ceil(words / 200));

        $('#wordCountDisplay').text(words.toLocaleString());
        $('#charCountDisplay').text(chars.toLocaleString());
        $('#readTimeDisplay').text(readTime + ' min');
    }

    // "Insert Default Template into Editor" button
    $('#btnInsertDefault').on('click', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Fetching template...');

        $.ajax({
            url: getDefaultUrl,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                const content = (res && res.status && res.data?.content) ? res.data.content : defaultContentFallback;
                $('#policyEditor').summernote('code', content);
                updateMetrics(content);
                showAlert('info', '<i class="fas fa-magic me-1"></i> Default template inserted into editor! Review your changes and click <strong>"Save Changes"</strong>.');
                $('#saveStatusIndicator').html('<i class="fas fa-exclamation-circle text-warning me-1"></i>Unsaved template inserted');
            },
            error: function() {
                // Fallback to local embedded default
                $('#policyEditor').summernote('code', defaultContentFallback);
                updateMetrics(defaultContentFallback);
                showAlert('info', '<i class="fas fa-magic me-1"></i> Default template inserted into editor! Review your changes and click <strong>"Save Changes"</strong>.');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-magic me-1"></i> Insert Default Template into Editor');
            }
        });
    });

    // Single Save button handler (works for both top and bottom save buttons)
    $('.btn-save-action').on('click', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const content = $('#policyEditor').summernote('code');

        $('.btn-save-action').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');
        $('#saveStatusIndicator').html('<i class="fas fa-spinner fa-spin text-primary me-1"></i>Saving policy...');

        $.ajax({
            url: saveUrl,
            type: 'POST',
            data: { content: content },
            dataType: 'json',
            success: function(res) {
                if (res && res.status) {
                    $('#pageStatusPill').removeClass('bg-secondary-subtle text-secondary border-secondary-subtle')
                                       .addClass('bg-success-subtle text-success border border-success-subtle')
                                       .html('<i class="fas fa-check-circle me-1"></i>Customized');

                    $('#saveStatusIndicator').html('<i class="fas fa-check-circle text-success me-1"></i>Saved at ' + (res.data?.published_at || 'just now'));
                    showAlert('success', '<i class="fas fa-check-circle me-1"></i><strong>Saved!</strong> ' + res.message);
                } else {
                    showAlert('danger', res.message || 'Failed to save policy.');
                    $('#saveStatusIndicator').html('<i class="fas fa-times-circle text-danger me-1"></i>Save failed');
                }
            },
            error: function() {
                showAlert('danger', 'Network error saving policy.');
                $('#saveStatusIndicator').html('<i class="fas fa-times-circle text-danger me-1"></i>Error');
            },
            complete: function() {
                $('.btn-save-action').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Changes');
            }
        });
    });

    // Flash alert banner renderer
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show shadow-sm" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('#ajaxAlertContainer').html(alertHtml);
        setTimeout(function() {
            $('#ajaxAlertContainer .alert').fadeOut('slow', function() {
                $(this).remove();
            });
        }, 6000);
    }
});
</script>
