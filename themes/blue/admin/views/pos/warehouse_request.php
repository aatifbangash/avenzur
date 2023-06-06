  <div class="box">
    
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><button id="wr_close" style="padding: 1px 6px;margin-right: 10px;">X</button><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                echo admin_form_open_multipart('stock_request/ajax_add', $attrib)
                ?>

                <?php
                        $data = array(
                                    'date'  => date("d/m/Y H:i"),
                                    'status' => 'pending',
                                    'shipping'   => '0',
                                    'attachments'=> '',
                                    'to_warehouse' => $this->session->userdata('warehouse_id'),
                                    'note'       => '',
                                    
                            );
                            
                            echo form_hidden($data);
                
                
                
                ?>
                <div class="row">
                    <div class="col-lg-12">

                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang('reference_no', 'ref'); ?>
                                <?php echo form_input('reference_no', ($_POST['reference_no'] ?? ''), 'class="form-control input-tip" id="ref"'); ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang('from_warehouse', 'from_warehouse'); ?>
                                <?php
                                $wh[''] = '';
                                foreach ($warehouses as $warehouse) {
                                    if($warehouse->id != $this->session->userdata('warehouse_id')){
                                    $wh[$warehouse->id] = $warehouse->name;
                                    }
                                }
                                echo form_dropdown('from_warehouse', $wh, ($_POST['from_warehouse'] ?? ''), 'id="from_warehouse" class="form-control input-tip" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('from_warehouse') . '" required="required" style="width:100%;" ');
                                ?>
                            </div>
                        </div>




                        <div class="col-md-12" id="sticker">
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a></div>
                                        <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item_wh" placeholder="' . $this->lang->line('add_product_to_order') . '"'); ?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>

                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang('order_items'); ?></label>

                                <div class="controls table-controls">
                                    <table id="toTable"
                                           class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                                        <thead>
                                        <tr>
                                            <th class="col-md-4"><?= lang('product') . ' (' . lang('code') . ' - ' . lang('name') . ')'; ?></th>
                                            <th class="col-md-1">sale price</th>
                                            <th class="col-md-1"><?= lang('quantity'); ?></th>
                                            <th><?= lang('subtotal'); ?> (<span
                                                    class="currency"><?= $default_currency->code ?></span>)
                                            </th>
                                            <th style="width: 30px !important; text-align: center;"><i
                                                    class="fa fa-trash-o"
                                                    style="opacity:0.5; filter:alpha(opacity=50);"></i></th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>




                            <div
                                class="from-group"><?php echo form_submit('add_transfer', $this->lang->line('submit'), 'id="add_transfer" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                
                            </div>
                        </div>

                    </div>
                </div>

                <div id="bottom-total" class="well well-sm" style="margin-bottom: 0;">
                    <table class="table table-bordered table-condensed totals" style="margin-bottom:0;">
                        <tr class="warning">
                            <td><?= lang('items') ?> <span class="totals_val pull-right" id="wh_titems">0</span></td>
                            <td><?= lang('total') ?> <span class="totals_val pull-right" id="wh_total">0.00</span></td>
                            <td><?= lang('shipping') ?> <span class="totals_val pull-right" id="wh_tship">0.00</span></td>
                            <td><?= lang('grand_total') ?> <span class="totals_val pull-right" id="wh_gtotal">0.00</span>
                            </td>
                        </tr>
                    </table>
                </div>

                <?php echo form_close(); ?>

            </div>

        </div>
    </div>
</div>

