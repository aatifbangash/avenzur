<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript">
    var count = 1, an = 1, po_edit = false, product_variant = 0, DT = <?= $Settings->default_tax_rate ?>, DC = '<?= $default_currency->code ?>', shipping = 0,
        product_tax = 0, invoice_tax = 0, total_discount = 0, total = 0,
        tax_rates = <?php echo json_encode($tax_rates); ?>, poitems = {},
         
    $(document).ready(function () {
        <?php if ($this->input->get('supplier')) { ?>
        if (!localStorage.getItem('poitems')) {
            localStorage.setItem('posupplier', <?=$this->input->get('supplier'); ?>);
        }
        <?php
    } ?>
    <?php if ($Owner || $Admin) {
        ?>
        if (!localStorage.getItem('podate')) {
            $("#podate").datetimepicker({
                format: site.dateFormats.js_ldate,
                fontAwesome: true,
                language: 'sma',
                weekStart: 1,
                todayBtn: 1,
                autoclose: 1,
                todayHighlight: 1,
                startView: 2,
                forceParse: 0
            }).datetimepicker('update', new Date());
        }
        $(document).on('change', '#podate', function (e) {
            localStorage.setItem('podate', $(this).val());
        });
        if (podate = localStorage.getItem('podate')) {
            $('#podate').val(podate);
        }
        <?php
    } ?>
        $('#extras').on('ifChecked', function () {
            $('#extras-con').slideDown();
        });
        $('#extras').on('ifUnchecked', function () {
            $('#extras-con').slideUp();
        });
    });
</script>

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true" id="close_button"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_purchase_by_csv'); ?></h4>
        </div>

        <?php $attrib = ['role' => 'form'];
            echo admin_form_open_multipart('purchases/mapPurchases', $attrib); 
            // echo admin_form_open_multipart('purchases/purchase_by_csv', $attrib); 
        ?>

        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="form-group">
                <?= lang('date *', 'podate'); ?>
                <?php echo form_input('date', ($_POST['date'] ?? date($dateFormats['php_ldate'], now())), 'class="form-control input-tip datetime" id="podate" required="required"'); ?>
             </div>

            <div class="form-group">
                <?php echo lang('Warehouse *', 'powarehouse2'); ?>
                <div class="controls">
                    <?php
                        $wh[''] = '';
                        foreach ($warehouses as $warehouse) {
                            $wh[$warehouse->id] = $warehouse->name;
                        }
                        
                        echo form_dropdown('warehouse', $wh, ($_POST['warehouse'] ?? $Settings->default_warehouse), 'id="powarehouse2" class="form-control input-tip select" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('warehouse') . '" required="required" style="width:100%;" ');
                    ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo lang('supplier *', 'posupplier'); ?>
                <div class="controls">
                    <?php
                        $su[''] = '';
                        foreach ($suppliers as $supplier) {
                            $su[$supplier->id] = $supplier->name;
                        }
                        
                        echo form_dropdown('supplier', $su, ($_POST['supplier'] ?? ''), 'id="posupplier" class="form-control input-tip select" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('supplier') . '" required="required" style="width:100%;" ');
                    ?>
                </div>
            </div>

            <!-- <div class="form-group">
                <?= lang('supplier', 'posupplier'); ?>
                <div class="input-group">
                    <input type="text" name="supplier" value="" id="posupplier" class="form-control" style="width:100%;" placeholder="<?= lang('select') . ' ' . lang('supplier') ?>">
                    <input type="hidden" name="supplier_id" value="" id="supplier_id" class="form-control">

                    <div class="input-group-addon no-print" style="padding: 2px 5px; border-left: 0;">
                        <a href="<?= admin_url('suppliers/add'); ?>" id="add-supplier" class="external" data-toggle="modal" data-target="#myModal">
                            <i class="fa fa-2x fa-plus-circle" id="addIcon"></i>
                        </a>
                    </div>
                </div>
            </div> -->



            <!-- <div class="form-group">
                                            <?= lang('Parent Supplier', 'posupplier'); ?>
                                            <?php if ($Owner || $Admin || $GP['suppliers-add'] || $GP['suppliers-index']) {
                                                ?><div class="input-group"><?php
                                            } ?>
                                                <input type="hidden" name="supplier" value="" id="posupplier"
                                                       class="form-control" style="width:100%;"
                                                       placeholder="<?= lang('select') . ' ' . lang('supplier') ?>">
                                                <input type="hidden" name="supplier_id" value="" id="supplier_id"
                                                       class="form-control">
                                                <?php if ($Owner || $Admin || $GP['suppliers-index']) {
                                                ?>
                                                    <div class="input-group-addon no-print" style="padding: 2px 5px; border-left: 0;">
                                                        <a href="#" id="view-supplier" class="external" data-toggle="modal" data-target="#myModal">
                                                            <i class="fa fa-2x fa-user" id="addIcon"></i>
                                                        </a>
                                                    </div>
                                                <?php
                                                } ?>
                                                <?php if ($Owner || $Admin || $GP['suppliers-add']) {
                                                ?>
                                                <div class="input-group-addon no-print" style="padding: 2px 5px;">
                                                    <a href="<?= admin_url('suppliers/add'); ?>" id="add-supplier" class="external" data-toggle="modal" data-target="#myModal">
                                                        <i class="fa fa-2x fa-plus-circle" id="addIcon"></i>
                                                    </a>
                                                </div>
                                                <?php
                                                } ?>
                                                <?php if ($Owner || $Admin || $GP['suppliers-add'] || $GP['suppliers-index']) {
                                                ?></div><?php
                                                } ?>
                                        </div> -->

            
            <div class="form-group">
                <?= lang('attachments', 'document') ?>
                <input id="attachment" type="file" data-browse-label="<?= lang('browse'); ?>" name="attachment" data-show-upload="false" data-show-preview="false" class="form-control file">
            </div>
              
            <div class="form-group">
                <?= lang('csv_file', 'csv_file') ?>
                <input id="csv_file" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" required="required"
                        data-show-upload="false" data-show-preview="false" class="form-control file">
            </div>

            <div class="clearfix"></div>
            <div class="modal-footer">
            <!-- <div class="col-md-12"> -->
                            <?php
                            $data = array(
                                'name' => 'add_pruchase',
                                'onclick'=>"return confirm('Are you sure to proceed?')"
                            );
                            ?>
                            <div
                                class="from-group"><?php echo form_submit($data, $this->lang->line('submit'), 'id="add_pruchase" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                            </div>
                        <!-- </div> -->
                <!-- <?php echo form_submit('submit_map_notification', lang('Submit'), 'class="btn btn-primary"'); ?> -->
            <!-- <div class="btn  btn-primary" id="submitAcceptDispatch" >Upload</div> -->
            </div>
        </div>     
    </div>
    <?php echo form_close(); ?>
</div>