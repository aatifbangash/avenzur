<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        $('#addRowBtn').click(function() {
            var newRow = '<tr>' +
                '<td><input type="text" placeholder="Enter Description" class="form-control" name="description[]" /></td>' +
                '<td><input type="text" placeholder="Enter Amount" class="form-control" name="payment_amount[]" /></td>' +
                '</tr>';
            $('#poTable tbody').append(newRow);
        });
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-info-circle"></i><?= lang('Customer Payment'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                
                <div class="row">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="poTable"
                                class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th><?php echo $this->lang->line('Reference No.'); ?></th>
                                <th><?php echo $this->lang->line('Customer') ?></th>
                                <th><?php echo $this->lang->line('Date') ?></th>
                                <th><?php echo $this->lang->line('Payment Amount') ?></th>
                                <th><?php echo $this->lang->line('Note') ?></th>
                                <th><?php echo $this->lang->line('Actions') ?></th>
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                                <?php 
                                    $count = 0;
                                    foreach($payments as $payment){
                                        $count++;
                                        ?>
                                            <tr>
                                                <td><?= $count; ?></td>
                                                <td><?= $payment->reference_no; ?></td>
                                                <td><?= $payment->company; ?></td>
                                                <td><?= $payment->date; ?></td>
                                                <td><?= $payment->amount; ?></td>
                                                <td><?= $payment->note; ?></td>
                                                <td><a href="<?php echo admin_url('customers/edit_payment/' . $payment->id); ?>" class="tip" title="Edit Payment"><i class="fa fa-edit"></i></a></td>
                                            </tr>
                                        <?php
                                    }
                                ?>
                            </tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>
                
            </div>

        </div>
    </div>
</div>

