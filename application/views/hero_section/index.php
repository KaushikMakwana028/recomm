<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --rc-navy: #00204E;
        --rc-navy-dark: #001a3d;
        --rc-green: #34A129;
        --rc-green-dark: #2a8720;
        --rc-forest-900: #07271b;
        --rc-green-600: #16794f;
        --rc-green-500: #1f9d63;
        --rc-emerald-400: #2ecc71;
        --rc-ink: #0e2a1f;
        --rc-muted: #5f7c70;
        --rc-line: #e2ede7;
        --rc-bg: #f3f8f6;
        --rc-white: #ffffff;
        --rc-radius-lg: 20px;
        --rc-radius-md: 14px;
        --rc-shadow: 0 6px 24px rgba(11, 61, 41, 0.08);
    }

    /* =========================================================
       PAGE WRAPPER
       ========================================================= */
    .hero-admin-wrap {
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        color: var(--rc-ink);
        padding-bottom: 2.5rem;
    }

    .hero-admin-wrap *,
    .hp-modal * {
        font-family: inherit;
    }

    .hero-admin-wrap .fas,
    .hero-admin-wrap .far,
    .hp-modal .fas,
    .hp-modal .far {
        font-family: "Font Awesome 5 Free", "Font Awesome 6 Free", "FontAwesome";
    }

    /* =========================================================
       PAGE HEADER
       ========================================================= */
    .hero-admin-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        background: var(--rc-white);
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-md);
        padding: 1.25rem 1.75rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--rc-shadow);
    }

    .hero-admin-header .hp-title-wrap {
        display: flex;
        align-items: flex-start;
        gap: 0.9rem;
        min-width: 0;
    }

    .hero-admin-header .hp-title-icon {
        flex: 0 0 auto;
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: rgba(52, 161, 41, 0.12);
        color: var(--rc-green);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
    }

    .hero-admin-header h4 {
        font-weight: 700;
        color: var(--rc-forest-900);
        font-size: 1.3rem;
        margin: 0 0 0.2rem;
    }

    .hero-admin-header small {
        color: var(--rc-muted);
        line-height: 1.45;
        display: block;
    }

    /* =========================================================
       CARDS
       ========================================================= */
    .hero-card {
        background: var(--rc-white);
        border: 1px solid var(--rc-line);
        border-radius: var(--rc-radius-md);
        box-shadow: var(--rc-shadow);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    .hero-card-header {
        padding: 1rem 1.5rem;
        background: linear-gradient(180deg, #fbfdfc, #f4f9f6);
        border-bottom: 1px solid var(--rc-line);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        min-height: 66px;
    }

    .hero-card-header h5 {
        margin: 0;
        font-weight: 700;
        color: var(--rc-forest-900);
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-size: 1.05rem;
    }

    .hero-card-header .btn,
    .hero-card-header .badge {
        flex: 0 0 auto;
        white-space: nowrap;
    }

    .hero-card-body {
        padding: 1.5rem;
    }

    /* =========================================================
       LIVE FRONTEND PREVIEW  (mirrors React Home.jsx hero)
       ========================================================= */
    .hero-preview-box {
        container-type: inline-size;
        container-name: hp;
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, var(--rc-navy) 0%, var(--rc-navy-dark) 100%);
        color: #fff;
        border-radius: var(--rc-radius-md);
        padding: 1.5rem 2rem 2.25rem;
        margin-bottom: 1.75rem;
        box-shadow: 0 14px 34px rgba(0, 32, 78, 0.28);
        font-family: 'Poppins', sans-serif;
    }

    .hero-preview-box::after {
        content: '';
        position: absolute;
        top: -80px;
        right: -80px;
        width: 320px;
        height: 320px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(52, 161, 41, 0.18), transparent 70%);
        pointer-events: none;
    }

    .hero-preview-box>* {
        position: relative;
        z-index: 1;
    }

    .hp-preview-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.5rem 1rem;
        padding-bottom: 0.9rem;
        margin-bottom: 1.75rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.12);
    }

    .hp-live-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: var(--rc-green);
        color: #fff;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        padding: 0.4rem 0.8rem;
        border-radius: 8px;
    }

    .hp-preview-note {
        color: rgba(255, 255, 255, 0.6);
        font-size: 0.8rem;
    }

    .hp-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        align-items: center;
    }

    .hp-grid>div {
        min-width: 0;
    }

    .hero-preview-title {
        font-weight: 800;
        font-size: clamp(1.9rem, 4.4vw, 3.2rem);
        font-size: clamp(1.9rem, 3.7cqw, 3.2rem);
        line-height: 1.15;
        margin-bottom: 1rem;
        color: #fff;
        word-wrap: break-word;
    }

    .hero-preview-accent {
        color: var(--rc-green);
        display: block;
    }

    .hero-preview-lead {
        color: rgba(255, 255, 255, 0.85);
        font-size: clamp(0.95rem, 1.6vw, 1.1rem);
        font-size: clamp(0.92rem, 1.55cqw, 1.1rem);
        line-height: 1.6;
        margin-bottom: 1.5rem;
    }

    .hp-btn-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .hp-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.65rem 1.5rem;
        border-radius: 8px;
        font-size: clamp(0.9rem, 1.5cqw, 1.15rem);
        line-height: 1.4;
        text-align: center;
        cursor: default;
        user-select: none;
    }

    .hp-btn-cta {
        background: var(--rc-green);
        border: 2px solid var(--rc-green);
        color: #fff;
        font-weight: 700;
    }

    .hp-btn-ghost {
        background: transparent;
        border: 2px solid rgba(255, 255, 255, 0.55);
        color: #fff;
        font-weight: 600;
    }

    .hp-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.15);
        margin-top: 1.75rem;
        padding-top: 1.5rem;
    }

    .hp-stat-num {
        color: var(--rc-green);
        font-weight: 800;
        font-size: clamp(1.1rem, 2.4vw, 1.6rem);
        font-size: clamp(1.1rem, 2.1cqw, 1.6rem);
        line-height: 1.2;
    }

    .hp-stat-label {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-top: 2px;
    }

    /* ---- Slider (same look as HeroSlider.jsx) ---- */
    .hp-slider {
        position: relative;
        width: 100%;
        aspect-ratio: 4 / 3;
        border-radius: 18px;
        overflow: hidden;
        background: #031527;
        box-shadow: 0 20px 44px rgba(0, 0, 0, 0.35);
        touch-action: pan-y;
    }

    .hp-slide {
        position: absolute;
        inset: 0;
        opacity: 0;
        transform: scale(1.04);
        transition: opacity 0.8s ease-in-out, transform 0.8s ease-out;
        pointer-events: none;
        z-index: 1;
    }

    .hp-slide.is-active {
        opacity: 1;
        transform: scale(1);
        pointer-events: auto;
        z-index: 2;
    }

    .hp-slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .hp-slide-caption {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        padding: 2.2rem 1rem 1rem;
        background: linear-gradient(180deg, transparent 0%, rgba(0, 32, 78, 0.75) 100%);
        pointer-events: none;
        color: #fff;
    }

    .hp-slide-caption span {
        font-size: 0.85rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.6);
    }

    .hp-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(0, 32, 78, 0.65);
        -webkit-backdrop-filter: blur(4px);
        backdrop-filter: blur(4px);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        cursor: pointer;
        font-size: 0.85rem;
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .hp-arrow:hover {
        background: rgba(0, 32, 78, 0.9);
        transform: translateY(-50%) scale(1.06);
    }

    .hp-arrow-prev {
        left: 12px;
    }

    .hp-arrow-next {
        right: 12px;
    }

    .hp-dots {
        position: absolute;
        left: 50%;
        bottom: 14px;
        transform: translateX(-50%);
        z-index: 10;
        display: flex;
        gap: 8px;
        background: rgba(0, 20, 48, 0.45);
        -webkit-backdrop-filter: blur(6px);
        backdrop-filter: blur(6px);
        padding: 5px 10px;
        border-radius: 20px;
    }

    .hp-dot {
        width: 8px;
        height: 8px;
        border-radius: 4px;
        border: none;
        padding: 0;
        background: rgba(255, 255, 255, 0.5);
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .hp-dot.is-active {
        width: 24px;
        background: var(--rc-green);
    }

    .hp-slider-empty {
        width: 100%;
        aspect-ratio: 4 / 3;
        border-radius: 18px;
        border: 2px dashed rgba(255, 255, 255, 0.25);
        background: rgba(255, 255, 255, 0.05);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 1.5rem;
        color: rgba(255, 255, 255, 0.65);
    }

    .hp-slider-empty i {
        font-size: 2.5rem;
        margin-bottom: 0.6rem;
        opacity: 0.8;
    }

    .hp-slider-empty p {
        margin: 0;
        font-size: 0.9rem;
    }

    /* Preview stacks like the frontend on narrow widths: slider first, text centered */
    @container hp (max-width: 760px) {
        .hp-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .hp-slider-col {
            order: -1;
        }

        .hp-text-col {
            text-align: center;
        }

        .hp-btn-row {
            justify-content: center;
        }

        .hp-btn {
            flex: 1 1 auto;
        }

        .hero-preview-title {
            font-size: clamp(1.6rem, 7vw, 2.2rem);
        }

        .hp-preview-bar {
            margin-bottom: 1.25rem;
        }

        .hp-preview-note {
            display: none;
        }
    }

    @container hp (max-width: 420px) {
        .hp-btn {
            width: 100%;
            flex-basis: 100%;
        }

        .hp-stats {
            gap: 0.5rem;
        }

        .hp-stat-label {
            font-size: 0.62rem;
        }

        .hp-arrow {
            width: 34px;
            height: 34px;
        }
    }

    /* =========================================================
       BUTTONS / FORM
       ========================================================= */
    .btn-recomm-primary {
        background: linear-gradient(135deg, var(--rc-green-600), var(--rc-emerald-400));
        color: #fff;
        border: none;
        font-weight: 600;
        padding: 0.6rem 1.4rem;
        border-radius: 10px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        white-space: nowrap;
    }

    .btn-recomm-primary:hover,
    .btn-recomm-primary:focus {
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(31, 157, 99, 0.3);
    }

    .hero-admin-wrap .form-label,
    .hp-modal .form-label {
        font-weight: 600;
        font-size: 0.86rem;
        color: var(--rc-forest-900);
        margin-bottom: 0.4rem;
    }

    .hero-admin-wrap .form-control,
    .hero-admin-wrap .form-select,
    .hp-modal .form-control,
    .hp-modal .form-select {
        border-radius: 10px;
        border: 1px solid var(--rc-line);
        background-color: #fbfdfc;
        padding: 0.6rem 0.9rem;
        font-size: 0.92rem;
        color: var(--rc-ink);
    }

    .hero-admin-wrap textarea.form-control {
        resize: vertical;
        min-height: 96px;
    }

    .hero-admin-wrap .form-control:focus,
    .hero-admin-wrap .form-select:focus,
    .hp-modal .form-control:focus,
    .hp-modal .form-select:focus {
        background-color: #fff;
        border-color: var(--rc-emerald-400);
        box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.15);
    }

    .hero-admin-wrap .form-check-input:checked,
    .hp-modal .form-check-input:checked {
        background-color: var(--rc-green);
        border-color: var(--rc-green);
    }

    .hero-admin-wrap .form-check-input:focus,
    .hp-modal .form-check-input:focus {
        box-shadow: 0 0 0 3px rgba(52, 161, 41, 0.2);
        border-color: var(--rc-green);
    }

    .hp-form-hint {
        display: block;
        margin-top: 0.35rem;
        font-size: 0.76rem;
        color: var(--rc-muted);
    }

    .hp-chip-label {
        display: block;
        font-size: 0.72rem;
        color: var(--rc-muted);
        margin: 0.5rem 0 0.1rem;
    }

    .suggestion-chip {
        display: inline-block;
        font-size: 0.76rem;
        background: var(--rc-bg);
        border: 1px dashed var(--rc-green-500);
        color: var(--rc-green-600);
        padding: 3px 9px;
        border-radius: 6px;
        cursor: pointer;
        margin-right: 4px;
        margin-top: 4px;
        transition: all 0.15s ease;
        user-select: none;
    }

    .suggestion-chip:hover {
        background: var(--rc-green-600);
        color: #fff;
        border-style: solid;
    }

    /* =========================================================
       SLIDER TABLE
       ========================================================= */
    .slide-thumb {
        width: 100px;
        height: 65px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid var(--rc-line);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        display: block;
    }

    .hp-table thead th {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--rc-muted);
        font-weight: 600;
        background: #f6faf8;
        border-bottom: 1px solid var(--rc-line);
        padding: 0.85rem 1rem;
        white-space: nowrap;
    }

    .hp-table tbody td {
        padding: 0.9rem 1rem;
        border-color: var(--rc-line);
    }

    .hp-table .hp-slide-title {
        font-weight: 600;
        color: var(--rc-forest-900);
        font-size: 0.92rem;
        line-height: 1.35;
        display: block;
    }

    .hp-table .btn-edit-slide,
    .hp-table .hp-btn-delete {
        width: 36px;
        height: 36px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }

    .hp-table .btn-edit-slide {
        color: var(--rc-green-600);
        border-color: var(--rc-green-500);
    }

    .hp-table .btn-edit-slide:hover {
        background: var(--rc-green-600);
        border-color: var(--rc-green-600);
        color: #fff;
    }

    .hp-table .form-switch .form-check-input {
        width: 2.4em;
        height: 1.25em;
        cursor: pointer;
        margin: 0;
    }

    /* =========================================================
       MODALS
       ========================================================= */
    .hp-modal .modal-content {
        border-radius: 16px;
        overflow: hidden;
    }

    .hp-modal .modal-header,
    .hp-modal .modal-footer {
        background: #f6faf8 !important;
        border-color: var(--rc-line);
    }

    /* =========================================================
       RESPONSIVE
       ========================================================= */
    @media (max-width: 991.98px) {
        .hero-preview-box {
            padding: 1.25rem 1.5rem 1.75rem;
        }
    }

    @media (max-width: 767.98px) {
        .hero-admin-wrap {
            padding-left: 0.25rem;
            padding-right: 0.25rem;
        }

        .hero-admin-header {
            flex-direction: column;
            align-items: stretch;
            padding: 1.1rem;
            gap: 1rem;
        }

        .hero-admin-header h4 {
            font-size: 1.1rem;
        }

        .hero-admin-header .btn {
            width: 100%;
        }

        .hero-admin-header .d-flex {
            width: 100%;
        }

        .hero-preview-box {
            padding: 1rem 1rem 1.5rem;
            border-radius: 14px;
        }

        .hero-card-body {
            padding: 1.1rem;
        }

        .hero-card-header {
            padding: 0.9rem 1.1rem;
            min-height: 0;
        }

        .hero-card-header h5 {
            font-size: 0.98rem;
        }

        .hp-save-row {
            justify-content: stretch !important;
        }

        .hp-save-row .btn {
            width: 100%;
        }

        /* Slider table -> stacked cards */
        .hp-table thead {
            display: none;
        }

        .hp-table,
        .hp-table tbody {
            display: block;
            width: 100%;
        }

        .hp-table tbody tr.hp-slide-row {
            display: grid;
            grid-template-columns: 96px 1fr;
            grid-template-areas:
                "img title"
                "meta meta";
            column-gap: 0.9rem;
            row-gap: 0.75rem;
            padding: 1rem;
            border-bottom: 1px solid var(--rc-line);
        }

        .hp-table tbody tr.hp-slide-row:last-child {
            border-bottom: none;
        }

        .hp-table tbody tr.hp-slide-row>td {
            display: block;
            padding: 0;
            border: none;
            background: transparent;
            text-align: left !important;
            width: auto !important;
        }

        .hp-table td.hp-td-img {
            grid-area: img;
        }

        .hp-table td.hp-td-title {
            grid-area: title;
            align-self: center;
        }

        .hp-table .slide-thumb {
            width: 96px;
            height: 64px;
        }

        /* order / active / actions share one row */
        .hp-table tbody tr.hp-slide-row {
            grid-template-columns: 96px 1fr auto auto;
            grid-template-areas:
                "img title title title"
                "order active . actions";
            align-items: center;
        }

        .hp-table td.hp-td-order {
            grid-area: order;
        }

        .hp-table td.hp-td-active {
            grid-area: active;
        }

        .hp-table td.hp-td-actions {
            grid-area: actions;
            justify-self: end;
        }

        .hp-table td.hp-td-order::before,
        .hp-table td.hp-td-active::before {
            content: attr(data-label);
            display: block;
            font-size: 0.66rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--rc-muted);
            margin-bottom: 4px;
        }

        .hp-table td.hp-td-active .form-switch {
            display: block;
        }

        .hp-table tbody tr.hp-empty-row {
            display: block;
        }

        .hp-table tbody tr.hp-empty-row>td {
            display: block;
            width: 100%;
        }
    }

    @media (max-width: 400px) {
        .hp-table tbody tr.hp-slide-row {
            grid-template-columns: 80px 1fr auto auto;
        }

        .hp-table .slide-thumb {
            width: 80px;
            height: 56px;
        }
    }
</style>

<div class="container-fluid hero-admin-wrap">

    <!-- Top Header -->
    <div class="hero-admin-header">
        <div class="hp-title-wrap">
            <span class="hp-title-icon"><i class="fas fa-images"></i></span>
            <div>
                <h4>Hero Banner &amp; Slider Management</h4>
                <small>Customize the home page hero headline, subtitle accent, description, CTA buttons, and slider images</small>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-recomm-primary" data-bs-toggle="modal" data-bs-target="#addSlideModal">
                <i class="fas fa-plus-circle me-1"></i> Add Slider Image
            </button>
        </div>
    </div>

    <!-- Live Preview Banner (mirrors the frontend hero) -->
    <div class="hero-preview-box">
        <div class="hp-preview-bar">
            <span class="hp-live-badge"><i class="fas fa-eye"></i> Live Frontend Preview</span>
            <span class="hp-preview-note">Preview dynamically mirrors your saved configuration</span>
        </div>

        <div class="hp-grid">
            <div class="hp-text-col">
                <h1 class="hero-preview-title">
                    <?= htmlspecialchars($content->title ?? 'Delicious Food & Beverages') ?>
                    <span class="hero-preview-accent"><?= htmlspecialchars($content->subtitle ?? 'Delivered to Your Door') ?></span>
                </h1>
                <p class="hero-preview-lead">
                    <?= htmlspecialchars($content->description ?? 'Discover premium quality food and beverages. Fresh, organic, and delivered with care.') ?>
                </p>
                <div class="hp-btn-row">
                    <span class="hp-btn hp-btn-cta"><?= htmlspecialchars($content->btn_text ?? 'Shop Now') ?> <i class="fas fa-arrow-right"></i></span>
                    <span class="hp-btn hp-btn-ghost"><?= htmlspecialchars($content->btn2_text ?? 'Explore Products') ?></span>
                </div>

                <div class="hp-stats">
                    <div>
                        <div class="hp-stat-num">500+</div>
                        <div class="hp-stat-label">Products</div>
                    </div>
                    <div>
                        <div class="hp-stat-num">50K+</div>
                        <div class="hp-stat-label">Happy Customers</div>
                    </div>
                    <div>
                        <div class="hp-stat-num">4.8★</div>
                        <div class="hp-stat-label">Avg. Rating</div>
                    </div>
                </div>
            </div>

            <div class="hp-slider-col">
                <?php if (!empty($sliders)): ?>
                    <div id="heroPreviewSlider" class="hp-slider">
                        <?php foreach ($sliders as $k => $sl):
                            $img_src = filter_var($sl->image, FILTER_VALIDATE_URL) ? $sl->image : base_url($sl->image);
                        ?>
                            <div class="hp-slide <?= $k === 0 ? 'is-active' : '' ?>">
                                <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($sl->title) ?>">
                                <?php if (!empty($sl->title)): ?>
                                    <div class="hp-slide-caption">
                                        <span><?= htmlspecialchars($sl->title) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>

                        <?php if (count($sliders) > 1): ?>
                            <button type="button" class="hp-arrow hp-arrow-prev" aria-label="Previous Slide">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button type="button" class="hp-arrow hp-arrow-next" aria-label="Next Slide">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                            <div class="hp-dots">
                                <?php foreach ($sliders as $k => $sl): ?>
                                    <button type="button" class="hp-dot <?= $k === 0 ? 'is-active' : '' ?>" data-index="<?= $k ?>" aria-label="Go to slide <?= $k + 1 ?>"></button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="hp-slider-empty">
                        <i class="fas fa-image"></i>
                        <p>No active slider images uploaded yet. Click "Add Slider Image" above.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Datalist for Link Suggestions -->
    <datalist id="heroLinkSuggestions">
        <option value="/products">All Products Catalog</option>
        <option value="/categories">All Categories</option>
        <option value="#featured">Featured Products Section</option>
        <option value="#categories">Categories Section</option>
        <option value="#stores">Nearby Stores Section</option>
        <option value="/cart">Cart Page</option>
        <option value="/wishlist">Wishlist</option>
    </datalist>

    <div class="row g-4">
        <!-- Left Column: Hero Text & Settings -->
        <div class="col-12 col-xl-6">
            <div class="hero-card">
                <div class="hero-card-header">
                    <h5><i class="fas fa-edit text-primary"></i> Hero Content &amp; Text</h5>
                    <span class="badge bg-light text-dark border">Text &amp; Buttons</span>
                </div>
                <div class="hero-card-body">
                    <form action="<?= base_url('hero-section/update-content') ?>" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Main Headline Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($content->title ?? '') ?>" placeholder="e.g. Delicious Food & Beverages">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Highlight Accent Subtitle</label>
                            <input type="text" name="subtitle" class="form-control" value="<?= htmlspecialchars($content->subtitle ?? '') ?>" placeholder="e.g. Delivered to Your Door">
                            <small class="hp-form-hint">Displayed in bright green accent below the main title</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description / Subtitle Paragraph</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Short description of your services..."><?= htmlspecialchars($content->description ?? '') ?></textarea>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Primary Button Text</label>
                                <input type="text" name="btn_text" class="form-control" value="<?= htmlspecialchars($content->btn_text ?? 'Shop Now') ?>" placeholder="Shop Now">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Primary Button Link</label>
                                <input type="text" name="btn_link" id="input_btn_link" list="heroLinkSuggestions" class="form-control" value="<?= htmlspecialchars($content->btn_link ?? '/products') ?>" placeholder="/products">
                                <div>
                                    <small class="hp-chip-label">Suggestions:</small>
                                    <span class="suggestion-chip" onclick="setLink('input_btn_link', '/products')">+ /products</span>
                                    <span class="suggestion-chip" onclick="setLink('input_btn_link', '/categories')">+ /categories</span>
                                    <span class="suggestion-chip" onclick="setLink('input_btn_link', '#featured')">+ #featured</span>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Secondary Button Text</label>
                                <input type="text" name="btn2_text" class="form-control" value="<?= htmlspecialchars($content->btn2_text ?? 'Explore Products') ?>" placeholder="Explore Products">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Secondary Button Link</label>
                                <input type="text" name="btn2_link" id="input_btn2_link" list="heroLinkSuggestions" class="form-control" value="<?= htmlspecialchars($content->btn2_link ?? '#featured') ?>" placeholder="#featured">
                                <div>
                                    <small class="hp-chip-label">Suggestions:</small>
                                    <span class="suggestion-chip" onclick="setLink('input_btn2_link', '#featured')">+ #featured</span>
                                    <span class="suggestion-chip" onclick="setLink('input_btn2_link', '/products')">+ /products</span>
                                    <span class="suggestion-chip" onclick="setLink('input_btn2_link', '#categories')">+ #categories</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end hp-save-row">
                            <button type="submit" class="btn btn-recomm-primary px-4">
                                <i class="fas fa-save me-1"></i> Save Hero Text Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column: Slider Images List -->
        <div class="col-12 col-xl-6">
            <div class="hero-card">
                <div class="hero-card-header">
                    <h5><i class="fas fa-sliders-h text-success"></i> Slider Images</h5>
                    <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#addSlideModal">
                        <i class="fas fa-plus me-1"></i> Add Slide
                    </button>
                </div>
                <div class="hero-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 hp-table">
                            <thead>
                                <tr>
                                    <th style="width: 120px;">Image</th>
                                    <th>Title / Caption</th>
                                    <th class="text-center" style="width: 80px;">Order</th>
                                    <th class="text-center" style="width: 90px;">Active</th>
                                    <th class="text-center" style="width: 110px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($sliders)): ?>
                                    <?php foreach ($sliders as $slide):
                                        $img_url = filter_var($slide->image, FILTER_VALIDATE_URL) ? $slide->image : base_url($slide->image);
                                    ?>
                                        <tr class="hp-slide-row">
                                            <td class="hp-td-img">
                                                <a href="<?= $img_url ?>" target="_blank" title="View Full Image">
                                                    <img src="<?= $img_url ?>" class="slide-thumb" alt="<?= htmlspecialchars($slide->title) ?>">
                                                </a>
                                            </td>
                                            <td class="hp-td-title">
                                                <span class="hp-slide-title"><?= !empty($slide->title) ? htmlspecialchars($slide->title) : '<span class="text-muted fst-italic">No title</span>' ?></span>
                                                <!-- <small class="text-muted">ID: #<?= $slide->id ?></small> -->
                                            </td>
                                            <td class="text-center hp-td-order" data-label="Order">
                                                <span class="badge bg-light text-dark border px-2 py-1"><?= $slide->sort_order ?></span>
                                            </td>
                                            <td class="text-center hp-td-active" data-label="Active">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input slide-status-toggle"
                                                        type="checkbox"
                                                        role="switch"
                                                        data-id="<?= $slide->id ?>"
                                                        <?= $slide->is_active ? 'checked' : '' ?>>
                                                </div>
                                            </td>
                                            <td class="text-center hp-td-actions">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-primary btn-edit-slide"
                                                        data-id="<?= $slide->id ?>"
                                                        data-title="<?= htmlspecialchars($slide->title ?? '') ?>"
                                                        data-order="<?= $slide->sort_order ?>"
                                                        data-active="<?= $slide->is_active ?>"
                                                        data-img="<?= $img_url ?>"
                                                        title="Edit Slide">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="<?= base_url('hero-section/delete-slide/' . $slide->id) ?>"
                                                        class="btn btn-sm btn-outline-danger hp-btn-delete"
                                                        onclick="return confirm('Are you sure you want to delete this slider image?')"
                                                        title="Delete Slide">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr class="hp-empty-row">
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="fas fa-images fa-2x mb-2 d-block text-secondary opacity-50"></i>
                                            No slider images uploaded yet. Click <strong>Add Slide</strong> to upload images for your Hero slider.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add New Slide -->
<div class="modal fade hp-modal" id="addSlideModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('hero-section/add-slide') ?>" method="POST" enctype="multipart/form-data" class="d-flex flex-column overflow-hidden">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle text-success me-2"></i> Add Slider Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Select Image <span class="text-danger">*</span></label>
                        <input type="file" name="image" class="form-control" accept="image/*" required id="addSlideFileInput" onchange="previewUploadImage(this, 'addSlidePreview')">
                        <small class="hp-form-hint">Recommended aspect ratio: 4:3 (JPG, PNG, WebP, max 5MB) — matches the frontend slider</small>
                    </div>

                    <div class="mb-3 text-center d-none" id="addSlidePreviewContainer">
                        <img src="" id="addSlidePreview" class="img-fluid rounded border shadow-sm" style="max-height: 180px;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Slide Title / Caption (Optional)</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Fresh Organic Farm Vegetables">
                    </div>

                    <div class="row g-3 mb-1">
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0" min="0">
                            <small class="hp-form-hint">Lower number displays first</small>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="addIsActive" checked>
                                <label class="form-check-label" for="addIsActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-recomm-primary">
                        <i class="fas fa-upload me-1"></i> Upload Slide
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Slide -->
<div class="modal fade hp-modal" id="editSlideModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <form id="editSlideForm" action="" method="POST" enctype="multipart/form-data" class="d-flex flex-column overflow-hidden">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold"><i class="fas fa-edit text-primary me-2"></i> Edit Slider Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3 text-center">
                        <label class="form-label d-block text-start">Current Image</label>
                        <img src="" id="editSlideCurrentImg" class="img-fluid rounded border shadow-sm" style="max-height: 160px; object-fit: cover;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Replace Image (Optional)</label>
                        <input type="file" name="image" class="form-control" accept="image/*" onchange="previewUploadImage(this, 'editSlideCurrentImg')">
                        <small class="hp-form-hint">Leave empty to keep existing image</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Slide Title / Caption</label>
                        <input type="text" name="title" id="editSlideTitle" class="form-control" placeholder="Slide Title">
                    </div>

                    <div class="row g-3 mb-1">
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" id="editSlideOrder" class="form-control" min="0">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editSlideActive">
                                <label class="form-check-label" for="editSlideActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-recomm-primary">
                        <i class="fas fa-save me-1"></i> Update Slide
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function setLink(inputId, value) {
        const el = document.getElementById(inputId);
        if (el) {
            el.value = value;
            el.focus();
        }
    }

    function previewUploadImage(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById(previewId);
                if (preview) {
                    preview.src = e.target.result;
                    const container = document.getElementById(previewId + 'Container');
                    if (container) {
                        container.classList.remove('d-none');
                    }
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    /* Live preview slider — same behaviour as the frontend HeroSlider (fade, autoplay, swipe, pill dots) */
    function initHeroPreviewSlider() {
        const root = document.getElementById('heroPreviewSlider');
        if (!root) return;

        const slides = root.querySelectorAll('.hp-slide');
        const dots = root.querySelectorAll('.hp-dot');
        const prevBtn = root.querySelector('.hp-arrow-prev');
        const nextBtn = root.querySelector('.hp-arrow-next');
        const total = slides.length;
        let current = 0;
        let paused = false;
        let touchStartX = null;
        let touchEndX = null;

        function show(index) {
            current = (index + total) % total;
            slides.forEach((s, i) => s.classList.toggle('is-active', i === current));
            dots.forEach((d, i) => d.classList.toggle('is-active', i === current));
        }

        if (total <= 1) return;

        if (nextBtn) nextBtn.addEventListener('click', () => show(current + 1));
        if (prevBtn) prevBtn.addEventListener('click', () => show(current - 1));
        dots.forEach(d => d.addEventListener('click', () => show(parseInt(d.getAttribute('data-index'), 10) || 0)));

        root.addEventListener('mouseenter', () => {
            paused = true;
        });
        root.addEventListener('mouseleave', () => {
            paused = false;
        });

        root.addEventListener('touchstart', e => {
            touchStartX = e.targetTouches[0].clientX;
        }, {
            passive: true
        });
        root.addEventListener('touchmove', e => {
            touchEndX = e.targetTouches[0].clientX;
        }, {
            passive: true
        });
        root.addEventListener('touchend', () => {
            if (touchStartX === null || touchEndX === null) return;
            const distance = touchStartX - touchEndX;
            if (distance > 50) show(current + 1);
            else if (distance < -50) show(current - 1);
            touchStartX = null;
            touchEndX = null;
        });

        setInterval(() => {
            if (!paused) show(current + 1);
        }, 4500);
    }

    document.addEventListener('DOMContentLoaded', function() {
        initHeroPreviewSlider();

        const editButtons = document.querySelectorAll('.btn-edit-slide');
        const editModalEl = document.getElementById('editSlideModal');
        const editModal = editModalEl ? new bootstrap.Modal(editModalEl) : null;

        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const title = this.getAttribute('data-title');
                const order = this.getAttribute('data-order');
                const active = this.getAttribute('data-active');
                const img = this.getAttribute('data-img');

                const form = document.getElementById('editSlideForm');
                form.action = '<?= base_url('hero-section/edit-slide/') ?>' + id;

                document.getElementById('editSlideTitle').value = title;
                document.getElementById('editSlideOrder').value = order;
                document.getElementById('editSlideActive').checked = (active == '1');
                document.getElementById('editSlideCurrentImg').src = img;

                if (editModal) {
                    editModal.show();
                }
            });
        });

        const toggles = document.querySelectorAll('.slide-status-toggle');
        toggles.forEach(toggle => {
            toggle.addEventListener('change', function() {
                const id = this.getAttribute('data-id');
                const status = this.checked ? 1 : 0;

                const formData = new FormData();
                formData.append('id', id);
                formData.append('status', status);

                fetch('<?= base_url('hero-section/toggle-status') ?>', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.status) {
                            alert(data.message || 'Failed to update status');
                            toggle.checked = !toggle.checked;
                        }
                    })
                    .catch(err => {
                        console.error('Error updating status:', err);
                        alert('Network error updating status');
                        toggle.checked = !toggle.checked;
                    });
            });
        });
    });
</script>