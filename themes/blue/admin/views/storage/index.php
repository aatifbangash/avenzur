<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" />
<style>
/* Increase vertical spacing */
#storageTree .jstree-node {
    padding: 5px 0;   /* top & bottom padding */
    line-height: 1.8; /* increase line height for vertical spacing */
}

/* Increase horizontal spacing */
#storageTree .jstree-icon {
    margin-right: 8px; /* more space between arrow/icon and text */
}

/* Optional: increase left padding for child nodes */
#storageTree .jstree-children {
    margin-left: 20px; /* increase horizontal indentation */
}

/* Optional: bigger font for readability */
#storageTree .jstree-node {
    font-size: 15px;
}

/* Optional: change arrow size */
#storageTree .jstree-icon {
    width: 18px;
    height: 18px;
}

/* Capacity badge styles */
.capacity-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    margin-left: 8px;
}

.capacity-low {
    background: #d4edda;
    color: #155724;
}

.capacity-medium {
    background: #fff3cd;
    color: #856404;
}

.capacity-high {
    background: #f8d7da;
    color: #721c24;
}

.capacity-full {
    background: #dc3545;
    color: white;
}

.item-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    background: #007bff;
    color: white;
    margin-left: 5px;
}

/* Clickable node cursor */
#storageTree .jstree-anchor {
    cursor: pointer;
}

/* Modal styles */
.modal-content {
    border-radius: 8px;
}

.location-detail-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 8px 8px 0 0;
}

.capacity-progress {
    height: 20px;
    border-radius: 10px;
    background: #e9ecef;
    overflow: hidden;
    margin: 10px 0;
}

.capacity-progress-bar {
    height: 100%;
    transition: width 0.4s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
    font-weight: 600;
}

.product-item {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    transition: all 0.3s;
}

.product-item:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.product-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 6px;
    border: 2px solid #ddd;
}

.product-placeholder {
    width: 60px;
    height: 60px;
    background: #f0f0f0;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #999;
}

.stat-box {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    margin-bottom: 15px;
}

.stat-value {
    font-size: 28px;
    font-weight: 700;
    color: #007bff;
}

.stat-label {
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
    font-weight: 600;
}
</style>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-star"></i><?= lang('Storage Locations'); ?>
        </h2>
    </div>
        <div class="box-content">
            <?php if($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <!-- Warehouse Selector -->
            <div class="row" style="margin-bottom: 20px;">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="warehouse_select">
                            <i class="fa fa-warehouse"></i> Select Warehouse:
                        </label>
                        <select id="warehouse_select" class="form-control select2" style="width: 100%;">
                            <option value="">-- Select Warehouse --</option>
                            <?php if(isset($warehouses) && !empty($warehouses)): ?>
                                <?php foreach($warehouses as $warehouse): ?>
                                    <option value="<?= $warehouse->id ?>" <?= (isset($selected_warehouse) && $selected_warehouse == $warehouse->id) ? 'selected' : '' ?>>
                                        <?= $warehouse->name ?> <?= isset($warehouse->code) ? '(' . $warehouse->code . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-8" style="padding-top: 25px;">
                    <a href="#" id="addStorageBtn" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Add Storage Location
                    </a>
                    <button type="button" class="btn btn-success" id="refreshTreeBtn">
                        <i class="fa fa-refresh"></i> Refresh Tree
                    </button>
                </div>
            </div>
            
            <hr />
            
            <!-- Loading indicator -->
            <div id="treeLoading" style="text-align: center; padding: 40px; display: none;">
                <i class="fa fa-spinner fa-spin fa-3x text-primary"></i>
                <p style="margin-top: 15px; color: #6c757d;">Loading storage locations...</p>
            </div>
            
            <!-- Empty state -->
            <div id="emptyState" style="text-align: center; padding: 40px; color: #6c757d; display: none;">
                <i class="fa fa-inbox fa-3x"></i>
                <p style="margin-top: 15px;">Please select a warehouse to view storage locations</p>
            </div>
            
            <!-- Tree container -->
            <div id="storageTree"></div>

        </div>
    
</div>

<!-- Location Details Modal -->
<div class="modal fade" id="locationDetailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="location-detail-header">
                <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 1;">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title" id="modalLocationName">
                    <i class="fa fa-map-marker"></i> <span id="locationNameText"></span>
                </h4>
                <p id="locationPath" style="margin: 5px 0 0 0; opacity: 0.9; font-size: 14px;"></p>
            </div>
            <div class="modal-body">
                <!-- Loading State -->
                <div id="modalLoading" style="text-align: center; padding: 40px;">
                    <i class="fa fa-spinner fa-spin fa-3x text-primary"></i>
                    <p style="margin-top: 15px; color: #6c757d;">Loading details...</p>
                </div>

                <!-- Content -->
                <div id="modalContent" style="display: none;">
                    <!-- Stats Row -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stat-box">
                                <div class="stat-value" id="statTotalProducts">0</div>
                                <div class="stat-label">Products</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <div class="stat-value" id="statTotalQuantity">0</div>
                                <div class="stat-label">Total Units</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <div class="stat-value" id="statCapacityUsed">0</div>
                                <div class="stat-label">Capacity Used</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <div class="stat-value" id="statCapacityAvailable">0</div>
                                <div class="stat-label">Available</div>
                            </div>
                        </div>
                    </div>

                    <!-- Capacity Progress Bar -->
                    <div style="margin: 20px 0;">
                        <strong>Capacity Usage:</strong>
                        <div class="capacity-progress">
                            <div class="capacity-progress-bar" id="capacityBar" style="width: 0%;">
                                0%
                            </div>
                        </div>
                    </div>

                    <!-- Products List -->
                    <div style="margin-top: 25px;">
                        <h5><i class="fa fa-cube"></i> Stored Products</h5>
                        <hr>
                        <div id="productsList">
                            <!-- Products will be loaded here -->
                        </div>
                        <div id="noProducts" style="display: none; text-align: center; padding: 40px; color: #6c757d;">
                            <i class="fa fa-inbox fa-3x"></i>
                            <p style="margin-top: 15px;">No products stored in this location</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/jstree.min.js"></script>
<script>
    let treeData = <?= json_encode($hierarchy) ?>;
    let currentWarehouseId = <?= isset($selected_warehouse) && $selected_warehouse ? $selected_warehouse : 'null' ?>;

    // Initialize tree on page load
    $(document).ready(function() {
        // If no warehouse selected, show empty state
        if (!currentWarehouseId) {
            $('#emptyState').show();
            $('#storageTree').hide();
        } else {
            initializeTree(treeData);
        }

        // Warehouse change handler
        $('#warehouse_select').on('change', function() {
            let warehouseId = $(this).val();
            if (warehouseId) {
                // Reload page with selected warehouse
                window.location.href = "<?= admin_url('storage') ?>?warehouse_id=" + warehouseId;
            } else {
                $('#storageTree').hide();
                $('#emptyState').show();
            }
        });

        // Add storage button click
        $('#addStorageBtn').on('click', function(e) {
            e.preventDefault();
            let warehouseId = $('#warehouse_select').val();
            if (warehouseId) {
                window.location.href = "<?= admin_url('storage/add') ?>?warehouse_id=" + warehouseId;
            } else {
                alert('Please select a warehouse first');
            }
        });

        // Refresh tree button
        $('#refreshTreeBtn').on('click', function() {
            let warehouseId = $('#warehouse_select').val();
            if (warehouseId) {
                window.location.reload();
            } else {
                alert('Please select a warehouse first');
            }
        });
    });

    // Initialize jsTree
    function initializeTree(data) {
        $('#emptyState').hide();
        $('#storageTree').show();

        // Destroy existing tree if any
        if ($.jstree.reference('#storageTree')) {
            $('#storageTree').jstree('destroy');
        }

        $('#storageTree').jstree({
            'core' : {
                'data' : convertToJsTree(data)
            }
        });

        // Expand ALL nodes after tree loads
        $('#storageTree').on('ready.jstree', function () {
            $('#storageTree').jstree('open_all');
        });

        // Handle node click to show details
        $('#storageTree').on('select_node.jstree', function (e, data) {
            let locationId = data.node.id;
            showLocationDetails(locationId);
        });
    }

    // Convert PHP hierarchy to jsTree format
    function convertToJsTree(data){
        let result = [];
        data.forEach(item => {

            let label = item.type.toUpperCase() + ": " + item.name;

            // Add item count badge if there are items
            if (item.item_count > 0) {
                label += ` <span class="item-badge">${item.item_count} item${item.item_count > 1 ? 's' : ''}</span>`;
            }

            // Add capacity badge for leaf nodes or nodes with items
            if (item.item_count > 0 || item.children.length === 0) {
                let capacityClass = 'capacity-low';
                if (item.is_full) {
                    capacityClass = 'capacity-full';
                } else if (item.capacity_percent >= 80) {
                    capacityClass = 'capacity-high';
                } else if (item.capacity_percent >= 50) {
                    capacityClass = 'capacity-medium';
                }
                
                label += ` <span class="capacity-badge ${capacityClass}">${item.capacity_used}/${item.capacity} (${item.capacity_percent}%)</span>`;
            }

            result.push({
                id: item.id,
                text: label,
                data: {
                    item_count: item.item_count,
                    capacity: item.capacity,
                    capacity_used: item.capacity_used,
                    capacity_percent: item.capacity_percent,
                    type: item.type,
                    name: item.name
                },
                children: convertToJsTree(item.children)
            });
        });
        return result;
    }

    // Handle node click to show details
    $('#storageTree').on('select_node.jstree', function (e, data) {
        let locationId = data.node.id;
        showLocationDetails(locationId);
    });

    // Function to show location details in modal
    function showLocationDetails(locationId) {
        $('#locationDetailModal').modal('show');
        $('#modalLoading').show();
        $('#modalContent').hide();

        $.ajax({
            url: "<?= admin_url('storage/get_location_details'); ?>",
            type: "POST",
            dataType: "json",
            data: {
                location_id: locationId,
                <?= $this->security->get_csrf_token_name(); ?>: "<?= $this->security->get_csrf_hash(); ?>"
            },
            success: function(response) {
                $('#modalLoading').hide();
                
                if (response.status === 'success') {
                    displayLocationDetails(response);
                    $('#modalContent').show();
                } else {
                    alert(response.msg || 'Failed to load location details');
                    $('#locationDetailModal').modal('hide');
                }
            },
            error: function(xhr, status, error) {
                $('#modalLoading').hide();
                alert('Network error occurred. Please try again.');
                console.error('AJAX Error:', error);
                $('#locationDetailModal').modal('hide');
            }
        });
    }

    // Function to display location details in modal
    function displayLocationDetails(data) {
        let location = data.location;
        let products = data.products;

        // Set header info
        $('#locationNameText').text(location.type.toUpperCase() + ' - ' + location.name);
        $('#locationPath').html('<i class="fa fa-sitemap"></i> ' + location.full_path);

        // Set stats
        $('#statTotalProducts').text(data.total_products);
        $('#statTotalQuantity').text(location.capacity_used);
        $('#statCapacityUsed').text(location.capacity_used);
        $('#statCapacityAvailable').text(location.capacity_available);

        // Set capacity bar
        let capacityPercent = location.capacity_percent;
        let barColor = '#28a745'; // Green
        if (location.is_full) {
            barColor = '#dc3545'; // Red
        } else if (capacityPercent >= 80) {
            barColor = '#dc3545'; // Red
        } else if (capacityPercent >= 50) {
            barColor = '#ffc107'; // Yellow
        }

        $('#capacityBar').css({
            'width': capacityPercent + '%',
            'background': barColor
        }).text(capacityPercent + '%');

        // Display products
        if (products.length > 0) {
            let productsHtml = '';
            products.forEach(function(product) {
                let imageHtml = '';
                if (product.image) {
                    imageHtml = `<img src="<?= base_url(); ?>assets/uploads/${product.image}" class="product-image" alt="${product.name}">`;
                } else {
                    imageHtml = `<div class="product-placeholder"><i class="fa fa-image fa-2x"></i></div>`;
                }

                productsHtml += `
                    <div class="product-item">
                        <div class="row">
                            <div class="col-md-2">
                                ${imageHtml}
                            </div>
                            <div class="col-md-7">
                                <strong style="font-size: 16px; color: #007bff;">${product.name}</strong><br>
                                <span style="color: #6c757d; font-size: 13px;">
                                    <i class="fa fa-barcode"></i> ${product.code}
                                    &nbsp;•&nbsp;
                                    <i class="fa fa-folder"></i> ${product.category}
                                </span><br>
                                <span style="color: #6c757d; font-size: 12px;">
                                    <i class="fa fa-clock-o"></i> Stored: ${product.stored_at}
                                </span>
                            </div>
                            <div class="col-md-3" style="text-align: right;">
                                <div style="background: #007bff; color: white; padding: 10px 15px; border-radius: 8px; display: inline-block;">
                                    <div style="font-size: 24px; font-weight: 700;">${product.quantity}</div>
                                    <div style="font-size: 11px; opacity: 0.9;">UNITS</div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            $('#productsList').html(productsHtml);
            $('#productsList').show();
            $('#noProducts').hide();
        } else {
            $('#productsList').hide();
            $('#noProducts').show();
        }
    }
</script>