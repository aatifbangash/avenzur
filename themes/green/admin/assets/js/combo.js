$(document).ready(function () {
    if (!localStorage.getItem('bundle_name')) {
        localStorage.setItem('bundle_name', '');
    }

    ItemnTotals();
    $('.bootbox').on('hidden.bs.modal', function (e) {
        $('#add_item').focus();
    });
    $('body a, body button').attr('tabindex', -1);
    check_add_item_val();
    if (site.settings.set_focus != 1) {
        $('#add_item').focus();
    }

    //localStorage.clear();
    // If there is any item in localStorage
    if (localStorage.getItem('comboitems')) {
        loadItems();
    }

    // clear localStorage and reload
    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('comboitems')) {
                    localStorage.removeItem('comboitems');
                }
                if (localStorage.getItem('combo_name')) {
                    localStorage.removeItem('combo_name');
                }
                if (localStorage.getItem('buy_quantity')) {
                    localStorage.removeItem('buy_quantity');
                }
                if (localStorage.getItem('sg_primary_product')) {
                    localStorage.removeItem('sg_primary_product');
                }
                if (localStorage.getItem('primary_product_id')) {
                    localStorage.removeItem('primary_product_id');
                }

                $('#modal-loading').show();
                location.reload();
            }
        });
    });

    // save and load the fields in and/or from localStorage
    $('#combo_name').change(function (e) {
        localStorage.setItem('combo_name', $(this).val());
    });
    if (combo_name = localStorage.getItem('combo_name')) {
        $('#combo_name').val(combo_name);
    }


    $('#buy_quantity').change(function (e) {
        localStorage.setItem('buy_quantity', $(this).val());
    });
    if (buy_quantity = localStorage.getItem('buy_quantity')) {
        $('#buy_quantity').val(buy_quantity);
    }

    $('.cls_sg_primary_product').change(function (e) {
        localStorage.setItem('sg_primary_product', $(this).val());
        localStorage.setItem('primary_product_id', $('.cls_primary_product_id').val());
    });
    if (sg_primary_product = localStorage.getItem('sg_primary_product')) {
        $('.cls_sg_primary_product').val(sg_primary_product);
    } 
    $('.cls_primary_product_id').change(function (e) {
        localStorage.setItem('primary_product_id', $(this).val());
    });
    if (primary_product_id = localStorage.getItem('primary_product_id')) {
        $('.cls_primary_product_id').val(primary_product_id);
    }
 

    // if (qawarehouse = localStorage.getItem('qawarehouse')) {
    //     $('#qawarehouse').select2("val", qawarehouse);
    // }

    //$(document).on('change', '#combo_description', function (e) {
        $('#combo_description').redactor('destroy');
        $('#combo_description').redactor({
            buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
            formattingTags: ['p', 'pre', 'h3', 'h4'],
            minHeight: 100,
            changeCallback: function (e) {
                var v = this.get();
                localStorage.setItem('combo_description', v);
            }
        });
        if (combo_description = localStorage.getItem('combo_description')) {
            $('#combo_description').redactor('set', combo_description);
        }

    // prevent default action upon enter
    $('body').bind('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
        if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });


    /* ----------------------
     * Delete Row Method
     * ---------------------- */

    $(document).on('click', '.qadel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete comboitems[item_id];
        row.remove();
        if(comboitems.hasOwnProperty(item_id)) { } else {
            localStorage.setItem('comboitems', JSON.stringify(comboitems));
            loadItems();
            return;
        }
    });

    /* --------------------------
     * Edit Row Quantity Method
     -------------------------- */

    $(document).on("change", '.budiscount', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var discount = parseFloat($(this).val());
       var item_id = row.attr('data-item-id');
        comboitems[item_id].row.discount = discount;   
        // comboitems[item_id].row.batchno = row.find('.rbatchno').val(); 
        // comboitems[item_id].row.expiry = row.find('.rexpiry').val(); 
        // comboitems[item_id].row.sale_price = row.find('.rsaleprice').val(); 
        // comboitems[item_id].row.unit_cost = row.find('.runitcost').val();   
        // comboitems[item_id].row.serial = row.find('.rserial').val();  
        localStorage.setItem('comboitems', JSON.stringify(comboitems));
        loadItems(); 
    }); 
    
    $(document).on("change", '.rquantity', function () {
        var row = $(this).closest('tr');
        var quantity = $(this).val();
       var item_id = row.attr('data-item-id');
        comboitems[item_id].row.quantity = quantity;
        localStorage.setItem('comboitems', JSON.stringify(comboitems));
    });

    $(document).on("change", '.rtype', function () {
        var row = $(this).closest('tr');
        var new_type = $(this).val(),
        item_id = row.attr('data-item-id');
        comboitems[item_id].row.type = new_type;
        localStorage.setItem('comboitems', JSON.stringify(comboitems));
    });

    $(document).on("change", '.rvariant', function () {
        var row = $(this).closest('tr');
        var new_opt = $(this).val(),
        item_id = row.attr('data-item-id');
        comboitems[item_id].row.option = new_opt;
        localStorage.setItem('comboitems', JSON.stringify(comboitems));
    });


});


/* -----------------------
 * Load Items to table
 ----------------------- */

function loadItems() {

    if (localStorage.getItem('comboitems')) {
        count = 1;
        an = 1;  
        $("#qaTable tbody").empty(); 
        comboitems = JSON.parse(localStorage.getItem('comboitems'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(comboitems, function(o){return [parseInt(o.order)];}) : comboitems;
        $.each(sortedItems, function () {
        
            var item = this;
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            item.order = item.order ? item.order : new Date().getTime();
            var product_id = item.row.id, oqty = item.row.oqty, item_qty = item.row.qty, item_option = item.row.option, item_code = item.row.code, item_serial = item.row.serial, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
            var type = item.row.type ? item.row.type : '';  
            var batchno= item.row.batchno;
            var expiry= item.row.expiry;
            var sale_price= item.row.price;
            var quantity= item.row.quantity; 
            var discount= item.row.discount; 

          
            // var opt = $("<select id=\"poption\" name=\"variant\[\]\" class=\"form-control select rvariant\" />");
            // if(item.options !== false) {
            //     $.each(item.options, function () {
            //         if (item.row.option == this.id)
            //             $("<option />", {value: this.id, text: this.name, selected: 'selected'}).appendTo(opt);
            //         else
            //             $("<option />", {value: this.id, text: this.name}).appendTo(opt);
            //     });
            // } else {
            //     $("<option />", {value: 0, text: 'n/a'}).appendTo(opt);
            //     opt = opt.hide();
            // }

            var row_no = item.id;
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td width="60%"><input name="product_id[]" type="hidden" class="rid" value="' + product_id + '"><span class="sname" id="name_' + row_no + '">' + item_code +' - ' + item_name +'</span></td>';
         //   tr_html += '<td>'+(opt.get(0).outerHTML)+'</td>';
          //  tr_html += '<td><input class="form-control rbatchno" name="batchno[]" type="text" value="'+batchno+'" autocomplete="off"></td>';
         //   tr_html += '<td><input class="form-control date rexpiry" name="expiry[]" type="text" value="'+expiry+'" autocomplete="off"></td>';
            tr_html += '<td>'+sale_price+'</td>';
            tr_html += '<td><input class="form-control rquantity" name="quantity[]" type="text" value="'+quantity+'" autocomplete="off"   data-item-id="' + item_id + '"  style="width:100px"></td>';
            tr_html += '<td><input class="form-control budiscount" name="discount[]" type="text" value="'+discount+'" autocomplete="off"   data-item-id="' + item_id + '"  style="width:100px"></td>';
             
         //   tr_html += '<td><input class="form-control runitcost" name="unit_cost[]" type="text" value="'+unit_cost+'" autocomplete="off"></td>';
         //   tr_html += '<td><select name="type[]" class="form-contol select rtype" style="width:100%;"><option value="subtraction"'+(type == 'subtraction' ? ' selected' : '')+'>'+type_opt.subtraction+'</option><option value="addition"'+(type == 'addition' ? ' selected' : '')+'>'+type_opt.addition+'</option></select></td>';
        //    tr_html += '<td><input class="form-control text-center rquantity" tabindex="'+((site.settings.set_focus == 1) ? an : (an+1))+'" name="quantity[]" type="text" value="' + formatQuantity2(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();"><input type="hidden" name="edit_quantity[]" value="'+oqty+'"></td>';
            if (site.settings.product_serial == 1) {
           //     tr_html += '<td class="text-right"><input class="form-control input-sm rserial" name="serial[]" type="text" id="serial_' + row_no + '" value="'+item_serial+'" autocomplete="off"></td>';
            }
            tr_html += '<td class="text-center"><i class="fa fa-times tip qadel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.prependTo("#qaTable");
            count += parseFloat(item_qty);
            an++;

        });

        var col = 7;
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th><th class="text-center">' + formatQty(parseFloat(count) - 1) + '</th>';
        if (site.settings.product_serial == 1) { tfoot += '<th></th>'; }
        tfoot += '<th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
     //   $('#qaTable tfoot').html(tfoot);
        $('select.select').select2({minimumResultsForSearch: 7});
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $("html, body").animate({scrollTop: $('#sticker').offset().top}, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
        set_page_focus();
    }
}

/* -----------------------------
 * Add Purchase Item Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */
function add_combo_item(item) {

    if (count == 1) {
        comboitems = {};
    }
    if (item == null)
        return;

    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (comboitems[item_id]) {

        var new_qty = parseFloat(comboitems[item_id].row.qty) + 1;
        comboitems[item_id].row.base_quantity = new_qty;
        if(comboitems[item_id].row.unit != comboitems[item_id].row.base_unit) {
            $.each(comboitems[item_id].units, function(){
                if (this.id == comboitems[item_id].row.unit) {
                    comboitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        comboitems[item_id].row.qty = new_qty;

       

    } else {
        comboitems[item_id] = item;
    }  
    comboitems[item_id].order = new Date().getTime();
    localStorage.setItem('comboitems', JSON.stringify(comboitems));
    loadItems();
    return true;
}

if (typeof (Storage) === "undefined") {
    $(window).bind('beforeunload', function (e) {
        if (count > 1) {
            var message = "You will loss data!";
            return message;
        }
    });
}
