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
        --rc-bg-tint: #f3f8f6;
        --rc-white: #ffffff;
        --rc-sidebar-w: 260px;
    }

    /* ---------- Shell ---------- */
    #sidebar {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        width: var(--rc-sidebar-w);
        display: flex;
        flex-direction: column;
        background: var(--rc-white);
        border-right: 1px solid var(--rc-line);
        box-shadow: 3px 0 20px rgba(11, 61, 41, 0.05);
        z-index: 1040;
        transition: transform .28s cubic-bezier(.4, 0, .2, 1);
        font-family: 'Inter', 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    /* thin custom scrollbar for the nav list */
    #sidebar .components {
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: var(--rc-line) transparent;
    }

    #sidebar .components::-webkit-scrollbar {
        width: 5px;
    }

    #sidebar .components::-webkit-scrollbar-thumb {
        background: var(--rc-line);
        border-radius: 10px;
    }

    /* ---------- Logo header ---------- */
    .sidebar-header {
        position: relative;
        text-align: center;
        padding: 1.15rem 1rem .9rem;
        flex-shrink: 0;
    }

    .sidebar-header::after {
        content: "";
        position: absolute;
        left: 1.25rem;
        right: 1.25rem;
        bottom: 0;
        height: 1px;
        background: var(--rc-line);
    }

    .sidebar-logo {
        max-width: 108px;
        width: 100%;
        height: auto;
        display: block;
        margin: 0 auto;
    }

    /* ---------- Nav list ---------- */
    #sidebar .components {
        list-style: none;
        margin: 0;
        padding: 1rem .85rem 1rem;
        flex: 1 1 auto;
    }

    #sidebar .components li {
        margin-bottom: .3rem;
    }

    #sidebar .components a {
        position: relative;
        display: flex;
        align-items: center;
        gap: .8rem;
        padding: .68rem .8rem;
        border-radius: 12px;
        color: var(--rc-muted);
        text-decoration: none;
        font-size: .92rem;
        font-weight: 600;
        letter-spacing: .01em;
        transition: background .18s ease, color .18s ease, transform .18s ease;
    }

    #sidebar .components a i {
        width: 34px;
        height: 34px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        font-size: .95rem;
        background: var(--rc-bg-tint);
        color: var(--rc-muted);
        transition: background .18s ease, color .18s ease;
    }

    #sidebar .components a:hover {
        background: var(--rc-bg-tint);
        color: var(--rc-forest-900);
        transform: translateX(2px);
    }

    #sidebar .components a:hover i {
        background: rgba(46, 204, 113, .14);
        color: var(--rc-green-600);
    }

    /* Active state */
    #sidebar .components a.active {
        color: var(--rc-forest-900);
        background: rgba(46, 204, 113, .1);
    }

    #sidebar .components a.active::before {
        content: "";
        position: absolute;
        left: -0.85rem;
        top: 12%;
        bottom: 12%;
        width: 4px;
        border-radius: 0 4px 4px 0;
        background: linear-gradient(180deg, var(--rc-emerald-400), var(--rc-lime-300));
    }

    #sidebar .components a.active i {
        background: linear-gradient(135deg, var(--rc-green-600), var(--rc-emerald-400));
        color: #fff;
        box-shadow: 0 4px 10px -3px rgba(46, 204, 113, .5);
    }

    /* ---------- Logout pinned at bottom ---------- */
    .sidebar-footer {
        flex-shrink: 0;
        padding: .85rem .85rem 1.15rem;
        border-top: 1px solid var(--rc-line);
    }

    .sidebar-footer a {
        display: flex;
        align-items: center;
        gap: .8rem;
        padding: .68rem .8rem;
        border-radius: 12px;
        color: #c0293a;
        text-decoration: none;
        font-size: .92rem;
        font-weight: 600;
        transition: background .18s ease, color .18s ease;
    }

    .sidebar-footer a i {
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        background: rgba(220, 53, 69, .1);
        font-size: .95rem;
    }

    .sidebar-footer a:hover {
        background: rgba(220, 53, 69, .1);
        color: #a4202e;
    }

    /* ---------- Overlay (mobile) ---------- */
    .sidebar-overlay {
        position: fixed;
        inset: 0;
        background: rgba(11, 61, 41, .35);
        backdrop-filter: blur(2px);
        z-index: 1030;
        opacity: 0;
        visibility: hidden;
        transition: opacity .25s ease, visibility .25s ease;
    }

    .sidebar-overlay.show {
        opacity: 1;
        visibility: visible;
    }

    /* ---------- Responsive behavior ---------- */
    @media (min-width: 992px) {
        #sidebar {
            transform: translateX(0);
        }

        /* push page content over when sidebar is present */
        .rc-page-wrapper,
        #content {
            margin-left: var(--rc-sidebar-w);
        }
    }

    @media (max-width: 991.98px) {
        #sidebar {
            transform: translateX(-100%);
        }

        #sidebar.show {
            transform: translateX(0);
        }

        .rc-page-wrapper,
        #content {
            margin-left: 0;
        }
    }
</style>

<!-- Sidebar -->
<nav id="sidebar">

    <div class="sidebar-header">
        <img src="<?php echo base_url('assets/recomm-logo-sidebar.png'); ?>" alt="ReComm" class="sidebar-logo">
    </div>

    <ul class="components">
        <li>
            <a href="<?= base_url('dashboard') ?>" class="<?= ($this->uri->segment(1) == 'dashboard' || $this->uri->segment(1) == '') ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="<?= base_url('users') ?>" class="<?= $this->uri->segment(1) == 'users' ? 'active' : '' ?>">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
        </li>
        <li>
            <a href="<?= base_url('categories') ?>" class="<?= $this->uri->segment(1) == 'categories' ? 'active' : '' ?>">
                <i class="fas fa-list"></i>
                <span>Categories</span>
            </a>
        </li>
        <li>
            <a href="<?= base_url('products') ?>" class="<?= $this->uri->segment(1) == 'products' ? 'active' : '' ?>">
                <i class="fas fa-box"></i>
                <span>Products</span>
            </a>
        </li>
        <li>
            <a href="<?= base_url('orders') ?>" class="<?= $this->uri->segment(1) == 'orders' ? 'active' : '' ?>">
                <i class="fas fa-shopping-cart"></i>
                <span>Orders</span>
            </a>
        </li>
        <li>
            <a href="<?= base_url('settings') ?>" class="<?= $this->uri->segment(1) == 'settings' ? 'active' : '' ?>">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <a href="<?= base_url('logout') ?>" onclick="return confirm('Are you sure you want to logout?')">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</nav>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script>
    (function() {
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('sidebarOverlay');

        function openSidebar() {
            sidebar.classList.add('show');
            overlay.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
            document.body.style.overflow = '';
        }

        function toggleSidebar() {
            sidebar.classList.contains('show') ? closeSidebar() : openSidebar();
        }

        // Expose globally so your header's hamburger button can call it,
        // e.g. <button onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        window.toggleSidebar = toggleSidebar;
        window.closeSidebar = closeSidebar;

        // Also auto-bind common existing hooks so you don't have to touch the header
        document.addEventListener('click', function(e) {
            if (e.target.closest('#sidebarToggle, .sidebar-toggle, [data-sidebar-toggle]')) {
                toggleSidebar();
            }
        });

        // Tapping the dark overlay closes the drawer
        overlay.addEventListener('click', closeSidebar);

        // Close automatically when a nav link is tapped on mobile
        sidebar.querySelectorAll('.components a').forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth < 992) closeSidebar();
            });
        });

        // Reset state cleanly when resizing across the breakpoint
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 992) closeSidebar();
        });
    })();
</script>