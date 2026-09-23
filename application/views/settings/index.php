<style>
    /* ===== Delivery Settings (ds-) — page-scoped, PremiumSaaS admin style ===== */
    .ds-wrap {
        --ds-primary: #6366F1;
        --ds-primary-dark: #4F46E5;
        --ds-primary-light: #EEF2FF;
        --ds-text: #1E293B;
        --ds-text-muted: #64748B;
        --ds-border: #E2E8F0;
        --ds-bg-card: #FFFFFF;
        --ds-bg-page: #F8FAFC;
        --ds-success: #10B981;
        --ds-radius: 14px;
        --ds-shadow: 0 1px 3px rgba(15, 23, 42, 0.06), 0 1px 2px rgba(15, 23, 42, 0.04);
        --ds-shadow-lg: 0 10px 25px -5px rgba(99, 102, 241, 0.15), 0 8px 10px -6px rgba(99, 102, 241, 0.08);

        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        background: var(--ds-bg-page);
        padding: clamp(16px, 4vw, 32px);
        min-height: 100%;
        color: var(--ds-text);
    }

    [data-theme="dark"] .ds-wrap {
        --ds-text: #E2E8F0;
        --ds-text-muted: #94A3B8;
        --ds-border: #2D3548;
        --ds-bg-card: #1A2035;
        --ds-bg-page: #10121F;
        --ds-primary-light: rgba(99, 102, 241, 0.12);
    }

    .ds-container {
        max-width: 760px;
        margin: 0 auto;
    }

    /* Header */
    .ds-header {
        margin-bottom: 20px;
    }

    .ds-header h1 {
        font-size: clamp(20px, 3vw, 26px);
        font-weight: 700;
        color: var(--ds-text);
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0 0 6px 0;
    }

    .ds-header .ds-icon-badge {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: var(--ds-primary-light);
        color: var(--ds-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        flex-shrink: 0;
    }

    .ds-header p {
        color: var(--ds-text-muted);
        font-size: 14px;
        line-height: 1.6;
        margin: 0;
        max-width: 60ch;
    }

    /* Card */
    .ds-card {
        background: var(--ds-bg-card);
        border: 1px solid var(--ds-border);
        border-radius: var(--ds-radius);
        box-shadow: var(--ds-shadow);
        overflow: hidden;
    }

    /* Field block */
    .ds-field {
        padding: clamp(16px, 4vw, 24px) clamp(16px, 4vw, 28px);
        border-bottom: 1px solid var(--ds-border);
        transition: background 0.15s ease;
    }

    .ds-field:last-of-type {
        border-bottom: none;
    }

    .ds-field-top {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        margin-bottom: 14px;
    }

    .ds-field-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: var(--ds-primary-light);
        color: var(--ds-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .ds-field-label-group {
        flex: 1;
        min-width: 0;
    }

    .ds-field label {
        font-weight: 600;
        font-size: 15px;
        color: var(--ds-text);
        display: block;
        margin-bottom: 3px;
    }

    .ds-field .ds-req {
        color: #EF4444;
        margin-left: 2px;
    }

    .ds-field-desc {
        font-size: 13px;
        color: var(--ds-text-muted);
        line-height: 1.55;
        margin: 0;
    }

    /* Input group */
    .ds-input-group {
        display: flex;
        align-items: stretch;
        border: 1.5px solid var(--ds-border);
        border-radius: 10px;
        overflow: hidden;
        background: var(--ds-bg-card);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .ds-input-group:focus-within {
        border-color: var(--ds-primary);
        box-shadow: 0 0 0 3.5px rgba(99, 102, 241, 0.15);
    }

    .ds-input-affix {
        display: flex;
        align-items: center;
        padding: 0 14px;
        background: var(--ds-bg-page);
        color: var(--ds-text-muted);
        font-size: 14px;
        font-weight: 600;
        white-space: nowrap;
        border-right: 1px solid var(--ds-border);
    }

    .ds-input-affix.ds-suffix {
        border-right: none;
        border-left: 1px solid var(--ds-border);
        font-weight: 500;
        font-size: 13px;
    }

    .ds-input-group input[type="number"] {
        flex: 1;
        min-width: 0;
        border: none;
        outline: none;
        background: transparent;
        padding: 13px 14px;
        font-size: 16px;
        font-weight: 600;
        color: var(--ds-text);
        -moz-appearance: textfield;
    }

    .ds-input-group input[type="number"]::-webkit-outer-spin-button,
    .ds-input-group input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Current value pill */
    .ds-current-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 10px;
        padding: 5px 12px;
        background: var(--ds-primary-light);
        color: var(--ds-primary-dark);
        border-radius: 20px;
        font-size: 12.5px;
        font-weight: 600;
    }

    [data-theme="dark"] .ds-current-pill {
        color: #A5B4FC;
    }

    .ds-current-pill i {
        font-size: 10px;
    }

    .ds-hint {
        font-size: 12px;
        color: var(--ds-text-muted);
        margin-top: 8px;
        display: block;
    }

    /* Footer / submit */
    .ds-footer {
        padding: 18px clamp(16px, 4vw, 28px);
        background: var(--ds-bg-page);
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
    }

    .ds-btn-save {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, var(--ds-primary), var(--ds-primary-dark));
        color: #fff;
        border: none;
        padding: 12px 26px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14.5px;
        cursor: pointer;
        box-shadow: var(--ds-shadow-lg);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        width: 100%;
        justify-content: center;
    }

    .ds-btn-save:hover {
        transform: translateY(-1px);
    }

    .ds-btn-save:active {
        transform: translateY(0);
    }

    @media (min-width: 576px) {
        .ds-btn-save {
            width: auto;
            justify-content: flex-start;
        }
    }

    /* Mobile tightening */
    @media (max-width: 480px) {
        .ds-field-top {
            gap: 10px;
        }

        .ds-field-icon {
            width: 34px;
            height: 34px;
            font-size: 14px;
        }

        .ds-input-affix {
            padding: 0 10px;
            font-size: 13px;
        }

        .ds-input-group input[type="number"] {
            font-size: 15px;
            padding: 11px 10px;
        }
    }
</style>

<div class="ds-wrap">
    <div class="ds-container">

        <div class="ds-header">
            <h1><span class="ds-icon-badge"><i class="fas fa-map-marked-alt"></i></span>Delivery &amp; Discovery Radius Setting</h1>
            <p>Set the maximum delivery distance for nearby vendor discovery. Customers will only see vendors who are within this radius and have stock available.</p>
        </div>

        <form action="<?= base_url('settings/delivery') ?>" method="POST">
            <div class="ds-card">

                <div class="ds-field">
                    <div class="ds-field-top">
                        <div class="ds-field-icon"><i class="fas fa-route"></i></div>
                        <div class="ds-field-label-group">
                            <label for="delivery_radius_km">Maximum Delivery Radius<span class="ds-req">*</span></label>
                            <p class="ds-field-desc">The farthest a vendor can be from a customer to appear in discovery results.</p>
                        </div>
                    </div>
                    <div class="ds-input-group">
                        <input type="number"
                            step="0.5"
                            min="1"
                            max="100"
                            id="delivery_radius_km"
                            name="delivery_radius_km"
                            value="<?= set_value('delivery_radius_km', $delivery_radius_km) ?>"
                            required>
                        <span class="ds-input-affix ds-suffix">Kilometers</span>
                    </div>
                    <span class="ds-hint">Sensible default: 8–10 km</span>
                    <div class="ds-current-pill"><i class="fas fa-circle"></i>Active: <?= htmlspecialchars($delivery_radius_km) ?> km</div>
                </div>

                <div class="ds-field">
                    <div class="ds-field-top">
                        <div class="ds-field-icon"><i class="fas fa-truck-fast"></i></div>
                        <div class="ds-field-label-group">
                            <label for="delivery_charge_per_km">Delivery Charge per KM<span class="ds-req">*</span></label>
                            <p class="ds-field-desc">Distance between vendor shop &amp; customer address is multiplied by this rate.</p>
                        </div>
                    </div>
                    <div class="ds-input-group">
                        <span class="ds-input-affix">₹</span>
                        <input type="number"
                            step="0.5"
                            min="0"
                            max="500"
                            id="delivery_charge_per_km"
                            name="delivery_charge_per_km"
                            value="<?= set_value('delivery_charge_per_km', $delivery_charge_per_km) ?>"
                            required>
                        <span class="ds-input-affix ds-suffix">per KM</span>
                    </div>
                    <div class="ds-current-pill"><i class="fas fa-circle"></i>Active: ₹<?= number_format($delivery_charge_per_km, 2) ?>/km</div>
                </div>

                <div class="ds-field">
                    <div class="ds-field-top">
                        <div class="ds-field-icon"><i class="fas fa-bolt"></i></div>
                        <div class="ds-field-label-group">
                            <label for="immediate_delivery_charge">Immediate Delivery Extra Charge<span class="ds-req">*</span></label>
                            <p class="ds-field-desc">Flat surcharge added when a customer selects the <strong>Immediately</strong> delivery slot.</p>
                        </div>
                    </div>
                    <div class="ds-input-group">
                        <span class="ds-input-affix">₹</span>
                        <input type="number"
                            step="1"
                            min="0"
                            max="10000"
                            id="immediate_delivery_charge"
                            name="immediate_delivery_charge"
                            value="<?= set_value('immediate_delivery_charge', $immediate_delivery_charge ?? 50.00) ?>"
                            required>
                        <span class="ds-input-affix ds-suffix">flat fee</span>
                    </div>
                <div class="ds-field">
                    <div class="ds-field-top">
                        <div class="ds-field-icon"><i class="fas fa-boxes-stacked"></i></div>
                        <div class="ds-field-label-group">
                            <label for="low_stock_threshold">Low Stock Warning Threshold<span class="ds-req">*</span></label>
                            <p class="ds-field-desc">Products with inventory below this quantity will display a subtle <strong>"Only X left"</strong> warning badge.</p>
                        </div>
                    </div>
                    <div class="ds-input-group">
                        <input type="number"
                            step="1"
                            min="1"
                            max="1000"
                            id="low_stock_threshold"
                            name="low_stock_threshold"
                            value="<?= set_value('low_stock_threshold', $low_stock_threshold ?? 5) ?>"
                            required>
                        <span class="ds-input-affix ds-suffix">units</span>
                    </div>
                    <span class="ds-hint">Default: 5 units (products with stock &le; 5 show "Only X left")</span>
                    <div class="ds-current-pill"><i class="fas fa-circle"></i>Active: <?= htmlspecialchars($low_stock_threshold ?? 5) ?> units</div>
                </div>

                <div class="ds-footer">
                    <button type="submit" class="ds-btn-save">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                </div>

            </div>
        </form>

    </div>
</div>