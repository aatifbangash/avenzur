<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript">
    var count = 1, an = 1;
    var type_opt = {'addition': '<?= lang('addition'); ?>', 'subtraction': '<?= lang('subtraction'); ?>'};
    $(document).ready(function () {
        if (localStorage.getItem('remove_buls')) {
            if (localStorage.getItem('buitems')) {
                localStorage.removeItem('buitems');
            }
            if (localStorage.getItem('bundle_name')) {
                localStorage.removeItem('bundle_name');
            } 
            if (localStorage.getItem('bundle_description')) {
                localStorage.removeItem('bundle_description');
            }
            localStorage.removeItem('remove_buls');
        }
        <?php if ($bundle) {
    ?> 
        localStorage.setItem('qadate', '<?= $this->sma->hrld($bundle->date); ?>');
        localStorage.setItem('bundle_name', '<?= $bundle->bundle_name; ?>'); 
        localStorage.setItem('bundle_description', '<?= str_replace(["\r", "\n"], '', $this->sma->decode_html($bundle->bundle_description)); ?>');
        localStorage.setItem('buitems', JSON.stringify(<?= $bundle_items; ?>));
        localStorage.setItem('remove_buls', '1');
        <?php
} ?>
        
        $("#add_item").autocomplete({
            source: '<?= admin_url('products/bu_suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
                else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                }
                else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    bootbox.alert('<?= lang('no_match_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).val('');
                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_bundle_item(ui.item);
                    if (row)
                        $(this).val('');
                } else {
                    bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('edit_bundle'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?php echo lang('enter_info'); ?></p>
                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
                echo admin_form_open_multipart('products/edit_bundle/' . $bundle->id, $attrib);
                ?>
                <div class="row">
                    <div class="col-lg-12"> 
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('bundle_name', 'bundle_name'); ?>
                                <?php echo form_input('bundle_name', (isset($_POST['bundle_name']) ? $_POST['bundle_name'] : $bundle->bundle_name), 'class="form-control input-tip" id="bundle_name"'); ?>
                            </div>
                        </div> 

                        <div class="clearfix"></div> 
                        <div class="col-md-12" id="sticker">
                            <div class="well well-sm">
                                <div class="form-group" style="margin-bottom:0;">
                                    <div class="input-group wide-tip">
                                        <div class="input-group-addon" style="padding-left: 10px; padding-right: 10px;">
                                            <i class="fa fa-2x fa-barcode addIcon"></i></a></div>
                                        <?php echo form_input('add_item', '', 'class="form-control input-lg" id="add_item" placeholder="' . lang('add_product_to_bundle') . '"'); ?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="control-group table-group">
                                <label class="table-label"><?= lang('products'); ?> *</label>

                                <div class="controls table-controls">
                                    <table id="qaTable" class="table items table-striped table-bordered table-condensed table-hover">
                                        <thead>
                                        <tr>
                                            <th><?= lang('product_name') . ' (' . lang('product_code') . ')'; ?></th> 
                                            <th class="col-md-2"><?= lang('Product Price'); ?></th>
                                             <th class="col-md-2"><?= lang('discount'); ?>(%)</th> 
                                            <th style="max-width: 30px !important; text-align: center;">
                                                <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="clearfix"></div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <?= lang('bundle_description', 'bundle_description'); ?>
                                    <?php echo form_textarea('bundle_description', (isset($_POST['bundle_description']) ? $_POST['bundle_description'] : ''), 'class="form-control" id="bundle_description" style="margin-top: 10px; height: 100px;"'); ?>
                                </div>
                            </div>
                            <div class="clearfix"></div>

                        <div class="col-md-12">
                            <div
                                class="fprom-group"><?php echo form_submit('edit_bundle', lang('submit'), 'id="edit_bundle" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); ?>
                                <button type="button" class="btn btn-danger" id="reset"><?= lang('reset') ?></div>
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>

            </div>

        </div>
    </div>
</div>
