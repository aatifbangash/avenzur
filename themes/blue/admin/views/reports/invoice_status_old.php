<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<script>
    $(document).ready(function () {
        oTable = $('#InvStatusData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 
            'bServerSide': true,
            'sAjaxSource': '<?= admin_url('reports/getInvoiceStatusReport/?v=1') ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                <?php if ($this->input->post('start_date')) { ?>
                aoData.push({"name": "start_date", "value": "<?= $this->input->post('start_date') ?>"});
                aoData.push({"name": "end_date", "value": "<?= $this->input->post('end_date') ?>"});
                <?php } ?>
                <?php if ($this->input->post('invoice_id')) { ?>
                aoData.push({"name": "invoice_id", "value": "<?= $this->input->post('invoice_id') ?>"});
                <?php } ?>
                <?php if ($this->input->post('customer')) { ?>
                aoData.push({"name": "customer", "value": "<?= $this->input->post('customer') ?>"});
                <?php } ?>
                <?php if ($this->input->post('salesman')) { ?>
                aoData.push({"name": "salesman", "value": "<?= $this->input->post('salesman') ?>"});
                <?php } ?>
                <?php if ($this->input->post('warehouse')) { ?>
                aoData.push({"name": "warehouse", "value": "<?= $this->input->post('warehouse') ?>"});
                <?php } ?>
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            "aoColumns": [
                null, // Date
                null, // Invoice
                null, // Area
                null, // Sales Man
                null, // Customer No
                null, // Customer Name
                {"mRender": currencyFormat}, // Invoice Total
                {"mRender": currencyFormat}, // Return
                {"mRender": currencyFormat}, // Discount
                {"mRender": currencyFormat}, // Paid
                {"mRender": currencyFormat}  // Outstanding
            ],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
                var invoice_total = 0, return_total = 0, discount_total = 0, paid_total = 0, outstanding_total = 0;
                for (var i = 0; i < aaData.length; i++) {
                    invoice_total += parseFloat(aaData[aiDisplay[i]][6]);
                    return_total += parseFloat(aaData[aiDisplay[i]][7]);
                    discount_total += parseFloat(aaData[aiDisplay[i]][8]);
                    paid_total += parseFloat(aaData[aiDisplay[i]][9]);
                    outstanding_total += parseFloat(aaData[aiDisplay[i]][10]);
                }
                var nCells = nRow.getElementsByTagName('th');
                nCells[6].innerHTML = currencyFormat(parseFloat(invoice_total));
                nCells[7].innerHTML = currencyFormat(parseFloat(return_total));
                nCells[8].innerHTML = currencyFormat(parseFloat(discount_total));
                nCells[9].innerHTML = currencyFormat(parseFloat(paid_total));
                nCells[10].innerHTML = currencyFormat(parseFloat(outstanding_total));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label: "[<?=lang('date');?> (dd-mmm-yy)]", filter_type: "text", data: []},
            {column_number: 1, filter_default_label: "[<?=lang('invoice');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('area');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('sales_man');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('customer_no');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('customer_name');?>]", filter_type: "text", data: []},
        ], "footer");
    });
</script>

<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
        
        $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file-text"></i><?= lang('invoice_status_report'); ?> 
            <?php if ($this->input->post('start_date')) {
                echo ' From ' . $this->input->post('start_date') . ' to ' . $this->input->post('end_date');
            } ?>
        </h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang('warehouses') ?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu">
                        <li><a href="<?= admin_url('reports/invoice_status') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                        <li class="divider"></li>
                        <?php
                        foreach ($warehouses as $warehouse) {
                            echo '<li><a href="' . admin_url('reports/invoice_status/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                        }
                        ?>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('list_results'); ?></p>

                <div id="form">

                    <?php echo form_open('reports/invoice_status'); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="start_date"><?= lang('start_date'); ?></label>
                                <?php echo form_input('start_date', (isset($_POST['start_date']) ? $_POST['start_date'] : ''), 'class="form-control input-tip date" id="start_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="end_date"><?= lang('end_date'); ?></label>
                                <?php echo form_input('end_date', (isset($_POST['end_date']) ? $_POST['end_date'] : ''), 'class="form-control input-tip date" id="end_date"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="invoice_id"><?= lang('invoice'); ?></label>
                                <?php echo form_input('invoice_id', (isset($_POST['invoice_id']) ? $_POST['invoice_id'] : ''), 'class="form-control" id="invoice_id" placeholder="' . lang('invoice') . '"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang('customer'); ?></label>
                                <?php echo form_input('customer', (isset($_POST['customer']) ? $_POST['customer'] : ''), 'class="form-control" id="customer" placeholder="' . lang('customer') . '"'); ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="salesman"><?= lang('sales_man'); ?></label>
                                <?php
                                $sm[''] = lang('select') . ' ' . lang('sales_man');
                                foreach ($salesmen as $salesman_item) {
                                    $sm[$salesman_item->id] = $salesman_item->name;
                                }
                                echo form_dropdown('salesman', $sm, (isset($_POST['salesman']) ? $_POST['salesman'] : ''), 'class="form-control" id="salesman"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang('warehouse'); ?></label>
                                <?php
                                $wh[''] = lang('select') . ' ' . lang('warehouse');
                                foreach ($warehouses as $warehouse) {
                                    $wh[$warehouse->id] = $warehouse->name;
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ''), 'class="form-control" id="warehouse" placeholder="' . lang('select') . ' ' . lang('warehouse') . '"');
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls">
                            <?php echo form_submit('submit_report', $this->lang->line('submit'), 'class="btn btn-primary"'); ?>
                        </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>

                <div class="clearfix"></div>

                <div class="table-responsive">
                    <table id="InvStatusData" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-condensed table-hover table-striped reports-table">
                        <thead>
                        <tr class="primary">
                            <th><?= lang('date'); ?></th>
                            <th><?= lang('invoice'); ?></th>
                            <th><?= lang('area'); ?></th>
                            <th><?= lang('sales_man'); ?></th>
                            <th><?= lang('customer_no'); ?></th>
                            <th><?= lang('customer_name'); ?></th>
                            <th><?= lang('invoice'); ?></th>
                            <th><?= lang('return'); ?></th>
                            <th><?= lang('discount'); ?></th>
                            <th><?= lang('paid'); ?></th>
                            <th><?= lang('outstanding'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="11" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
