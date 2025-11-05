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

                                <!-- Add Pharmacy Button -->
                                <div class="row" style="margin-bottom: 20px;">
                                    <div class="col-md-12">
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
                                <div id="hierarchy-container" class="hierarchy-container-wrapper">
                                    <div id="hierarchy-tree" class="hierarchy-tree">
                                        <!-- Hierarchy will render here -->
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
    <div class="modal-dialog modal-lg" role="document" style="width: 95%; max-width: 1200px;">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-bottom: none;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 0.8;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal_add_pharmacy_label" style="color: white; font-weight: 600;">
                    <i class="fa fa-plus-circle"></i> <?php echo lang('add_pharmacy'); ?>
                </h4>
            </div>
            <form id="form_add_pharmacy" method="post" role="form">
                <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                <input type="hidden" name="company_id" id="company_id_add" value="<?php echo isset($company_id) ? $company_id : ''; ?>">
                <div class="modal-body" style="padding: 30px;">
                    <!-- Left Column - Pharmacy Info -->
                    <div class="row">
                        <div class="col-md-6">
                            <h5 style="color: #667eea; font-weight: 600; margin-bottom: 20px; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                                <i class="fa fa-hospital-o"></i> <?php echo lang('pharmacy_information'); ?>
                            </h5>

                            <div class="form-group">
                                <label for="pharmacy_code"><?php echo lang('pharmacy_code'); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="pharmacy_code" name="code" placeholder="PHR-001" required style="border-radius: 4px; border: 1px solid #ddd;">
                                <small class="text-muted"><i class="fa fa-info-circle"></i> <?php echo lang('unique_code'); ?></small>
                            </div>

                            <div class="form-group">
                                <label for="pharmacy_name"><?php echo lang('pharmacy_name'); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="pharmacy_name" name="name" placeholder="<?php echo lang('enter_pharmacy_name'); ?>" required style="border-radius: 4px; border: 1px solid #ddd;">
                            </div>

                            <div class="form-group">
                                <label for="pharmacy_phone"><?php echo lang('phone'); ?> <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="pharmacy_phone" name="phone" placeholder="+966 50 0000 0000" required style="border-radius: 4px; border: 1px solid #ddd;">
                            </div>

                            <div class="form-group">
                                <label for="pharmacy_email"><?php echo lang('email'); ?></label>
                                <input type="email" class="form-control" id="pharmacy_email" name="email" placeholder="<?php echo lang('enter_email'); ?>" style="border-radius: 4px; border: 1px solid #ddd;">
                            </div>
                        </div>

                        <!-- Right Column - Address -->
                        <div class="col-md-6">
                            <h5 style="color: #667eea; font-weight: 600; margin-bottom: 20px; border-bottom: 2px solid #667eea; padding-bottom: 10px;">
                                <i class="fa fa-map-marker"></i> <?php echo lang('location_details'); ?>
                            </h5>

                            <div class="form-group">
                                <label for="pharmacy_address"><?php echo lang('address'); ?> <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="pharmacy_address" name="address" rows="4" placeholder="<?php echo lang('enter_complete_address'); ?>" required style="border-radius: 4px; border: 1px solid #ddd; resize: vertical;"></textarea>
                            </div>

                            <input type="hidden" name="warehouse_type" value="mainwarehouse">
                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="background: #f5f5f5; border-top: 1px solid #ddd; padding: 15px;">
                    <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 4px;">
                        <i class="fa fa-times"></i> <?php echo lang('cancel'); ?>
                    </button>
                    <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 4px;">
                        <i class="fa fa-save"></i> <?php echo lang('add_pharmacy'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Pharmacy Modal -->
<div class="modal fade" id="modal_edit_pharmacy" tabindex="-1" role="dialog" aria-labelledby="modal_edit_pharmacy_label">
    <div class="modal-dialog modal-lg" role="document" style="width: 95%; max-width: 1200px;">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #8B5CF6 0%, #6D28D9 100%); border-bottom: none;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 0.8;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal_edit_pharmacy_label" style="color: white; font-weight: 600;">
                    <i class="fa fa-edit"></i> <?php echo lang('edit_pharmacy'); ?>
                </h4>
            </div>
            <form id="form_edit_pharmacy" method="post" role="form">
                <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                <input type="hidden" name="company_id" id="company_id_edit" value="<?php echo isset($company_id) ? $company_id : ''; ?>">
                <div class="modal-body" style="padding: 30px;">
                    <input type="hidden" id="pharmacy_id_edit" name="id">
                    
                    <!-- Left Column - Pharmacy Info -->
                    <div class="row">
                        <div class="col-md-6">
                            <h5 style="color: #8B5CF6; font-weight: 600; margin-bottom: 20px; border-bottom: 2px solid #8B5CF6; padding-bottom: 10px;">
                                <i class="fa fa-hospital-o"></i> <?php echo lang('pharmacy_information'); ?>
                            </h5>

                            <div class="form-group">
                                <label for="pharmacy_code_edit"><?php echo lang('pharmacy_code'); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="pharmacy_code_edit" name="code" placeholder="PHR-001" required style="border-radius: 4px; border: 1px solid #ddd;">
                                <small class="text-muted"><i class="fa fa-info-circle"></i> <?php echo lang('unique_code'); ?></small>
                            </div>

                            <div class="form-group">
                                <label for="pharmacy_name_edit"><?php echo lang('pharmacy_name'); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="pharmacy_name_edit" name="name" placeholder="<?php echo lang('enter_pharmacy_name'); ?>" required style="border-radius: 4px; border: 1px solid #ddd;">
                            </div>

                            <div class="form-group">
                                <label for="pharmacy_phone_edit"><?php echo lang('phone'); ?> <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="pharmacy_phone_edit" name="phone" placeholder="+966 50 0000 0000" required style="border-radius: 4px; border: 1px solid #ddd;">
                            </div>

                            <div class="form-group">
                                <label for="pharmacy_email_edit"><?php echo lang('email'); ?></label>
                                <input type="email" class="form-control" id="pharmacy_email_edit" name="email" placeholder="<?php echo lang('enter_email'); ?>" style="border-radius: 4px; border: 1px solid #ddd;">
                            </div>
                        </div>

                        <!-- Right Column - Address -->
                        <div class="col-md-6">
                            <h5 style="color: #8B5CF6; font-weight: 600; margin-bottom: 20px; border-bottom: 2px solid #8B5CF6; padding-bottom: 10px;">
                                <i class="fa fa-map-marker"></i> <?php echo lang('location_details'); ?>
                            </h5>

                            <div class="form-group">
                                <label for="pharmacy_address_edit"><?php echo lang('address'); ?> <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="pharmacy_address_edit" name="address" rows="4" placeholder="<?php echo lang('enter_complete_address'); ?>" required style="border-radius: 4px; border: 1px solid #ddd; resize: vertical;"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="background: #f5f5f5; border-top: 1px solid #ddd; padding: 15px;">
                    <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 4px;">
                        <i class="fa fa-times"></i> <?php echo lang('cancel'); ?>
                    </button>
                    <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #8B5CF6 0%, #6D28D9 100%); border: none; border-radius: 4px;">
                        <i class="fa fa-save"></i> <?php echo lang('update'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Branch Modal -->
<div class="modal fade" id="modal_add_branch" tabindex="-1" role="dialog" aria-labelledby="modal_add_branch_label">
    <div class="modal-dialog modal-lg" role="document" style="width: 95%; max-width: 1000px;">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #17a2b8 0%, #0c5460 100%); border-bottom: none;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 0.8;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal_add_branch_label" style="color: white; font-weight: 600;">
                    <i class="fa fa-plus-circle"></i> <?php echo lang('add_branch'); ?>
                </h4>
            </div>
            <form id="form_add_branch" method="post" role="form">
                <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                <div class="modal-body" style="padding: 30px;">
                    <!-- Left Column -->
                    <div class="row">
                        <div class="col-md-6">
                            <h5 style="color: #17a2b8; font-weight: 600; margin-bottom: 20px; border-bottom: 2px solid #17a2b8; padding-bottom: 10px;">
                                <i class="fa fa-map-marker"></i> <?php echo lang('branch_information'); ?>
                            </h5>

                            <div class="form-group">
                                <label for="branch_pharmacy_id"><?php echo lang('pharmacy'); ?> <span class="text-danger">*</span></label>
                                <select id="branch_pharmacy_id" name="pharmacy_id" class="form-control" required style="border-radius: 4px; border: 1px solid #ddd;">
                                    <option value=""><?php echo lang('select_pharmacy'); ?></option>
                                    <!-- Options will be populated by JS -->
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="branch_code"><?php echo lang('branch_code'); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="branch_code" name="code" placeholder="BR-001" required style="border-radius: 4px; border: 1px solid #ddd;">
                                <small class="text-muted"><i class="fa fa-info-circle"></i> <?php echo lang('unique_code'); ?></small>
                            </div>

                            <div class="form-group">
                                <label for="branch_name"><?php echo lang('branch_name'); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="branch_name" name="name" placeholder="<?php echo lang('enter_branch_name'); ?>" required style="border-radius: 4px; border: 1px solid #ddd;">
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <h5 style="color: #17a2b8; font-weight: 600; margin-bottom: 20px; border-bottom: 2px solid #17a2b8; padding-bottom: 10px;">
                                <i class="fa fa-home"></i> <?php echo lang('location_details'); ?>
                            </h5>

                            <div class="form-group">
                                <label for="branch_address"><?php echo lang('address'); ?> <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="branch_address" name="address" rows="3" placeholder="<?php echo lang('enter_complete_address'); ?>" required style="border-radius: 4px; border: 1px solid #ddd; resize: vertical;"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="branch_phone"><?php echo lang('phone'); ?> <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="branch_phone" name="phone" placeholder="+966 50 0000 0000" required style="border-radius: 4px; border: 1px solid #ddd;">
                            </div>

                            <div class="form-group">
                                <label for="branch_email"><?php echo lang('email'); ?></label>
                                <input type="email" class="form-control" id="branch_email" name="email" placeholder="<?php echo lang('enter_email'); ?>" style="border-radius: 4px; border: 1px solid #ddd;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="background: #f5f5f5; border-top: 1px solid #ddd; padding: 15px;">
                    <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 4px;">
                        <i class="fa fa-times"></i> <?php echo lang('cancel'); ?>
                    </button>
                    <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #17a2b8 0%, #0c5460 100%); border: none; border-radius: 4px;">
                        <i class="fa fa-save"></i> <?php echo lang('add_branch'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Branch Modal -->
<div class="modal fade" id="modal_edit_branch" tabindex="-1" role="dialog" aria-labelledby="modal_edit_branch_label">
    <div class="modal-dialog modal-lg" role="document" style="width: 95%; max-width: 1000px;">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #0c5460 0%, #17a2b8 100%); border-bottom: none;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 0.8;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal_edit_branch_label" style="color: white; font-weight: 600;">
                    <i class="fa fa-edit"></i> <?php echo lang('edit_branch'); ?>
                </h4>
            </div>
            <form id="form_edit_branch" method="post" role="form">
                <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                <div class="modal-body" style="padding: 30px;">
                    <input type="hidden" id="branch_id_edit" name="id">
                    
                    <!-- Left Column -->
                    <div class="row">
                        <div class="col-md-6">
                            <h5 style="color: #0c5460; font-weight: 600; margin-bottom: 20px; border-bottom: 2px solid #0c5460; padding-bottom: 10px;">
                                <i class="fa fa-map-marker"></i> <?php echo lang('branch_information'); ?>
                            </h5>

                            <div class="form-group">
                                <label for="branch_pharmacy_id_edit"><?php echo lang('pharmacy'); ?> <span class="text-danger">*</span></label>
                                <select id="branch_pharmacy_id_edit" name="pharmacy_id" class="form-control" required style="border-radius: 4px; border: 1px solid #ddd;">
                                    <option value=""><?php echo lang('select_pharmacy'); ?></option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="branch_code_edit"><?php echo lang('branch_code'); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="branch_code_edit" name="code" placeholder="BR-001" required style="border-radius: 4px; border: 1px solid #ddd;">
                                <small class="text-muted"><i class="fa fa-info-circle"></i> <?php echo lang('unique_code'); ?></small>
                            </div>

                            <div class="form-group">
                                <label for="branch_name_edit"><?php echo lang('branch_name'); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="branch_name_edit" name="name" placeholder="<?php echo lang('enter_branch_name'); ?>" required style="border-radius: 4px; border: 1px solid #ddd;">
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <h5 style="color: #0c5460; font-weight: 600; margin-bottom: 20px; border-bottom: 2px solid #0c5460; padding-bottom: 10px;">
                                <i class="fa fa-home"></i> <?php echo lang('location_details'); ?>
                            </h5>

                            <div class="form-group">
                                <label for="branch_address_edit"><?php echo lang('address'); ?> <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="branch_address_edit" name="address" rows="3" placeholder="<?php echo lang('enter_complete_address'); ?>" required style="border-radius: 4px; border: 1px solid #ddd; resize: vertical;"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="branch_phone_edit"><?php echo lang('phone'); ?> <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="branch_phone_edit" name="phone" placeholder="+966 50 0000 0000" required style="border-radius: 4px; border: 1px solid #ddd;">
                            </div>

                            <div class="form-group">
                                <label for="branch_email_edit"><?php echo lang('email'); ?></label>
                                <input type="email" class="form-control" id="branch_email_edit" name="email" placeholder="<?php echo lang('enter_email'); ?>" style="border-radius: 4px; border: 1px solid #ddd;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer" style="background: #f5f5f5; border-top: 1px solid #ddd; padding: 15px;">
                    <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 4px;">
                        <i class="fa fa-times"></i> <?php echo lang('cancel'); ?>
                    </button>
                    <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #0c5460 0%, #17a2b8 100%); border: none; border-radius: 4px;">
                        <i class="fa fa-save"></i> <?php echo lang('update'); ?>
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

    /* Hierarchy Tree Styles */
    .hierarchy-container-wrapper {
        background: #f5f5f5;
        border: 1px solid #ddd;
        border-radius: 4px;
        overflow-y: auto;
        overflow-x: auto;
        max-height: 600px;
        min-height: 300px;
        padding: 20px;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    /* Scrollbar styling for webkit browsers */
    .hierarchy-container-wrapper::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .hierarchy-container-wrapper::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .hierarchy-container-wrapper::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .hierarchy-container-wrapper::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .hierarchy-tree-container {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        padding: 20px 0;
        overflow-x: auto;
    }

    .hierarchy-item {
        position: relative;
        padding-left: 30px;
        margin-bottom: 2px;
    }

    .hierarchy-item:not(.last) {
        padding-bottom: 0;
    }

    .hierarchy-connector {
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 1px;
        background: #ddd;
        content: '';
    }

    .hierarchy-item.last .hierarchy-connector {
        height: 22px;
    }

    .hierarchy-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 15px;
        width: 14px;
        height: 1px;
        background: #ddd;
    }

    .hierarchy-node {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        margin-bottom: 4px;
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        transition: all 0.2s ease;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        font-size: 13px;
        cursor: pointer;
        user-select: none;
    }

    .hierarchy-node:hover {
        border-color: #999;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transform: translateY(-1px);
    }

    .hierarchy-node.pharmacy-node {
        background: linear-gradient(135deg, #17a2b8 0%, #0c5460 100%);
        border-color: #17a2b8;
        color: white;
        font-weight: 500;
    }

    .hierarchy-node.pharmacy-node .node-code {
        color: rgba(255, 255, 255, 0.85);
    }

    .hierarchy-node.branch-node {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        border-color: #28a745;
        color: white;
        font-weight: 500;
    }

    .hierarchy-node.branch-node .node-code {
        color: rgba(255, 255, 255, 0.85);
    }

    .expand-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        min-width: 18px;
        margin-right: 4px;
        font-size: 11px;
        font-weight: bold;
        opacity: 0.8;
        transition: transform 0.2s ease;
    }

    .expand-toggle.collapsed {
        transform: rotate(-90deg);
    }

    .expand-toggle.no-children {
        visibility: hidden;
    }

    .node-icon {
        font-size: 14px;
        min-width: 14px;
        text-align: center;
        opacity: 0.9;
    }

    .node-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 1px;
    }

    .node-title {
        font-weight: 500;
        font-size: 12px;
        line-height: 1.2;
    }

    .node-code {
        font-size: 10px;
        opacity: 0.8;
        font-family: 'Courier New', monospace;
    }

    .hierarchy-children {
        position: relative;
        margin-top: 2px;
    }

    .hierarchy-children.collapsed {
        display: none;
    }

    .no-data-message {
        padding: 8px 12px;
        color: #999;
        font-style: italic;
        font-size: 12px;
        margin-left: 30px;
        margin-bottom: 4px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .hierarchy-item {
            padding-left: 24px;
        }

        .hierarchy-node {
            padding: 6px 10px;
            font-size: 12px;
            gap: 6px;
        }

        .node-icon {
            font-size: 12px;
        }

        .node-title {
            font-size: 11px;
        }

        .node-code {
            font-size: 9px;
        }

        .expand-toggle {
            width: 16px;
            height: 16px;
            font-size: 10px;
        }
    }
</style>

<!-- ===== SCRIPTS ===== -->
<script>
    console.log('pharmacy_hierarchy.php script loaded');
    
    // Store company_id globally
    // var COMPANY_ID = '<?php echo isset($company_id) ? $company_id : ''; ?>';
        var COMPANY_ID = 'a7c63a62-b96b-11f0-a2b6-005056c00001';

    /**
     * Show animated alert with success/error/warning/info types
     * @param {string} type - 'success', 'error', 'warning', 'info'
     * @param {string} title - Alert title
     * @param {string} message - Alert message
     * @param {function} callback - Optional callback after alert closes
     */
    function showAnimatedAlert(type, title, message, callback) {
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ⓘ'
        };
        
        const colors = {
            success: '#10B981',
            error: '#EF4444',
            warning: '#F59E0B',
            info: '#3B82F6'
        };
        
        const bgColors = {
            success: '#ECFDF5',
            error: '#FEF2F2',
            warning: '#FFFBEB',
            info: '#EFF6FF'
        };
        
        // Use SweetAlert if available, otherwise use browser alert
        if (typeof swal === 'function') {
            swal({
                title: title,
                text: message,
                type: type,
                html: `
                    <div style="
                        background: ${bgColors[type]};
                        border-left: 4px solid ${colors[type]};
                        padding: 20px;
                        border-radius: 8px;
                        text-align: left;
                        animation: slideIn 0.3s ease-out;
                    ">
                        <div style="
                            display: flex;
                            align-items: center;
                            gap: 15px;
                            margin-bottom: 10px;
                        ">
                            <span style="
                                font-size: 28px;
                                color: ${colors[type]};
                                font-weight: bold;
                            ">${icons[type]}</span>
                            <div style="text-align: left;">
                                <div style="
                                    font-size: 16px;
                                    font-weight: 600;
                                    color: #1F2937;
                                    margin-bottom: 5px;
                                ">${title}</div>
                                <div style="
                                    font-size: 14px;
                                    color: #6B7280;
                                    line-height: 1.5;
                                ">${message}</div>
                            </div>
                        </div>
                    </div>
                    <style>
                        @keyframes slideIn {
                            from {
                                opacity: 0;
                                transform: translateY(-20px);
                            }
                            to {
                                opacity: 1;
                                transform: translateY(0);
                            }
                        }
                    </style>
                `,
                confirmButtonColor: colors[type],
                confirmButtonText: 'OK',
                allowOutsideClick: false,
                allowEscapeKey: true,
                didClose: callback
            });
        } else {
            // Fallback: Use custom HTML modal if SweetAlert not available
            showCustomAlert(type, title, message, colors, bgColors, icons, callback);
        }
    }
    
    /**
     * Custom alert modal (fallback when SweetAlert is not available)
     */
    function showCustomAlert(type, title, message, colors, bgColors, icons, callback) {
        // Remove existing custom alert if present
        $('#custom-alert-modal').remove();
        
        const html = `
            <div id="custom-alert-modal" style="
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                animation: fadeIn 0.2s ease-out;
            ">
                <div style="
                    background: white;
                    border-radius: 12px;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                    max-width: 500px;
                    width: 90%;
                    overflow: hidden;
                    animation: slideInUp 0.3s ease-out;
                ">
                    <!-- Header -->
                    <div style="
                        background: ${colors[type]};
                        padding: 20px;
                        color: white;
                    ">
                        <div style="font-size: 24px; font-weight: bold; display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 32px;">${icons[type]}</span>
                            <span>${title}</span>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div style="
                        padding: 30px;
                        background: ${bgColors[type]};
                        border-left: 4px solid ${colors[type]};
                    ">
                        <p style="
                            margin: 0;
                            font-size: 14px;
                            color: #4B5563;
                            line-height: 1.6;
                        ">${message}</p>
                    </div>
                    
                    <!-- Footer -->
                    <div style="
                        padding: 20px;
                        text-align: right;
                        background: #F9FAFB;
                        border-top: 1px solid #E5E7EB;
                    ">
                        <button onclick="closeCustomAlert()" style="
                            background: ${colors[type]};
                            color: white;
                            border: none;
                            padding: 10px 30px;
                            border-radius: 6px;
                            cursor: pointer;
                            font-weight: 600;
                            transition: background 0.2s;
                        " onmouseover="this.style.opacity = '0.9'" onmouseout="this.style.opacity = '1'">
                            OK
                        </button>
                    </div>
                </div>
            </div>
            <style>
                @keyframes fadeIn {
                    from {
                        opacity: 0;
                    }
                    to {
                        opacity: 1;
                    }
                }
                @keyframes slideInUp {
                    from {
                        opacity: 0;
                        transform: translateY(30px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
            </style>
        `;
        
        $('body').append(html);
        
        // Store callback for later
        window.customAlertCallback = callback;
    }
    
    /**
     * Close custom alert modal
     */
    function closeCustomAlert() {
        $('#custom-alert-modal').fadeOut(300, function() {
            $(this).remove();
            if (window.customAlertCallback && typeof window.customAlertCallback === 'function') {
                window.customAlertCallback();
                window.customAlertCallback = null;
            }
        });
    }
    
    $(function() {
        console.log('Document ready - calling initPharmacySetup');
        initPharmacySetup();
    });

    function initPharmacySetup() {
        console.log('initPharmacySetup() called');
        
        // Load pharmacies immediately
        loadPharmacies();
        
        // Load all pharmacies for the branch form dropdowns (so they're ready immediately)
        loadPharmaciesForAdd();
        
        // Load hierarchy tree
        loadHierarchyTree();

        // Event handlers
        $('#pharmacy_id_for_branches').on('change', function() {
            console.log('Pharmacy for branches changed');
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

        $('#form_edit_pharmacy').on('submit', function(e) {
            e.preventDefault();
            updatePharmacy();
        });

        $('#form_edit_branch').on('submit', function(e) {
            e.preventDefault();
            updateBranch();
        });

        // Modal handlers
        $('#modal_add_pharmacy').on('show.bs.modal', function() {
            // Reset form and set company_id
            $('#form_add_pharmacy')[0].reset();
            $('#company_id_add').val(COMPANY_ID);
        });

        $('#modal_edit_pharmacy').on('show.bs.modal', function() {
            // Set company_id
            $('#company_id_edit').val(COMPANY_ID);
        });

        // Reload pharmacies for branch modal (in case new pharmacy was added)
        $('#modal_add_branch').on('show.bs.modal', function() {
            loadPharmaciesForAdd();
        });
    }

    function loadPharmacies() {
        console.log('loadPharmacies() called');
        let url = '<?php echo admin_url('organization_setup/get_pharmacies'); ?>';
        console.log('Calling URL:', url);
        
        $.ajax({
            url: url,
            type: 'GET',
            data: { company_id: COMPANY_ID },
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(data) {
                console.log('AJAX Success - Data received:', data);
                let tbody = $('#pharmacies_table tbody');
                tbody.empty();

                if (data.success && data.data && data.data.length > 0) {
                    console.log('Adding ' + data.data.length + ' rows to table');
                    $.each(data.data, function(i, pharmacy) {
                        tbody.append(`
                            <tr>
                                <td><strong>${pharmacy.code}</strong></td>
                                <td>${pharmacy.name}</td>
                                <td>${pharmacy.address || '-'}</td>
                                <td>${pharmacy.phone || '-'}</td>
                                <td><span class="label label-primary">${pharmacy.warehouse_type || 'mainwarehouse'}</span></td>
                                <td>
                                    <div class="btn-group btn-group-xs">
                                        <button class="btn btn-info" onclick="editPharmacy('${pharmacy.id}')">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger" onclick="deletePharmacy('${pharmacy.id}')">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `);
                    });
                } else {
                    console.warn('No data received or success is false:', data);
                    tbody.append('<tr><td colspan="6" class="text-center text-muted"><i class="fa fa-inbox"></i> <?php echo lang("no_data"); ?></td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error - Status:', status, 'Error:', error);
                console.error('Response Status:', xhr.status);
                console.error('Response Text:', xhr.responseText);
                console.error('Response:', xhr);
            },
            complete: function() {
                console.log('AJAX Complete');
            }
        });
    }

    function loadPharmaciesForAdd() {
        $.ajax({
            url: '<?php echo admin_url('organization_setup/get_all_pharmacies'); ?>',
            type: 'GET',
            data: { company_id: COMPANY_ID },
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
            url: '<?php echo admin_url('organization_setup/get_branches'); ?>',
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
                                        <button class="btn btn-info" onclick="editBranch('${branch.id}')">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger" onclick="deleteBranch('${branch.id}')">
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
        // Client-side validation
        if (!$('#pharmacy_code').val()) {
            showNotification('error', 'Please enter pharmacy code');
            return;
        }
        if (!$('#pharmacy_name').val()) {
            showNotification('error', 'Please enter pharmacy name');
            return;
        }
        if (!$('#pharmacy_address').val()) {
            showNotification('error', 'Please enter pharmacy address');
            return;
        }
        if (!$('#pharmacy_phone').val()) {
            showNotification('error', 'Please enter pharmacy phone');
            return;
        }

        let formData = $('#form_add_pharmacy').serializeArray();
        let data = {};
        $.each(formData, function() {
            data[this.name] = this.value;
        });

        $.ajax({
            url: '<?php echo admin_url('organization_setup/add_pharmacy'); ?>',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAnimatedAlert('success', 'Pharmacy Created!', response.message, function() {
                        $('#modal_add_pharmacy').modal('hide');
                        $('#form_add_pharmacy')[0].reset();
                        loadPharmacies();
                        loadPharmaciesForAdd();
                        loadHierarchyTree();
                    });
                } else {
                    showAnimatedAlert('error', 'Failed to Create Pharmacy', response.message);
                }
            },
            error: function() {
                showAnimatedAlert('error', 'Error', 'An error occurred while creating pharmacy');
            }
        });
    }

    function updatePharmacy() {
        // Client-side validation
        if (!$('#pharmacy_code_edit').val()) {
            showAnimatedAlert('error', 'Validation Error', 'Please enter pharmacy code');
            return;
        }
        if (!$('#pharmacy_name_edit').val()) {
            showAnimatedAlert('error', 'Validation Error', 'Please enter pharmacy name');
            return;
        }
        if (!$('#pharmacy_address_edit').val()) {
            showAnimatedAlert('error', 'Validation Error', 'Please enter pharmacy address');
            return;
        }
        if (!$('#pharmacy_phone_edit').val()) {
            showAnimatedAlert('error', 'Validation Error', 'Please enter pharmacy phone');
            return;
        }

        let formData = $('#form_edit_pharmacy').serializeArray();
        let data = {};
        $.each(formData, function() {
            data[this.name] = this.value;
        });

        $.ajax({
            url: '<?php echo admin_url('organization_setup/update_pharmacy'); ?>',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAnimatedAlert('success', 'Pharmacy Updated!', response.message, function() {
                        $('#modal_edit_pharmacy').modal('hide');
                        $('#form_edit_pharmacy')[0].reset();
                        loadPharmacies();
                        loadPharmaciesForAdd();
                        loadHierarchyTree();
                    });
                } else {
                    showAnimatedAlert('error', 'Failed to Update Pharmacy', response.message);
                }
            },
            error: function() {
                showAnimatedAlert('error', 'Error', 'An error occurred while updating pharmacy');
            }
        });
    }

    function addBranch() {
        // Client-side validation
        if (!$('#branch_pharmacy_id').val()) {
            showNotification('error', 'Please select a pharmacy');
            return;
        }
        if (!$('#branch_code').val()) {
            showNotification('error', 'Please enter branch code');
            return;
        }
        if (!$('#branch_name').val()) {
            showNotification('error', 'Please enter branch name');
            return;
        }
        if (!$('#branch_address').val()) {
            showNotification('error', 'Please enter branch address');
            return;
        }
        if (!$('#branch_phone').val()) {
            showNotification('error', 'Please enter branch phone');
            return;
        }

        let formData = $('#form_add_branch').serializeArray();
        let data = {};
        $.each(formData, function() {
            data[this.name] = this.value;
        });

        $.ajax({
            url: '<?php echo admin_url('organization_setup/add_branch'); ?>',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAnimatedAlert('success', 'Branch Created!', response.message, function() {
                        $('#modal_add_branch').modal('hide');
                        $('#form_add_branch')[0].reset();
                        loadBranches();
                        loadHierarchyTree();
                    });
                } else {
                    showAnimatedAlert('error', 'Failed to Create Branch', response.message);
                }
            },
            error: function() {
                showAnimatedAlert('error', 'Error', 'An error occurred while creating branch');
            }
        });
    }

    function editPharmacy(id) {
        console.log('editPharmacy() called with id:', id);
        
        // Fetch pharmacy details
        $.ajax({
            url: '<?php echo admin_url('organization_setup/get_pharmacy_details'); ?>',
            type: 'GET',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                console.log('EditPharmacy Response:', response);
                if (response.success && response.data) {
                    const pharmacy = response.data;
                    console.log('Pharmacy Data:', pharmacy);
                    
                    // Use setTimeout to ensure all rendering is complete
                    setTimeout(function() {
                        // Populate edit form
                        console.log('Setting form values...');
                        
                        $('#pharmacy_id_edit').val(pharmacy.id);
                        $('#company_id_edit').val(COMPANY_ID);
                        $('#pharmacy_code_edit').val(pharmacy.code);
                        $('#pharmacy_name_edit').val(pharmacy.name);
                        
                        // Set textarea values
                        $('#pharmacy_address_edit').val(pharmacy.address || '');
                        $('#pharmacy_phone_edit').val(pharmacy.phone || '');
                        $('#pharmacy_email_edit').val(pharmacy.email || '');
                        
                        console.log('Form values set');
                        
                        // Show edit modal
                        $('#modal_edit_pharmacy').modal('show');
                        
                    }, 200);
                } else {
                    showAnimatedAlert('error', 'Error', response.message || 'Failed to load pharmacy details');
                }
            },
            error: function(xhr, status, error) {
                console.log('EditPharmacy Error:', error, status, xhr);
                showAnimatedAlert('error', 'Error', 'Failed to load pharmacy details');
            }
        });
    }

    function deletePharmacy(id) {
        showAnimatedAlert('warning', 'Delete Pharmacy?', 'This action cannot be undone. All associated branches will also be deleted.', function() {
            // Show custom confirmation with Yes/No
            const customConfirm = confirm('Are you sure you want to delete this pharmacy? This cannot be undone.');
            if (!customConfirm) return;
            
            // Get CSRF token
            let csrfToken = $('input[name="<?php echo $this->security->get_csrf_token_name(); ?>"]').val();
            
            let deleteData = {
                id: id
            };
            deleteData['<?php echo $this->security->get_csrf_token_name(); ?>'] = csrfToken;
            
            $.ajax({
                url: '<?php echo admin_url('organization_setup/delete_pharmacy'); ?>',
                type: 'POST',
                data: deleteData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAnimatedAlert('success', 'Deleted!', response.message, function() {
                            loadPharmacies();
                            loadPharmaciesForAdd();
                            loadHierarchyTree();
                        });
                    } else {
                        showAnimatedAlert('error', 'Failed to Delete', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Delete error:', error, xhr);
                    showAnimatedAlert('error', 'Error', 'An error occurred while deleting pharmacy');
                }
            });
        });
    }

    function editBranch(id) {
        console.log('editBranch() called with id:', id);
        
        // Fetch branch details
        $.ajax({
            url: '<?php echo admin_url('organization_setup/get_branch_details'); ?>',
            type: 'GET',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                console.log('EditBranch Response:', response);
                if (response.success && response.data) {
                    const branch = response.data;
                    console.log('Branch Data:', branch);
                    
                    // Load pharmacies for dropdown
                    loadPharmaciesForEdit();
                    
                    // Use setTimeout to ensure all rendering is complete
                    setTimeout(function() {
                        // Populate edit form
                        console.log('Setting branch form values...');
                        
                        $('#branch_id_edit').val(branch.id);
                        $('#branch_pharmacy_id_edit').val(branch.pharmacy_id);
                        $('#branch_code_edit').val(branch.code);
                        $('#branch_name_edit').val(branch.name);
                        $('#branch_address_edit').val(branch.address || '');
                        $('#branch_phone_edit').val(branch.phone || '');
                        $('#branch_email_edit').val(branch.email || '');
                        
                        console.log('Branch form values set');
                        
                        // Show edit modal
                        $('#modal_edit_branch').modal('show');
                        
                    }, 200);
                } else {
                    showAnimatedAlert('error', 'Error', response.message || 'Failed to load branch details');
                }
            },
            error: function(xhr, status, error) {
                console.log('EditBranch Error:', error, status, xhr);
                showAnimatedAlert('error', 'Error', 'Failed to load branch details');
            }
        });
    }

    function updateBranch() {
        // Client-side validation
        if (!$('#branch_pharmacy_id_edit').val()) {
            showAnimatedAlert('error', 'Validation Error', 'Please select a pharmacy');
            return;
        }
        if (!$('#branch_code_edit').val()) {
            showAnimatedAlert('error', 'Validation Error', 'Please enter branch code');
            return;
        }
        if (!$('#branch_name_edit').val()) {
            showAnimatedAlert('error', 'Validation Error', 'Please enter branch name');
            return;
        }
        if (!$('#branch_address_edit').val()) {
            showAnimatedAlert('error', 'Validation Error', 'Please enter branch address');
            return;
        }
        if (!$('#branch_phone_edit').val()) {
            showAnimatedAlert('error', 'Validation Error', 'Please enter branch phone');
            return;
        }

        // Get form data
        let formData = $('#form_edit_branch').serializeArray();
        let data = {};
        $.each(formData, function() {
            data[this.name] = this.value;
        });

        // Ensure CSRF token is included
        if (!data['<?php echo $this->security->get_csrf_token_name(); ?>']) {
            let csrfToken = $('input[name="<?php echo $this->security->get_csrf_token_name(); ?>"]').val();
            data['<?php echo $this->security->get_csrf_token_name(); ?>'] = csrfToken;
        }

        console.log('Updating branch with data:', data);

        $.ajax({
            url: '<?php echo admin_url('organization_setup/update_branch'); ?>',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                console.log('Update branch response:', response);
                if (response.success) {
                    showAnimatedAlert('success', 'Branch Updated!', response.message, function() {
                        $('#modal_edit_branch').modal('hide');
                        $('#form_edit_branch')[0].reset();
                        loadBranches();
                        loadHierarchyTree();
                    });
                } else {
                    showAnimatedAlert('error', 'Failed to Update Branch', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Update branch error:', error, xhr);
                if (xhr.status === 302) {
                    showAnimatedAlert('error', 'CSRF Error', 'Security token validation failed. Please try again.');
                } else {
                    showAnimatedAlert('error', 'Error', 'An error occurred while updating branch');
                }
            }
        });
    }

    function deleteBranch(id) {
        showAnimatedAlert('warning', 'Delete Branch?', 'This action cannot be undone. The branch will be permanently deleted.', function() {
            // Show custom confirmation with Yes/No
            const customConfirm = confirm('Are you sure you want to delete this branch? This cannot be undone.');
            if (!customConfirm) return;
            
            // Get CSRF token
            let csrfToken = $('input[name="<?php echo $this->security->get_csrf_token_name(); ?>"]').val();
            
            let deleteData = {
                id: id
            };
            deleteData['<?php echo $this->security->get_csrf_token_name(); ?>'] = csrfToken;
            
            $.ajax({
                url: '<?php echo admin_url('organization_setup/delete_branch'); ?>',
                type: 'POST',
                data: deleteData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAnimatedAlert('success', 'Deleted!', response.message, function() {
                            loadBranches();
                            loadHierarchyTree();
                        });
                    } else {
                        showAnimatedAlert('error', 'Failed to Delete', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Delete error:', error, xhr);
                    showAnimatedAlert('error', 'Error', 'An error occurred while deleting branch');
                }
            });
        });
    }

    function loadPharmaciesForEdit() {
        $.ajax({
            url: '<?php echo admin_url('organization_setup/get_all_pharmacies'); ?>',
            type: 'GET',
            data: { company_id: COMPANY_ID },
            dataType: 'json',
            success: function(data) {
                let $select = $('#branch_pharmacy_id_edit');
                
                $select.find('option:not(:first)').remove();
                
                if (data.success && data.data.length > 0) {
                    $.each(data.data, function(i, pharmacy) {
                        $select.append('<option value="' + pharmacy.id + '">' + pharmacy.name + ' (' + pharmacy.code + ')</option>');
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading pharmacies for edit:', error);
            }
        });
    }

    function loadHierarchyTree() {
        $.ajax({
            url: '<?php echo admin_url('organization_setup/get_hierarchy_tree'); ?>',
            type: 'GET',
            data: { company_id: COMPANY_ID },
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    renderHierarchyTree(data.data);
                }
            }
        });
    }

    function renderHierarchyTree(data) {
        let html = '<div class="hierarchy-tree-container">';
        
        if (data && data.length > 0) {
            console.log('Rendering hierarchy tree with data:', data);
            
            // Loop through companies/pharmacy groups (top level)
            data.forEach((company, companyIndex) => {
                const hasPharmacies = company.pharmacies && company.pharmacies.length > 0;
                
                // Check if pharmacies exist at this level
                if (hasPharmacies) {
                    // Loop through pharmacies under each company
                    company.pharmacies.forEach((pharmacy, pharmacyIndex) => {
                        const isLastPharmacy = pharmacyIndex === company.pharmacies.length - 1;
                        const pharmacyClass = isLastPharmacy ? 'last' : '';
                        const hasBranches = pharmacy.branches && pharmacy.branches.length > 0;
                        
                        html += `
                            <div class="hierarchy-item ${pharmacyClass}">
                                <div class="hierarchy-connector"></div>
                                <div class="hierarchy-node pharmacy-node" onclick="toggleChildren(this, event)">
                                    <span class="expand-toggle ${hasBranches ? '' : 'no-children'}">▼</span>
                                    <div class="node-icon"><i class="fa fa-hospital-o"></i></div>
                                    <div class="node-content">
                                        <div class="node-title">${pharmacy.name}</div>
                                        <div class="node-code">${pharmacy.code}</div>
                                    </div>
                                </div>
                                
                                <div class="hierarchy-children ${hasBranches ? '' : 'collapsed'}">
                        `;
                        
                        if (hasBranches) {
                            pharmacy.branches.forEach((branch, branchIndex) => {
                                const isLastBranch = branchIndex === pharmacy.branches.length - 1;
                                const branchClass = isLastBranch ? 'last' : '';
                                
                                html += `
                                    <div class="hierarchy-item ${branchClass}">
                                        <div class="hierarchy-connector"></div>
                                        <div class="hierarchy-node branch-node">
                                            <span class="expand-toggle no-children"></span>
                                            <div class="node-icon"><i class="fa fa-map-marker"></i></div>
                                            <div class="node-content">
                                                <div class="node-title">${branch.name}</div>
                                                <div class="node-code">${branch.code}</div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                        } else {
                            html += '<div class="no-data-message">No branches</div>';
                        }
                        
                        html += `
                                </div>
                            </div>
                        `;
                    });
                }
            });
        } else {
            html += '<p class="text-center text-muted" style="padding: 40px;"><?php echo lang("no_hierarchy_data"); ?></p>';
        }
        
        html += '</div>';
        $('#hierarchy-tree').html(html);
    }

    /**
     * Toggle children visibility and rotate expand arrow
     */
    function toggleChildren(element, event) {
        event.stopPropagation();
        
        const toggle = element.querySelector('.expand-toggle');
        if (toggle && toggle.classList.contains('no-children')) {
            return; // Don't toggle if no children
        }
        
        const childrenContainer = element.nextElementSibling;
        if (childrenContainer && childrenContainer.classList.contains('hierarchy-children')) {
            childrenContainer.classList.toggle('collapsed');
            if (toggle) {
                toggle.classList.toggle('collapsed');
            }
        }
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