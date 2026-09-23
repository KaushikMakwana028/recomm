<style>
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

        --rc-red-600: #c0293a;
        --rc-blue-600: #2f6fb3;

        --rc-radius-lg: 20px;
        --rc-radius-md: 14px;
        --rc-radius-sm: 10px;
        --rc-shadow: 0 6px 24px rgba(11, 61, 41, 0.08);
        --rc-shadow-hover: 0 12px 32px rgba(11, 61, 41, 0.14);
    }

    .rc-support-container {
        font-family: 'Inter', 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
        color: var(--rc-ink);
        padding-bottom: 2rem;
    }

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

    .rc-stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .rc-stat-card {
        background: var(--rc-white);
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-md);
        padding: 1.2rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: var(--rc-shadow);
        transition: transform .18s ease;
    }
    .rc-stat-card:hover { transform: translateY(-2px); }

    .rc-stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .rc-filter-bar {
        background: var(--rc-white);
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-md);
        padding: 1rem 1.2rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--rc-shadow);
    }

    .rc-table-card {
        background: var(--rc-white);
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-lg);
        overflow: hidden;
        box-shadow: var(--rc-shadow);
    }

    .rc-support-table {
        margin: 0;
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .rc-support-table th {
        background: var(--rc-bg);
        color: var(--rc-forest-900);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
        padding: 0.9rem 1rem;
        border-bottom: 1px solid var(--rc-line);
        white-space: nowrap;
    }
    .rc-support-table td {
        padding: 1rem;
        border-bottom: 1px solid var(--rc-line);
        vertical-align: middle;
        font-size: 0.88rem;
    }
    .rc-support-table tbody tr:hover {
        background-color: rgba(31, 157, 99, 0.02);
    }

    /* Badges */
    .badge-status-open {
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fde68a;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.76rem;
    }
    .badge-status-in-progress {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.76rem;
    }
    .badge-status-resolved {
        background: #f0fdf4;
        color: #15803d;
        border: 1px solid #bbf7d0;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.76rem;
    }

    .badge-cat {
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #e2e8f0;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 0.75rem;
    }

    /* Responsive Scrollbar & Sticky Action Column */
    .rc-table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f8fafc;
    }
    .rc-table-responsive::-webkit-scrollbar {
        height: 8px;
    }
    .rc-table-responsive::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .rc-table-responsive::-webkit-scrollbar-track {
        background: #f8fafc;
    }

    .rc-support-table th.col-action,
    .rc-support-table td.col-action {
        position: sticky;
        right: 0;
        background: #ffffff;
        box-shadow: -4px 0 10px rgba(0, 0, 0, 0.06);
        z-index: 3;
        text-align: center;
        white-space: nowrap;
        min-width: 105px;
    }
    .rc-support-table th.col-action {
        background: var(--rc-bg);
        z-index: 4;
    }
    .rc-support-table tbody tr:hover td.col-action {
        background-color: #f6fcf8;
    }
    .rc-support-table tbody tr {
        transition: background-color 0.15s ease;
    }
    .rc-support-table tbody tr:hover {
        background-color: rgba(31, 157, 99, 0.04);
    }
</style>

<div class="rc-support-container container-fluid px-3 px-md-4">
    <!-- Header -->
    <div class="rc-page-header mt-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rc-header-badge">
                <i class="fas fa-headset"></i>
            </div>
            <div>
                <h1 class="h4 mb-0 fw-bold" style="color: var(--rc-forest-900);">Customer Support Requests</h1>
                <p class="rc-subtitle mb-0 small text-muted">Manage order-related problems, missing items, payment, and refund inquiries</p>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="rc-stat-grid">
        <div class="rc-stat-card">
            <div class="rc-stat-icon" style="background: rgba(15, 92, 62, 0.1); color: var(--rc-forest-800);">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <div>
                <div class="small text-muted fw-semibold">Total Tickets</div>
                <div class="h4 mb-0 fw-bold"><?= (int)$total_count ?></div>
            </div>
        </div>

        <div class="rc-stat-card">
            <div class="rc-stat-icon" style="background: rgba(242, 169, 59, 0.15); color: #d97706;">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div>
                <div class="small text-muted fw-semibold">Open / Action Required</div>
                <div class="h4 mb-0 fw-bold text-warning"><?= (int)$open_count ?></div>
            </div>
        </div>

        <div class="rc-stat-card">
            <div class="rc-stat-icon" style="background: rgba(47, 111, 179, 0.15); color: var(--rc-blue-600);">
                <i class="fas fa-spinner fa-spin-pulse"></i>
            </div>
            <div>
                <div class="small text-muted fw-semibold">In Progress</div>
                <div class="h4 mb-0 fw-bold text-primary"><?= (int)$in_progress_count ?></div>
            </div>
        </div>

        <div class="rc-stat-card">
            <div class="rc-stat-icon" style="background: rgba(46, 204, 113, 0.15); color: var(--rc-green-600);">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <div class="small text-muted fw-semibold">Resolved</div>
                <div class="h4 mb-0 fw-bold text-success"><?= (int)$resolved_count ?></div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="rc-filter-bar">
        <form method="get" action="<?= base_url('support-requests') ?>" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search ticket #, order #, customer, issue..." value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
            </div>

            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Statuses (<?= (int)$total_count ?>)</option>
                    <option value="Open" <?= ($status_filter === 'Open') ? 'selected' : '' ?>>Open (<?= (int)$open_count ?>)</option>
                    <option value="In Progress" <?= ($status_filter === 'In Progress') ? 'selected' : '' ?>>In Progress (<?= (int)$in_progress_count ?>)</option>
                    <option value="Resolved" <?= ($status_filter === 'Resolved') ? 'selected' : '' ?>>Resolved (<?= (int)$resolved_count ?>)</option>
                </select>
            </div>

            <div class="col-md-3">
                <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Issue Categories</option>
                    <?php 
                    $cats = ['Payment issue', 'Order issue', 'Missing item', 'Wrong item', 'Delivery issue', 'Return/Refund issue', 'Other'];
                    foreach ($cats as $c): ?>
                        <option value="<?= $c ?>" <?= ($category_filter === $c) ? 'selected' : '' ?>><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-success text-white w-100 fw-bold" style="background: var(--rc-green-600); border-color: var(--rc-green-600);">Filter</button>
                <?php if (!empty($search) || !empty($status_filter) || !empty($category_filter)): ?>
                    <a href="<?= base_url('support-requests') ?>" class="btn btn-sm btn-outline-secondary" title="Clear Filters"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tickets Table -->
    <div class="rc-table-card">
        <div class="rc-table-responsive table-responsive">
            <table class="rc-support-table">
                <thead>
                    <tr>
                        <th style="min-width: 175px;">Ticket Reference</th>
                        <th style="min-width: 110px;">Date &amp; Time</th>
                        <th style="min-width: 165px;">Customer</th>
                        <th style="min-width: 155px;">Order Details</th>
                        <th style="min-width: 130px;">Category</th>
                        <th style="min-width: 200px;">Description</th>
                        <th style="min-width: 120px;">Status</th>
                        <th class="col-action" style="min-width: 105px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($requests)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                                <span class="fw-bold">No support requests found matching criteria.</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($requests as $r): ?>
                            <tr style="cursor: pointer;" onclick="if(event.target.tagName !== 'A' && !event.target.closest('a')) { window.location='<?= base_url('support-requests/view/' . $r->id) ?>'; }" title="Click row to view ticket details">
                                <td>
                                    <a href="<?= base_url('support-requests/view/' . $r->id) ?>" class="fw-bold text-decoration-none d-inline-flex align-items-center gap-1.5" style="color: var(--rc-green-600); font-size: 0.92rem;" title="View ticket details">
                                        <span>#<?= htmlspecialchars($r->ticket_number) ?></span>
                                        <i class="fas fa-external-link-alt" style="font-size: 0.7rem; opacity: 0.7;"></i>
                                    </a>
                                </td>
                                <td>
                                    <div class="small fw-semibold"><?= date('d M Y', strtotime($r->created_at)) ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;"><?= date('h:i A', strtotime($r->created_at)) ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($r->customer_name ?: 'Customer #' . $r->customer_id) ?></div>
                                    <?php if (!empty($r->customer_email)): ?>
                                        <div class="text-muted small" style="font-size: 0.78rem;"><?= htmlspecialchars($r->customer_email) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($r->customer_mobile)): ?>
                                        <div class="text-muted small" style="font-size: 0.76rem;"><i class="fas fa-phone-alt me-1"></i><?= htmlspecialchars($r->customer_mobile) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div>
                                        <a href="<?= base_url('orders/view/' . $r->order_id) ?>" class="fw-bold text-decoration-none" style="color: var(--rc-green-600);" title="View full order" onclick="event.stopPropagation();">
                                            #<?= htmlspecialchars($r->order_number ?: $r->order_id) ?>
                                        </a>
                                    </div>
                                    <?php if (isset($r->order_total)): ?>
                                        <div class="small text-muted">₹<?= number_format((float)$r->order_total, 2) ?> &bull; <span class="badge bg-light text-secondary border"><?= ucfirst($r->order_status ?: 'pending') ?></span></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge-cat"><?= htmlspecialchars($r->category) ?></span>
                                </td>
                                <td style="max-width: 250px;">
                                    <div class="text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($r->description) ?>">
                                        <?= htmlspecialchars($r->description) ?>
                                    </div>
                                    <?php if (!empty($r->admin_response)): ?>
                                        <div class="text-success small mt-1 text-truncate" style="font-size: 0.74rem;">
                                            <i class="fas fa-reply me-1"></i><?= htmlspecialchars($r->admin_response) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($r->status === 'Open'): ?>
                                        <span class="badge-status-open"><i class="fas fa-exclamation-circle me-1"></i>Open</span>
                                    <?php elseif ($r->status === 'In Progress'): ?>
                                        <span class="badge-status-in-progress"><i class="fas fa-spinner fa-spin-pulse me-1"></i>In Progress</span>
                                    <?php else: ?>
                                        <span class="badge-status-resolved"><i class="fas fa-check-circle me-1"></i>Resolved</span>
                                    <?php endif; ?>
                                </td>
                                <td class="col-action text-center">
                                    <a href="<?= base_url('support-requests/view/' . $r->id) ?>" class="btn btn-sm btn-success text-white fw-bold d-inline-flex align-items-center gap-1.5 px-3 py-1.5 shadow-sm" style="background: var(--rc-green-600); border-color: var(--rc-green-600); border-radius: 8px; font-size: 0.8rem; white-space: nowrap;" title="View Ticket &amp; Respond">
                                        <i class="fas fa-eye"></i>
                                        <span>View</span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
