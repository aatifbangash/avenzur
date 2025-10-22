<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('add grn'); ?></h2>

        <!-- CSV upload icon -->
        
    </div>
    <div class="box-content">

    <div class="row">
                    <div class="col-lg-12">
<div class="container mt-4">
    <div class="">
        
        <div class="card-body">
            <?= form_open('admin/purchase_order/add_grn/'.$po_id, ['class' => 'needs-validation', 'novalidate' => true]); ?>

          
            <h5 class="mb-3">Purchase Order Items</h5>

            <table class="table table-bordered" id="itemsTable">
                <thead class="thead-light">
                    <tr>
                        <th style="3%">No</th>
                        <th style="width:25%">Item Name</th>
                        <th style="width:15%">Actual Quantity</th>
                        <th style="width:15%">Received Quantity</th>
                        <th>Comment</th>
                       
                    </tr>
                </thead>
                <tbody>
                    <?php 
                            $i = 1;
                            foreach ($rows as $key => $row) { ?>
                                <tr>
                                    <td>
                                        <?php echo $i; ?>  
                                        <input type="hidden" name="items[<?php echo $key; ?>][item_id]" value="<?php echo $row->id; ?>">  
                                    </td>
                                    <td><?php echo $row->product_name; ?></td>
                                    <td>
                                        <input type="text" 
                                            name="items[<?php echo $key; ?>][actual_quantity]" 
                                            value="<?php echo $row->quantity; ?>" 
                                            class="form-control" readonly>
                                    </td>
                                    <td>
                                        <input type="text" 
                                            name="items[<?php echo $key; ?>][quantity]" 
                                            class="form-control" placeholder="" required>
                                    </td>
                                    <td>
                                        <input type="text" 
                                            name="items[<?php echo $key; ?>][remarks]" 
                                            class="form-control" placeholder="">
                                    </td>
                                </tr>
                            <?php 
                                $i++;
                            } 
                            ?>

                </tbody>
            </table>
        
            <div class="mb-3">
                <?= form_label('Remarks', 'remarks'); ?>
                <?= form_textarea([
                    'name' => 'remarks',
                    'id' => 'remarks',
                    'rows' => 3,
                    'class' => 'form-control',
                    'placeholder' => 'Enter any additional details or notes',
                    'value' => set_value('remarks')
                ]); ?>
            </div>
           

            <div class="text-end">
                <button type="submit" class="btn btn-success">Submit GRN</button>
            </div>

            <?= form_close(); ?>
        </div>
    </div>
    </div>
    </div>
</div>

</div>


