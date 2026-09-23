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

    .rc-ticket-details {
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

    .rc-card {
        background: var(--rc-white);
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-lg);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--rc-shadow);
    }

    .rc-card-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--rc-forest-900);
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--rc-line);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .badge-status-open {
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fde68a;
        font-weight: 700;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.85rem;
    }
    .badge-status-in-progress {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        font-weight: 700;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.85rem;
    }
    .badge-status-resolved {
        background: #f0fdf4;
        color: #15803d;
        border: 1px solid #bbf7d0;
        font-weight: 700;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.85rem;
    }
</style>

<div class="rc-ticket-details container-fluid px-3 px-md-4">
    <!-- Header -->
    <div class="rc-page-header mt-3">
        <div class="d-flex align-items-center gap-3">
            <a href="<?= base_url('support-requests') ?>" class="btn btn-outline-secondary btn-sm" style="border-radius: 10px;">
                <i class="fas fa-arrow-left me-1"></i> Back to Requests
            </a>
            <div>
                <h1 class="h4 mb-0 fw-bold" style="color: var(--rc-forest-900);">
                    Support Ticket #<?= htmlspecialchars($ticket->ticket_number) ?>
                </h1>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-0.5" style="border-radius: 6px; font-weight: 600;">
                        <?= htmlspecialchars($ticket->category) ?>
                    </span>
                    <span class="text-muted small">&bull; Submitted <?= date('d M Y, h:i A', strtotime($ticket->created_at)) ?></span>
                </div>
            </div>
        </div>

        <div>
            <?php if ($ticket->status === 'Open'): ?>
                <span class="badge-status-open"><i class="fas fa-exclamation-circle me-1"></i> Open</span>
            <?php elseif ($ticket->status === 'In Progress'): ?>
                <span class="badge-status-in-progress"><i class="fas fa-spinner fa-spin-pulse me-1"></i> In Progress</span>
            <?php else: ?>
                <span class="badge-status-resolved"><i class="fas fa-check-circle me-1"></i> Resolved</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Issue details & Order info -->
        <div class="col-lg-7">
            <!-- Customer Issue Description -->
            <div class="rc-card">
                <div class="rc-card-title">
                    <i class="fas fa-comment-dots text-success"></i> Customer's Reported Issue
                </div>

                <div class="mb-3 p-3 rounded" style="background: #f8fafc; border: 1.5px solid #e2e8f0; font-size: 0.95rem; line-height: 1.6;">
                    <div class="text-muted small mb-1 fw-bold text-uppercase" style="letter-spacing: 0.05em;">Issue Category: <span class="text-dark"><?= htmlspecialchars($ticket->category) ?></span></div>
                    <div style="color: var(--rc-forest-900); white-space: pre-wrap;"><?= htmlspecialchars($ticket->description) ?></div>
                </div>

                <?php if (!empty($ticket->admin_response)): ?>
                    <div class="p-3 rounded" style="background: #f0fdf4; border: 1.5px solid #bbf7d0;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-success small text-uppercase" style="letter-spacing: 0.05em;"><i class="fas fa-reply me-1"></i> Current Admin Response</span>
                            <?php if (!empty($ticket->resolved_at)): ?>
                                <span class="text-muted small" style="font-size: 0.76rem;">Resolved on <?= date('d M Y, h:i A', strtotime($ticket->resolved_at)) ?></span>
                            <?php endif; ?>
                        </div>
                        <div style="color: #14532d; white-space: pre-wrap; font-size: 0.92rem;"><?= htmlspecialchars($ticket->admin_response) ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Linked Order Items -->
            <div class="rc-card">
                <div class="rc-card-title d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-box-open text-success"></i> Linked Order Items (<?= count($order_items) ?>)</span>
                    <a href="<?= base_url('orders/view/' . $ticket->order_id) ?>" target="_blank" class="small fw-bold text-decoration-none" style="color: var(--rc-green-600);">
                        View Full Order <i class="fas fa-external-link-alt ms-1"></i>
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Store / Vendor</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($item->product_name) ?></div>
                                        <?php if (!empty($item->variant_name)): ?>
                                            <span class="badge bg-light text-secondary border" style="font-size: 0.72rem;"><?= htmlspecialchars($item->variant_name) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge px-2 py-0.5 rounded-pill" style="background: #f1f5f9; color: #00204E; border: 1px solid #cbd5e1; font-size: 0.74rem;">
                                            🏪 <?= htmlspecialchars($item->store_name ?: $item->vendor_name ?: 'Store') ?>
                                        </span>
                                    </td>
                                    <td class="text-center fw-bold"><?= (int)$item->quantity ?></td>
                                    <td class="text-end">₹<?= number_format((float)$item->price, 2) ?></td>
                                    <td class="text-end fw-bold text-success">₹<?= number_format((float)($item->price * $item->quantity), 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Status Timeline -->
            <?php if (!empty($history)): ?>
                <div class="rc-card">
                    <div class="rc-card-title">
                        <i class="fas fa-history text-success"></i> Order & Support History Timeline
                    </div>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($history as $h): 
                            $log_message = !empty($h->remarks) ? $h->remarks : (!empty($h->comment) ? $h->comment : (!empty($h->message) ? $h->message : 'Order status updated to ' . ucfirst($h->status ?? '')));
                            $author = !empty($h->changed_by) ? $h->changed_by : (!empty($h->created_by) ? $h->created_by : 'System');
                        ?>
                            <li class="d-flex gap-3 mb-3 pb-2 border-bottom">
                                <div class="text-success"><i class="fas fa-circle-dot mt-1"></i></div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                                        <div class="fw-semibold text-dark" style="font-size: 0.88rem;"><?= htmlspecialchars($log_message) ?></div>
                                        <?php if (!empty($h->status)): ?>
                                            <span class="badge bg-light text-secondary border" style="font-size: 0.72rem;"><?= ucfirst($h->status) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted small" style="font-size: 0.76rem;">
                                        By <strong><?= htmlspecialchars(ucfirst($author)) ?></strong> &bull; <?= date('d M Y, h:i A', strtotime($h->created_at)) ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column: Status Update & Customer / Order Details -->
        <div class="col-lg-5">
            <!-- Update Status & Response Form -->
            <div class="rc-card" style="border: 2px solid #86efac; background: #ffffff;">
                <div class="rc-card-title">
                    <i class="fas fa-edit text-success"></i> Update Status & Respond
                </div>

                <form method="post" action="<?= base_url('support-requests/update-status') ?>">
                    <input type="hidden" name="id" value="<?= (int)$ticket->id ?>">

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Ticket Status</label>
                        <select name="status" class="form-select" required>
                            <option value="Open" <?= ($ticket->status === 'Open') ? 'selected' : '' ?>>⚠️ Open (Needs Action)</option>
                            <option value="In Progress" <?= ($ticket->status === 'In Progress') ? 'selected' : '' ?>>🔄 In Progress (Under Review)</option>
                            <option value="Resolved" <?= ($ticket->status === 'Resolved') ? 'selected' : '' ?>>✅ Resolved (Issue Addressed)</option>
                        </select>
                        <div class="form-text small">Selecting 'Resolved' sets resolution time and informs customer.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Admin Response to Customer</label>
                        <textarea name="admin_response" rows="4" class="form-control" placeholder="Enter resolution notes, refund confirmation, or instructions for the customer..."><?= htmlspecialchars($ticket->admin_response ?? '') ?></textarea>
                        <div class="form-text small">This message will be visible to the customer when viewing this order.</div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 fw-bold py-2 shadow-sm" style="background: var(--rc-green-600); border-color: var(--rc-green-600); border-radius: 10px;">
                        <i class="fas fa-check-circle me-1"></i> Update Ticket & Save Response
                    </button>
                </form>
            </div>

            <!-- Customer Details Card -->
            <div class="rc-card">
                <div class="rc-card-title">
                    <i class="fas fa-user text-success"></i> Customer Information
                </div>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width: 48px; height: 48px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #475569; font-size: 1.1rem;">
                        <?= strtoupper(substr($ticket->customer_name ?: 'C', 0, 1)) ?>
                    </div>
                    <div>
                        <div class="fw-bold text-dark"><?= htmlspecialchars($ticket->customer_name ?: 'Customer #' . $ticket->customer_id) ?></div>
                        <div class="text-muted small">Customer ID: #<?= (int)$ticket->customer_id ?></div>
                    </div>
                </div>

                <div class="small mb-2">
                    <strong class="text-muted d-block">Email:</strong>
                    <span><?= htmlspecialchars($ticket->customer_email ?: 'N/A') ?></span>
                </div>
                <div class="small mb-2">
                    <strong class="text-muted d-block">Mobile:</strong>
                    <span><?= htmlspecialchars($ticket->customer_mobile ?: 'N/A') ?></span>
                </div>
            </div>

            <!-- Order Summary Card -->
            <div class="rc-card">
                <div class="rc-card-title">
                    <i class="fas fa-receipt text-success"></i> Order Summary
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom small">
                    <span class="text-muted">Order Number:</span>
                    <strong class="text-dark">#<?= htmlspecialchars($ticket->order_number ?: $ticket->order_id) ?></strong>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom small">
                    <span class="text-muted">Order Date:</span>
                    <span><?= date('d M Y, h:i A', strtotime($ticket->order_created_at)) ?></span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom small">
                    <span class="text-muted">Order Status:</span>
                    <span class="badge bg-light text-secondary border"><?= ucfirst($ticket->order_status ?: 'pending') ?></span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom small">
                    <span class="text-muted">Payment Method:</span>
                    <span class="text-uppercase"><?= htmlspecialchars($ticket->payment_method ?: 'COD') ?></span>
                </div>
                <div class="d-flex justify-content-between py-2 small fw-bold">
                    <span>Order Total:</span>
                    <span class="text-success fs-6">₹<?= number_format((float)($ticket->order_total ?? 0), 2) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
