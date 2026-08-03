<style>
    /* Styling variables and custom elements for orders management */
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

    .rc-orders-container {
        font-family: 'Inter', 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
        color: var(--rc-ink);
        padding-bottom: 2rem;
    }

    .rc-orders-container h1,
    .rc-orders-container h2,
    .rc-orders-container h3,
    .rc-orders-container h4,
    .rc-orders-container h5,
    .rc-orders-container h6 {
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

    /* ---------- Filters Panel ---------- */
    .rc-filters-card {
        background: var(--rc-white);
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-md);
        box-shadow: var(--rc-shadow);
        padding: 1.15rem 1.25rem;
        margin-bottom: 1.5rem;
    }

    .rc-filters-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 0.85rem;
        align-items: center;
    }

    .rc-search-wrapper {
        flex: 1 1 300px;
        position: relative;
        min-width: 250px;
    }

    .rc-search-wrapper i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--rc-muted);
        font-size: 0.95rem;
    }

    .rc-input-search {
        width: 100%;
        padding: 0.72rem 1rem 0.72rem 2.5rem;
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-sm);
        background: var(--rc-bg);
        color: var(--rc-ink);
        font-size: 0.88rem;
        font-weight: 500;
        outline: none;
        transition: all 0.2s ease;
    }

    .rc-input-search:focus {
        border-color: var(--rc-green-500);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.1);
    }

    .rc-select-filter {
        flex: 0 1 200px;
        min-width: 150px;
        padding: 0.72rem 2rem 0.72rem 1rem;
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-sm);
        background: var(--rc-bg) url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%235f7c70' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e") no-repeat right 1rem center/10px 10px;
        color: var(--rc-ink);
        font-size: 0.88rem;
        font-weight: 500;
        outline: none;
        appearance: none;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .rc-select-filter:focus {
        border-color: var(--rc-green-500);
        background-color: #fff;
        box-shadow: 0 0 0 4px rgba(46, 204, 113, 0.1);
    }

    /* ---------- Table Styles ---------- */
    .rc-table-card {
        background: var(--rc-white);
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-lg);
        box-shadow: var(--rc-shadow);
        padding: 1.5rem;
        overflow: hidden;
    }

    .rc-table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.15rem;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .rc-table-header h3 {
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .rc-table-header h3 span {
        font-size: 0.85rem;
        font-weight: 500;
        background: rgba(31, 157, 99, 0.08);
        color: var(--rc-green-600);
        padding: 0.2rem 0.6rem;
        border-radius: 999px;
    }

    .rc-table-wrap {
        overflow-x: auto;
        border-radius: var(--rc-radius-md);
        border: 1px solid var(--rc-line);
    }

    .rc-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        min-width: 800px;
    }

    .rc-table thead th {
        background: #f8faf8;
        font-size: .74rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--rc-muted);
        font-weight: 700;
        border-bottom: 2px solid var(--rc-line);
        padding: 0.9rem 1rem;
        text-align: left;
    }

    .rc-table tbody td {
        padding: 1rem;
        border-bottom: 1px solid var(--rc-line);
        font-size: .88rem;
        color: var(--rc-ink);
        vertical-align: middle;
    }

    .rc-table tbody tr:last-child td {
        border-bottom: none;
    }

    .rc-table tbody tr:hover {
        background: rgba(46, 204, 113, .03);
    }

    .rc-order-link {
        font-weight: 700;
        color: var(--rc-green-600);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        transition: color 0.15s ease;
    }

    .rc-order-link:hover {
        color: var(--rc-forest-900);
    }

    .rc-badge-status {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .35rem .75rem;
        border-radius: 999px;
        font-size: .74rem;
        font-weight: 700;
        letter-spacing: .01em;
        text-transform: capitalize;
    }

    .rc-badge-status.pending { background: #fffbeb; color: var(--rc-gold-500); border: 1px solid #fde68a; }
    .rc-badge-status.confirmed { background: #eff6ff; color: var(--rc-blue-600); border: 1px solid #bfdbfe; }
    .rc-badge-status.packed { background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe; }
    .rc-badge-status.out_for_delivery { background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; }
    .rc-badge-status.delivered { background: #ecfdf5; color: var(--rc-green-600); border: 1px solid #a7f3d0; }
    .rc-badge-status.cancelled { background: #fef2f2; color: var(--rc-red-600); border: 1px solid #fecaca; }

    .rc-vendor-badge {
        display: inline-block;
        background: #f0faf4;
        color: var(--rc-green-600);
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
        border: 1px solid #dcf6e6;
        margin: 0.15rem;
        max-width: 180px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
    }

    /* ---------- Actions ---------- */
    .rc-btn-icon-view {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(31, 157, 99, 0.08);
        color: var(--rc-green-600);
        border: 1px solid rgba(31, 157, 99, 0.12);
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .rc-btn-icon-view:hover {
        background: var(--rc-green-600);
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(31, 157, 99, 0.2);
    }

    /* ---------- Pagination ---------- */
    .rc-table-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        flex-wrap: wrap;
        margin-top: 1.25rem;
        padding-top: 1.15rem;
        border-top: 1px solid var(--rc-line);
    }

    .rc-pg-info {
        font-size: .84rem;
        color: var(--rc-muted);
        font-weight: 500;
    }

    .rc-pagination {
        display: flex;
        align-items: center;
        gap: .3rem;
        flex-wrap: wrap;
    }

    .rc-pg-btn {
        min-width: 34px;
        height: 34px;
        padding: 0 .6rem;
        border: 1px solid var(--rc-line);
        background: var(--rc-white);
        color: var(--rc-forest-900);
        border-radius: var(--rc-radius-sm);
        font-size: .84rem;
        font-weight: 600;
        cursor: pointer;
        transition: all .15s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .rc-pg-btn:hover:not(:disabled):not(.active) {
        border-color: var(--rc-green-500);
        color: var(--rc-green-600);
        background: rgba(46, 204, 113, .06);
    }

    .rc-pg-btn.active {
        background: linear-gradient(135deg, var(--rc-green-600), var(--rc-green-500));
        border-color: transparent;
        color: #fff;
        box-shadow: 0 4px 10px rgba(22, 121, 79, 0.25);
    }

    .rc-pg-btn:disabled {
        opacity: .4;
        cursor: not-allowed;
    }

    /* Empty state */
    .rc-empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
        color: var(--rc-muted);
    }

    .rc-empty-state i {
        font-size: 2.2rem;
        color: var(--rc-muted);
        opacity: 0.6;
        margin-bottom: 0.75rem;
        display: block;
    }

    .rc-empty-state p {
        font-size: 0.95rem;
        font-weight: 600;
        margin: 0;
    }
</style>

<div class="rc-orders-container">

    <!-- Page Header -->
    <div class="rc-page-header">
        <div class="rc-header-left">
            <div class="rc-header-badge"><i class="fas fa-shopping-cart"></i></div>
            <div>
                <h1>Orders Management</h1>
                <div class="rc-subtitle">Track, filter, and review details of all customer orders</div>
            </div>
        </div>
        <div>
            <ul class="rc-breadcrumb">
                <li><a href="<?= base_url('dashboard') ?>"><i class="fas fa-home"></i> Dashboard</a></li>
                <li>/</li>
                <li>Orders</li>
            </ul>
        </div>
    </div>

    <!-- Filters panel -->
    <div class="rc-filters-card">
        <div class="rc-filters-grid">
            <div class="rc-search-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" id="rc-order-search" class="rc-input-search" placeholder="Search by order number or customer name...">
            </div>
            <select id="rc-status-filter" class="rc-select-filter">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="packed">Packed</option>
                <option value="out_for_delivery">Out for Delivery</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
    </div>

    <!-- Table Card -->
    <div class="rc-table-card">
        <div class="rc-table-header">
            <h3 id="table-title-main">Orders List <span id="orders-count"><?= count($orders) ?></span></h3>
        </div>

        <div class="rc-table-wrap">
            <table class="rc-table" id="rc-orders-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Vendor Name(s)</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th style="width: 80px; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody id="rc-orders-tbody">
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $order): ?>
                            <tr class="rc-order-row" data-number="<?= htmlspecialchars($order->order_number) ?>" data-customer="<?= htmlspecialchars(strtolower($order->customer_name)) ?>" data-status="<?= htmlspecialchars($order->status) ?>">
                                <td>
                                    <a href="<?= base_url('orders/view/' . $order->id) ?>" class="rc-order-link">
                                        <i class="fas fa-receipt"></i>
                                        <?= htmlspecialchars($order->order_number) ?>
                                    </a>
                                </td>
                                <td>
                                    <i class="far fa-calendar-alt text-muted mr-1"></i>
                                    <?= date('d M Y, h:i A', strtotime($order->created_on)) ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($order->customer_name) ?></strong>
                                </td>
                                <td>
                                    <?php 
                                    if (!empty($order->vendor_names)) {
                                        $vnames = explode(', ', $order->vendor_names);
                                        foreach ($vnames as $vname) {
                                            echo '<span class="rc-vendor-badge" title="' . htmlspecialchars($vname) . '">' . htmlspecialchars($vname) . '</span>';
                                        }
                                    } else {
                                        echo '<span class="text-muted">—</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= $order->total_items ?> Items</span>
                                </td>
                                <td>
                                    <strong>₹<?= number_format($order->total_amount, 2) ?></strong>
                                </td>
                                <td>
                                    <span class="rc-badge-status <?= htmlspecialchars($order->status) ?>">
                                        <i class="fas fa-circle" style="font-size: 6px; vertical-align: middle;"></i>
                                        <?= htmlspecialchars(str_replace('_', ' ', $order->status)) ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <a href="<?= base_url('orders/view/' . $order->id) ?>" class="rc-btn-icon-view" title="View details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr id="no-orders-tr">
                            <td colspan="8">
                                <div class="rc-empty-state">
                                    <i class="fas fa-box-open"></i>
                                    <p>No orders found in the system.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="rc-table-footer" id="rc-table-footer" style="display: none;">
            <div class="rc-pg-info" id="rc-pg-info"></div>
            <div class="rc-pagination" id="rc-pagination"></div>
        </div>
    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var searchInput = document.getElementById("rc-order-search");
        var statusFilter = document.getElementById("rc-status-filter");
        var tbody = document.getElementById("rc-orders-tbody");
        if (!tbody) return;

        var allRows = Array.prototype.slice.call(tbody.querySelectorAll("tr.rc-order-row"));
        var perPage = 10;
        var currentPage = 1;
        var filteredRows = allRows;

        function filterOrders() {
            var searchVal = searchInput.value.toLowerCase().trim();
            var statusVal = statusFilter.value;

            filteredRows = allRows.filter(function(row) {
                var orderNum = row.getAttribute("data-number").toLowerCase();
                var customer = row.getAttribute("data-customer");
                var status = row.getAttribute("data-status");

                var matchesSearch = !searchVal || orderNum.indexOf(searchVal) !== -1 || customer.indexOf(searchVal) !== -1;
                var matchesStatus = !statusVal || status === statusVal;

                return matchesSearch && matchesStatus;
            });

            // Handle empty search results view
            var noOrdersTr = document.getElementById("no-orders-tr");
            if (filteredRows.length === 0) {
                if (!noOrdersTr) {
                    noOrdersTr = document.createElement("tr");
                    noOrdersTr.id = "no-orders-tr";
                    noOrdersTr.innerHTML = '<td colspan="8"><div class="rc-empty-state"><i class="fas fa-search"></i><p>No matching orders found.</p></div></td>';
                    tbody.appendChild(noOrdersTr);
                } else {
                    noOrdersTr.style.display = "";
                    noOrdersTr.innerHTML = '<td colspan="8"><div class="rc-empty-state"><i class="fas fa-search"></i><p>No matching orders found.</p></div></td>';
                }
            } else {
                if (noOrdersTr) noOrdersTr.style.display = "none";
            }

            currentPage = 1;
            renderTable();
        }

        function renderTable() {
            var total = filteredRows.length;
            var totalPages = Math.ceil(total / perPage);

            allRows.forEach(function(row) {
                row.style.display = "none";
            });

            var startIdx = (currentPage - 1) * perPage;
            var endIdx = Math.min(startIdx + perPage, total);

            for (var i = startIdx; i < endIdx; i++) {
                filteredRows[i].style.display = "";
            }

            document.getElementById("orders-count").textContent = total;

            // Render pagination
            var footer = document.getElementById("rc-table-footer");
            var infoEl = document.getElementById("rc-pg-info");
            var pagEl = document.getElementById("rc-pagination");

            if (total <= perPage) {
                footer.style.display = "none";
                return;
            }

            footer.style.display = "flex";
            infoEl.textContent = 'Showing ' + (startIdx + 1) + '\u2013' + endIdx + ' of ' + total + ' orders';
            pagEl.innerHTML = '';

            // Previous Button
            var prevBtn = document.createElement("button");
            prevBtn.type = "button";
            prevBtn.className = "rc-pg-btn";
            prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
            prevBtn.disabled = currentPage === 1;
            prevBtn.onclick = function() {
                if (currentPage > 1) {
                    currentPage--;
                    renderTable();
                }
            };
            pagEl.appendChild(prevBtn);

            // Page buttons
            for (var p = 1; p <= totalPages; p++) {
                (function(pageNum) {
                    var btn = document.createElement("button");
                    btn.type = "button";
                    btn.className = "rc-pg-btn" + (pageNum === currentPage ? " active" : "");
                    btn.textContent = pageNum;
                    btn.onclick = function() {
                        currentPage = pageNum;
                        renderTable();
                    };
                    pagEl.appendChild(btn);
                })(p);
            }

            // Next Button
            var nextBtn = document.createElement("button");
            nextBtn.type = "button";
            nextBtn.className = "rc-pg-btn";
            nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.onclick = function() {
                if (currentPage < totalPages) {
                    currentPage++;
                    renderTable();
                }
            };
            pagEl.appendChild(nextBtn);
        }

        searchInput.addEventListener("input", filterOrders);
        statusFilter.addEventListener("change", filterOrders);

        // Initial render
        renderTable();
    });
</script>
