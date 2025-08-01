<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header no-print">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('print_barcode_label'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" onclick="window.print();return false;" id="print-icon" class="tip" title="<?= lang('print') ?>">
                        <i class="icon fa fa-print"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="row" style="padding:10px;">
                <div class="col-md-3">
                            
                            Pharmacy: <select class="form-control" name="pharmacy" id="pharmacy">
                                <option value="">All</option>
                            <?php
                            $selected_warehouse_id[] = isset($warehouse) ? $warehouse : '';
                            $dp['all'] = 'All';
                            foreach ($warehouses as $warehouse) {
                                ?>
                               
                                    <option value="<?=$warehouse->id;?>" <?php if($pharmacy == $warehouse->id) {?> selected <?php }?> ><?=$warehouse->name;?></option>
                             <?php   
                                
                            }
                            ?>
                            </select>
                          
                        </div>
                    <div class="col-md-3">Purchase Inv No:<input type="text" id="purchase_id" name="purchase_id" class="form-control input-tip" value="<?=$purchase_id;?>"></div>
                    <div class="col-md-3">Transfer Inv No:<input type="text" id="transfer_id" name="transfer_id" class="form-control input-tip" value="<?=$transfer_id;?>"></div>
                    <div class="col-md-3">Item Code*:<input type="text" id="item_code" name="item_code" class="form-control input-tip" value="<?=$item_code;?>"></div>
                    <div class="col-md-3" style="margin-top:19px;"> <input type="button" id="searchByNumber" class="btn btn-primary" value="Search"></div>
             </div>
                <!-- <p class="introtext no-print"><?php echo sprintf(
                    lang('print_barcode_heading'),
                    anchor('admin/system_settings/categories', lang('categories') . ' & ' . lang('subcategories')),
                    '',
                    anchor('admin/purchases', lang('purchases')),
                    anchor('admin/transfers', lang('transfers'))
); ?></p> -->

                <div class="well well-sm no-print">
                    <div class="form-group">
                        
                    
                        <?php //lang('add_product', 'add_item'); ?>
                        <?php //echo form_input('add_item', '', 'class="form-control" id="add_item" placeholder="' . $this->lang->line('add_item') . '"'); ?>
                    </div>
                    <?= admin_form_open('products/print_barcodes', 'id="barcode-print-form" data-toggle="validator"'); ?>
                    <input type="hidden" name="purchase_id" value="<?php echo $this->input->get('purchase', true);?>" >
                    <input type="hidden" name="transfer_id" value="<?php echo $this->input->get('transfer', true);?>" >
                    <input type="hidden" name="pharmacy_id" id="pharmacy_id" value="" >
                    <div class="controls table-controls">
                        <table id="bcTable"
                               class="table items table-striped table-bordered table-condensed table-hover">
                            <thead>
                            <tr>
                                <th class="col-xs-1"> &nbsp;</th>
                                <th class="col-xs-4"><?= lang('product_name') . ' (' . $this->lang->line('product_code') . ')'; ?></th>
                                <th class="col-xs-1"><?= lang('quantity'); ?></th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>                       

                    <div class="form-group">
                        <?php echo form_submit('print', lang('Print'), 'class="btn btn-primary"'); ?>
                    </div>
                    <?= form_close(); ?>
                    <div class="clearfix"></div>
                </div>
                <div id="barcode-con">
                    <?php
                    if ($this->input->post('print')) {
                        if (!empty($barcodes)) {
                            echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" title="' . lang('print') . '"><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                            $c = 1;
                            if ($style == 12 || $style == 18 || $style == 24 || $style == 40) {
                                echo '<div class="barcodea4">';
                            } elseif ($style != 50) {
                                echo '<div class="barcode">';
                            }
                            foreach ($barcodes as $item) {
                                for ($r = 1; $r <= $item['quantity']; $r++) {
                                    echo '<div class="item style' . $style . '" ' .
                                    ($style == 50 && $this->input->post('cf_width') && $this->input->post('cf_height') ?
                                        'style="width:' . $this->input->post('cf_width') . 'in;height:' . $this->input->post('cf_height') . 'in;border:0;"' : '')
                                    . '>';
                                    if ($style == 50) {
                                        if ($this->input->post('cf_orientation')) {
                                            $ty        = (($this->input->post('cf_height') / $this->input->post('cf_width')) * 100) . '%';
                                            $landscape = '
                                                -webkit-transform-origin: 0 0;
                                                -moz-transform-origin:    0 0;
                                                -ms-transform-origin:     0 0;
                                                transform-origin:         0 0;
                                                -webkit-transform: translateY(' . $ty . ') rotate(-90deg);
                                                -moz-transform:    translateY(' . $ty . ') rotate(-90deg);
                                                -ms-transform:     translateY(' . $ty . ') rotate(-90deg);
                                                transform:         translateY(' . $ty . ') rotate(-90deg);
                                                ';
                                            echo '<div class="div50" style="width:' . $this->input->post('cf_height') . 'in;height:' . $this->input->post('cf_width') . 'in;border: 1px dotted #CCC;' . $landscape . '">';
                                        } else {
                                            echo '<div class="div50" style="width:' . $this->input->post('cf_width') . 'in;height:' . $this->input->post('cf_height') . 'in;border: 1px dotted #CCC;padding-top:0.025in;">';
                                        }
                                    }
                                    if ($item['image']) {
                                        echo '<span class="product_image"><img src="' . base_url('assets/uploads/thumbs/' . $item['image']) . '" alt="" /></span>';
                                    }
                                    if ($item['site']) {
                                        echo '<span class="barcode_site">' . $item['site'] . '</span>';
                                    }
                                    if ($item['name']) {
                                        echo '<span class="barcode_name">' . $item['name'] . '</span>';
                                    }
                                    if ($item['price']) {
                                        echo '<span class="barcode_price">' . lang('price') . ' ';
                                        if ($item['currencies']) {
                                            $rates = [];
                                            foreach ($currencies as $currency) {
                                                $rates[] = $currency->code . ': ' . $this->sma->formatMoney($item['rprice'] * $currency->rate, 'none');
                                            }
                                            echo implode(', ', $rates);
                                        } else {
                                            echo $item['price'];
                                        }
                                        echo '</span> ';
                                    }
                                    if ($item['unit']) {
                                        echo '<span class="barcode_unit">' . lang('unit') . ': ' . $item['unit'] . '</span>, ';
                                    }
                                    if ($item['category']) {
                                        echo '<span class="barcode_category">' . lang('category') . ': ' . $item['category'] . '</span> ';
                                    }
                                    if ($item['variants']) {
                                        echo '<span class="variants">' . lang('variants') . ': ';
                                        foreach ($item['variants'] as $variant) {
                                            echo $variant->name . ', ';
                                        }
                                        echo '</span> ';
                                    }
                                    echo '<span class="barcode_image"><img src="' . admin_url('products/barcode/' . $item['barcode'] . '/' . $item['bcs'] . '/' . $item['bcis']) . '" alt="' . $item['barcode'] . '" class="bcimg" /></span>';
                                    if ($item['vat_number']) {
                                        echo '<span class="barcode_vat">' . 'VAT NO. ' . $item['vat_number'] . '</span> ';
                                    }
                                    if ($style == 50) {
                                        echo '</div>';
                                    }
                                    echo '</div>';
                                    if ($style == 40) {
                                        if ($c % 40 == 0) {
                                            echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                        }
                                    } elseif ($style == 30) {
                                        if ($c % 30 == 0) {
                                            echo '</div><div class="clearfix"></div><div class="barcode">';
                                        }
                                    } elseif ($style == 24) {
                                        if ($c % 24 == 0) {
                                            echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                        }
                                    } elseif ($style == 20) {
                                        if ($c % 20 == 0) {
                                            echo '</div><div class="clearfix"></div><div class="barcode">';
                                        }
                                    } elseif ($style == 18) {
                                        if ($c % 18 == 0) {
                                            echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                        }
                                    } elseif ($style == 14) {
                                        if ($c % 14 == 0) {
                                            echo '</div><div class="clearfix"></div><div class="barcode">';
                                        }
                                    } elseif ($style == 12) {
                                        if ($c % 12 == 0) {
                                            echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                        }
                                    } elseif ($style == 10) {
                                        if ($c % 10 == 0) {
                                            echo '</div><div class="clearfix"></div><div class="barcode">';
                                        }
                                    }
                                    
                                    $c++;
                                }
                            }
                            if ($style != 50) {
                                echo '</div>';
                            }
                            echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" title="' . lang('print') . '"><i class="icon fa fa-print"></i> ' . lang('print') . '</button>';
                        } else {
                            //echo '<h3>' . lang('no_product_selected') . '</h3>'; 
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var ac = false; bcitems = {};
    // if (localStorage.getItem('bcitems')) {
    //     bcitems = JSON.parse(localStorage.getItem('bcitems'));
    // }
   
    <?php if ($items) {
        ?>
    localStorage.setItem('bcitems', JSON.stringify(<?= $items; ?>));
        <?php
    } else {?>
     localStorage.setItem('bcitems', JSON.stringify(bcitems));
    <?php }?>
    $(document).ready(function() {
        <?php if ($this->input->post('print')) {
            ?>
            $( window ).load(function() {
                $('html, body').animate({
                    scrollTop: ($("#barcode-con").offset().top)-15
                }, 1000);
            });
            <?php
        } ?>
        if (localStorage.getItem('bcitems')) {
            loadItems();
        }
        $("#add_item").autocomplete({
            source: '<?= admin_url('products/get_suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).val('');
                }
                else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                }
                else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>', function () {
                        $('#add_item').focus();
                    });
                    $(this).val('');

                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_product_item(ui.item);
                    if (row) {
                        $(this).val('');
                    }
                } else {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>');
                }
            }
        });
        check_add_item_val();

        $('#style').change(function (e) {
            localStorage.setItem('bcstyle', $(this).val());
            if ($(this).val() == 50) {
                $('.cf-con').slideDown();
            } else {
                $('.cf-con').slideUp();
            }
        });
        if (style = localStorage.getItem('bcstyle')) {
            $('#style').val(style);
            $('#style').select2("val", style);
            if (style == 50) {
                $('.cf-con').slideDown();
            } else {
                $('.cf-con').slideUp();
            }
        }

        $('#cf_width').change(function (e) {
            localStorage.setItem('cf_width', $(this).val());
        });
        if (cf_width = localStorage.getItem('cf_width')) {
            $('#cf_width').val(cf_width);
        }

        $('#cf_height').change(function (e) {
            localStorage.setItem('cf_height', $(this).val());
        });
        if (cf_height = localStorage.getItem('cf_height')) {
            $('#cf_height').val(cf_height);
        }

        $('#cf_orientation').change(function (e) {
            localStorage.setItem('cf_orientation', $(this).val());
        });
        if (cf_orientation = localStorage.getItem('cf_orientation')) {
            $('#cf_orientation').val(cf_orientation);
        }

        $(document).on('ifChecked', '#site_name', function(event) {
            localStorage.setItem('bcsite_name', 1);
        });
        $(document).on('ifUnchecked', '#site_name', function(event) {
            localStorage.setItem('bcsite_name', 0);
        });
        if (site_name = localStorage.getItem('bcsite_name')) {
            if (site_name == 1)
                $('#site_name').iCheck('check');
            else
                $('#site_name').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#product_name', function(event) {
            localStorage.setItem('bcproduct_name', 1);
        });
        $(document).on('ifUnchecked', '#product_name', function(event) {
            localStorage.setItem('bcproduct_name', 0);
        });
        if (product_name = localStorage.getItem('bcproduct_name')) {
            if (product_name == 1)
                $('#product_name').iCheck('check');
            else
                $('#product_name').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#price', function(event) {
            localStorage.setItem('bcprice', 1);
        });
        $(document).on('ifUnchecked', '#price', function(event) {
            localStorage.setItem('bcprice', 0);
            $('#currencies').iCheck('uncheck');
        });
        if (price = localStorage.getItem('bcprice')) {
            if (price == 1)
                $('#price').iCheck('check');
            else
                $('#price').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#currencies', function(event) {
            localStorage.setItem('bccurrencies', 1);
        });
        $(document).on('ifUnchecked', '#currencies', function(event) {
            localStorage.setItem('bccurrencies', 0);
        });
        if (currencies = localStorage.getItem('bccurrencies')) {
            if (currencies == 1)
                $('#currencies').iCheck('check');
            else
                $('#currencies').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#unit', function(event) {
            localStorage.setItem('bcunit', 1);
        });
        $(document).on('ifUnchecked', '#unit', function(event) {
            localStorage.setItem('bcunit', 0);
        });
        if (unit = localStorage.getItem('bcunit')) {
            if (unit == 1)
                $('#unit').iCheck('check');
            else
                $('#unit').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#category', function(event) {
            localStorage.setItem('bccategory', 1);
        });
        $(document).on('ifUnchecked', '#category', function(event) {
            localStorage.setItem('bccategory', 0);
        });
        if (category = localStorage.getItem('bccategory')) {
            if (category == 1)
                $('#category').iCheck('check');
            else
                $('#category').iCheck('uncheck');
        }
        $(document).on('ifChecked', '#check_promo', function(event) {
            localStorage.setItem('bccheck_promo', 1);
        });
        $(document).on('ifUnchecked', '#check_promo', function(event) {
            localStorage.setItem('bccheck_promo', 0);
        });
        if (check_promo = localStorage.getItem('bccheck_promo')) {
            if (check_promo == 1)
                $('#check_promo').iCheck('check');
            else
                $('#check_promo').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#product_image', function(event) {
            localStorage.setItem('bcproduct_image', 1);
        });
        $(document).on('ifUnchecked', '#product_image', function(event) {
            localStorage.setItem('bcproduct_image', 0);
        });
        if (product_image = localStorage.getItem('bcproduct_image')) {
            if (product_image == 1)
                $('#product_image').iCheck('check');
            else
                $('#product_image').iCheck('uncheck');
        }

        $(document).on('ifChecked', '#variants', function(event) {
            localStorage.setItem('bcvariants', 1);
        });
        $(document).on('ifUnchecked', '#variants', function(event) {
            localStorage.setItem('bcvariants', 0);
        });
        if (variants = localStorage.getItem('bcvariants')) {
            if (variants == 1)
                $('#variants').iCheck('check');
            else
                $('#variants').iCheck('uncheck');
        }

        $(document).on('ifChecked', '.checkbox', function(event) {
            var item_id = $(this).attr('data-item-id');
            var vt_id = $(this).attr('id');
            bcitems[item_id]['selected_variants'][vt_id] = 1;
            localStorage.setItem('bcitems', JSON.stringify(bcitems));
        });
        $(document).on('ifUnchecked', '.checkbox', function(event) {
            var item_id = $(this).attr('data-item-id');
            var vt_id = $(this).attr('id');
            bcitems[item_id]['selected_variants'][vt_id] = 0;
            localStorage.setItem('bcitems', JSON.stringify(bcitems));
        });

        $(document).on('click', '.del', function () {
            var id = $(this).attr('id');
            delete bcitems[id];
            localStorage.setItem('bcitems', JSON.stringify(bcitems));
            $(this).closest('#row_' + id).remove();
        });

        $('#pharmacy').on('change', function (){
            var id = $(this).val();
            document.getElementById('pharmacy_id').value = id;
        });

        $('#reset').click(function (e) {

            bootbox.confirm(lang.r_u_sure, function (result) {
                if (result) {
                    if (localStorage.getItem('bcitems')) {
                        localStorage.removeItem('bcitems');
                    }
                    if (localStorage.getItem('bcstyle')) {
                        localStorage.removeItem('bcstyle');
                    }
                    if (localStorage.getItem('bcsite_name')) {
                        localStorage.removeItem('bcsite_name');
                    }
                    if (localStorage.getItem('bcproduct_name')) {
                        localStorage.removeItem('bcproduct_name');
                    }
                    if (localStorage.getItem('bcprice')) {
                        localStorage.removeItem('bcprice');
                    }
                    if (localStorage.getItem('bccurrencies')) {
                        localStorage.removeItem('bccurrencies');
                    }
                    if (localStorage.getItem('bcunit')) {
                        localStorage.removeItem('bcunit');
                    }
                    if (localStorage.getItem('bccategory')) {
                        localStorage.removeItem('bccategory');
                    }
                    // if (localStorage.getItem('cf_width')) {
                    //     localStorage.removeItem('cf_width');
                    // }
                    // if (localStorage.getItem('cf_height')) {
                    //     localStorage.removeItem('cf_height');
                    // }
                    // if (localStorage.getItem('cf_orientation')) {
                    //     localStorage.removeItem('cf_orientation');
                    // }

                    $('#modal-loading').show();
                    window.location.replace("<?= admin_url('products/print_barcodes'); ?>");
                }
            });
        });

        var old_row_qty;
        $(document).on("focus", '.quantity', function () {
            old_row_qty = $(this).val();
        }).on("change", '.quantity', function () {
            var row = $(this).closest('tr');
            if (!is_numeric($(this).val())) {
                $(this).val(old_row_qty);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            var new_qty = parseFloat($(this).val()),
            item_id = row.attr('data-item-id');
            bcitems[item_id].qty = new_qty;
            localStorage.setItem('bcitems', JSON.stringify(bcitems));
        })
    });

    function add_product_item(item) {
        ac = true;
        if (item == null) {
            return false;
        }
        item_id = item.id;
        if (bcitems[item_id]) {
            bcitems[item_id].qty = parseFloat(bcitems[item_id].qty) + 1;
        } else {
            bcitems[item_id] = item;
            bcitems[item_id]['selected_variants'] = {};
            $.each(item.variants, function () {
                bcitems[item_id]['selected_variants'][this.id] = 1;
            });
        }

        localStorage.setItem('bcitems', JSON.stringify(bcitems));
        loadItems();
        return true;

    }

    function loadItems () {
        var checked= '<?php echo $item_code == '' ? "checked" : "" ; ?>';
        if (localStorage.getItem('bcitems')) {
            $("#bcTable tbody").empty();
            bcitems = JSON.parse(localStorage.getItem('bcitems'));
            $.each(bcitems, function () {

                var item = this;
                var row_no = item.id;
                var vd = '';
                var newTr = $('<tr id="row_' + row_no + '" class="row_' + item.id + '" data-item-id="' + item.id + '"></tr>');
                tr_html = '<td><input name="product[]" type="checkbox" '+checked+' value="' + item.id + '"></td>';
                tr_html += '<td><input name="product_new[]" type="hidden" value="' + item.id + '"><span id="name_' + row_no + '">' + item.name + ' (' + item.code + ')'+' - '+item.avz_item_code+'</span></td>';
                tr_html += '<td><input class="form-control quantity text-center" name="quantity['+item.id+']" type="text" value="' + formatDecimal(item.qty) + '" data-id="' + row_no + '" data-item="' + item.id + '" id="quantity_' + row_no + '" onClick="this.select();"></td>';
              
                newTr.html(tr_html);
                newTr.appendTo("#bcTable");
            });
            $('input[type="radio"]').not('.skip').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%'
            });
            return true;
        }
    }

    document.getElementById('searchByNumber').addEventListener('click', function() {
    var pidValue = document.getElementById('purchase_id').value; 
    var tidValue = document.getElementById('transfer_id').value; 
    var itemCodeValue = document.getElementById('item_code').value;
    var pharmacyValue = document.getElementById('pharmacy').value; 
    if (itemCodeValue) { 
        var baseUrl = window.location.href.split('?')[0]; 
        var newUrl = baseUrl + "?pharmacy=" + encodeURIComponent(pharmacyValue) + "&purchase=" + encodeURIComponent(pidValue) + "&item_code=" + encodeURIComponent(itemCodeValue);
        window.location.href = newUrl; 
    } else {
        alert("Please enter a item number."); 
    }
});

</script>
