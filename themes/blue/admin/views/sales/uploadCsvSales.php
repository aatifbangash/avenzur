<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript">
    var count = 1, an = 1, product_variant = 0, DT = <?= $Settings->default_tax_rate ?>,
        product_tax = 0, invoice_tax = 0, total_discount = 0, total = 0,
        tax_rates = <?php echo json_encode($tax_rates); ?>;

    $(document).ready(function () {
        <?php if ($Owner || $Admin) { ?>
            if (!localStorage.getItem('sldate')) {
                $("#sldate").datetimepicker({
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
            $(document).on('change', '#sldate', function (e) {
                localStorage.setItem('sldate', $(this).val());
            });
            if (podate = localStorage.getItem('sldate')) {
                $('#sldate').val(podate);
            }
            $(document).on('change', '#slbiller', function (e) {
            localStorage.setItem('slbiller', $(this).val());
            });
            if (slbiller = localStorage.getItem('slbiller')) {
                $('#slbiller').val(slbiller);
            }
            <?php
        } ?>
        
        if (!localStorage.getItem('slref')) {
            localStorage.setItem('slref', '<?=$slnumber?>');
        }
    });
</script>

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true" id="close_button"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_sale_by_csv'); ?></h4>
        </div>

        <?php $attrib = ['role' => 'form'];
        // MARK: redirection 
            echo admin_form_open_multipart('sales/mapSales', $attrib); 
        ?>

        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="form-group">
                <?= lang('Date *', 'sldate'); ?>
                <?php echo form_input('date', ($_POST['date'] ?? date($dateFormats['php_ldate'], now())), 'class="form-control input-tip datetime" id="sldate" required="required"'); ?>
            </div>

            <?php if ($Owner || $Admin || !$this->session->userdata('biller_id')) { ?>
                <div class="form-group">
                    <?= lang('Biller *', 'slbiller2'); ?>
                    <?php 
                        $bl[''] = '';
                        foreach ($billers as $biller) {
                            $bl[$biller->id] = $biller->company && $biller->company != '-' ? $biller->company : $biller->name;
                        }

                        echo form_dropdown('biller', $bl, ($_POST['biller'] ?? $Settings->default_biller), 'id="slbiller2" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('biller') . '" required="required" class="form-control input-tip select" style="width:100%;"');
                    ?>
                </div>
                <?php
            } else {
                $biller_input = [
                    'type'  => 'hidden',
                    'name'  => 'biller',
                    'id'    => 'slbiller2',
                    'value' => $this->session->userdata('biller_id'),
                ];

                echo form_input($biller_input);
            } ?>

            <?php if (!$Settings->restrict_user || $Owner || $Admin) { ?>
                <div class="form-group">
                    <?php echo lang('Warehouse *', 'slwarehouse2'); ?>
                    <div class="controls">
                        <?php
                            $wh[''] = '';
                            foreach ($warehouses as $warehouse) {
                                $wh[$warehouse->id] = $warehouse->name;
                            }
                            
                            echo form_dropdown('warehouse', $wh, ($_POST['warehouse'] ?? $Settings->default_warehouse), 'id="slwarehouse2" class="form-control input-tip select" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('warehouse') . '" required="required" style="width:100%;" ');
                        ?>
                    </div>
                </div>
                <?php
            } else {
                $warehouse_input = [
                    'type'  => 'hidden',
                    'name'  => 'warehouse',
                    'id'    => 'slwarehouse2',
                    'value' => $this->session->userdata('warehouse_id'),
                ];

                echo form_input($warehouse_input);
            } ?>

            <div class="form-group">
                <?php echo lang('Customer *', 'slcustomer2'); ?>
                <div class="controls">
                    <?php
                        $cu[''] = '';
                        foreach ($customers as $customer) {
                            $cu[$customer->id] = $customer->name;
                        }

                        echo form_dropdown('customer', $cu,($_POST['customer'] ?? ''), 'id="slcustomer2" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('customer') . '" required="required" class="form-control input-tip" style="width:100%;"');
                    ?>
                </div>
            </div>
              
            <div class="form-group">
                <?= lang('csv_file', 'csv_file') ?>
                <input id="csv_file" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" required="required"
                        data-show-upload="false" data-show-preview="false" class="form-control file">
            </div>

            <div class="clearfix"></div>

            <div class="modal-footer">
                <?php
                    $data = array(
                        'name' => 'add_sale',
                        'onclick'=>"return confirm('Are you sure to proceed?')"
                    );
                ?>
                <div
                    class="from-group"><?php echo form_submit($data, $this->lang->line('submit'), 'id="add_sale" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                </div>
            </div>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>