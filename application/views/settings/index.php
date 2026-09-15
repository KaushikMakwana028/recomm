<div class="row">
    <div class="col-md-8 offset-md-2 col-lg-6 offset-lg-3">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="mb-0 text-primary fw-bold">
                    <i class="fas fa-map-marked-alt me-2"></i>Delivery & Discovery Radius Setting
                </h5>
            </div>
            <div class="card-body p-4">
                <p class="text-muted small mb-4">
                    Set the maximum delivery distance (in kilometers) for nearby vendor discovery. Customers will only see vendors who are within this radius and have stock available.
                </p>

                <form action="<?= base_url('settings/delivery') ?>" method="POST">
                    <div class="mb-3">
                        <label for="delivery_radius_km" class="form-label fw-semibold">
                            Maximum Delivery Radius (km) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" 
                                   step="0.5" 
                                   min="1" 
                                   max="100" 
                                   class="form-control form-control-lg" 
                                   id="delivery_radius_km" 
                                   name="delivery_radius_km" 
                                   value="<?= set_value('delivery_radius_km', $delivery_radius_km) ?>" 
                                   required>
                            <span class="input-group-text bg-light text-muted">Kilometers</span>
                        </div>
                        <div class="form-text mt-2 text-muted">
                            Sensible default: 8–10 km. Current active setting: <strong><?= htmlspecialchars($delivery_radius_km) ?> km</strong>.
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                            <i class="fas fa-save me-1"></i> Save Setting
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>