<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>
<script>
    function exportTableToExcel(tableId, filename = 'table.xlsx') {
        const table = document.getElementById(tableId);
        const wb = XLSX.utils.table_to_book(table, {
            sheet: 'Sheet 1'
        });
        XLSX.writeFile(wb, filename);
    }
    function generatePDF(){
       $('.viewtype').val('pdf');
       document.getElementById("searchForm").submit();
       $('.viewtype').val('');
    }
    $(document).ready(function() {

    });
</script>
<?php if($viewtype=='pdf'){ ?>
    <link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet">
  <?php  } ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-book"></i><?= lang('general_ledger_report'); ?></h2>
        <?php  if($viewtype!='pdf'){?>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="javascript:void(0);" onclick="exportTableToExcel('poTable', 'GL_Report.xlsx')" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a></li>
                <li class="dropdown"> <a href="javascript:void(0);" onclick="generatePDF()" id="pdf" class="tip" title="<?= lang('download_PDF') ?>"><i class="icon fa fa-file-pdf-o"></i></a></li>
            </ul>
        </div>
        <?php } ?>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php
                if($viewtype!='pdf')
                {
                    $attrib = ['data-toggle' => 'validator', 'role' => 'form','id' => 'searchForm'];
                    echo admin_form_open_multipart('reports/GLReport', $attrib)
                    ?>
                    <input type="hidden" name="viewtype" id="viewtype" class="viewtype" value="" >

                <div class="row">
                    <div class="col-lg-12">

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('From Date', 'podate'); ?>
                                <?php echo form_input('from_date', ($start_date ?? ''), 'class="form-control input-tip date" id="fromdate"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('To Date', 'podate'); ?>
                                <?php echo form_input('to_date', ($end_date ?? ''), 'class="form-control input-tip date" id="todate"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="from-group">
                                <button type="submit" style="margin-top: 28px;" class="btn btn-primary"
                                        id="load_report"><?= lang('Load Report') ?></button>
                            </div>
                        </div>

                    </div>
                </div>
                <?php echo form_close();
                } ?>
                <hr/>
                <div class="row">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="poTable"
                               class="table items table-striped table-bordered table-condensed table-hover sortable_table tbl_pdf">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th><?= lang('voucher'); ?></th>
                                <th><?= lang('voucher_id'); ?></th>
                                <th><?= lang('trx_id'); ?></th>
                                <th><?= lang('date'); ?></th>
                                <th><?= lang('reference'); ?></th>
                                <th><?= lang('account_number'); ?></th>
                                <th><?= lang('account_name'); ?></th>
                                <th><?= lang('description'); ?></th>
                                <th><?= lang('debit'); ?></th>
                                <th><?= lang('credit'); ?></th>
                                
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                            <?php
                            $count = 0;
                            $total_debit = 0;
                            $total_credit = 0;

                            if (!empty($gl_report)) {
                                foreach ($gl_report as $row) {
                                    $link = '';
                                    if ($row->voucher == 'Sales Invoice') {
                                        $link = admin_url('sales?sid=' . $row->voucher_id);
                                    } elseif ($row->voucher == 'Purchase Invoice') {
                                        $link = admin_url('purchases?pid=' . $row->voucher_id);
                                    } elseif ($row->voucher == 'Sales Return') {
                                        $link = admin_url('returns?rid=' . $row->voucher_id);
                                    } elseif ($row->voucher == 'Credit Note') {
                                        $link = admin_url('customers/view_credit_memo/' . $row->voucher_id);
                                    } elseif ($row->voucher == 'Debit Note') {
                                        $link = admin_url('suppliers/view_debit_memo/' . $row->voucher_id);
                                    } elseif ($row->voucher == 'Service Invoice') {
                                        //$link = admin_url('suppliers/service_invoice?service_invoice_id=' . $row->voucher_id);
                                    } elseif ($row->voucher == 'Petty Cash') {
                                        //$link = admin_url('suppliers/petty_cash?petty_cash_id=' . $row->voucher_id);
                                    } elseif ($row->voucher == 'Supplier Payment') {
                                        $link = admin_url('suppliers/view_payment/' . $row->voucher_id);
                                    } elseif ($row->voucher == 'Salaries Voucher') {
                                        //$link = admin_url('payroll/salary_vouchers?voucher_id=' . $row->voucher_id);
                                    } elseif ($row->voucher == 'Collection') {
                                        $link = admin_url('customers/view_payment/' . $row->voucher_id);
                                    } elseif ($row->voucher == 'Customer Advance') {
                                        $link = admin_url('customers/view_payment/' . $row->voucher_id);
                                    }
                                    $count++;
                                    $total_debit += $row->debit;
                                    $total_credit += $row->credit;
                                    ?>
                                    <tr>
                                        <td><?= $count; ?></td>
                                        <td><?= $row->voucher; ?></td>
                                        <td><a href="<?= $link; ?>" target="_blank"><?= $row->voucher_id; ?></a></td>
                                        <td><a href="<?= admin_url('entries/view/journal/' . $row->trx_id); ?>" target="_blank"><?= $row->trx_id; ?></a></td>
                                        <td><?= $row->date; ?></td>
                                        <td><?= $row->reference; ?></td>
                                        <td><?= $row->account_number; ?></td>
                                        <td><?= $row->account_name; ?></td>
                                        <td><?= $row->description; ?></td>
                                        <td class="text-right"><?= $row->debit > 0 ? $this->sma->formatNumber($row->debit) : '0'; ?></td>
                                        <td class="text-right"><?= $row->credit > 0 ? $this->sma->formatNumber($row->credit) : '0'; ?></td>
                                        
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="11" class="text-center">No data found for the selected date range.</td>
                                </tr>
                                <?php
                            }
                            ?>

                            <?php if (!empty($gl_report)) { ?>
                            <tr class="active">
                                <th colspan="9" class="text-right"><?= lang('total'); ?></th>
                                <th class="text-right"><?= $this->sma->formatNumber($total_debit); ?></th>
                                <th class="text-right"><?= $this->sma->formatNumber($total_credit); ?></th>
                                <th>&nbsp;</th>
                            </tr>
                            <?php } ?>

                            </tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>

                </div>

            </div>
        </div>
    </div>