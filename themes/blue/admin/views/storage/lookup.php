<style>
.warehouse-lookup {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
}

.scan-section {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 25px;
    margin-bottom: 25px;
}

.scan-input-group {
    max-width: 600px;
    margin: 0 auto;
}

.product-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 25px;
    margin-top: 20px;
}

.product-header {
    border-bottom: 2px solid #007bff;
    padding-bottom: 15px;
    margin-bottom: 20px;
}

.product-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.info-item {
    padding: 12px;
    background: #f8f9fa;
    border-radius: 6px;
    border-left: 3px solid #007bff;
}

.info-label {
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
    margin-bottom: 5px;
    font-weight: 600;
}

.info-value {
    font-size: 16px;
    color: #212529;
    font-weight: 500;
}

.location-badge {
    display: inline-block;
    padding: 8px 15px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 14px;
}

.location-badge.active {
    background: #28a745;
    color: white;
}

.location-badge.inactive {
    background: #dc3545;
    color: white;
}

.assign-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-top: 20px;
}

.location-select {
    width: 100%;
    padding: 12px;
    border: 2px solid #ced4da;
    border-radius: 6px;
    font-size: 14px;
    margin-bottom: 15px;
}

.location-select:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.btn-assign {
    padding: 12px 30px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-assign.success {
    background: #28a745;
    color: white;
}

.btn-assign.success:hover {
    background: #218838;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.btn-clear {
    background: #6c757d;
    color: white;
    margin-left: 10px;
}

.btn-clear:hover {
    background: #5a6268;
}

.quantity-input {
    width: 120px;
    padding: 8px;
    border: 2px solid #ced4da;
    border-radius: 6px;
    margin-right: 10px;
}

.alert-custom {
    padding: 15px 20px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-size: 14px;
}

.alert-danger-custom {
    background: #f8d7da;
    color: #721c24;
    border-left: 4px solid #dc3545;
}

.alert-success-custom {
    background: #d4edda;
    color: #155724;
    border-left: 4px solid #28a745;
}

.loading {
    text-align: center;
    padding: 40px;
}

.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #007bff;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
    margin: 0 auto;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Recommendation Cards */
.recommendations-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.recommendation-card {
    background: white;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.recommendation-card:hover {
    border-color: #007bff;
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2);
    transform: translateY(-2px);
}

.recommendation-card.selected {
    border-color: #28a745;
    background: #f0fff4;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.recommendation-card.recommended-best {
    border-color: #ffc107;
    background: linear-gradient(135deg, #fffef7 0%, #fff9e6 100%);
}

.recommendation-card.recommended-best::before {
    content: "⭐ RECOMMENDED";
    position: absolute;
    top: -10px;
    right: 10px;
    background: #ffc107;
    color: #000;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.5px;
}

.recommendation-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.priority-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.priority-badge.priority-1 {
    background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
    color: #000;
    box-shadow: 0 2px 8px rgba(255, 215, 0, 0.4);
}

.priority-badge.priority-2 {
    background: linear-gradient(135deg, #c0c0c0 0%, #e8e8e8 100%);
    color: #333;
    box-shadow: 0 2px 8px rgba(192, 192, 192, 0.4);
}

.priority-badge.priority-3 {
    background: linear-gradient(135deg, #cd7f32 0%, #d4956e 100%);
    color: #fff;
    box-shadow: 0 2px 8px rgba(205, 127, 50, 0.4);
}

.capacity-warning {
    background: #dc3545;
    color: white;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.location-path {
    font-size: 14px;
    font-weight: 600;
    color: #007bff;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.location-path i {
    color: #dc3545;
}

.recommendation-reason {
    font-size: 13px;
    color: #6c757d;
    font-style: italic;
    margin-bottom: 12px;
    padding: 8px 12px;
    background: #f8f9fa;
    border-left: 3px solid #007bff;
    border-radius: 4px;
}

.usage-info {
    font-size: 12px;
    color: #495057;
    margin-bottom: 12px;
    padding: 6px 10px;
    background: #e7f3ff;
    border-radius: 4px;
}

.capacity-section {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #e0e0e0;
}

.capacity-label {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: #495057;
    margin-bottom: 6px;
    font-weight: 600;
}

.capacity-bar-container {
    width: 100%;
    height: 12px;
    background: #e9ecef;
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: 6px;
}

.capacity-bar {
    height: 100%;
    transition: width 0.4s ease;
    border-radius: 6px;
}

.capacity-bar.capacity-low {
    background: linear-gradient(90deg, #28a745 0%, #34ce57 100%);
}

.capacity-bar.capacity-medium {
    background: linear-gradient(90deg, #ffc107 0%, #ffcd38 100%);
}

.capacity-bar.capacity-high {
    background: linear-gradient(90deg, #dc3545 0%, #e4606d 100%);
}

.capacity-available {
    font-size: 12px;
    color: #28a745;
    font-weight: 600;
}

.no-recommendations {
    text-align: center;
    padding: 40px;
    color: #6c757d;
}

.no-recommendations i {
    color: #dee2e6;
    margin-bottom: 15px;
}

.no-recommendations p {
    font-size: 14px;
    margin: 0;
}

</style>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-warehouse"></i><?= lang('Warehouse Product Lookup & Shelving'); ?></h2>
    </div>
    <div class="box-content warehouse-lookup">
        <!-- Warehouse Selection Section -->
        <div class="scan-section" style="margin-bottom: 20px;">
            <h4 style="margin-bottom: 15px;">
                <i class="fa fa-building"></i> Select Warehouse <span style="color: red;">*</span>
            </h4>
            <div class="row">
                <div class="col-md-6">
                    <select id="warehouse_select" class="form-control select2" style="width: 100%;" required>
                        <option value="">-- Select Warehouse --</option>
                        <?php if (isset($warehouses) && !empty($warehouses)): ?>
                            <?php foreach($warehouses as $warehouse): ?>
                                <option value="<?= $warehouse->id ?>" 
                                    <?= (isset($selected_warehouse) && $selected_warehouse == $warehouse->id) ? 'selected' : '' ?>>
                                    <?= $warehouse->name ?> (<?= $warehouse->code ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small class="text-muted">
                        <i class="fa fa-info-circle"></i> Product recommendations will be filtered by selected warehouse
                    </small>
                </div>
                <div class="col-md-6">
                    <div id="warehouse_info" style="padding: 10px; background: #e7f3ff; border-radius: 6px; display: none;">
                        <i class="fa fa-check-circle text-success"></i> 
                        <strong>Warehouse Selected:</strong> <span id="warehouse_name_display"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scan Product Section -->
        <div class="scan-section">
            <h3 class="text-center" style="margin-bottom: 20px;">
                <i class="fa fa-barcode"></i> Scan Product Barcode
            </h3>
            
            <div class="scan-input-group">
                <div class="input-group">
                    <div class="input-group-addon" style="padding: 10px 15px;">
                        <i class="fa fa-2x fa-barcode text-primary"></i>
                    </div>
                    <?php echo form_input('lookup_code', '', 'class="form-control input-lg" id="lookup_code" placeholder="Scan or enter product code..." autofocus'); ?>
                    <div class="input-group-addon" style="padding: 10px 15px;">
                        <button type="button" class="btn btn-primary btn-sm" id="searchBtn" style="border: none; background: transparent;">
                            <i class="fa fa-search fa-2x text-primary"></i>
                        </button>
                    </div>
                    <div class="input-group-addon" style="padding: 10px 15px;">
                        <button type="button" class="btn btn-default btn-sm" id="clearBtn" style="border: none; background: transparent;">
                            <i class="fa fa-times fa-2x text-danger"></i>
                        </button>
                    </div>
                </div>
                <small class="text-muted" style="display: block; text-align: center; margin-top: 10px;">
                    <i class="fa fa-info-circle"></i> Press Enter after scanning or click search button
                </small>
            </div>
        </div>

        <div id="result"></div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    var lookupInProgress = false;

    // Initialize warehouse selector
    $('#warehouse_select').select2({
        placeholder: "-- Select Warehouse --",
        allowClear: true
    });

    // Handle warehouse change
    $('#warehouse_select').on('change', function() {
        var warehouseId = $(this).val();
        var warehouseName = $('#warehouse_select option:selected').text();
        
        if (warehouseId) {
            $('#warehouse_info').show();
            $('#warehouse_name_display').text(warehouseName);
            $('#lookup_code').prop('disabled', false).focus();
            
            // Store warehouse_id in a data attribute for later use
            $('#lookup_code').data('warehouse-id', warehouseId);
        } else {
            $('#warehouse_info').hide();
            $('#lookup_code').prop('disabled', true);
            $('#lookup_code').data('warehouse-id', '');
            clearResults();
            showAlert('Please select a warehouse first', 'danger');
        }
    });

    // Disable lookup input if no warehouse selected on load
    if (!$('#warehouse_select').val()) {
        $('#lookup_code').prop('disabled', true);
    }

    // Focus on input field if warehouse is already selected
    if ($('#warehouse_select').val()) {
        var warehouseName = $('#warehouse_select option:selected').text();
        $('#warehouse_info').show();
        $('#warehouse_name_display').text(warehouseName);
        $('#lookup_code').data('warehouse-id', $('#warehouse_select').val());
        $("#lookup_code").focus();
    }

    // Handle Enter key
    $("#lookup_code").on("keypress", function(e) {
        if (e.which === 13 || e.keyCode === 13) {
            e.preventDefault();
            performLookup();
        }
    });

    // Handle search button click
    $("#searchBtn").on("click", function() {
        performLookup();
    });

    // Handle clear button
    $("#clearBtn").on("click", function() {
        clearResults();
    });

    function performLookup() {
        var code = $("#lookup_code").val().trim();
        var warehouseId = $('#lookup_code').data('warehouse-id') || $('#warehouse_select').val();
        
        if (!warehouseId) {
            showAlert('Please select a warehouse first', 'danger');
            $('#warehouse_select').focus();
            return;
        }
        
        if (!code) {
            showAlert('Please enter or scan a product code', 'danger');
            return;
        }

        if (lookupInProgress) {
            return; // Prevent multiple simultaneous requests
        }

        lookupProduct(code, warehouseId);
    }

    function clearResults() {
        $("#lookup_code").val('');
        $("#result").html('');
        $("#lookup_code").focus();
    }

    function showAlert(message, type) {
        var alertClass = type === 'danger' ? 'alert-danger-custom' : 'alert-success-custom';
        $("#result").html(`
            <div class='alert-custom ${alertClass}'>
                <i class='fa fa-${type === 'danger' ? 'exclamation-circle' : 'check-circle'}'></i> ${message}
            </div>
        `);
        
        // Auto-hide success messages after 5 seconds
        if (type === 'success') {
            setTimeout(function() {
                $("#result").fadeOut(500, function() {
                    $(this).html('').show();
                });
            }, 5000);
        }
    }

    function showLoading() {
        $("#result").html(`
            <div class='loading'>
                <div class='spinner'></div>
                <p style='margin-top: 15px; color: #6c757d;'>Searching for product...</p>
            </div>
        `);
    }

    function lookupProduct(code, warehouseId) {
        lookupInProgress = true;
        showLoading();

        $.ajax({
            url: "<?= admin_url('storage/lookup_ajax'); ?>",
            type: "POST",
            dataType: "json",
            data: {
                avz_code: code,
                warehouse_id: warehouseId,
                <?= $this->security->get_csrf_token_name(); ?>: "<?= $this->security->get_csrf_hash(); ?>"
            },
            success: function(data) {
                lookupInProgress = false;

                if (data.status === 'error') {
                    showAlert(data.msg, 'danger');
                    $("#lookup_code").select();
                    return;
                }

                displayProductDetails(data);
            },
            error: function(xhr, status, error) {
                lookupInProgress = false;
                showAlert('Network error occurred. Please try again.', 'danger');
                console.error('AJAX Error:', error);
            }
        });
    }

    function displayProductDetails(data) {
        var product = data.product;
        var location = data.current_location;
        var recommendations = data.recommendations || [];

        var locationHTML = location 
            ? `<span class='location-badge active'>
                <i class='fa fa-map-marker'></i> ${location.location_type.toUpperCase()} - ${location.location_name}
               </span>`
            : `<span class='location-badge inactive'>
                <i class='fa fa-exclamation-triangle'></i> Not Shelved Yet
               </span>`;

        var productImage = product.image 
            ? `<img src="<?= base_url(); ?>assets/uploads/${product.image}" alt="${product.name}" style="max-width: 150px; border-radius: 8px; border: 2px solid #ddd;">`
            : `<div style="width: 150px; height: 150px; background: #f0f0f0; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <i class="fa fa-image fa-3x text-muted"></i>
               </div>`;

        // Generate recommendation cards
        var recommendationsHTML = '';
        if (recommendations && recommendations.length > 0) {
            recommendationsHTML = recommendations.map(function(rec, index) {
                var priorityClass = rec.priority == 1 ? 'priority-best' : (rec.priority == 2 ? 'priority-good' : 'priority-available');
                var priorityLabel = rec.priority == 1 ? 'Best Match' : (rec.priority == 2 ? 'Category Match' : 'Available');
                var priorityIcon = rec.priority == 1 ? 'star' : (rec.priority == 2 ? 'tags' : 'inbox');
                
                var capacityPercent = rec.capacity_total > 0 ? Math.round((rec.capacity_used / rec.capacity_total) * 100) : 0;
                var capacityClass = capacityPercent < 50 ? 'capacity-low' : (capacityPercent < 80 ? 'capacity-medium' : 'capacity-high');
                
                var usageInfo = '';
                if (rec.times_used && rec.times_used > 0) {
                    usageInfo = `<div class='usage-info'>
                        <i class='fa fa-history'></i> Used ${rec.times_used} time${rec.times_used > 1 ? 's' : ''}
                        ${rec.last_used ? ' • Last: ' + new Date(rec.last_used).toLocaleDateString() : ''}
                    </div>`;
                }

                return `
                    <div class='recommendation-card ${priorityClass} ${index === 0 ? 'recommended-best' : ''}' 
                         data-location-id='${rec.id}' 
                         onclick='selectRecommendation(${rec.id}, this)'>
                        
                        <div class='recommendation-header'>
                            <span class='priority-badge priority-${rec.priority}'>
                                <i class='fa fa-${priorityIcon}'></i> ${priorityLabel}
                            </span>
                            ${rec.is_full ? '<span class="capacity-warning"><i class="fa fa-exclamation-triangle"></i> Full</span>' : ''}
                        </div>

                        <div class='location-path'>
                            <i class='fa fa-map-marker'></i> ${rec.full_path || (rec.location_type.toUpperCase() + ' - ' + rec.location_name)}
                        </div>

                        <div class='recommendation-reason'>
                            <i class='fa fa-info-circle'></i> ${rec.reason || 'Available location'}
                        </div>

                        ${usageInfo}

                        <div class='capacity-section'>
                            <div class='capacity-label'>
                                <span>Capacity: ${rec.capacity_used || 0} / ${rec.capacity_total || 100}</span>
                                <span>${capacityPercent}% Used</span>
                            </div>
                            <div class='capacity-bar-container'>
                                <div class='capacity-bar ${capacityClass}' style='width: ${capacityPercent}%'></div>
                            </div>
                            <div class='capacity-available'>
                                <i class='fa fa-check-circle'></i> ${rec.capacity_available || 100} units available
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            recommendationsHTML = `
                <div class='no-recommendations'>
                    <i class='fa fa-exclamation-circle fa-3x'></i>
                    <p>No storage locations available in this warehouse. Please create new storage locations first.</p>
                </div>
            `;
        }

        var resultHTML = `
            <div class='product-card'>
                <div class='product-header'>
                    <h3 style='margin: 0; color: #007bff;'>
                        <i class='fa fa-cube'></i> Product Details
                    </h3>
                </div>

                <div style='display: flex; gap: 20px; margin-bottom: 25px;'>
                    <div>
                        ${productImage}
                    </div>
                    <div style='flex: 1;'>
                        <div class='product-info'>
                            <div class='info-item'>
                                <div class='info-label'>Product Name</div>
                                <div class='info-value'>${product.name || 'N/A'}</div>
                            </div>
                            <div class='info-item'>
                                <div class='info-label'>Product Code</div>
                                <div class='info-value'>${product.code || 'N/A'}</div>
                            </div>
                            <div class='info-item'>
                                <div class='info-label'>Category</div>
                                <div class='info-value'>${product.category || 'N/A'}</div>
                            </div>
                            <div class='info-item'>
                                <div class='info-label'>Current Location</div>
                                <div class='info-value'>${locationHTML}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class='assign-section'>
                    <h4 style='margin-bottom: 20px;'>
                        <i class='fa fa-lightbulb-o'></i> Recommended Storage Locations
                    </h4>
                    
                    <div class='recommendations-container'>
                        ${recommendationsHTML}
                    </div>

                    <div style='margin-top: 20px;'>
                        <input type='hidden' id='assign_location' value='${recommendations.length > 0 ? recommendations[0].id : ''}'>
                        
                        <div class='row'>
                            <div class='col-md-12'>
                                <label style='font-weight: 600; margin-bottom: 8px;'>
                                    <i class='fa fa-hashtag'></i> Quantity to Assign
                                </label>
                                <input type="number" id="assign_quantity" class="quantity-input form-control" value="1" min="1" style="width: 150px;">
                            </div>
                        </div>

                        <div style='margin-top: 20px; text-align: center;'>
                            <button class='btn-assign success' onclick="assignLocation(${product.id})" ${recommendations.length === 0 ? 'disabled' : ''}>
                                <i class='fa fa-check-circle'></i> Assign to Selected Location
                            </button>
                            <button class='btn-assign btn-clear' onclick="clearResults()">
                                <i class='fa fa-times'></i> Clear
                            </button>
                        </div>
                    </div>

                    ${location ? `
                        <div style='margin-top: 15px; padding: 12px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;'>
                            <i class='fa fa-info-circle text-warning'></i> 
                            <strong>Note:</strong> This product is currently in <strong>${location.location_name}</strong>. 
                            Assigning to a new location will update its storage position.
                        </div>
                    ` : ''}
                </div>
            </div>
        `;

        $("#result").html(resultHTML);

        // Auto-select first recommendation
        if (recommendations.length > 0) {
            $('.recommendation-card').first().addClass('selected');
        }
    }

    // Handle recommendation card selection
    window.selectRecommendation = function(locationId, element) {
        // Remove selected class from all cards
        $('.recommendation-card').removeClass('selected');
        
        // Add selected class to clicked card
        $(element).addClass('selected');
        
        // Update hidden input
        $('#assign_location').val(locationId);
    };

    // Make assignLocation available globally
    window.assignLocation = function(product_id) {
        var location_id = $("#assign_location").val();
        var quantity = $("#assign_quantity").val() || 1;

        if (!location_id) {
            showAlert('Please select a storage location', 'danger');
            return;
        }

        // Disable button to prevent double-click
        $(".btn-assign.success").prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Assigning...');

        $.ajax({
            url: "<?= admin_url('storage/assign_product'); ?>",
            type: "POST",
            dataType: "json",
            data: {
                product_id: product_id,
                storage_location_id: location_id,
                quantity: quantity,
                <?= $this->security->get_csrf_token_name(); ?>: "<?= $this->security->get_csrf_hash(); ?>"
            },
            success: function(data) {
                $(".btn-assign.success").prop('disabled', false).html('<i class="fa fa-check-circle"></i> Assign to Location');
                
                if (data.status === 'success') {
                    showAlert(data.msg || 'Product assigned successfully!', 'success');
                    $("#lookup_code").val('').focus();
                    
                    // Clear result after 2 seconds
                    setTimeout(function() {
                        $("#result").html('');
                    }, 2000);
                } else {
                    showAlert(data.msg || 'Failed to assign product', 'danger');
                }
            },
            error: function(xhr, status, error) {
                $(".btn-assign.success").prop('disabled', false).html('<i class="fa fa-check-circle"></i> Assign to Location');
                showAlert('Network error occurred. Please try again.', 'danger');
                console.error('AJAX Error:', error);
            }
        });
    };

    window.clearResults = clearResults;
});
</script>
