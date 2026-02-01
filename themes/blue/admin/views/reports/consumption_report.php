<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript">
    $(document).ready(function() {
        // When main agent is selected, populate agent2 dropdown
        $('#agent').on('change', function() {
            var main_agent = $(this).val();
            var agent2_dropdown = $('#agent2');
            
            if (main_agent) {
                $.ajax({
                    url: '<?php echo admin_url('reports/get_agent2_by_main_agent'); ?>',
                    type: 'POST',
                    data: { 
                        main_agent: main_agent,
                        <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
                    },
                    success: function(response) {
                        agent2_dropdown.html(response);
                    },
                    error: function() {
                        agent2_dropdown.html('<option value="">Select Agent 2</option>');
                    }
                });
            } else {
                agent2_dropdown.html('<option value="">Select Agent 2</option>');
            }
        });
    });
</script>

<div class="box">
    <div class="box-header">
    <h2 class="blue"><i class="fa-fw fa fa-star-o"></i><a href="<?= admin_url('reports/consumption_report'); ?>"><?= lang('Stock Consumption Report'); ?></a>
    </h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext">
                    <?php
                    $attrib = ['data-toggle' => 'validator', 'role' => 'form','id' => 'searchForm', 'name'=>'searchForm', 'method' => 'get'];
                    echo admin_form_open_multipart('reports/consumption_report', $attrib)
                    ?>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?php echo lang('Item', 'item'); ?>
                            <?php echo form_input('sgproduct', (isset($_GET['sgproduct']) ? $_GET['sgproduct'] : ''), 'class="form-control" id="suggest_product2" data-bv-notempty="true"'); ?>
                            <input type="hidden" name="item" value="<?= isset($_GET['item']) ? $_GET['item'] : 0 ?>" id="report_product_id2" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="period">Period</label>
                            <select class="form-control" id="period" name="period">
                                <option value="1" <?= (isset($period) && $period == 1) ? 'selected' : ''; ?>>Last 1 Month</option>
                                <option value="3" <?= (isset($period) && $period == 3) ? 'selected' : ''; ?>>Last 3 Months</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('Company', 'agent'); ?>
                            <?php
                            $ag[''] = 'Select Company';
                            if (!empty($agents)) {
                                foreach ($agents as $agentItem) {
                                    $ag[$agentItem->main_agent] = $agentItem->main_agent;
                                }
                            }
                            echo form_dropdown('agent', $ag, ($agent ?? ''), 'id="agent" class="form-control input-tip select" data-placeholder="' . lang('select') . ' Agent" style="width:100%;" ', null);
                            ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('Agent 2', 'agent2'); ?>
                            <select name="agent2" id="agent2" class="form-control input-tip select" style="width:100%;">
                                <option value="">Select Agent 2</option>
                                <?php if (!empty($agent2)): ?>
                                    <option value="<?= $agent2 ?>" selected><?= $agent2 ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('supplier', 'posupplier'); ?>
                            <?php
                            $sp[''] = '';
                            foreach ($suppliers as $supplier) {
                                $sp[$supplier->id] = $supplier->company . ' (' . $supplier->name . ') - '. $supplier->sequence_code;

                            }
                            echo form_dropdown('supplier_id', $sp, $supplier_id, 'id="supplier_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('supplier') . '" required="required" style="width:100%;" ', null); ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="from-group">
                            <button type="submit" name="submit" style="margin-top: 28px;" class="btn btn-primary"
                                    id="load_report"><?= lang('Load Report') ?></button>
                        </div>
                    </div>
                        
                    <?php echo form_close(); ?>
                </p>
                <div class="table-responsive">
                    <table id="TOData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                #
                            </th>
                            <th><?= lang('code'); ?></th>
                            <th colspan="2"><?= lang('name'); ?></th>
                            <!--<th><?php //echo lang('cost'); ?></th>-->
                            <th><?= lang('Available Quantity'); ?></th>
                            <th><?= lang('Avg Sale'); ?></th>
                            <th colspan="2"><?= lang('Required Stock'); ?></th>
                            <th><?= lang('Months'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                            <?php 
                                if($stock_array){
                                    $count = 0;
                                    foreach($stock_array as $stock){
                                        $count++;
                                        ?>
                                            <tr>
                                                <td class="dataTables_empty"><?= $count; ?></td>
                                                <td class="dataTables_empty"><?= $stock->code; ?></td>
                                                <td colspan="2" class="dataTables_empty"><?= $stock->name; ?></td>
                                                <!--<td class="dataTables_empty"><?php //echo number_format((float) $stock->cost, 2, '.', ''); ?></td>-->
                                                <td class="dataTables_empty"><?= number_format((float) $stock->available_stock, 2, '.', ''); ?></td>
                                                <td class="dataTables_empty"><?= isset($stock->avg_stock) ? number_format((float) ($stock->avg_stock), 2, '.', '') : number_format((float) ($stock->avg_last_3_months_sales) / ($period), 2, '.', ''); ?></td>
                                                <td colspan="2" class="dataTables_empty">
                                                    <?php 
                                                        if(isset($stock->required_stock)){
                                                            $required_stock = $stock->required_stock;
                                                        }else{
                                                            $required_stock = ($stock->avg_last_3_months_sales / ($period)) - $stock->available_stock > 0 ? number_format((float) ($stock->avg_last_3_months_sales / ($period)) - $stock->available_stock, 2, '.', '') : '0.00';
                                                        } 
                                                    ?>
                                                    <?= $required_stock; ?>
                                                    
                                                </td>
                                                <td class="dataTables_empty"><?= isset($period) ? $period : '1'; ?> months</td>
                                            </tr>
                                        <?php
                                    }
                                                    
                                }else{
                            ?>
                                <tr><td colspan="11" class="dataTables_empty"><?= lang('Could not load data'); ?></td></tr>
                            <?php
                                }
                            ?>
                            
                        </tbody>
                        
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
