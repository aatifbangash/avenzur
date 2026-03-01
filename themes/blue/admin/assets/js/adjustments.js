// Format batch option for Select2 display
function formatBatchOption(option) {
    if (!option.id) {
        return option.text;
    }
    
    var $option = $(option.element);
    var avz_code = $option.data('avz') || $option.attr('data-avz');
    
    if (!avz_code || avz_code === '' || avz_code === 'undefined' || avz_code === 'null') {
        return option.text;
    }
    
    // Create markup with inline styles that Select2 will render
    var markup = '<span class="batch-option">' + 
                 '<span class="batch-no">' + option.text + '</span> ' +
                 '<span class="avz-code" style="color: #3498db; font-size: 11px;">(' + avz_code + ')</span>' +
                 '</span>';
    
    return $(markup);
}

$(document).ready(function () {
    if (!localStorage.getItem('qaref')) {
        localStorage.setItem('qaref', '');
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
    if (localStorage.getItem('qaitems')) {
        loadItems();
    }

    // clear localStorage and reload
    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('qaitems')) {
                    localStorage.removeItem('qaitems');
                }
                if (localStorage.getItem('qaref')) {
                    localStorage.removeItem('qaref');
                }
                if (localStorage.getItem('qawarehouse')) {
                    localStorage.removeItem('qawarehouse');
                }
                if (localStorage.getItem('qanote')) {
                    localStorage.removeItem('qanote');
                }
                if (localStorage.getItem('qadate')) {
                    localStorage.removeItem('qadate');
                }

                $('#modal-loading').show();
                location.reload();
            }
        });
    });

    // save and load the fields in and/or from localStorage
    $('#qaref').change(function (e) {
        localStorage.setItem('qaref', $(this).val());
    });
    if (qaref = localStorage.getItem('qaref')) {
        $('#qaref').val(qaref);
    }
    $('#qawarehouse').change(function (e) {
        localStorage.setItem('qawarehouse', $(this).val());
    });
    if (qawarehouse = localStorage.getItem('qawarehouse')) {
        $('#qawarehouse').select2("val", qawarehouse);
    }

    //$(document).on('change', '#qanote', function (e) {
        $('#qanote').redactor('destroy');
        $('#qanote').redactor({
            buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic', 'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'],
            formattingTags: ['p', 'pre', 'h3', 'h4'],
            minHeight: 100,
            changeCallback: function (e) {
                var v = this.get();
                localStorage.setItem('qanote', v);
            }
        });
        if (qanote = localStorage.getItem('qanote')) {
            $('#qanote').redactor('set', qanote);
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
        delete qaitems[item_id];
        row.remove();
        if(qaitems.hasOwnProperty(item_id)) { } else {
            saveCurrentGridValues();
            localStorage.setItem('qaitems', JSON.stringify(qaitems));
            loadItems();
            return;
        }
    });

    /* --------------------------
     * Edit Row Quantity Method
     -------------------------- */

    $(document).on("change", '.rquantity', function () {
        var row = $(this).closest('tr');
        if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var new_qty = parseFloat($(this).val()),
        item_id = row.attr('data-item-id');
        qaitems[item_id].row.qty = new_qty;
        saveCurrentGridValues();
        localStorage.setItem('qaitems', JSON.stringify(qaitems));
        loadItems();
    });

    $(document).on("change", '.rtype', function () {
        var row = $(this).closest('tr');
        var new_type = $(this).val(),
        item_id = row.attr('data-item-id');
        qaitems[item_id].row.type = new_type;
        localStorage.setItem('qaitems', JSON.stringify(qaitems));
    });

    $(document).on("change", '.rvariant', function () {
        var row = $(this).closest('tr');
        var new_opt = $(this).val(),
        item_id = row.attr('data-item-id');
        qaitems[item_id].row.option = new_opt;
        localStorage.setItem('qaitems', JSON.stringify(qaitems));
    });

    // Handle AVZ Code change
    $(document).on("focus", '.ravzcode', function () {
        old_avz_code = $(this).val();
    });
    
    $(document).on("change", '.ravzcode', function () {
        var row = $(this).closest('tr');
        var avz_code = $(this).val(),
        item_id = row.attr('data-item-id');
        qaitems[item_id].row.avz_code = avz_code;
        localStorage.setItem('qaitems', JSON.stringify(qaitems));
    });

    // Handle Purchase Price change
    $(document).on("change", '.rpurchase', function () {
        var row = $(this).closest('tr');
        var purchase_price = $(this).val(),
        item_id = row.attr('data-item-id');
        qaitems[item_id].row.purchase_price = purchase_price;
        localStorage.setItem('qaitems', JSON.stringify(qaitems));
    });

    // Handle Cost Price change
    $(document).on("change", '.rcost', function () {
        var row = $(this).closest('tr');
        var cost_price = $(this).val(),
        item_id = row.attr('data-item-id');
        qaitems[item_id].row.cost_price = cost_price;
        localStorage.setItem('qaitems', JSON.stringify(qaitems));
    });

    // Handle Sale Price change
    $(document).on("change", '.rsale', function () {
        var row = $(this).closest('tr');
        var sale_price = $(this).val(),
        item_id = row.attr('data-item-id');
        qaitems[item_id].row.sale_price = sale_price;
        localStorage.setItem('qaitems', JSON.stringify(qaitems));
    });

    // Handle Batch input change (for addition type)
    $(document).on("change", '.rbatch-input', function () {
        var row = $(this).closest('tr');
        var batch_no = $(this).val(),
        item_id = row.attr('data-item-id');
        qaitems[item_id].row.batch_no = batch_no;
        localStorage.setItem('qaitems', JSON.stringify(qaitems));
    });

    // Handle Expiry change and blur
    $(document).on("change blur", '.rexpiry', function () {
        var item_id = $(this).closest('tr').attr('data-item-id');
        var expiry_val = $(this).val();
        console.log('Expiry event (change/blur) - item_id:', item_id, 'expiry:', expiry_val, 'qaitems[item_id] exists:', !!qaitems[item_id]);
        if (qaitems[item_id]) {
            qaitems[item_id].row.expiry = expiry_val;
            localStorage.setItem('qaitems', JSON.stringify(qaitems));
            console.log('Expiry saved to localStorage:', qaitems[item_id].row.expiry);
        } else {
            console.error('qaitems[' + item_id + '] does not exist!');
        }
    });


});


/* -----------------------
 * Save current grid values to localStorage before rebuilding
 ----------------------- */
function saveCurrentGridValues() {
    if (!qaitems || Object.keys(qaitems).length === 0) {
        return;
    }
    
    $('#qaTable tbody tr').each(function() {
        var $row = $(this);
        var item_id = $row.attr('data-item-id');
        
        if (item_id && qaitems[item_id]) {
            // Save all current input values to qaitems
            var expiry = $row.find('.rexpiry').val();
            var batch = $row.find('.rbatch').val() || $row.find('.rbatch-input').val();
            var avz_code = $row.find('.ravzcode').val();
            var purchase_price = $row.find('.rpurchase').val();
            var cost_price = $row.find('.rcost').val();
            var sale_price = $row.find('.rsale').val();
            var quantity = $row.find('.rquantity').val();
            
            // Update qaitems with current values
            if (expiry !== undefined) qaitems[item_id].row.expiry = expiry;
            if (batch !== undefined) qaitems[item_id].row.batch_no = batch;
            if (avz_code !== undefined) qaitems[item_id].row.avz_code = avz_code;
            if (purchase_price !== undefined) qaitems[item_id].row.purchase_price = purchase_price;
            if (cost_price !== undefined) qaitems[item_id].row.cost_price = cost_price;
            if (sale_price !== undefined) qaitems[item_id].row.sale_price = sale_price;
            if (quantity !== undefined) qaitems[item_id].row.qty = quantity;
        }
    });
    
    // Save to localStorage
    localStorage.setItem('qaitems', JSON.stringify(qaitems));
}

/* -----------------------
 * Load Items to table
 ----------------------- */

function loadItems() {

    if (localStorage.getItem('qaitems')) {
        count = 1;
        an = 1;
        $("#qaTable tbody").empty();
        qaitems = JSON.parse(localStorage.getItem('qaitems'));
        sortedItems = (site.settings.item_addition == 1) ? _.sortBy(qaitems, function(o){return [parseInt(o.order)];}) : qaitems;
        $.each(sortedItems, function () {
            var item = this;
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            item.order = item.order ? item.order : new Date().getTime();
            var product_id = item.row.id, oqty = item.row.oqty, item_qty = item.row.qty, item_option = item.row.option, item_code = item.row.code, item_serial = item.row.serial, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
            var type = item.row.type ? item.row.type : 'addition';
            var batch_no = item.row.batch_no ? item.row.batch_no : '';
            var expiry = item.row.expiry ? item.row.expiry : '';
            var current_qty = item.row.current_qty ? item.row.current_qty : 0;
            var avz_code = item.row.avz_code ? item.row.avz_code : '';
            var purchase_price = item.row.purchase_price ? item.row.purchase_price : '';
            var cost_price = item.row.cost_price ? item.row.cost_price : '';
            var sale_price = item.row.sale_price ? item.row.sale_price : '';

            var row_no = item.id;
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html = '<td><input name="product_id[]" type="hidden" class="rid" value="' + product_id + '"><span class="sname" id="name_' + row_no + '">' + item_code +' - ' + item_name +'</span></td>';
            tr_html += '<td><select name="type[]" class="form-control select rtype" data-row="' + row_no + '" style="width:100%;"><option value="subtraction"'+(type == 'subtraction' ? ' selected' : '')+'>'+type_opt.subtraction+'</option><option value="addition"'+(type == 'addition' ? ' selected' : '')+'>'+type_opt.addition+'</option></select></td>';
            
            // Batch field - dropdown for subtraction, input for addition
            if (type == 'subtraction') {
                tr_html += '<td><select name="batch_no[]" class="form-control rbatch" data-row="' + row_no + '" data-product="' + product_id + '" id="batch_' + row_no + '" style="width:100%;"><option value="">Select</option></select></td>';
            } else {
                tr_html += '<td><input class="form-control text-center rbatch-input" name="batch_no[]" type="text" value="' + batch_no + '" id="batch_' + row_no + '"></td>';
            }
            
            // Expiry field - readonly for subtraction, editable for addition
            var expiry_readonly = type == 'subtraction' ? 'readonly' : '';
            tr_html += '<td><input class="form-control text-center date rexpiry" ' + expiry_readonly + ' name="expiry[]" autocomplete="off" type="text" value="' + expiry + '" data-id="' + row_no + '" data-item="' + item_id + '" id="expiry_' + row_no + '" style="font-size: 11px; padding: 4px 2px;"></td>';
            
            // AVZ Code field
            tr_html += '<td><input class="form-control text-center ravzcode" name="avz_code[]" type="text" value="' + avz_code + '" id="avz_' + row_no + '"></td>';
            
            // Purchase Price field
            tr_html += '<td><input class="form-control text-center rpurchase" name="purchase_price[]" type="text" value="' + purchase_price + '" id="purchase_' + row_no + '"></td>';
            
            // Cost Price field
            tr_html += '<td><input class="form-control text-center rcost" name="cost_price[]" type="text" value="' + cost_price + '" id="cost_' + row_no + '"></td>';
            
            // Sale Price field
            tr_html += '<td><input class="form-control text-center rsale" name="sale_price[]" type="text" value="' + sale_price + '" id="sale_' + row_no + '"></td>';
            
            // Quantity field with current qty display for subtraction only
            var current_qty_html = type == 'subtraction' ? '<small class="text-muted" id="current_qty_' + row_no + '">Current: ' + formatQuantity2(current_qty) + '</small>' : '';
            tr_html += '<td><input class="form-control text-center rquantity" tabindex="'+((site.settings.set_focus == 1) ? an : (an+1))+'" name="quantity[]" type="text" value="' + formatQuantity2(item_qty) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();">' + current_qty_html + '<input type="hidden" name="edit_quantity[]" value="'+oqty+'"></td>';
            
            tr_html += '<td class="text-center"><i class="fa fa-times tip qadel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.prependTo("#qaTable");
            count += parseFloat(item_qty);
            an++;

        });

        var col = 8; // Product + Type + Batch + Expiry + AVZ + Purchase + Cost + Sale
        var tfoot = '<tr id="tfoot" class="tfoot active"><th colspan="'+col+'">Total</th><th class="text-center">' + formatQty(parseFloat(count) - 1) + '</th>';
        tfoot += '<th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        $('#qaTable tfoot').html(tfoot);
        $('select.select').select2({minimumResultsForSearch: 7});
        
        // Load batches for each product with type 'subtraction'
        $('.rbatch').each(function() {
            var row_no = $(this).data('row');
            var product_id = $(this).data('product');
            var $row = $(this).closest('tr');
            var item_id = $row.data('item-id');
            var current_batch = qaitems[item_id] ? qaitems[item_id].row.batch_no : '';
            console.log('Loading batches for row ' + row_no + ', item_id: ' + item_id + ', current_batch: ' + current_batch);
            loadProductBatches(product_id, row_no, current_batch);
        });
        
        // Datepicker is initialized globally in core.js for all .date elements
        // No need for custom initialization here
        
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
function add_adjustment_item(item) {

    if (count == 1) {
        qaitems = {};
    }
    if (item == null)
        return;

    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (qaitems[item_id]) {

        var new_qty = parseFloat(qaitems[item_id].row.qty) + 1;
        qaitems[item_id].row.base_quantity = new_qty;
        if(qaitems[item_id].row.unit != qaitems[item_id].row.base_unit) {
            $.each(qaitems[item_id].units, function(){
                if (this.id == qaitems[item_id].row.unit) {
                    qaitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        qaitems[item_id].row.qty = new_qty;
        
        // Preserve existing values for expiry, batch, prices if they exist
        // This prevents fields from being cleared when quantity is updated
        if (item.row.expiry) qaitems[item_id].row.expiry = item.row.expiry;
        if (item.row.batch_no) qaitems[item_id].row.batch_no = item.row.batch_no;
        if (item.row.purchase_price) qaitems[item_id].row.purchase_price = item.row.purchase_price;
        if (item.row.cost_price) qaitems[item_id].row.cost_price = item.row.cost_price;
        if (item.row.sale_price) qaitems[item_id].row.sale_price = item.row.sale_price;
        if (item.row.avz_code || item.row.avz_item_code) qaitems[item_id].row.avz_code = item.row.avz_code || item.row.avz_item_code;
        if (item.row.type) qaitems[item_id].row.type = item.row.type;

    } else {
        qaitems[item_id] = item;
        
        // Initialize all fields to prevent undefined values
        qaitems[item_id].row.batch_no = item.row.batch_no || '';
        qaitems[item_id].row.expiry = item.row.expiry || '';
        qaitems[item_id].row.purchase_price = item.row.purchase_price || '';
        qaitems[item_id].row.cost_price = item.row.cost_price || '';
        qaitems[item_id].row.sale_price = item.row.sale_price || '';
        qaitems[item_id].row.avz_code = item.row.avz_code || item.row.avz_item_code || '';
        qaitems[item_id].row.type = item.row.type || 'addition';
    }
    qaitems[item_id].order = new Date().getTime();
    
    // Save current grid values before rebuilding to prevent data loss
    saveCurrentGridValues();
    
    localStorage.setItem('qaitems', JSON.stringify(qaitems));
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

// Load product batches for a specific product
function loadProductBatches(product_id, row_no, selected_batch) {
    var warehouse_id = $('#qawarehouse').val();
    
    if (!warehouse_id) {
        return;
    }
    
    $.ajax({
        type: 'GET',
        url: site.base_url + 'products/get_product_batches',
        data: {
            product_id: product_id,
            warehouse_id: warehouse_id
        },
        dataType: 'json',
        success: function(data) {
            console.log('Batch data received for row ' + row_no + ', selected_batch: ' + selected_batch);
            
            var batch_select = $('#batch_' + row_no);
            batch_select.empty();
            batch_select.append('<option value="">Select</option>');
            
            var batch_found = false;
            
            if (data && data.length > 0) {
                $.each(data, function(index, batch) {
                    console.log('Processing batch:', batch);
                    var display_text = batch.batch_no;
                    if (batch.avz_item_code) {
                        display_text += ' (' + batch.avz_item_code + ')';
                    }
                    var selected_attr = '';
                    
                    // Pre-select if this batch matches the selected_batch parameter
                    if (selected_batch && batch.batch_no == selected_batch) {
                        console.log('Batch MATCH found! Batch: ' + batch.batch_no + ' == Selected: ' + selected_batch);
                        selected_attr = ' selected';
                        batch_found = true;
                        
                        // Auto-populate fields when batch is pre-selected
                        $('#expiry_' + row_no).val(batch.expiry || '');
                        $('#purchase_' + row_no).val(batch.purchase_price || '');
                        $('#cost_' + row_no).val(batch.cost_price || '');
                        $('#sale_' + row_no).val(batch.sale_price || '');
                        $('#avz_' + row_no).val(batch.avz_item_code || '');
                        $('#current_qty_' + row_no).text('Current: ' + formatQuantity2(batch.quantity || 0));
                    }
                    
                    batch_select.append('<option value="' + batch.batch_no + '"' + selected_attr + ' data-expiry="' + batch.expiry + '" data-qty="' + batch.quantity + '" data-purchase="' + (batch.purchase_price || '') + '" data-cost="' + (batch.cost_price || '') + '" data-sale="' + (batch.sale_price || '') + '" data-avz="' + (batch.avz_item_code || '') + '">' + display_text + '</option>');
                });
                
            } else {
                batch_select.append('<option value="">No batches available</option>');
            }
            
            batch_select.select2({
                minimumResultsForSearch: -1,
                width: '100%',
                templateResult: formatBatchOption,
                templateSelection: formatBatchOption,
                escapeMarkup: function(markup) { 
                    if (typeof markup === 'string') {
                        return markup;
                    }
                    // If it's a jQuery object, get the HTML
                    if (markup && markup.jquery) {
                        return markup[0].outerHTML;
                    }
                    return markup;
                }
            });
            
            // Set the selected value after Select2 initialization
            if (selected_batch && batch_found) {
                batch_select.val(selected_batch).trigger('change.select2');
            }
        }
    });
}

// Handle batch selection change
$(document).on('change', '.rbatch', function() {
    var selected_option = $(this).find('option:selected');
    var row_no = $(this).data('row');
    var item_id = $(this).closest('tr').data('item-id');
    
    // Use attr() instead of data() for Select2 compatibility
    var expiry = selected_option.attr('data-expiry');
    var qty = selected_option.attr('data-qty');
    var purchase_price = selected_option.attr('data-purchase');
    var cost_price = selected_option.attr('data-cost');
    var sale_price = selected_option.attr('data-sale');
    var avz_code = selected_option.attr('data-avz');

    console.log('Selected batch data:', {
        expiry: expiry,
        qty: qty,   
        purchase_price: purchase_price,
        cost_price: cost_price,
        sale_price: sale_price,
        avz_code: avz_code
    });
    
    // Only update if new data is available, otherwise preserve existing values
    if (expiry && expiry !== 'undefined' && expiry !== '') {
        $('#expiry_' + row_no).val(expiry);
        if (qaitems[item_id]) {
            qaitems[item_id].row.expiry = expiry;
        }
    }
    
    // Update prices - check for undefined, null, and empty string but allow "0"
    if (purchase_price !== undefined && purchase_price !== null && purchase_price !== '' && purchase_price !== 'undefined') {
        $('#purchase_' + row_no).val(purchase_price);
        if (qaitems[item_id]) {
            qaitems[item_id].row.purchase_price = purchase_price;
        }
    }
    
    if (cost_price !== undefined && cost_price !== null && cost_price !== '' && cost_price !== 'undefined') {
        $('#cost_' + row_no).val(cost_price);
        if (qaitems[item_id]) {
            qaitems[item_id].row.cost_price = cost_price;
        }
    }
    
    if (sale_price !== undefined && sale_price !== null && sale_price !== '' && sale_price !== 'undefined') {
        $('#sale_' + row_no).val(sale_price);
        if (qaitems[item_id]) {
            qaitems[item_id].row.sale_price = sale_price;
        }
    }
    
    // Update AVZ code
    if (avz_code !== undefined && avz_code !== null && avz_code !== '' && avz_code !== 'undefined') {
        $('#avz_' + row_no).val(avz_code);
        if (qaitems[item_id]) {
            qaitems[item_id].row.avz_code = avz_code;
        }
    }
    
    // Update current quantity display
    $('#current_qty_' + row_no).text('Current: ' + formatQuantity2(qty || 0));
    
    // Update batch_no and current_qty in localStorage
    if (qaitems[item_id]) {
        qaitems[item_id].row.batch_no = $(this).val();
        qaitems[item_id].row.current_qty = qty || 0;
        localStorage.setItem('qaitems', JSON.stringify(qaitems));
    }
});

// Reload batches when warehouse changes
$(document).on('change', '#qawarehouse', function() {
    if (confirm('Changing warehouse will clear all items. Continue?')) {
        localStorage.removeItem('qaitems');
        loadItems();
    } else {
        // Revert to previous warehouse
        var prev_warehouse = localStorage.getItem('qawarehouse');
        $(this).val(prev_warehouse).trigger('change.select2');
    }
});

// Handle type change (addition/subtraction)
$(document).on('change', '.rtype', function() {
    var row_no = $(this).data('row');
    var type = $(this).val();
    var $row = $(this).closest('tr');
    var product_id = $row.find('.rid').val();
    var item_id = $row.data('item-id');
    
    // Get current values before changing UI
    var current_batch = qaitems[item_id] ? qaitems[item_id].row.batch_no : '';
    var current_expiry = qaitems[item_id] ? qaitems[item_id].row.expiry : '';
    var current_purchase = qaitems[item_id] ? qaitems[item_id].row.purchase_price : '';
    var current_cost = qaitems[item_id] ? qaitems[item_id].row.cost_price : '';
    var current_sale = qaitems[item_id] ? qaitems[item_id].row.sale_price : '';
    var current_avz = qaitems[item_id] ? qaitems[item_id].row.avz_code : '';
    var saved_current_qty = qaitems[item_id] ? (qaitems[item_id].row.current_qty || 0) : 0;
    
    // Get current batch cell
    var $batchCell = $row.find('td').eq(2); // Batch column
    var $expiryInput = $('#expiry_' + row_no);
    var $qtyCell = $row.find('td').eq(8); // Quantity column (0-indexed: Product=0, Type=1, Batch=2, Expiry=3, AVZ=4, Purchase=5, Cost=6, Sale=7, Qty=8)
    
    if (type == 'subtraction') {
        // Change batch to dropdown
        var batch_html = '<select name="batch_no[]" class="form-control rbatch" data-row="' + row_no + '" data-product="' + product_id + '" id="batch_' + row_no + '" style="width:100%;"><option value="">Select</option></select>';
        $batchCell.html(batch_html);
        
        // Make expiry readonly
        $expiryInput.attr('readonly', true).removeClass('date').css({'font-size': '11px', 'padding': '4px 2px'});
        
        // Add current quantity display
        if ($qtyCell.find('#current_qty_' + row_no).length == 0) {
            $qtyCell.find('.rquantity').after('<small class="text-muted" id="current_qty_' + row_no + '">Current: ' + formatQuantity2(saved_current_qty) + '</small>');
        }
        
        // Load batches and preserve values if batch exists
        loadProductBatches(product_id, row_no, current_batch);
        
    } else {
        // Change batch to input
        var batch_html = '<input class="form-control text-center rbatch-input" name="batch_no[]" type="text" value="' + current_batch + '" id="batch_' + row_no + '">';
        $batchCell.html(batch_html);
        
        // Make expiry editable and preserve value
        $expiryInput.attr('readonly', false).val(current_expiry).css({'font-size': '11px', 'padding': '4px 2px'});
        
        // Remove current quantity display
        $qtyCell.find('#current_qty_' + row_no).remove();
        
        // Datepicker is initialized globally in core.js for all .date elements
        // Just ensure the expiry field has the correct value
        if ($expiryInput.length) {
            $expiryInput.val(current_expiry);
        }
    }
    
    // Update localStorage - preserve all existing values
    if (qaitems[item_id]) {
        qaitems[item_id].row.type = type;
        // Don't clear other values - keep them
        qaitems[item_id].row.batch_no = current_batch;
        qaitems[item_id].row.expiry = current_expiry;
        qaitems[item_id].row.purchase_price = current_purchase;
        qaitems[item_id].row.cost_price = current_cost;
        qaitems[item_id].row.sale_price = current_sale;
        qaitems[item_id].row.avz_code = current_avz;
        localStorage.setItem('qaitems', JSON.stringify(qaitems));
    }
});
