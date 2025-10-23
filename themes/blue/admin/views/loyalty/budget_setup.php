<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa fa-cog"></i> <?= lang('Budget Setup'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="addBudget" class="tip btn btn-primary" title="Add New Budget">
                        <i class="fa fa-plus"></i> Add Budget
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext">Configure discount budgets at different organizational levels: Company, Pharmacy Group, Pharmacy, and Branch.</p>
                
                <!-- Budget Level Tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    <li class="active"><a href="#company" role="tab" data-toggle="tab">Company Level</a></li>
                    <li><a href="#pharmacy_group" role="tab" data-toggle="tab">Pharmacy Group</a></li>
                    <li><a href="#pharmacy" role="tab" data-toggle="tab">Pharmacy</a></li>
                    <li><a href="#branch" role="tab" data-toggle="tab">Branch</a></li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Company Level -->
                    <div class="tab-pane active" id="company">
                        <div class="table-responsive" style="margin-top: 20px;">
                            <table class="table table-bordered table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Company Name</th>
                                        <th>Budget Amount</th>
                                        <th>Allocated</th>
                                        <th>Spent</th>
                                        <th>Remaining</th>
                                        <th>Period</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Avenzur Healthcare</td>
                                        <td>SAR 500,000</td>
                                        <td>SAR 500,000</td>
                                        <td>SAR 325,000</td>
                                        <td>SAR 175,000</td>
                                        <td>2025</td>
                                        <td><span class="label label-warning">65% Used</span></td>
                                        <td>
                                            <a href="#" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i> View</a>
                                            <a href="#" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Edit</a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pharmacy Group Level -->
                    <div class="tab-pane" id="pharmacy_group">
                        <div class="table-responsive" style="margin-top: 20px;">
                            <table class="table table-bordered table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Group Name</th>
                                        <th>Budget Amount</th>
                                        <th>Allocated</th>
                                        <th>Spent</th>
                                        <th>Remaining</th>
                                        <th>Period</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Riyadh Region</td>
                                        <td>SAR 200,000</td>
                                        <td>SAR 200,000</td>
                                        <td>SAR 145,000</td>
                                        <td>SAR 55,000</td>
                                        <td>2025</td>
                                        <td><span class="label label-warning">72% Used</span></td>
                                        <td>
                                            <a href="#" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i> View</a>
                                            <a href="#" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Edit</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Jeddah Region</td>
                                        <td>SAR 150,000</td>
                                        <td>SAR 150,000</td>
                                        <td>SAR 105,000</td>
                                        <td>SAR 45,000</td>
                                        <td>2025</td>
                                        <td><span class="label label-warning">70% Used</span></td>
                                        <td>
                                            <a href="#" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i> View</a>
                                            <a href="#" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Edit</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Dammam Region</td>
                                        <td>SAR 150,000</td>
                                        <td>SAR 150,000</td>
                                        <td>SAR 75,000</td>
                                        <td>SAR 75,000</td>
                                        <td>2025</td>
                                        <td><span class="label label-success">50% Used</span></td>
                                        <td>
                                            <a href="#" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i> View</a>
                                            <a href="#" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Edit</a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pharmacy Level -->
                    <div class="tab-pane" id="pharmacy">
                        <div class="table-responsive" style="margin-top: 20px;">
                            <table class="table table-bordered table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Pharmacy Name</th>
                                        <th>Group</th>
                                        <th>Budget Amount</th>
                                        <th>Spent</th>
                                        <th>Remaining</th>
                                        <th>Period</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Main Pharmacy - Riyadh</td>
                                        <td>Riyadh Region</td>
                                        <td>SAR 100,000</td>
                                        <td>SAR 75,000</td>
                                        <td>SAR 25,000</td>
                                        <td>2025</td>
                                        <td><span class="label label-warning">75% Used</span></td>
                                        <td>
                                            <a href="#" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i> View</a>
                                            <a href="#" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Edit</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Central Pharmacy - Riyadh</td>
                                        <td>Riyadh Region</td>
                                        <td>SAR 100,000</td>
                                        <td>SAR 70,000</td>
                                        <td>SAR 30,000</td>
                                        <td>2025</td>
                                        <td><span class="label label-warning">70% Used</span></td>
                                        <td>
                                            <a href="#" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i> View</a>
                                            <a href="#" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Edit</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Main Pharmacy - Jeddah</td>
                                        <td>Jeddah Region</td>
                                        <td>SAR 80,000</td>
                                        <td>SAR 58,000</td>
                                        <td>SAR 22,000</td>
                                        <td>2025</td>
                                        <td><span class="label label-warning">72% Used</span></td>
                                        <td>
                                            <a href="#" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i> View</a>
                                            <a href="#" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Edit</a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Branch Level -->
                    <div class="tab-pane" id="branch">
                        <div class="table-responsive" style="margin-top: 20px;">
                            <table class="table table-bordered table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Branch Name</th>
                                        <th>Pharmacy</th>
                                        <th>Budget Amount</th>
                                        <th>Spent</th>
                                        <th>Remaining</th>
                                        <th>Period</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Riyadh Branch 1</td>
                                        <td>Main Pharmacy - Riyadh</td>
                                        <td>SAR 50,000</td>
                                        <td>SAR 38,000</td>
                                        <td>SAR 12,000</td>
                                        <td>2025</td>
                                        <td><span class="label label-warning">76% Used</span></td>
                                        <td>
                                            <a href="#" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i> View</a>
                                            <a href="#" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Edit</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Riyadh Branch 2</td>
                                        <td>Main Pharmacy - Riyadh</td>
                                        <td>SAR 50,000</td>
                                        <td>SAR 37,000</td>
                                        <td>SAR 13,000</td>
                                        <td>2025</td>
                                        <td><span class="label label-warning">74% Used</span></td>
                                        <td>
                                            <a href="#" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i> View</a>
                                            <a href="#" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Edit</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Jeddah Branch 1</td>
                                        <td>Main Pharmacy - Jeddah</td>
                                        <td>SAR 40,000</td>
                                        <td>SAR 28,000</td>
                                        <td>SAR 12,000</td>
                                        <td>2025</td>
                                        <td><span class="label label-warning">70% Used</span></td>
                                        <td>
                                            <a href="#" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i> View</a>
                                            <a href="#" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Edit</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Dammam Branch 1</td>
                                        <td>Main Pharmacy - Dammam</td>
                                        <td>SAR 40,000</td>
                                        <td>SAR 20,000</td>
                                        <td>SAR 20,000</td>
                                        <td>2025</td>
                                        <td><span class="label label-success">50% Used</span></td>
                                        <td>
                                            <a href="#" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i> View</a>
                                            <a href="#" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Edit</a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info" style="margin-top: 20px;">
                    <i class="fa fa-info-circle"></i> 
                    <strong>Note:</strong> This is a placeholder view showing budget hierarchy. Full budget configuration and allocation functionality will be implemented in the next phase.
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $('#addBudget').click(function(e) {
        e.preventDefault();
        alert('Add Budget Configuration functionality will be implemented here.');
    });

    // Handle view and edit buttons
    $('a[href="#"]').click(function(e) {
        if ($(this).find('i').hasClass('fa-eye') || $(this).find('i').hasClass('fa-edit')) {
            e.preventDefault();
            alert('This functionality will be implemented in the next phase.');
        }
    });
});
</script>
