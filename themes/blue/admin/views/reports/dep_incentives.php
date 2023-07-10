<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-heart"></i><?= lang('Departmental_Incentives_Report'); ?> <?php
            if ($this->input->post('start_date')) {
                echo 'From ' . $this->input->post('start_date') . ' to ' . $this->input->post('end_date');
            }
            ?>
        </h2>

    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('customize_report'); ?></p>
                <div>
                    <?php
                    $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                    echo admin_form_open_multipart('reports/departmental_incentive', $attrib);
                    ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('start_date', 'start_date'); ?>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control datetime" id="start_date"'); ?>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang('end_date', 'end_date'); ?>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control datetime" id="end_date"'); ?>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang('created_by'); ?></label>
                                <?php
                                $us[''] = lang('select') . ' ' . lang('user');
                                if (!empty($users) && sizeof($users) > 0) {
                                    foreach ($users as $user) {
                                        $us[$user->id] = $user->first_name . ' ' . $user->last_name;
                                    }
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ''), 'class="form-control" id="user" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('user') . '"');

                                ?>
                            </div>
                        </div>

                    </div>
                    <div class="form-group">
                        <div
                        <button class="btn btn-primary" name="submit" type="submit">Submit</button>
                    </div>
                </div>
                <?php echo form_close(); ?>

            </div>
            <div class="clearfix"></div>
            <?php
            # Setting categoryId as a key and name as a value for the categoryList
            $categoriesList = [];
            if (!empty($categories) && sizeof($categories)) {
                foreach ($categories as $idx => $cat) {
                    $categoriesList[$cat->id] = $cat->name;
                }
            }

            # Group the products by category. CategoryId will be the Key for the Array and all related products to that category will be the values.
            $productsByCategory = [];
            if (!empty($products) && sizeof($products) > 0) {
                foreach ($products as $idx => $product) {
                    $productsByCategory[$product->category_id][] = $product;
                }
            }

            if (!empty($productsByCategory)) {
                foreach ($productsByCategory as $categoryId => $categoryProducts) {
                    ?>
                    <div class="table-responsive">
                        <table id="" cellpadding="0" cellspacing="0" border="0"
                               class="table table-bordered table-hover table-striped ">
                            <thead>
                            <h3><?= ($categoriesList[$categoryId] ?? $categoryId) ?></h3>
                            <tr style="text-align:center;">
                                <th><?= lang('Item code'); ?></th>
                                <th><?= lang('Item name'); ?></th>
                                <th><?= lang('Incentive Qty'); ?></th>
                                <th><?= lang('Incentive value'); ?></th>
                                <th><?= lang('Total Quantity'); ?></th>
                                <th><?= lang('Last selling price'); ?></th>
                                <th><?= lang('Incentive selling value'); ?></th>
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                            <?php
                            foreach ($categoryProducts as $product) {
                                $values = $product->incentive_value;
                                $getLast = $values[strlen($values) - 1];
                                if ($product->incentive_value <= round($product->total_quantity)) {
                                    if ($getLast == "%") {
                                        $removeLast = rtrim($values, "%");
                                        $incentive = ($removeLast / 100) * ($product->total_price);
                                        $incentivePrice = round($incentive);
                                    } else {
                                        $incentive = ($product->incentive_value) / ($product->incentive_qty);
                                        $incentivePrice = round(($incentive) * ($product->total_quantity));
                                    }
                                } else {
                                    $incentivePrice = 0;
                                }
                                ?>
                                <tr>
                                    <td><?= $product->code ?></td>
                                    <td><?= $product->name ?></td>
                                    <td><?= $product->incentive_qty ?></td>
                                    <td><?= $product->incentive_value ?></td>
                                    <td><?= $product->total_quantity ?></td>
                                    <td><?= $product->total_price ?></td>
                                    <td><?= $incentivePrice ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </div>
</div>

