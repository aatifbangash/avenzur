<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-hospital-o"></i> <?php echo lang('pharmacy_hierarchy_setup'); ?>
                </h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            
            <div class="box-body">
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs" role="tablist" style="margin-bottom: 20px; border-bottom: 2px solid #f4f4f4;">
                    <li role="presentation" class="active">
                        <a href="#pharmacy-section" aria-controls="pharmacy-section" role="tab" data-toggle="tab" class="tab-link">
                            <i class="fa fa-hospital-o"></i> <?php echo lang('pharmacies'); ?>
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#branch-section" aria-controls="branch-section" role="tab" data-toggle="tab" class="tab-link">
                            <i class="fa fa-map-marker"></i> <?php echo lang('branches'); ?>
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#hierarchy-section" aria-controls="hierarchy-section" role="tab" data-toggle="tab" class="tab-link">
                            <i class="fa fa-sitemap"></i> <?php echo lang('hierarchy_view'); ?>
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- PHARMACIES TAB -->
                    <div role="tabpanel" class="tab-pane fade in active" id="pharmacy-section">
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="section-title">
                                    <i class="fa fa-hospital-o"></i> <?php echo lang('manage_pharmacies'); ?>
                                </h4>
                                <p class="text-muted"><?php echo lang('pharmacy_description'); ?></p>

                                <!-- Pharmacy Group Selector -->
                                <div class="row" style="margin-bottom: 20px;">
                                    <div class="col-md-6">
                                        <label for="pharmacy_group_id"><?php echo lang('pharmacy_group'); ?> *</label>
                                        <select id="pharmacy_group_id" class="form-control select2" style="width: 100%;">
                                            <option value=""><?php echo lang('select_pharmacy_group'); ?></option>
                                            <!-- Options will be populated by JS -->
                                        </select>
                                    </div>
                                    <div class="col-md-6" style="padding-top: 24px;">
                                        <button type="button" class="btn btn-primary" id="btn_add_pharmacy" data-toggle="modal" data-target="#modal_add_pharmacy">
                                            <i class="fa fa-plus"></i> <?php echo lang('add_pharmacy'); ?>
                                        </button>
                                    </div>
                                </div>

                                <!-- Pharmacies Table -->
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover" id="pharmacies_table">
                                        <thead>
                                            <tr>
                                                <th width="5%"><?php echo lang('id'); ?></th>
                                                <th><?php echo lang('code'); ?></th>
                                                <th><?php echo lang('name'); ?></th>
                                                <th><?php echo lang('address'); ?></th>
                                                <th><?php echo lang('phone'); ?></th>
                                                <th><?php echo lang('warehouse_type'); ?></th>
                                                <th width="15%"><?php echo lang('actions'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Populated by AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BRANCHES TAB -->
                    <div role="tabpanel" class="tab-pane fade" id="branch-section">
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="section-title">
                                    <i class="fa fa-map-marker"></i> <?php echo lang('manage_branches'); ?>
                                </h4>
                                <p class="text-muted"><?php echo lang('branch_description'); ?></p>

                                <!-- Pharmacy Selector for Branches -->
                                <div class="row" style="margin-bottom: 20px;">
                                    <div class="col-md-6">
                                        <label for="pharmacy_id_for_branches"><?php echo lang('pharmacy'); ?> *</label>
                                        <select id="pharmacy_id_for_branches" class="form-control select2" style="width: 100%;">
                                            <option value=""><?php echo lang('select_pharmacy'); ?></option>
                                            <!-- Options will be populated by JS -->
                                        </select>
                                    </div>
                                    <div class="col-md-6" style="padding-top: 24px;">
                                        <button type="button" class="btn btn-primary" id="btn_add_branch" data-toggle="modal" data-target="#modal_add_branch">
                                            <i class="fa fa-plus"></i> <?php echo lang('add_branch'); ?>
                                        </button>
                                    </div>
                                </div>

                                <!-- Branches Table -->
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover" id="branches_table">
                                        <thead>
                                            <tr>
                                                <th width="5%"><?php echo lang('id'); ?></th>
                                                <th><?php echo lang('code'); ?></th>
                                                <th><?php echo lang('name'); ?></th>
                                                <th><?php echo lang('pharmacy'); ?></th>
                                                <th><?php echo lang('address'); ?></th>
                                                <th><?php echo lang('phone'); ?></th>
                                                <th width="15%"><?php echo lang('actions'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Populated by AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- HIERARCHY VIEW TAB -->
                    <div role="tabpanel" class="tab-pane fade" id="hierarchy-section">
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="section-title">
                                    <i class="fa fa-sitemap"></i> <?php echo lang('organization_hierarchy'); ?>
                                </h4>
                                <p class="text-muted"><?php echo lang('hierarchy_view_description'); ?></p>

                                <!-- Hierarchy Container -->
                                <div id="hierarchy-container" style="background: #f5f5f5; padding: 20px; border-radius: 4px; min-height: 400px;">
                                    <div id="hierarchy-tree" class="hierarchy-tree">
                                        <!-- ECharts will render here -->
                                    </div>
                                </div>

                                <div style="margin-top: 20px;">
                                    <p class="text-info" style="font-size: 12px;">
                                        <i class="fa fa-info-circle"></i> 
                                        <?php echo lang('click_node_to_view_details'); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODALS ===== -->

<!-- Add Pharmacy Modal -->
<div class="modal fade" id="modal_add_pharmacy" tabindex="-1" role="dialog" aria-labelledby="modal_add_pharmacy_label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal_add_pharmacy_label">
                    <i class="fa fa-hospital-o"></i> <?php echo lang('add_pharmacy'); ?>
                </h4>
            </div>
            <form id="form_add_pharmacy" method="post" data-toggle="validator" role="form">
                <div class="modal-body">
                    <p class="text-muted"><?php echo lang('enter_pharmacy_info'); ?></p>

                    <div class="form-group">
                        <label for="pharmacy_group_id_add"><?php echo lang('pharmacy_group'); ?> *</label>
                        <select id="pharmacy_group_id_add" name="pharmacy_group_id" class="form-control" required>
                            <option value=""><?php echo lang('select_pharmacy_group'); ?></option>
                            <!-- Options will be populated by JS -->
                        </select>
                        <small class="form-text text-muted"><?php echo lang('select_parent_company'); ?></small>
                    </div>

                    <div class="form-group">
                        <label for="pharmacy_code"><?php echo lang('pharmacy_code'); ?> *</label>
                        <input type="text" class="form-control" id="pharmacy_code" name="code" placeholder="<?php echo lang('e_g'); ?> PHARM001" required>
                        <small class="form-text text-muted"><?php echo lang('unique_code'); ?></small>
                    </div>

                    <div class="form-group">
                        <label for="pharmacy_name"><?php echo lang('pharmacy_name'); ?> *</label>
                        <input type="text" class="form-control" id="pharmacy_name" name="name" placeholder="<?php echo lang('enter_pharmacy_name'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="pharmacy_address"><?php echo lang('address'); ?> *</label>
                        <textarea class="form-control" id="pharmacy_address" name="address" rows="3" placeholder="<?php echo lang('enter_complete_address'); ?>" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="pharmacy_phone"><?php echo lang('phone'); ?> *</label>
                        <input type="tel" class="form-control" id="pharmacy_phone" name="phone" placeholder="<?php echo lang('enter_phone_number'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="pharmacy_email"><?php echo lang('email'); ?></label>
                        <input type="email" class="form-control" id="pharmacy_email" name="email" placeholder="<?php echo lang('enter_email'); ?>">
                    </div>

                    <!-- Main Warehouse Info -->
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <i class="fa fa-warehouse"></i> <?php echo lang('main_warehouse'); ?>
                            </h4>
                        </div>
                        <div class="panel-body">
                            <p class="text-muted"><?php echo lang('main_warehouse_description'); ?></p>

                            <div class="form-group">
                                <label for="warehouse_code"><?php echo lang('warehouse_code'); ?> *</label>
                                <input type="text" class="form-control" id="warehouse_code" name="warehouse_code" placeholder="<?php echo lang('e_g'); ?> WH001" required>
                                <small class="form-text text-muted"><?php echo lang('unique_warehouse_code'); ?></small>
                            </div>

                            <div class="form-group">
                                <label for="warehouse_name"><?php echo lang('warehouse_name'); ?> *</label>
                                <input type="text" class="form-control" id="warehouse_name" name="warehouse_name" placeholder="<?php echo lang('enter_warehouse_name'); ?>" required>
                            </div>

                            <input type="hidden" name="warehouse_type" value="mainwarehouse">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo lang('cancel'); ?></button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> <?php echo lang('add_pharmacy'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Branch Modal -->
<div class="modal fade" id="modal_add_branch" tabindex="-1" role="dialog" aria-labelledby="modal_add_branch_label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal_add_branch_label">
                    <i class="fa fa-map-marker"></i> <?php echo lang('add_branch'); ?>
                </h4>
            </div>
            <form id="form_add_branch" method="post" data-toggle="validator" role="form">
                <div class="modal-body">
                    <p class="text-muted"><?php echo lang('enter_branch_info'); ?></p>

                    <div class="form-group">
                        <label for="branch_pharmacy_id"><?php echo lang('pharmacy'); ?> *</label>
                        <select id="branch_pharmacy_id" name="pharmacy_id" class="form-control" required>
                            <option value=""><?php echo lang('select_pharmacy'); ?></option>
                            <!-- Options will be populated by JS -->
                        </select>
                        <small class="form-text text-muted"><?php echo lang('select_parent_pharmacy'); ?></small>
                    </div>

                    <div class="form-group">
                        <label for="branch_code"><?php echo lang('branch_code'); ?> *</label>
                        <input type="text" class="form-control" id="branch_code" name="code" placeholder="<?php echo lang('e_g'); ?> BR001" required>
                        <small class="form-text text-muted"><?php echo lang('unique_code'); ?></small>
                    </div>

                    <div class="form-group">
                        <label for="branch_name"><?php echo lang('branch_name'); ?> *</label>
                        <input type="text" class="form-control" id="branch_name" name="name" placeholder="<?php echo lang('enter_branch_name'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="branch_address"><?php echo lang('address'); ?> *</label>
                        <textarea class="form-control" id="branch_address" name="address" rows="3" placeholder="<?php echo lang('enter_complete_address'); ?>" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="branch_phone"><?php echo lang('phone'); ?> *</label>
                        <input type="tel" class="form-control" id="branch_phone" name="phone" placeholder="<?php echo lang('enter_phone_number'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="branch_email"><?php echo lang('email'); ?></label>
                        <input type="email" class="form-control" id="branch_email" name="email" placeholder="<?php echo lang('enter_email'); ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo lang('cancel'); ?></button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> <?php echo lang('add_branch'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== CSS ===== -->
<style>
    .section-title {
        margin-top: 0;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #3c8dbc;
        color: #333;
        font-weight: 600;
    }

    .tab-link {
        padding: 10px 20px !important;
        border-radius: 4px 4px 0 0 !important;
        transition: all 0.3s ease;
    }

    .tab-link:hover {
        background-color: #f0f0f0;
    }

    .hierarchy-tree {
        width: 100%;
        height: 400px;
    }

    .modal-header.bg-primary {
        background-color: #3c8dbc;
        color: white;
    }

    .modal-header.bg-info {
        background-color: #0097bc;
        color: white;
    }

    .panel-heading {
        background-color: #f5f5f5;
        border-bottom: 1px solid #ddd;
    }

    .table-hover tbody tr:hover {
        background-color: #f5f5f5;
    }

    .btn-group-xs .btn {
        padding: 3px 8px;
        font-size: 12px;
    }

    .hierarchy-node {
        padding: 10px;
        margin: 5px;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-align: center;
    }

    .hierarchy-node.pharmacy {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
    }

    .hierarchy-node.branch {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        font-weight: 500;
    }
</style>

<!-- ===== SCRIPTS ===== -->
<script>
    $(function() {
        initPharmacySetup();
    });

    function initPharmacySetup() {
        // Load pharmacy groups FIRST, then initialize Select2
        loadPharmacyGroups();

        // Event handlers
        $('#pharmacy_group_id').on('change', function() {
            loadPharmacies();
        });

        $('#pharmacy_id_for_branches').on('change', function() {
            loadBranches();
        });

        // Form submissions
        $('#form_add_pharmacy').on('submit', function(e) {
            e.preventDefault();
            addPharmacy();
        });

        $('#form_add_branch').on('submit', function(e) {
            e.preventDefault();
            addBranch();
        });

        // Modal handlers
        $('#modal_add_pharmacy').on('show.bs.modal', function() {
            loadPharmacyGroupsForAdd();
        });

        $('#modal_add_branch').on('show.bs.modal', function() {
            loadPharmaciesForAdd();
        });
    }

    function loadPharmacyGroups() {
        $.ajax({
            url: '<?php echo admin_url('loyalty/get_pharmacy_groups'); ?>',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                let $select = $('#pharmacy_group_id');
                $select.find('option:not(:first)').remove();
                
                if (data.success && data.data.length > 0) {
                    $.each(data.data, function(i, group) {
                        $select.append('<option value="' + group.id + '">' + group.name + '</option>');
                    });
                }
                
                // Initialize Select2 after data is loaded
                if ($select.data('select2')) {
                    $select.select2('destroy');
                }
                $select.select2({
                    width: '100%',
                    allowClear: true
                });
            },
            error: function(xhr, status, error) {
                console.error('Error loading pharmacy groups:', error);
                console.log('Response:', xhr.responseText);
            }
        });
    }

    function loadPharmacyGroupsForAdd() {
        $.ajax({
            url: '<?php echo admin_url('loyalty/get_pharmacy_groups'); ?>',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                let $select = $('#pharmacy_group_id_add');
                $select.find('option:not(:first)').remove();
                
                if (data.success && data.data.length > 0) {
                    $.each(data.data, function(i, group) {
                        $select.append('<option value="' + group.id + '">' + group.name + '</option>');
                    });
                }
                
                // Initialize Select2 after data is loaded
                if ($select.data('select2')) {
                    $select.select2('destroy');
                }
                $select.select2({
                    width: '100%',
                    allowClear: true
                });
            },
            error: function(xhr, status, error) {
                console.error('Error loading pharmacy groups for add:', error);
            }
        });
    }

    function loadPharmacies() {
        let groupId = $('#pharmacy_group_id').val();
        if (!groupId) {
            $('#pharmacies_table tbody').empty();
            return;
        }

        $.ajax({
            url: '<?php echo admin_url('loyalty/get_pharmacies'); ?>',
            type: 'GET',
            data: { group_id: groupId },
            dataType: 'json',
            success: function(data) {
                let tbody = $('#pharmacies_table tbody');
                tbody.empty();

                if (data.success && data.data.length > 0) {
                    $.each(data.data, function(i, pharmacy) {
                        tbody.append(`
                            <tr>
                                <td>${pharmacy.id}</td>
                                <td>${pharmacy.code}</td>
                                <td>${pharmacy.name}</td>
                                <td>${pharmacy.address}</td>
                                <td>${pharmacy.phone}</td>
                                <td><span class="label label-primary">${pharmacy.warehouse_type}</span></td>
                                <td>
                                    <div class="btn-group btn-group-xs">
                                        <button class="btn btn-info" onclick="editPharmacy(${pharmacy.id})">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger" onclick="deletePharmacy(${pharmacy.id})">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `);
                    });
                } else {
                    tbody.append('<tr><td colspan="7" class="text-center text-muted"><?php echo lang("no_data"); ?></td></tr>');
                }
            }
        });
    }

    function loadPharmaciesForAdd() {
        $.ajax({
            url: '<?php echo admin_url('loyalty/get_all_pharmacies'); ?>',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                let $select = $('#branch_pharmacy_id');
                let $selectTab = $('#pharmacy_id_for_branches');
                
                $select.find('option:not(:first)').remove();
                $selectTab.find('option:not(:first)').remove();
                
                if (data.success && data.data.length > 0) {
                    $.each(data.data, function(i, pharmacy) {
                        $select.append('<option value="' + pharmacy.id + '">' + pharmacy.name + ' (' + pharmacy.code + ')</option>');
                        $selectTab.append('<option value="' + pharmacy.id + '">' + pharmacy.name + ' (' + pharmacy.code + ')</option>');
                    });
                }
                
                // Initialize Select2 after data is loaded
                if ($select.data('select2')) {
                    $select.select2('destroy');
                }
                if ($selectTab.data('select2')) {
                    $selectTab.select2('destroy');
                }
                $select.select2({
                    width: '100%',
                    allowClear: true
                });
                $selectTab.select2({
                    width: '100%',
                    allowClear: true
                });
            },
            error: function(xhr, status, error) {
                console.error('Error loading pharmacies for add:', error);
            }
        });
    }

    function loadBranches() {
        let pharmacyId = $('#pharmacy_id_for_branches').val();
        if (!pharmacyId) {
            $('#branches_table tbody').empty();
            return;
        }

        $.ajax({
            url: '<?php echo admin_url('loyalty/get_branches'); ?>',
            type: 'GET',
            data: { pharmacy_id: pharmacyId },
            dataType: 'json',
            success: function(data) {
                let tbody = $('#branches_table tbody');
                tbody.empty();

                if (data.success && data.data.length > 0) {
                    $.each(data.data, function(i, branch) {
                        tbody.append(`
                            <tr>
                                <td>${branch.id}</td>
                                <td>${branch.code}</td>
                                <td>${branch.name}</td>
                                <td>${branch.pharmacy_name}</td>
                                <td>${branch.address}</td>
                                <td>${branch.phone}</td>
                                <td>
                                    <div class="btn-group btn-group-xs">
                                        <button class="btn btn-info" onclick="editBranch(${branch.id})">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger" onclick="deleteBranch(${branch.id})">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `);
                    });
                } else {
                    tbody.append('<tr><td colspan="7" class="text-center text-muted"><?php echo lang("no_data"); ?></td></tr>');
                }
            }
        });
    }

    function addPharmacy() {
        let formData = $('#form_add_pharmacy').serializeArray();
        let data = {};
        $.each(formData, function() {
            data[this.name] = this.value;
        });

        $.ajax({
            url: '<?php echo admin_url('loyalty/add_pharmacy_setup'); ?>',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.message);
                    $('#modal_add_pharmacy').modal('hide');
                    $('#form_add_pharmacy')[0].reset();
                    loadPharmacies();
                    loadPharmaciesForAdd();
                    loadHierarchyTree();
                } else {
                    showNotification('error', response.message);
                }
            },
            error: function() {
                showNotification('error', 'An error occurred');
            }
        });
    }

    function addBranch() {
        let formData = $('#form_add_branch').serializeArray();
        let data = {};
        $.each(formData, function() {
            data[this.name] = this.value;
        });

        $.ajax({
            url: '<?php echo admin_url('loyalty/add_branch_setup'); ?>',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.message);
                    $('#modal_add_branch').modal('hide');
                    $('#form_add_branch')[0].reset();
                    loadBranches();
                    loadHierarchyTree();
                } else {
                    showNotification('error', response.message);
                }
            },
            error: function() {
                showNotification('error', 'An error occurred');
            }
        });
    }

    function editPharmacy(id) {
        alert('Edit functionality to be implemented');
    }

    function deletePharmacy(id) {
        if (confirm('<?php echo lang("confirm_delete"); ?>')) {
            $.ajax({
                url: '<?php echo admin_url('loyalty/delete_pharmacy'); ?>',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification('success', response.message);
                        loadPharmacies();
                        loadPharmaciesForAdd();
                        loadHierarchyTree();
                    } else {
                        showNotification('error', response.message);
                    }
                }
            });
        }
    }

    function editBranch(id) {
        alert('Edit functionality to be implemented');
    }

    function deleteBranch(id) {
        if (confirm('<?php echo lang("confirm_delete"); ?>')) {
            $.ajax({
                url: '<?php echo admin_url('loyalty/delete_branch'); ?>',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification('success', response.message);
                        loadBranches();
                        loadHierarchyTree();
                    } else {
                        showNotification('error', response.message);
                    }
                }
            });
        }
    }

    function loadHierarchyTree() {
        $.ajax({
            url: '<?php echo admin_url('loyalty/get_hierarchy_tree'); ?>',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    renderHierarchyTree(data.data);
                }
            }
        });
    }

    function renderHierarchyTree(data) {
        // Simple tree rendering (can be enhanced with ECharts later)
        let html = '<div class="hierarchy-container">';
        
        if (data && data.length > 0) {
            data.forEach(group => {
                html += `<div class="hierarchy-node pharmacy">
                    <strong>${group.name}</strong>
                    <small style="display:block; margin-top:5px;">${group.code}</small>
                </div>`;
                
                if (group.pharmacies && group.pharmacies.length > 0) {
                    html += '<div style="margin-left: 30px; padding-left: 15px; border-left: 2px solid #ddd;">';
                    
                    group.pharmacies.forEach(pharmacy => {
                        html += `<div class="hierarchy-node pharmacy">
                            <strong>${pharmacy.name}</strong>
                            <small style="display:block; margin-top:5px;">${pharmacy.code}</small>
                        </div>`;
                        
                        if (pharmacy.branches && pharmacy.branches.length > 0) {
                            html += '<div style="margin-left: 30px; padding-left: 15px; border-left: 2px solid #ddd;">';
                            
                            pharmacy.branches.forEach(branch => {
                                html += `<div class="hierarchy-node branch">
                                    <strong>${branch.name}</strong>
                                    <small style="display:block; margin-top:5px;">${branch.code}</small>
                                </div>`;
                            });
                            
                            html += '</div>';
                        }
                    });
                    
                    html += '</div>';
                }
            });
        } else {
            html += '<p class="text-center text-muted"><?php echo lang("no_hierarchy_data"); ?></p>';
        }
        
        html += '</div>';
        $('#hierarchy-tree').html(html);
    }

    function showNotification(type, message) {
        // Using existing notification system or fallback to alert
        if (typeof swal === 'function') {
            swal({
                title: type === 'success' ? 'Success' : 'Error',
                text: message,
                type: type,
                timer: 3000
            });
        } else {
            alert(message);
        }
    }
</script>
