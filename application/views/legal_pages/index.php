<div class="container-fluid px-0">
    <!-- Header banner -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 pb-2 border-bottom">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--primary-navy, #0A2A4D);">
                <i class="fas fa-file-contract me-2 text-success"></i>Legal Pages Manager
            </h4>
            <p class="text-muted small mb-0">
                View, draft, and publish your store legal policies. Built-in shipped defaults ensure your platform is never left blank.
            </p>
        </div>
        <div class="mt-3 mt-md-0">
            <span class="badge bg-light text-dark border px-3 py-2">
                <i class="fas fa-info-circle text-primary me-1"></i> 3 Core Policies Supported
            </span>
        </div>
    </div>

    <!-- Alert messages if any -->
    <?php if ($this->session->flashdata('message')): ?>
        <div class="alert alert-<?= $this->session->flashdata('message_type') ?: 'info' ?> alert-dismissible fade show shadow-sm" role="alert">
            <?= $this->session->flashdata('message') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Policy Cards Grid -->
    <div class="row g-4">
        <?php foreach ($pages as $p): ?>
            <?php 
                $iconClass = 'fa-file-alt';
                $accentColor = '#0f5c3e';
                $bgTint = 'rgba(15, 92, 62, 0.06)';
                if ($p['slug'] === 'terms_conditions') {
                    $iconClass = 'fa-file-signature';
                    $accentColor = '#0b3d29';
                    $bgTint = 'rgba(11, 61, 41, 0.08)';
                } elseif ($p['slug'] === 'privacy_policy') {
                    $iconClass = 'fa-shield-alt';
                    $accentColor = '#16794f';
                    $bgTint = 'rgba(22, 121, 79, 0.08)';
                } elseif ($p['slug'] === 'refund_policy') {
                    $iconClass = 'fa-undo-alt';
                    $accentColor = '#1f9d63';
                    $bgTint = 'rgba(31, 157, 99, 0.08)';
                }

                $isCustom = ($p['status'] === 'custom');
            ?>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden position-relative hover-shadow transition-all" style="border: 1px solid rgba(0,0,0,0.06) !important;">
                    <!-- Top color accent -->
                    <div style="height: 5px; background: <?= $accentColor ?>;"></div>

                    <div class="card-body p-4 d-flex flex-column">
                        <!-- Top title row -->
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div style="width: 48px; height: 48px; border-radius: 12px; background: <?= $bgTint ?>; color: <?= $accentColor ?>; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                                    <i class="fas <?= $iconClass ?>"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($p['title']) ?></h5>
                                    <span class="text-muted" style="font-size: 0.78rem;">Slug: <code><?= htmlspecialchars($p['slug']) ?></code></span>
                                </div>
                            </div>
                        </div>

                        <!-- Status pill -->
                        <div class="mb-3">
                            <?php if ($isCustom): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill" style="font-size: 0.8rem; font-weight: 600;">
                                    <i class="fas fa-check-circle me-1"></i>Customized
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill" style="font-size: 0.8rem; font-weight: 600;">
                                    <i class="fas fa-cube me-1"></i>Default (Shipped)
                                </span>
                            <?php endif; ?>

                            <?php if ($p['has_draft']): ?>
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill ms-1" style="font-size: 0.8rem; font-weight: 600;">
                                    <i class="fas fa-edit me-1"></i>Draft Saved
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Policy description summary -->
                        <p class="text-muted small flex-grow-1 mb-4" style="line-height: 1.55;">
                            <?= htmlspecialchars($p['guidance']['summary']) ?>
                        </p>

                        <!-- Metadata row -->
                        <div class="p-3 bg-light rounded-3 mb-4" style="font-size: 0.8rem;">
                            <div class="d-flex justify-content-between py-1 border-bottom border-white">
                                <span class="text-muted">Content Size:</span>
                                <span class="fw-semibold text-dark">
                                    <?= number_format($p['word_count']) ?> words (~<?= $p['read_time_mins'] ?> min)
                                </span>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom border-white">
                                <span class="text-muted">Last Updated:</span>
                                <span class="fw-semibold text-dark">
                                    <?= $p['updated_at'] ? date('d M Y, h:i A', strtotime($p['updated_at'])) : 'Shipped Default' ?>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted">Live Status:</span>
                                <span class="fw-semibold <?= $isCustom ? 'text-success' : 'text-primary' ?>">
                                    <?= $isCustom && $p['published_at'] ? 'Published ' . date('d M Y', strtotime($p['published_at'])) : 'Built-in Fallback Active' ?>
                                </span>
                            </div>
                        </div>

                        <!-- Action button -->
                        <div class="mt-auto pt-2">
                            <a href="<?= base_url('legal-pages/edit/' . $p['slug']) ?>" class="btn btn-primary w-100 py-2 fw-semibold rounded-3 d-flex align-items-center justify-content-center gap-2" style="background: <?= $accentColor ?>; border-color: <?= $accentColor ?>;">
                                <i class="fas fa-edit"></i> Edit Policy &amp; Drafts
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
