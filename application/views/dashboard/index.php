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

        --rc-radius-lg: 20px;
        --rc-radius-md: 14px;
        --rc-radius-sm: 10px;
        --rc-shadow: 0 6px 24px rgba(11, 61, 41, 0.08);
        --rc-shadow-hover: 0 12px 32px rgba(11, 61, 41, 0.14);
    }

    /* ---------- Page shell ---------- */
    .rc-dash {
        font-family: 'Inter', 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
        color: var(--rc-ink);
        background: var(--rc-bg);
        padding-bottom: 2rem;
    }

    .rc-dash h1,
    .rc-dash h2,
    .rc-dash h3,
    .rc-dash h4,
    .rc-dash h5,
    .rc-dash h6 {
        font-family: 'Poppins', 'Inter', sans-serif;
        color: var(--rc-forest-900);
    }

    /* ---------- Page header ---------- */
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
        background: rgba(46, 204, 113, 0.1);
        padding: .35rem .75rem;
        border-radius: 999px;
        font-weight: 600;
    }

    /* ---------- Stat cards ---------- */
    .rc-stat-card {
        position: relative;
        background: var(--rc-white);
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-lg);
        padding: 1.25rem 1.3rem;
        height: 100%;
        box-shadow: var(--rc-shadow);
        transition: transform .18s ease, box-shadow .18s ease;
        overflow: hidden;
    }

    .rc-stat-card::after {
        content: "";
        position: absolute;
        inset: auto -30px -30px auto;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: var(--rc-tint, rgba(46, 204, 113, .08));
        z-index: 0;
    }

    .rc-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--rc-shadow-hover);
    }

    .rc-stat-card .rc-icon {
        position: relative;
        z-index: 1;
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.2rem;
        background: var(--rc-grad);
        box-shadow: 0 8px 16px -6px rgba(11, 61, 41, .45);
        margin-bottom: .85rem;
    }

    .rc-stat-card h3 {
        position: relative;
        z-index: 1;
        font-size: 1.65rem;
        font-weight: 700;
        margin: 0 0 .15rem;
        letter-spacing: -.02em;
    }

    .rc-stat-card p {
        position: relative;
        z-index: 1;
        margin: 0;
        font-size: .82rem;
        font-weight: 600;
        color: var(--rc-muted);
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    /* Per-card brand tints (all in the ReComm family; gold reserved for money + pending) */
    .rc-stat-card.tint-1 {
        --rc-grad: linear-gradient(135deg, var(--rc-forest-800), var(--rc-green-500));
        --rc-tint: rgba(15, 92, 62, .08);
    }

    .rc-stat-card.tint-2 {
        --rc-grad: linear-gradient(135deg, var(--rc-green-600), var(--rc-emerald-400));
        --rc-tint: rgba(46, 204, 113, .1);
    }

    .rc-stat-card.tint-3 {
        --rc-grad: linear-gradient(135deg, var(--rc-emerald-400), var(--rc-lime-300));
        --rc-tint: rgba(126, 232, 168, .15);
    }

    .rc-stat-card.tint-4 {
        --rc-grad: linear-gradient(135deg, var(--rc-forest-700), var(--rc-green-500));
        --rc-tint: rgba(15, 92, 62, .08);
    }

    .rc-stat-card.tint-5 {
        --rc-grad: linear-gradient(135deg, var(--rc-green-600), var(--rc-lime-300));
        --rc-tint: rgba(46, 204, 113, .1);
    }

    .rc-stat-card.tint-gold {
        --rc-grad: linear-gradient(135deg, var(--rc-forest-800), var(--rc-gold-500));
        --rc-tint: rgba(242, 169, 59, .14);
    }

    .rc-stat-card.tint-amber {
        --rc-grad: linear-gradient(135deg, var(--rc-gold-500), var(--rc-gold-400));
        --rc-tint: rgba(242, 169, 59, .16);
    }

    .rc-stat-card.tint-success {
        --rc-grad: linear-gradient(135deg, var(--rc-green-600), var(--rc-emerald-400));
        --rc-tint: rgba(46, 204, 113, .1);
    }

    /* ---------- Cards / charts ---------- */
    .rc-card {
        background: var(--rc-white);
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-lg);
        box-shadow: var(--rc-shadow);
        overflow: hidden;
        height: 100%;
    }

    .rc-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.1rem 1.3rem;
        border-bottom: 1px solid var(--rc-line);
        gap: .75rem;
        flex-wrap: wrap;
    }

    .rc-card-header span {
        font-weight: 700;
        font-size: .95rem;
        color: var(--rc-forest-900);
        display: flex;
        align-items: center;
    }

    .rc-card-header span i {
        color: var(--rc-green-500);
    }

    .rc-card-body {
        padding: 1.3rem;
    }

    .rc-btn-view {
        border: 1px solid var(--rc-green-500);
        color: var(--rc-green-600);
        background: transparent;
        font-size: .78rem;
        font-weight: 600;
        padding: .35rem .85rem;
        border-radius: 999px;
        text-decoration: none;
        transition: all .15s ease;
        display: inline-flex;
        align-items: center;
    }

    .rc-btn-view:hover {
        background: var(--rc-green-500);
        color: #fff;
    }

    /* ---------- Avatars ---------- */
    .rc-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 700;
        flex-shrink: 0;
        background: linear-gradient(135deg, var(--rc-forest-800), var(--rc-emerald-400));
    }

    .rc-avatar-sm {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        font-size: .85rem;
    }

    .rc-avatar-lg {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        font-size: 1.1rem;
    }

    .rc-avatar.role-vendor {
        background: linear-gradient(135deg, var(--rc-green-600), var(--rc-lime-300));
    }

    .rc-avatar.role-admin {
        background: linear-gradient(135deg, var(--rc-forest-900), var(--rc-gold-500));
    }

    /* ---------- Status badges ---------- */
    .rc-badge {
        font-size: .72rem;
        font-weight: 700;
        padding: .32rem .7rem;
        border-radius: 999px;
        text-transform: capitalize;
        letter-spacing: .02em;
        display: inline-block;
    }

    .rc-badge.pending {
        background: rgba(242, 169, 59, .15);
        color: #a4680f;
    }

    .rc-badge.processing {
        background: rgba(15, 92, 62, .12);
        color: var(--rc-forest-700);
    }

    .rc-badge.completed {
        background: rgba(46, 204, 113, .15);
        color: #1c7a45;
    }

    .rc-badge.cancelled {
        background: rgba(220, 53, 69, .12);
        color: #c0293a;
    }

    .rc-badge.role-vendor {
        background: rgba(15, 92, 62, .12);
        color: var(--rc-forest-700);
    }

    .rc-badge.role-admin {
        background: rgba(242, 169, 59, .15);
        color: #a4680f;
    }

    .rc-badge.role-user {
        background: rgba(95, 124, 112, .14);
        color: var(--rc-muted);
    }

    /* ---------- Table (desktop) ---------- */
    .rc-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .rc-table thead th {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--rc-muted);
        font-weight: 700;
        border-bottom: 1px solid var(--rc-line);
        padding: .6rem .5rem;
        text-align: left;
    }

    .rc-table tbody td {
        padding: .85rem .5rem;
        border-bottom: 1px solid var(--rc-line);
        vertical-align: middle;
        font-size: .88rem;
    }

    .rc-table tbody tr:last-child td {
        border-bottom: none;
    }

    .rc-table tbody tr {
        transition: background .15s ease;
    }

    .rc-table tbody tr:hover {
        background: rgba(46, 204, 113, .05);
    }

    .rc-order-no {
        color: var(--rc-green-600);
        font-weight: 700;
    }

    .rc-amount {
        font-weight: 700;
        color: var(--rc-forest-900);
    }

    .rc-date {
        color: var(--rc-muted);
        font-size: .8rem;
    }

    .rc-user-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: .85rem 0;
        border-bottom: 1px solid var(--rc-line);
        gap: .75rem;
    }

    .rc-user-row:last-child {
        border-bottom: none;
    }

    .rc-user-info h6 {
        margin: 0 0 2px;
        font-size: .9rem;
        font-weight: 700;
    }

    .rc-user-info small {
        color: var(--rc-muted);
        font-size: .78rem;
    }

    .rc-empty {
        text-align: center;
        padding: 2.5rem 1rem;
        color: var(--rc-muted);
    }

    .rc-empty i {
        font-size: 2.2rem;
        color: var(--rc-lime-300);
        margin-bottom: .75rem;
        display: block;
    }

    /* ---------- Mobile: stacked "receipt" cards instead of a table ---------- */
    @media (max-width:767.98px) {
        .rc-page-header {
            padding: 1rem;
            border-radius: var(--rc-radius-md);
        }

        .rc-page-header h1 {
            font-size: 1.15rem;
        }

        .rc-card-body {
            padding: 1rem;
        }

        .rc-table thead {
            display: none;
        }

        .rc-table,
        .rc-table tbody,
        .rc-table tr,
        .rc-table td {
            display: block;
            width: 100%;
        }

        .rc-table tbody tr {
            background: var(--rc-white);
            border: 1px solid var(--rc-line);
            border-radius: var(--rc-radius-md);
            margin-bottom: .75rem;
            padding: .85rem .9rem;
            box-shadow: var(--rc-shadow);
        }

        .rc-table tbody tr:hover {
            background: var(--rc-white);
        }

        .rc-table td {
            border-bottom: none !important;
            padding: .3rem 0 !important;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
        }

        .rc-table td::before {
            content: attr(data-label);
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: var(--rc-muted);
            flex-shrink: 0;
        }

        .rc-table td.rc-cell-customer {
            justify-content: flex-start;
        }

        .rc-table td.rc-cell-customer::before {
            display: none;
        }
    }
</style>

<div class="rc-dash">

    <!-- Page Header -->
    <div class="rc-page-header">
        <div class="rc-header-left">
            <div class="rc-header-badge"><i class="fas fa-tachometer-alt"></i></div>
            <div>
                <h1>Dashboard</h1>
                <div class="rc-subtitle">Overview of your ReComm marketplace</div>
            </div>
        </div>
        <ul class="rc-breadcrumb">
            <li><i class="fas fa-home"></i>&nbsp;Dashboard</li>
        </ul>
    </div>

    <!-- Statistics Cards Row 1 -->
    <div class="row g-3 g-md-4 mb-3 mb-md-4">
        <div class="col-6 col-xl-3">
            <div class="rc-stat-card tint-1">
                <div class="rc-icon"><i class="fas fa-users"></i></div>
                <h3><?= number_format($total_users) ?></h3>
                <p>Total Users</p>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="rc-stat-card tint-2">
                <div class="rc-icon"><i class="fas fa-store"></i></div>
                <h3><?= number_format($total_vendors) ?></h3>
                <p>Total Vendors</p>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="rc-stat-card tint-3">
                <div class="rc-icon"><i class="fas fa-box"></i></div>
                <h3><?= number_format($total_products) ?></h3>
                <p>Total Products</p>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="rc-stat-card tint-4">
                <div class="rc-icon"><i class="fas fa-list"></i></div>
                <h3><?= number_format($total_categories) ?></h3>
                <p>Total Categories</p>
            </div>
        </div>
    </div>

    <!-- Statistics Cards Row 2 -->
    <div class="row g-3 g-md-4 mb-3 mb-md-4">
        <div class="col-6 col-xl-3">
            <div class="rc-stat-card tint-5">
                <div class="rc-icon"><i class="fas fa-shopping-cart"></i></div>
                <h3><?= number_format($total_orders) ?></h3>
                <p>Total Orders</p>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="rc-stat-card tint-gold">
                <div class="rc-icon"><i class="fas fa-rupee-sign"></i></div>
                <h3>₹<?= number_format($total_revenue, 2) ?></h3>
                <p>Total Revenue</p>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="rc-stat-card tint-amber">
                <div class="rc-icon"><i class="fas fa-clock"></i></div>
                <h3><?= number_format($this->gm->countRows('orders', ['status' => 'pending'])) ?></h3>
                <p>Pending Orders</p>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="rc-stat-card tint-success">
                <div class="rc-icon"><i class="fas fa-check-circle"></i></div>
                <h3><?= number_format($this->gm->countRows('orders', ['status' => 'completed'])) ?></h3>
                <p>Completed Orders</p>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 g-md-4">
        <div class="col-12 col-xl-8">
            <div class="rc-card">
                <div class="rc-card-header">
                    <span><i class="fas fa-chart-area me-2"></i>Order Statistics (Last 7 Days)</span>
                </div>
                <div class="rc-card-body">
                    <div style="position:relative;height:280px;">
                        <canvas id="orderChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="rc-card">
                <div class="rc-card-header">
                    <span><i class="fas fa-chart-pie me-2"></i>Order Status</span>
                </div>
                <div class="rc-card-body">
                    <div style="position:relative;height:280px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Latest Orders and Users -->
    <div class="row g-3 g-md-4 mt-1">
        <!-- Latest Orders -->
        <div class="col-12 col-xl-8">
            <div class="rc-card">
                <div class="rc-card-header">
                    <span><i class="fas fa-shopping-cart me-2"></i>Latest Orders</span>
                    <a href="<?= base_url('orders') ?>" class="rc-btn-view">
                        View All <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="rc-card-body">
                    <?php if (!empty($latest_orders)): ?>
                        <table class="rc-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($latest_orders, 0, 5) as $order): ?>
                                    <?php
                                    $status = strtolower($order->status);
                                    $badge_class = in_array($status, ['pending', 'processing', 'completed', 'cancelled']) ? $status : 'processing';
                                    ?>
                                    <tr>
                                        <td data-label="Order #" class="rc-order-no"><?= $order->order_number ?></td>
                                        <td data-label="Customer" class="rc-cell-customer">
                                            <div class="d-flex align-items-center">
                                                <div class="rc-avatar rc-avatar-sm me-2">
                                                    <?= strtoupper(substr($order->user_name, 0, 1)) ?>
                                                </div>
                                                <?= $order->user_name ?>
                                            </div>
                                        </td>
                                        <td data-label="Amount" class="rc-amount">₹<?= number_format($order->total_amount, 2) ?></td>
                                        <td data-label="Status">
                                            <span class="rc-badge <?= $badge_class ?>"><?= ucfirst($order->status) ?></span>
                                        </td>
                                        <td data-label="Date" class="rc-date">
                                            <i class="fas fa-calendar me-1"></i><?= date('M d, Y', strtotime($order->created_on)) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="rc-empty">
                            <i class="fas fa-inbox"></i>
                            <p class="mb-0">No orders found</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Latest Users -->
        <div class="col-12 col-xl-4">
            <div class="rc-card">
                <div class="rc-card-header">
                    <span><i class="fas fa-users me-2"></i>Latest Users</span>
                    <a href="<?= base_url('users') ?>" class="rc-btn-view">
                        View All <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="rc-card-body">
                    <?php if (!empty($latest_users)): ?>
                        <?php foreach ($latest_users as $user): ?>
                            <div class="rc-user-row">
                                <div class="d-flex align-items-center">
                                    <div class="rc-avatar rc-avatar-lg me-3 role-<?= $user->role ?>">
                                        <?= strtoupper(substr($user->name, 0, 1)) ?>
                                    </div>
                                    <div class="rc-user-info">
                                        <h6><?= $user->name ?></h6>
                                        <small><i class="fas fa-phone me-1"></i><?= $user->mobile ?></small>
                                    </div>
                                </div>
                                <span class="rc-badge role-<?= $user->role ?>"><?= ucfirst($user->role) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="rc-empty">
                            <i class="fas fa-users"></i>
                            <p class="mb-0">No users found</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div><!-- /.rc-dash -->

<!-- Charts Script -->
<script>
    (function() {
        const forest = '#0f5c3e';
        const emerald = '#2ecc71';
        const gold = '#f2a93b';
        const teal = '#16794f';
        const red = '#dc3545';

        // Order Statistics Chart (Responsive)
        const orderCtx = document.getElementById('orderChart').getContext('2d');
        const orderGradient = orderCtx.createLinearGradient(0, 0, 0, 280);
        orderGradient.addColorStop(0, 'rgba(46,204,113,0.25)');
        orderGradient.addColorStop(1, 'rgba(46,204,113,0.02)');

        new Chart(orderCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($order_stats, 'date')) ?>,
                datasets: [{
                    label: 'Orders',
                    data: <?= json_encode(array_column($order_stats, 'count')) ?>,
                    borderColor: forest,
                    backgroundColor: orderGradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: emerald,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#0b3d29',
                        padding: 12,
                        borderRadius: 8,
                        titleFont: {
                            size: 13,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 12
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: {
                                size: 11
                            },
                            color: '#5f7c70'
                        },
                        grid: {
                            color: 'rgba(15,92,62,0.06)'
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 11
                            },
                            color: '#5f7c70'
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Order Status Chart (Responsive)
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Processing', 'Completed', 'Cancelled'],
                datasets: [{
                    data: [
                        <?= $this->gm->countRows('orders', ['status' => 'pending']) ?>,
                        <?= $this->gm->countRows('orders', ['status' => 'processing']) ?>,
                        <?= $this->gm->countRows('orders', ['status' => 'completed']) ?>,
                        <?= $this->gm->countRows('orders', ['status' => 'cancelled']) ?>
                    ],
                    backgroundColor: [gold, teal, emerald, red],
                    borderWidth: 3,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 14,
                            font: {
                                size: 11
                            },
                            usePointStyle: true,
                            color: '#0e2a1f'
                        }
                    },
                    tooltip: {
                        backgroundColor: '#0b3d29',
                        padding: 12,
                        borderRadius: 8,
                        titleFont: {
                            size: 13,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 12
                        }
                    }
                }
            }
        });
    })();
</script>