<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa fa-list-alt"></i> <?= lang('Loyalty Rules'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="addRule" class="tip btn btn-primary" title="Add New Rule">
                        <i class="fa fa-plus"></i> Add Rule
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext">Manage loyalty program rules and discount configurations.</p>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Rule ID</th>
                                <th>Rule Name</th>
                                <th>Type</th>
                                <th>Discount Value</th>
                                <th>Conditions</th>
                                <th>Status</th>
                                <th>Valid From</th>
                                <th>Valid To</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>LR-001</td>
                                <td>Silver Member Discount</td>
                                <td><span class="label label-info">Percentage</span></td>
                                <td>5%</td>
                                <td>Min Purchase: SAR 200</td>
                                <td><span class="label label-success">Active</span></td>
                                <td>2025-01-01</td>
                                <td>2025-12-31</td>
                                <td>
                                    <a href="#" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Edit</a>
                                    <a href="#" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> Delete</a>
                                </td>
                            </tr>
                            <tr>
                                <td>LR-002</td>
                                <td>Gold Member Discount</td>
                                <td><span class="label label-info">Percentage</span></td>
                                <td>10%</td>
                                <td>Min Purchase: SAR 500</td>
                                <td><span class="label label-success">Active</span></td>
                                <td>2025-01-01</td>
                                <td>2025-12-31</td>
                                <td>
                                    <a href="#" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Edit</a>
                                    <a href="#" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> Delete</a>
                                </td>
                            </tr>
                            <tr>
                                <td>LR-003</td>
                                <td>Platinum Member Discount</td>
                                <td><span class="label label-info">Percentage</span></td>
                                <td>15%</td>
                                <td>Min Purchase: SAR 1000</td>
                                <td><span class="label label-success">Active</span></td>
                                <td>2025-01-01</td>
                                <td>2025-12-31</td>
                                <td>
                                    <a href="#" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Edit</a>
                                    <a href="#" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> Delete</a>
                                </td>
                            </tr>
                            <tr>
                                <td>LR-004</td>
                                <td>Seasonal Promotion</td>
                                <td><span class="label label-warning">Fixed Amount</span></td>
                                <td>SAR 50</td>
                                <td>Category: All</td>
                                <td><span class="label label-success">Active</span></td>
                                <td>2025-10-01</td>
                                <td>2025-10-31</td>
                                <td>
                                    <a href="#" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Edit</a>
                                    <a href="#" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> Delete</a>
                                </td>
                            </tr>
                            <tr>
                                <td>LR-005</td>
                                <td>Buy 2 Get 1 Free</td>
                                <td><span class="label label-primary">BOGO</span></td>
                                <td>33% Off</td>
                                <td>Selected Products</td>
                                <td><span class="label label-danger">Inactive</span></td>
                                <td>2025-11-01</td>
                                <td>2025-11-30</td>
                                <td>
                                    <a href="#" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Edit</a>
                                    <a href="#" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> Delete</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> 
                    <strong>Note:</strong> This is a placeholder view. Full CRUD functionality for loyalty rules will be implemented in the next phase.
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $('#addRule').click(function(e) {
        e.preventDefault();
        alert('Add Loyalty Rule functionality will be implemented here.');
    });
});
</script>
