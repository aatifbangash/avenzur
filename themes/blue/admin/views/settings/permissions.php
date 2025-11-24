<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .table td:first-child {
        font-weight: bold;
    }

    label {
        margin-right: 10px;
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-folder-open"></i><?= lang('group_permissions'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('set_permissions'); ?></p>

                <?php if (!empty($p)) {
    if ($p->group_id != 1) {
        echo admin_form_open('system_settings/permissions/' . $id); ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped reports-table">

                                <thead>
                                <tr>
                                    <th colspan="6"
                                        class="text-center"><?php echo $group->description . ' ( ' . $group->name . ' ) ' . $this->lang->line('group_permissions'); ?></th>
                                </tr>
                                <tr>
                                    <th rowspan="2" class="text-center"><?= lang('module_name'); ?>
                                    </th>
                                    <th colspan="5" class="text-center"><?= lang('permissions'); ?></th>
                                </tr>
                                <tr>
                                    <th class="text-center"><?= lang('view'); ?></th>
                                    <th class="text-center"><?= lang('add'); ?></th>
                                    <th class="text-center"><?= lang('edit'); ?></th>
                                    <th class="text-center"><?= lang('delete'); ?></th>
                                    <th class="text-center"><?= lang('misc'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td><?= lang('products'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="products-index" <?php echo $p->{'products-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="products-add" <?php echo $p->{'products-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="products-edit" <?php echo $p->{'products-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="products-delete" <?php echo $p->{'products-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="products-cost" class="checkbox" name="products-cost" <?php echo $p->{'products-cost'} ? 'checked' : ''; ?>>
                                            <label for="products-cost" class="padding05"><?= lang('product_cost') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="products-price" class="checkbox" name="products-price" <?php echo $p->{'products-price'} ? 'checked' : ''; ?>>
                                            <label for="products-price" class="padding05"><?= lang('product_price') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="products-adjustments" class="checkbox" name="products-adjustments" <?php echo $p->{'products-adjustments'} ? 'checked' : ''; ?>>
                                            <label for="products-adjustments" class="padding05"><?= lang('adjustments') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="products-barcode" class="checkbox" name="products-barcode" <?php echo $p->{'products-barcode'} ? 'checked' : ''; ?>>
                                            <label for="products-barcode" class="padding05"><?= lang('print_barcodes') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="products-stock_count" class="checkbox" name="products-stock_count" <?php echo $p->{'products-stock_count'} ? 'checked' : ''; ?>>
                                            <label for="products-stock_count" class="padding05"><?= lang('stock_counts') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('sales'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-index" <?php echo $p->{'sales-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-add" <?php echo $p->{'sales-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-edit" <?php echo $p->{'sales-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-delete" <?php echo $p->{'sales-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-email" class="checkbox" name="sales-email" <?php echo $p->{'sales-email'} ? 'checked' : ''; ?>>
                                            <label for="sales-email" class="padding05"><?= lang('email') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-pdf" class="checkbox" name="sales-pdf" <?php echo $p->{'sales-pdf'} ? 'checked' : ''; ?>>
                                            <label for="sales-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <?php if (POS) {
            ?>
                                            <input type="checkbox" value="1" id="pos-index" class="checkbox" name="pos-index" <?php echo $p->{'pos-index'} ? 'checked' : ''; ?>>
                                            <label for="pos-index" class="padding05"><?= lang('pos') ?></label>
                                            <?php
        } ?>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-payments" class="checkbox" name="sales-payments" <?php echo $p->{'sales-payments'} ? 'checked' : ''; ?>>
                                            <label for="sales-payments" class="padding05"><?= lang('payments') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-return_sales" class="checkbox" name="sales-return_sales" <?php echo $p->{'sales-return_sales'} ? 'checked' : ''; ?>>
                                            <label for="sales-return_sales" class="padding05"><?= lang('return_sales') ?></label>
                                        </span>
                                          <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-coordinator" class="checkbox" name="sales-coordinator" <?php echo $p->{'sales-coordinator'} ? 'checked' : ''; ?>>
                                            <label for="sales-coordinator" class="padding05"><?= lang('Sales Coordinator') ?></label>
                                        </span>



                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-warehouse_supervisor" class="checkbox" name="sales-warehouse_supervisor" <?php echo $p->{'sales-warehouse_supervisor'} ? 'checked' : ''; ?>>
                                            <label for="sales-warehouse_supervisor" class="padding05"><?= lang('Warehouse Supervisor') ?></label>
                                        </span>
                                        
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-warehouse_supervisor_shipping" class="checkbox" name="sales-warehouse_supervisor_shipping" <?php echo $p->{'sales-warehouse_supervisor_shipping'} ? 'checked' : ''; ?>>
                                            <label for="sales-warehouse_supervisor_shipping" class="padding05"><?= lang('Warehouse Supervisor Shipping') ?></label>
                                        </span>

                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-accountant" class="checkbox" name="sales-accountant" <?php echo $p->{'sales-accountant'} ? 'checked' : ''; ?>>
                                            <label for="sales-accountant" class="padding05"><?= lang('Accountant') ?></label>
                                        </span>

                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-quality_supervisor" class="quality_supervisor" name="sales-quality_supervisor" <?php echo $p->{'sales-quality_supervisor'} ? 'checked' : ''; ?>>
                                            <label for="sales-quality_supervisor" class="padding05"><?= lang('Quality Supervisor') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('deliveries'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-deliveries" <?php echo $p->{'sales-deliveries'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-add_delivery" <?php echo $p->{'sales-add_delivery'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-edit_delivery" <?php echo $p->{'sales-edit_delivery'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-delete_delivery" <?php echo $p->{'sales-delete_delivery'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-pdf" class="checkbox" name="sales-pdf_delivery" <?php echo $p->{'sales-pdf_delivery'} ? 'checked' : ''; ?>>
                                            <label for="sales-pdf_delivery" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                    </td>
                                </tr>
                                <!--<tr>
                                    <td><?= lang('gift_cards'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-gift_cards" <?php echo $p->{'sales-gift_cards'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-add_gift_card" <?php echo $p->{'sales-add_gift_card'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-edit_gift_card" <?php echo $p->{'sales-edit_gift_card'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-delete_gift_card" <?php echo $p->{'sales-delete_gift_card'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>

                                    </td>
                                </tr>-->

                                <tr>
                                    <td><?= lang('quotes'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="quotes-index" <?php echo $p->{'quotes-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="quotes-add" <?php echo $p->{'quotes-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="quotes-edit" <?php echo $p->{'quotes-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="quotes-delete" <?php echo $p->{'quotes-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="quotes-pdf" class="checkbox" name="quotes-pdf" <?php echo $p->{'quotes-pdf'} ? 'checked' : ''; ?>>
                                            <label for="quotes-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('Contract Deals'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="contract-deals-index" <?php echo $p->{'contract-deals-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="contract-deals-add" <?php echo $p->{'contract-deals-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="contract-deals-edit" <?php echo $p->{'contract-deals-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="contract-deals-delete" <?php echo $p->{'contract-deals-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="contract-deals-email" class="checkbox" name="contract-deals-email" <?php echo $p->{'contract-deals-email'} ? 'checked' : ''; ?>>
                                            <label for="contract-deals-email" class="padding05"><?= lang('email') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="contract-deals-pdf" class="checkbox" name="contract-deals-pdf" <?php echo $p->{'contract-deals-pdf'} ? 'checked' : ''; ?>>
                                            <label for="po-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('Purchase Requisition'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="pr-index" <?php echo $p->{'pr-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="pr-add" <?php echo $p->{'pr-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="pr-edit" <?php echo $p->{'pr-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="pr-delete" <?php echo $p->{'pr-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="pr-email" class="checkbox" name="pr-email" <?php echo $p->{'pr-email'} ? 'checked' : ''; ?>>
                                            <label for="pr-email" class="padding05"><?= lang('email') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="pr-pdf" class="checkbox" name="pr-pdf" <?php echo $p->{'pr-pdf'} ? 'checked' : ''; ?>>
                                            <label for="pr-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('Purchase Order'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="po-index" <?php echo $p->{'po-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="po-add" <?php echo $p->{'po-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="po-edit" <?php echo $p->{'po-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="po-delete" <?php echo $p->{'po-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="po-email" class="checkbox" name="po-email" <?php echo $p->{'po-email'} ? 'checked' : ''; ?>>
                                            <label for="po-email" class="padding05"><?= lang('email') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="po-pdf" class="checkbox" name="po-pdf" <?php echo $p->{'po-pdf'} ? 'checked' : ''; ?>>
                                            <label for="po-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('purchases'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="purchases-index" <?php echo $p->{'purchases-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="purchases-add" <?php echo $p->{'purchases-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="purchases-edit" <?php echo $p->{'purchases-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="purchases-delete" <?php echo $p->{'purchases-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="purchases-email" class="checkbox" name="purchases-email" <?php echo $p->{'purchases-email'} ? 'checked' : ''; ?>>
                                            <label for="purchases-email" class="padding05"><?= lang('email') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="purchases-pdf" class="checkbox" name="purchases-pdf" <?php echo $p->{'purchases-pdf'} ? 'checked' : ''; ?>>
                                            <label for="purchases-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="purchases-payments" class="checkbox" name="purchases-payments" <?php echo $p->{'purchases-payments'} ? 'checked' : ''; ?>>
                                            <label for="purchases-payments" class="padding05"><?= lang('payments') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="purchases-expenses" class="checkbox" name="purchases-expenses" <?php echo $p->{'purchases-expenses'} ? 'checked' : ''; ?>>
                                            <label for="purchases-expenses" class="padding05"><?= lang('expenses') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="purchases-return_purchases" class="checkbox" name="purchases-return_purchases" <?php echo $p->{'purchases-return_purchases'} ? 'checked' : ''; ?>>
                                            <label for="purchases-return_purchases" class="padding05"><?= lang('return_purchases') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="purchase_supervisor" class="checkbox" name="purchase_supervisor" <?php echo $p->{'purchase_supervisor'} ? 'checked' : ''; ?>>
                                            <label for="purchase_supervisor" class="padding05"><?= lang('Purchase Supervisor') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="purchase_manager" class="checkbox" name="purchase_manager" <?php echo $p->{'purchase_manager'} ? 'checked' : ''; ?>>
                                            <label for="purchase_manager" class="padding05"><?= lang('Purchase Manager') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="receiving_supervisor" class="checkbox" name="purchase_receiving_supervisor" <?php echo $p->{'purchase_receiving_supervisor'} ? 'checked' : ''; ?>>
                                            <label for="receiving_supervisor" class="padding05"><?= lang('Receiving Supervisor') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="warehouse_supervisor" class="checkbox" name="purchase_warehouse_supervisor" <?php echo $p->{'purchase_warehouse_supervisor'} ? 'checked' : ''; ?>>
                                            <label for="warehouse_supervisor" class="padding05"><?= lang('Warehouse Supervisor') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('transfers'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="transfers-index" <?php echo $p->{'transfers-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="transfers-add" <?php echo $p->{'transfers-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="transfers-edit" <?php echo $p->{'transfers-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="transfers-delete" <?php echo $p->{'transfers-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="transfers-email" class="checkbox" name="transfers-email" <?php echo $p->{'transfers-email'} ? 'checked' : ''; ?>>
                                            <label for="transfers-email" class="padding05"><?= lang('email') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="transfers-pdf" class="checkbox" name="transfers-pdf" <?php echo $p->{'transfers-pdf'} ? 'checked' : ''; ?>>
                                            <label for="transfers-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>

                                        <span style="display:inline-block;">
                                        <input type="checkbox" value="1" class="checkbox" id="transfer_pharmacist"
                                        name="transfer_pharmacist" <?php echo $p->transfer_pharmacist ? 'checked' : ''; ?>>
                                        <label for="transfer_pharmacist" class="padding05"><?= lang('Pharmacist') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                        <input type="checkbox" value="1" class="checkbox" id="transfer_warehouse_supervisor"
                                        name="transfer_warehouse_supervisor" <?php echo $p->transfer_warehouse_supervisor ? 'checked' : ''; ?>>
                                        <label for="transfer_warehouse_supervisor" class="padding05"><?= lang('Warehouse Supervisor') ?></label>
                                        </span>

                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('Customer Returns'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="returns-index" <?php echo $p->{'returns-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="returns-add" <?php echo $p->{'returns-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="returns-edit" <?php echo $p->{'returns-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="returns-delete" <?php echo $p->{'returns-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="returns-email" class="checkbox" name="returns-email" <?php echo $p->{'returns-email'} ? 'checked' : ''; ?>>
                                            <label for="returns-email" class="padding05"><?= lang('email') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="returns-pdf" class="checkbox" name="returns-pdf" <?php echo $p->{'returns-pdf'} ? 'checked' : ''; ?>>
                                            <label for="returns-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('Supplier Returns'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="supplier-returns-index" <?php echo $p->{'supplier-returns-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="supplier-returns-add" <?php echo $p->{'supplier-returns-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="supplier-returns-edit" <?php echo $p->{'supplier-returns-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="supplier-returns-delete" <?php echo $p->{'supplier-returns-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="supplier-returns-email" class="checkbox" name="supplier-returns-email" <?php echo $p->{'supplier-returns-email'} ? 'checked' : ''; ?>>
                                            <label for="supplier-returns-email" class="padding05"><?= lang('email') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="supplier-returns-pdf" class="checkbox" name="supplier-returns-pdf" <?php echo $p->{'supplier-returns-pdf'} ? 'checked' : ''; ?>>
                                            <label for="supplier-returns-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('customers'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="customers-index" <?php echo $p->{'customers-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="customers-add" <?php echo $p->{'customers-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="customers-edit" <?php echo $p->{'customers-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="customers-delete" <?php echo $p->{'customers-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="customers-deposits" class="checkbox" name="customers-deposits" <?php echo $p->{'customers-deposits'} ? 'checked' : ''; ?>>
                                            <label for="customers-deposits" class="padding05"><?= lang('deposits') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="customers-delete_deposit" class="checkbox" name="customers-delete_deposit" <?php echo $p->{'customers-delete_deposit'} ? 'checked' : ''; ?>>
                                            <label for="customers-delete_deposit" class="padding05"><?= lang('delete_deposit') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                 <tr>
                                    <td><?= lang('Customer Payment'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="customer-payment-index" <?php echo $p->{'customer-payment-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="customer-payment-add" <?php echo $p->{'customer-payment-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="customer-payment-edit" <?php echo $p->{'customer-payment-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="customer-payment-delete" <?php echo $p->{'customer-payment-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="customer-payment-email" class="checkbox" name="customer-payment-email" <?php echo $p->{'customer-payment-email'} ? 'checked' : ''; ?>>
                                            <label for="customer-payment-email" class="padding05"><?= lang('email') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="customer-payment-pdf" class="checkbox" name="customer-payment-pdf" <?php echo $p->{'customer-payment-pdf'} ? 'checked' : ''; ?>>
                                            <label for="customer-payment-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('suppliers'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="suppliers-index" <?php echo $p->{'suppliers-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="suppliers-add" <?php echo $p->{'suppliers-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="suppliers-edit" <?php echo $p->{'suppliers-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="suppliers-delete" <?php echo $p->{'suppliers-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('Supplier Payment'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="supplier-payment-index" <?php echo $p->{'supplier-payment-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="supplier-payment-add" <?php echo $p->{'supplier-payment-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="supplier-payment-edit" <?php echo $p->{'supplier-payment-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="supplier-payment-delete" <?php echo $p->{'supplier-payment-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="supplier-payment-email" class="checkbox" name="supplier-payment-email" <?php echo $p->{'supplier-payment-email'} ? 'checked' : ''; ?>>
                                            <label for="supplier-payment-email" class="padding05"><?= lang('email') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="supplier-payment-pdf" class="checkbox" name="supplier-payment-pdf" <?php echo $p->{'supplier-payment-pdf'} ? 'checked' : ''; ?>>
                                            <label for="supplier-payment-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('reports'); ?></td>
                                    <td colspan="5">
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="report-stock" name="report-stock" <?php echo $p->{'report-stock'} ? 'checked' : ''; ?>>
                                            <label for="report-stock" class="padding05"><?= lang('Stock Report') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-item-movement" name="reports-item-movement" <?php echo $p->{'reports-item-movement'} ? 'checked' : ''; ?>>
                                            <label for="reports-item-movement" class="padding05"><?= lang('Item Movement Report') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-revenue"
                                            name="reports-revenue" <?php echo $p->{'reports-revenue'} ? 'checked' : ''; ?>><label for="reports-revenue" class="padding05"><?= lang('Revenue Report') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-purchase" name="reports-purchase" <?php echo $p->{'reports-purchase'} ? 'checked' : ''; ?>>
                                            <label for="daily_sales" class="padding05"><?= lang('Purchase Report') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-transfer" name="reports-transfer" <?php echo $p->{'reports-transfer'} ? 'checked' : ''; ?>>
                                            <label for="reports-transfer" class="padding05"><?= lang('Transfer Report') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-inventory-tb" name="reports-inventory-tb" <?php echo $p->{'reports-inventory-tb'} ? 'checked' : ''; ?>>
                                            <label for="reports-inventory-tb" class="padding05"><?= lang('Inventory TB') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-customer-tb" name="reports-customer-tb" <?php echo $p->{'reports-customer-tb'} ? 'checked' : ''; ?>>
                                            <label for="reports-customer-tb" class="padding05"><?= lang('Customer TB') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-customer-statement" name="reports-customer-statement" <?php echo $p->{'reports-customer-statement'} ? 'checked' : ''; ?>>
                                            <label for="reports-customer-statement" class="padding05"><?= lang('Customer Statement') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-customer-aging" name="reports-customer-aging" <?php echo $p->{'reports-customer-aging'} ? 'checked' : ''; ?>>
                                            <label for="reports-customer-aging" class="padding05"><?= lang('Customer Aging') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-supplier-tb" name="reports-supplier-tb" <?php echo $p->{'reports-supplier-tb'} ? 'checked' : ''; ?>>
                                            <label for="reports-supplier-tb" class="padding05"><?= lang('Supplier TB') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-supplier-statement" name="reports-supplier-statement" <?php echo $p->{'reports-supplier-statement'} ? 'checked' : ''; ?>>
                                            <label for="reports-supplier-statement" class="padding05"><?= lang('Supplier Statement') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-supplier-aging" name="reports-supplier-aging" <?php echo $p->{'reports-supplier-aging'} ? 'checked' : ''; ?>>
                                            <label for="reports-supplier-aging" class="padding05"><?= lang('Supplier Aging') ?></label>
                                        </span>
                                        
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('misc'); ?></td>
                                    <td colspan="5">
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="bulk_actions"
                                            name="bulk_actions" <?php echo $p->bulk_actions ? 'checked' : ''; ?>>
                                            <label for="bulk_actions" class="padding05"><?= lang('bulk_actions') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="edit_price"
                                            name="edit_price" <?php echo $p->edit_price ? 'checked' : ''; ?>>
                                            <label for="edit_price" class="padding05"><?= lang('edit_price_on_sale') ?></label>
                                        </span>
                                    </td>
                                </tr>
                                <!--<tr>
                                    <td><?= lang('Stock Requests'); ?></td>
                                     <td colspan="5">
                                    <span style="display:inline-block;">
                                        <input type="checkbox" value="1" class="checkbox" id="bulk_actions"
                                            name="stock_request_view" <?php echo $p->stock_request_view ? 'checked' : ''; ?>>
                                            <label for="bulk_actions" class="padding05"><?= lang('View') ?></label>
                                    </span>
                                    <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="edit_price"
                                            name="stock_request_approval" <?php echo $p->stock_request_approval ? 'checked' : ''; ?>>
                                            <label for="edit_price" class="padding05"><?= lang('Approval') ?></label>
                                    </span>
                                    <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="stock_pharmacist"
                                            name="stock_pharmacist" <?php echo $p->stock_pharmacist ? 'checked' : ''; ?>>
                                            <label for="stock_pharmacist" class="padding05"><?= lang('Pharmacist') ?></label>
                                    </span>
                                    <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="stock_warehouse_supervisor"
                                            name="stock_warehouse_supervisor" <?php echo $p->stock_warehouse_supervisor ? 'checked' : ''; ?>>
                                            <label for="stock_warehouse_supervisor" class="padding05"><?= lang('Warehouse Supervisor') ?></label>
                                    </span>
                                     </td>
                                </tr>-->
                                <tr>
                                    <td><?= lang('Truck Registration'); ?></td>
                                     <td colspan="5">
                                    <span style="display:inline-block;">
                                        <input type="checkbox" value="1" class="checkbox" id="bulk_actions"
                                            name="truck_registration_view" <?php echo $p->truck_registration_view ? 'checked' : ''; ?>>
                                            <label for="bulk_actions" class="padding05"><?= lang('View') ?></label>
                                    </span>
                                     </td>
                                </tr>

                                <!--<tr>
                                    <td><?= lang('Accountant'); ?></td>
                                     <td colspan="5">
                                    <span style="display:inline-block;">
                                        <input type="checkbox" value="1" class="checkbox" id="bulk_actions"
                                            name="accountant" <?php echo $p->accountant ? 'checked' : ''; ?>>
                                            <label for="bulk_actions" class="padding05"><?= lang('Accounts') ?></label>
                                    </span>
                                     </td>
                                </tr>-->

                                </tbody>
                            </table>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary"><?=lang('update')?></button>
                        </div>
                        <?php echo form_close();
    } else {
        echo $this->lang->line('group_x_allowed');
    }
} else {
    echo $this->lang->line('group_x_allowed');
} ?>


            </div>
        </div>
    </div>
</div>
