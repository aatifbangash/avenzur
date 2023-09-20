$(document).ready(function (e) {
    $('body a, body button').attr('tabindex', -1);
    check_add_item_val();
    if (site.settings.set_focus != 1) {
        $('#add_item').focus();
    }

    // $('#add_return, #edit_return').attr('disabled', true);
    $(document).on('change', '.rserial', function () {
        var item_id = $(this).closest('tr').attr('data-item-id');
        reitems[item_id].row.serial = $(this).val();
        localStorage.setItem('reitems', JSON.stringify(reitems));
    });

    // If there is any item in localStorage
    if (localStorage.getItem('reitems')) {
        loadItems();
    }

    var old_row_batchno;
    var currTabIndex;
    $(document)
        .on('focus', '.rbatchno', function () {
            old_row_batchno = $(this).val();
            currTabIndex = $(this).prop('tabindex');
        }).on('change', '.rbatchno', function () {
            var row = $(this).closest('tr');
            //var new_batchno = parseFloat($(this).val()),
            var new_batchno = $(this).val(),
                item_id = row.attr('data-item-id');
           
           var batchExpiry =  $(this).find(':selected').data('batchexpiry');
           reitems[item_id].row.expiry = batchExpiry;

           var batchQty =  $(this).find(':selected').data('batchqty');
           reitems[item_id].row.batchQuantity = batchQty;

           var batchPurchaseCost =  $(this).find(':selected').data('batchpurchasecost');
           reitems[item_id].row.batchPurchaseCost = batchPurchaseCost;
          

           reitems[item_id].row.batch_no = new_batchno;
            localStorage.setItem('reitems', JSON.stringify(reitems));
            loadItems();
        });
        // .on('blur', '.rbatchno', function () {
        //     var row = $(this).closest('tr');
        //     var new_batchno = $(this).val(),
        //     item_id = row.attr('data-item-id');
        //     var batchfound = findMatchingItemWithSameBatchNo(new_batchno, item_id, reitems);
        //     if(batchfound){
        //         $(this).val('');
        //         reitems[item_id].row.batchno = '';
        //         bootbox.alert("Cannot add same batch number for same product");
        //     }else{
        //         reitems[item_id].row.batchno = new_batchno;
        //         localStorage.setItem('reitems', JSON.stringify(reitems));
        //         //loadItems();
        //     }
        //     //$('[tabindex=' + (currTabIndex + 1) + ']').focus();
            
        // })
        // .on('change', '.rbatchno', function () {
        //     var row = $(this).closest('tr');
        //     /*if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
        //         $(this).val(old_row_batchno);
        //         bootbox.alert(lang.unexpected_value);
        //         return;
        //     }*/
        //     //var new_batchno = parseFloat($(this).val()),
        //     var new_batchno = $(this).val(),
        //         item_id = row.attr('data-item-id');
        //     /*poitems[item_id].row.batchno = new_batchno;
        //     localStorage.setItem('poitems', JSON.stringify(poitems));
        //     loadItems();*/
        // });

    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('reitems')) {
                    localStorage.removeItem('reitems');
                }
                if (localStorage.getItem('rediscount')) {
                    localStorage.removeItem('rediscount');
                }
                if (localStorage.getItem('reshipping')) {
                    localStorage.removeItem('reshipping');
                }
                if (localStorage.getItem('retax2')) {
                    localStorage.removeItem('retax2');
                }
                if (localStorage.getItem('reref')) {
                    localStorage.removeItem('reref');
                }
                if (localStorage.getItem('rewarehouse')) {
                    localStorage.removeItem('rewarehouse');
                }
                if (localStorage.getItem('renote')) {
                    localStorage.removeItem('renote');
                }
                if (localStorage.getItem('reinnote')) {
                    localStorage.removeItem('reinnote');
                }
                if (localStorage.getItem('recustomer')) {
                    localStorage.removeItem('recustomer');
                }
                if (localStorage.getItem('redate')) {
                    localStorage.removeItem('redate');
                }
                if (localStorage.getItem('rebiller')) {
                    localStorage.removeItem('rebiller');
                }

                $('#modal-loading').show();
                location.reload();
            }
        });
    });

    $('#redate').change(function (e) {
        localStorage.setItem('redate', $(this).val());
    });
    if ((redate = localStorage.getItem('redate'))) {
        $('#redate').val(redate);
    }

    $('#reref').change(function (e) {
        localStorage.setItem('reref', $(this).val());
    });
    if ((reref = localStorage.getItem('reref'))) {
        $('#reref').val(reref);
    }

    $('#rebiller').change(function (e) {
        localStorage.setItem('rebiller', $(this).val());
    });
    if ((rebiller = localStorage.getItem('rebiller'))) {
        $('#rebiller').val(rebiller);
    }

    $('#rewarehouse').change(function (e) {
        localStorage.setItem('rewarehouse', $(this).val());
    });
    if ((rewarehouse = localStorage.getItem('rewarehouse'))) {
        $('#rewarehouse').select2('val', rewarehouse);
    }

    $('#recustomer').change(function (e) {
        localStorage.setItem('recustomer', $(this).val());
    });
    if ((recustomer = localStorage.getItem('recustomer'))) {
        $('#recustomer')
            .val(recustomer)
            .select2({
                minimumInputLength: 1,
                data: [],
                initSelection: function (element, callback) {
                    $.ajax({
                        type: 'get',
                        async: false,
                        url: site.base_url + 'customers/getCustomer/' + $(element).val(),
                        dataType: 'json',
                        success: function (data) {
                            callback(data[0]);
                        },
                    });
                },
                ajax: {
                    url: site.base_url + 'customers/suggestions',
                    dataType: 'json',
                    quietMillis: 15,
                    data: function (term, page) {
                        return {
                            term: term,
                            limit: 10,
                        };
                    },
                    results: function (data, page) {
                        if (data.results != null) {
                            return { results: data.results };
                        } else {
                            return { results: [{ id: '', text: 'No Match Found' }] };
                        }
                    },
                },
            });
    }

    $('#retax2').change(function (e) {
        localStorage.setItem('retax2', $(this).val());
        $('#retax2').val($(this).val());
    });
    if ((retax2 = localStorage.getItem('retax2'))) {
        $('#retax2').select2('val', retax2);
    }

    $('#rediscount').change(function (e) {
        localStorage.setItem('rediscount', $(this).val());
    });
    if ((rediscount = localStorage.getItem('rediscount'))) {
        $('#rediscount').val(rediscount);
    }
    $('#reshipping').change(function (e) {
        localStorage.setItem('reshipping', $(this).val());
    });
    if ((reshipping = localStorage.getItem('reshipping'))) {
        $('#reshipping').val(reshipping);
    }

    $('#renote').redactor('destroy');
    $('#renote').redactor({
        buttons: [
            'formatting',
            '|',
            'alignleft',
            'aligncenter',
            'alignright',
            'justify',
            '|',
            'bold',
            'italic',
            'underline',
            '|',
            'unorderedlist',
            'orderedlist',
            '|',
            'link',
            '|',
            'html',
        ],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('renote', v);
        },
    });
    if ((renote = localStorage.getItem('renote'))) {
        $('#renote').redactor('set', renote);
    }

    $('#reinnote').redactor('destroy');
    $('#reinnote').redactor({
        buttons: [
            'formatting',
            '|',
            'alignleft',
            'aligncenter',
            'alignright',
            'justify',
            '|',
            'bold',
            'italic',
            'underline',
            '|',
            'unorderedlist',
            'orderedlist',
            '|',
            'link',
            '|',
            'html',
        ],
        formattingTags: ['p', 'pre', 'h3', 'h4'],
        minHeight: 100,
        changeCallback: function (e) {
            var v = this.get();
            localStorage.setItem('reinnote', v);
        },
    });
    if ((reinnote = localStorage.getItem('reinnote'))) {
        $('#reinnote').redactor('set', reinnote);
    }

    $('body').bind('keypress', function (e) {
        if ($(e.target).hasClass('redactor_editor')) {
            return true;
        }
        if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });

    if (site.settings.tax2 != 0) {
        $('#retax2').change(function () {
            localStorage.setItem('retax2', $(this).val());
            loadItems();
            return;
        });
    }

    var old_rediscount;
    $('#rediscount')
        .focus(function () {
            old_rediscount = $(this).val();
        })
        .change(function () {
            var new_discount = $(this).val() ? $(this).val() : '0';
            if (is_valid_discount(new_discount)) {
                localStorage.removeItem('rediscount');
                localStorage.setItem('rediscount', new_discount);
                loadItems();
                return;
            } else {
                $(this).val(old_rediscount);
                bootbox.alert(lang.unexpected_value);
                return;
            }
        });
    $('#reshipping').change(function () {
        var shipping = $(this).val() ? parseFloat($(this).val()) : 0;
        localStorage.setItem('reshipping', shipping);
        loadItems();
        return;
    });

    $(document).on('click', '.redel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete reitems[item_id];
        row.remove();
        if (reitems.hasOwnProperty(item_id)) {
        } else {
            localStorage.setItem('reitems', JSON.stringify(reitems));
            loadItems();
            return;
        }
    });

    $(document).on('click', '.edit', function () {
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');
        item = reitems[item_id];
        var qty = row.children().children('.rquantity').val(),
            product_option = row.children().children('.roption').val(),
            unit_price = formatDecimal(row.children().children('.ruprice').val()),
            discount = row.children().children('.rdiscount').val();
        if (item.options !== false) {
            $.each(item.options, function () {
                if (this.id == item.row.option && this.price != 0 && this.price != '' && this.price != null) {
                    unit_price = parseFloat(item.row.real_unit_price) + parseFloat(this.price);
                }
            });
        }
        var real_unit_price = item.row.real_unit_price;
        var net_price = unit_price;
        $('#prModalLabel').text(item.row.name + ' (' + item.row.code + ')');
        if (site.settings.tax1) {
            $('#ptax').select2('val', item.row.tax_rate);
            $('#old_tax').val(item.row.tax_rate);
            var item_discount = 0,
                ds = discount ? discount : '0';
            if (ds.indexOf('%') !== -1) {
                var pds = ds.split('%');
                if (!isNaN(pds[0])) {
                    item_discount = formatDecimal(parseFloat((unit_price * parseFloat(pds[0])) / 100), 4);
                } else {
                    item_discount = parseFloat(ds);
                }
            } else {
                item_discount = parseFloat(ds);
            }
            net_price -= item_discount;
            var pr_tax = item.row.tax_rate,
                pr_tax_val = 0;
            if (pr_tax !== null && pr_tax != 0) {
                $.each(tax_rates, function () {
                    if (this.id == pr_tax) {
                        if (this.type == 1) {
                            if (reitems[item_id].row.tax_method == 0) {
                                pr_tax_val = formatDecimal((net_price * parseFloat(this.rate)) / (100 + parseFloat(this.rate)), 4);
                                pr_tax_rate = formatDecimal(this.rate) + '%';
                                net_price -= pr_tax_val;
                            } else {
                                pr_tax_val = formatDecimal((net_price * parseFloat(this.rate)) / 100, 4);
                                pr_tax_rate = formatDecimal(this.rate) + '%';
                            }
                        } else if (this.type == 2) {
                            pr_tax_val = parseFloat(this.rate);
                            pr_tax_rate = this.rate;
                        }
                    }
                });
            }
        }
        if (site.settings.product_serial !== 0) {
            $('#pserial').val(row.children().children('.rserial').val());
        }
        var opt = '<p style="margin: 12px 0 0 0;">n/a</p>';
        if (item.options !== false) {
            var o = 1;
            opt = $('<select id="poption" name="poption" class="form-control select" />');
            $.each(item.options, function () {
                if (o == 1) {
                    if (product_option == '') {
                        product_variant = this.id;
                    } else {
                        product_variant = product_option;
                    }
                }
                $('<option />', { value: this.id, text: this.name }).appendTo(opt);
                o++;
            });
        } else {
            product_variant = 0;
        }

        uopt = '<p style="margin: 12px 0 0 0;">n/a</p>';
        if (item.units) {
            uopt = $('<select id="punit" name="punit" class="form-control select" />');
            $.each(item.units, function () {
                if (this.id == item.row.unit) {
                    $('<option />', { value: this.id, text: this.name, selected: true }).appendTo(uopt);
                } else {
                    $('<option />', { value: this.id, text: this.name }).appendTo(uopt);
                }
            });
        }

        $('#poptions-div').html(opt);
        $('#punits-div').html(uopt);
        $('select.select').select2({ minimumResultsForSearch: 7 });
        $('#pquantity').val(qty);
        $('#old_qty').val(qty);
        $('#pprice').val(unit_price);
        $('#punit_price').val(formatDecimal(parseFloat(unit_price) + parseFloat(pr_tax_val)));
        $('#poption').select2('val', item.row.option);
        $('#old_price').val(unit_price);
        $('#row_id').val(row_id);
        $('#item_id').val(item_id);
        $('#pserial').val(row.children().children('.rserial').val());
        $('#pdiscount').val(discount);
        $('#net_price').text(formatMoney(net_price));
        $('#pro_tax').text(formatMoney(pr_tax_val));
        $('#prModal').appendTo('body').modal('show');
    });

    $('#prModal').on('shown.bs.modal', function (e) {
        if ($('#poption').select2('val') != '') {
            $('#poption').select2('val', product_variant);
            product_variant = 0;
        }
    });

    $(document).on('change', '#pprice, #ptax, #pdiscount', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var unit_price = parseFloat($('#pprice').val());
        var item = reitems[item_id];
        var ds = $('#pdiscount').val() ? $('#pdiscount').val() : '0';
        if (ds.indexOf('%') !== -1) {
            var pds = ds.split('%');
            if (!isNaN(pds[0])) {
                item_discount = parseFloat((unit_price * parseFloat(pds[0])) / 100);
            } else {
                item_discount = parseFloat(ds);
            }
        } else {
            item_discount = parseFloat(ds);
        }
        unit_price -= item_discount;
        var pr_tax = $('#ptax').val(),
            item_tax_method = item.row.tax_method;
        var pr_tax_val = 0,
            pr_tax_rate = 0;
        if (pr_tax !== null && pr_tax != 0) {
            $.each(tax_rates, function () {
                if (this.id == pr_tax) {
                    if (this.type == 1) {
                        if (item_tax_method == 0) {
                            pr_tax_val = formatDecimal((unit_price * parseFloat(this.rate)) / (100 + parseFloat(this.rate)), 4);
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                            unit_price -= pr_tax_val;
                        } else {
                            pr_tax_val = formatDecimal((unit_price * parseFloat(this.rate)) / 100, 4);
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                        }
                    } else if (this.type == 2) {
                        pr_tax_val = parseFloat(this.rate);
                        pr_tax_rate = this.rate;
                    }
                }
            });
        }

        $('#net_price').text(formatMoney(unit_price));
        $('#pro_tax').text(formatMoney(pr_tax_val));
    });

    $(document).on('change', '#punit', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var item = reitems[item_id];
        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var opt = $('#poption').val(),
            unit = $('#punit').val(),
            base_quantity = $('#pquantity').val(),
            aprice = 0;
        if (item.options !== false) {
            $.each(item.options, function () {
                if (this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    aprice = parseFloat(this.price);
                }
            });
        }
        if (item.units && unit != reitems[item_id].row.base_unit) {
            $.each(item.units, function () {
                if (this.id == unit) {
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                    $('#pprice')
                        .val(formatDecimal(parseFloat(item.row.base_unit_price + aprice) * unitToBaseQty(1, this), 4))
                        .change();
                }
            });
        } else {
            $('#pprice')
                .val(formatDecimal(item.row.base_unit_price + aprice))
                .change();
        }
    });

    /* -----------------------
     * Edit Row Method
     ----------------------- */
    $(document).on('click', '#editItem', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id'),
            new_pr_tax = $('#ptax').val(),
            new_pr_tax_rate = false;
        if (new_pr_tax) {
            $.each(tax_rates, function () {
                if (this.id == new_pr_tax) {
                    new_pr_tax_rate = this;
                }
            });
        }
        var price = parseFloat($('#pprice').val());
        var unit = $('#punit').val();
        var base_quantity = parseFloat($('#pquantity').val());
        if (unit != reitems[item_id].row.base_unit) {
            $.each(reitems[item_id].units, function () {
                if (this.id == unit) {
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                }
            });
        }
        if (item.options !== false) {
            var opt = $('#poption').val();
            $.each(item.options, function () {
                if (this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    price = price - parseFloat(this.price);
                    // price = price - parseFloat(this.price) * parseFloat(base_quantity);
                }
            });
        }
        if (site.settings.product_discount == 1 && $('#pdiscount').val()) {
            if (!is_valid_discount($('#pdiscount').val()) || ($('#pdiscount').val() != 0 && $('#pdiscount').val() > price)) {
                bootbox.alert(lang.unexpected_value);
                return false;
            }
        }
        var discount = $('#pdiscount').val() ? $('#pdiscount').val() : '';
        if (!is_numeric($('#pquantity').val())) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var quantity = parseFloat($('#pquantity').val());
        // if (site.settings.product_discount == 1 && $('#padiscount').val()) {
        //     if (!is_numeric($('#padiscount').val()) || $('#padiscount').val() > price * quantity) {
        //         bootbox.alert(lang.unexpected_value);
        //         return false;
        //     }
        //     discount = formatDecimal(parseFloat($('#padiscount').val()) / quantity, 4);
        // }
        // console.log(discount);

        reitems[item_id].row.fup = 1;
        reitems[item_id].row.qty = quantity;
        reitems[item_id].row.base_quantity = parseFloat(base_quantity);
        reitems[item_id].row.real_unit_price = price;
        reitems[item_id].row.unit = unit;
        reitems[item_id].row.tax_rate = new_pr_tax;
        reitems[item_id].tax_rate = new_pr_tax_rate;
        reitems[item_id].row.discount = discount;
        reitems[item_id].row.option = $('#poption').val() ? $('#poption').val() : '';
        reitems[item_id].row.serial = $('#pserial').val();
        localStorage.setItem('reitems', JSON.stringify(reitems));
        $('#prModal').modal('hide');

        loadItems();
        return;
    });
    $(document).on('change', '#padiscount', function () {
        if (site.settings.product_discount == 1 && $(this).val()) {
            var row = $('#' + $('#row_id').val());
            var item_id = row.attr('data-item-id'),
                new_pr_tax = $('#ptax').val(),
                new_pr_tax_rate = false;
            var item = reitems[item_id];
            if (new_pr_tax) {
                $.each(tax_rates, function () {
                    if (this.id == new_pr_tax) {
                        new_pr_tax_rate = this;
                    }
                });
            }
            var quantity = parseFloat($('#pquantity').val());
            var price = parseFloat($('#pprice').val());
            var pr_tax = new_pr_tax_rate;
            var pr_tax_val = 0,
                pr_tax_rate = 0;
            var total_tax = 0;
            if (site.settings.tax1 == 1) {
                if (pr_tax !== false && pr_tax != 0 && pr_tax.rate != 0) {
                    if (pr_tax.type == 1) {
                        if (item.row.tax_method == 0) {
                            pr_tax_val = formatDecimal((price * parseFloat(pr_tax.rate)) / (100 + parseFloat(pr_tax.rate)), 4);
                            price = formatDecimal(price - parseFloat(pr_tax_val), 4);
                        } else {
                            pr_tax_val = formatDecimal((price * parseFloat(pr_tax.rate)) / 100, 4);
                            price = formatDecimal(price + parseFloat(pr_tax_val), 4);
                        }
                    } else if (pr_tax.type == 2) {
                        price =
                            item.row.tax_method == 0
                                ? formatDecimal(price - parseFloat(pr_tax.rate), 4)
                                : formatDecimal(price + parseFloat(pr_tax.rate), 4);
                    }
                }
            }
            var total = formatDecimal((price + parseFloat(pr_tax_val)) * quantity, 4);
            var expected_total = parseFloat($(this).val());
            var expected_discount = formatDecimal(((total - expected_total) / total) * 100, 4);
            $('#pdiscount').val(expected_discount + '%');
        }
    });

    /* -----------------------
     * Product option change
     ----------------------- */
    $(document).on('change', '#poption', function () {
        var row = $('#' + $('#row_id').val()),
            opt = $(this).val();
        var item_id = row.attr('data-item-id');
        var item = reitems[item_id];
        var unit = $('#punit').val(),
            base_quantity = parseFloat($('#pquantity').val()),
            base_unit_price = item.row.base_unit_price;
        if (unit != reitems[item_id].row.base_unit) {
            $.each(reitems[item_id].units, function () {
                if (this.id == unit) {
                    base_unit_price = formatDecimal(parseFloat(item.row.base_unit_price) * unitToBaseQty(1, this), 4);
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                }
            });
        }
        $('#pprice').val(parseFloat(base_unit_price)).trigger('change');
        if (item.options !== false) {
            $.each(item.options, function () {
                if (this.id == opt && this.price != 0 && this.price != '' && this.price != null) {
                    $('#pprice')
                        .val(parseFloat(base_unit_price) + parseFloat(this.price))
                        .trigger('change');
                    // .val(parseFloat(base_unit_price) + parseFloat(this.price) * parseFloat(base_quantity))
                }
            });
        }
    });

    /* ------------------------------
     * Show manual item addition modal
     ------------------------------- */
    $(document).on('click', '#addManually', function (e) {
        if (count == 1) {
            reitems = {};
        }
        $('#mnet_price').text('0.00');
        $('#mpro_tax').text('0.00');
        $('#mModal').appendTo('body').modal('show');
        return false;
    });

    $(document).on('click', '#addItemManually', function (e) {
        var mid = new Date().getTime(),
            mcode = $('#mcode').val(),
            mname = $('#mname').val(),
            mtax = parseInt($('#mtax').val()),
            munit = parseInt($('#munit').val()),
            mqty = parseFloat($('#mquantity').val()),
            mdiscount = $('#mdiscount').val() ? $('#mdiscount').val() : '0',
            unit_price = parseFloat($('#mprice').val()),
            mtax_rate = {};
        if (mcode && mname && mqty && unit_price) {
            $.each(tax_rates, function () {
                if (this.id == mtax) {
                    mtax_rate = this;
                }
            });

            reitems[mid] = {
                id: mid,
                item_id: mid,
                label: mname + ' (' + mcode + ')',
                row: {
                    id: mid,
                    code: mcode,
                    name: mname,
                    quantity: mqty,
                    base_quantity: mqty,
                    price: unit_price,
                    unit_price: unit_price,
                    real_unit_price: unit_price,
                    tax_rate: mtax,
                    unit: munit,
                    tax_method: 0,
                    qty: mqty,
                    type: 'manual',
                    discount: mdiscount,
                    serial: '',
                    option: '',
                },
                tax_rate: mtax_rate,
                units: false,
                options: false,
            };
            localStorage.setItem('reitems', JSON.stringify(reitems));
            loadItems();
        }
        $('#mModal').modal('hide');
        $('#mcode').val('');
        $('#mname').val('');
        $('#mtax').val('');
        $('#munit').val('');
        $('#mquantity').val('');
        $('#mdiscount').val('');
        $('#mprice').val('');
        return false;
    });

    $(document).on('change', '#mprice, #mtax, #mdiscount', function () {
        var unit_price = parseFloat($('#mprice').val());
        var ds = $('#mdiscount').val() ? $('#mdiscount').val() : '0';
        if (ds.indexOf('%') !== -1) {
            var pds = ds.split('%');
            if (!isNaN(pds[0])) {
                item_discount = parseFloat((unit_price * parseFloat(pds[0])) / 100);
            } else {
                item_discount = parseFloat(ds);
            }
        } else {
            item_discount = parseFloat(ds);
        }
        unit_price -= item_discount;
        var pr_tax = $('#mtax').val(),
            item_tax_method = 0;
        var pr_tax_val = 0,
            pr_tax_rate = 0;
        if (pr_tax !== null && pr_tax != 0) {
            $.each(tax_rates, function () {
                if (this.id == pr_tax) {
                    if (this.type == 1) {
                        if (item_tax_method == 0) {
                            pr_tax_val = formatDecimal((unit_price * parseFloat(this.rate)) / (100 + parseFloat(this.rate)), 4);
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                            unit_price -= pr_tax_val;
                        } else {
                            pr_tax_val = formatDecimal((unit_price * parseFloat(this.rate)) / 100, 4);
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                        }
                    } else if (this.type == 2) {
                        pr_tax_val = parseFloat(this.rate);
                        pr_tax_rate = this.rate;
                    }
                }
            });
        }

        $('#mnet_price').text(formatMoney(unit_price));
        $('#mpro_tax').text(formatMoney(pr_tax_val));
    });

    var old_row_serialno;
    var currTabIndex;
    $(document)
        .on('focus', '.rserialno', function () {
            old_row_serialno = $(this).val();
            currTabIndex = $(this).prop('tabindex');
        })
        .on('change', '.rserialno', function () {
            var row = $(this).closest('tr');
            var new_serialno = $(this).val(),
            item_id = row.attr('data-item-id');
            reitems[item_id].row.serial_number = new_serialno;
            localStorage.setItem('reitems', JSON.stringify(reitems));
            loadItems();
        });

    /* --------------------------
     * Edit Row Quantity Method
     --------------------------- */
    var old_row_qty;
    $(document)
        .on('focus', '.rquantity', function () {
            old_row_qty = $(this).val();
        })
        .on('change', '.rquantity', function () {
            var row = $(this).closest('tr');
            if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
                $(this).val(old_row_qty);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            var new_qty = parseFloat($(this).val()),
                item_id = row.attr('data-item-id');
            reitems[item_id].row.base_quantity = new_qty;
            if (reitems[item_id].row.unit != reitems[item_id].row.base_unit) {
                $.each(reitems[item_id].units, function () {
                    if (this.id == reitems[item_id].row.unit) {
                        reitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                    }
                });
            }
            reitems[item_id].row.qty = new_qty;
            localStorage.setItem('reitems', JSON.stringify(reitems));
            loadItems();
        });

    /* --------------------------
     * Edit Row Price Method
     -------------------------- */
    var old_price;
    $(document)
        .on('focus', '.rprice', function () {
            old_price = $(this).val();
        })
        .on('change', '.rprice', function () {
            var row = $(this).closest('tr');
            if (!is_numeric($(this).val())) {
                $(this).val(old_price);
                bootbox.alert(lang.unexpected_value);
                return;
            }

            var new_price = parseFloat($(this).val()),
                item_id = row.attr('data-item-id');
            reitems[item_id].row.base_unit_price = new_price;
            localStorage.setItem('reitems', JSON.stringify(reitems));
            loadItems();
        });

    var old_cost;
        $(document)
            .on('focus', '.rcost', function () {
                old_cost = $(this).val();
            })
            .on('change', '.rcost', function () {
                var row = $(this).closest('tr');
                if (!is_numeric($(this).val())) {
                    $(this).val(old_cost);
                    bootbox.alert(lang.unexpected_value);
                    return;
                }
                var new_cost = parseFloat($(this).val()),
                    item_id = row.attr('data-item-id');
                reitems[item_id].row.cost_price = new_cost;
                localStorage.setItem('reitems', JSON.stringify(reitems));
                loadItems();
            });

    /* --------------------------
     * Edit Row Bonus Method rbonus
     -------------------------- */
     var old_row_bonus;
     $(document)
         .on('focus', '.rbonus', function () {
             old_row_bonus = $(this).val();
         })
         .on('change', '.rbonus', function () {
             var row = $(this).closest('tr');
             if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
                 $(this).val(old_row_bonus);
                 bootbox.alert(lang.unexpected_value);
                 return;
             }
             var new_bonus = parseFloat($(this).val()),
                 item_id = row.attr('data-item-id');
                 reitems[item_id].row.bonus = new_bonus;
             localStorage.setItem('reitems', JSON.stringify(reitems));
             loadItems();
         });

    /* --------------------------
     * Edit Row Discount2 Method rdis2 rbatchno
     -------------------------- */
     var old_row_dis2;
     $(document)
         .on('focus', '.rdiscount2', function () {
             old_row_dis2 = $(this).val();
         })
         .on('change', '.rdiscount2', function () {
             var row = $(this).closest('tr');
             if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
                 $(this).val(old_row_dis2);
                 bootbox.alert(lang.unexpected_value);
                 return;
             }
             var new_dis2 = parseFloat($(this).val()),
                 item_id = row.attr('data-item-id');
             reitems[item_id].row.discount2 = new_dis2;
             localStorage.setItem('reitems', JSON.stringify(reitems));
             loadItems();
         });
 
         /* --------------------------
      * Edit Row Discount1 Method rdis1
      -------------------------- */
     var old_row_dis1;
     $(document)
         .on('focus', '.rdiscount1', function () {
             old_row_dis1 = $(this).val();
         })
         .on('change', '.rdiscount1', function () {
             var row = $(this).closest('tr');
             if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
                 $(this).val(old_row_dis1);
                 bootbox.alert(lang.unexpected_value);
                 return;
             }
             var new_dis1 = parseFloat($(this).val()),
                 item_id = row.attr('data-item-id');
                 reitems[item_id].row.discount1 = new_dis1;
             localStorage.setItem('reitems', JSON.stringify(reitems));
             loadItems();
         });
});

//localStorage.clear();
function loadItems() {
    if (localStorage.getItem('reitems')) {

        grand_total_vat = 0;
        grand_total_purchases = 0;
        grand_total_sales = 0;


        total = 0;
        count = 1;
        an = 1;
        product_tax = 0;
        invoice_tax = 0;
        product_discount = 0;
        order_discount = 0;
        total_discount = 0;

        $('#reTable tbody').empty();
        reitems = JSON.parse(localStorage.getItem('reitems'));
        sortedItems =
            site.settings.item_addition == 1
                ? _.sortBy(reitems, function (o) {
                      return [parseInt(o.order)];
                  })
                : reitems;
        $('#add_sale, #edit_sale').attr('disabled', false);
        $.each(sortedItems, function () {
            var item = this;
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            item.order = item.order ? item.order : new Date().getTime();

            const pattern = /^\d{2}\/\d{2}\/\d{4}$/;
            const isFormattedDate = pattern.test(item.row.expiry);

            var item_expiry_date='';
            if(item.row.expiry != null){                
                if(isFormattedDate){
                    item_expiry_date = item.row.expiry;
                }else{
                    item_expiry_date = new Date(item.row.expiry).toLocaleDateString('en-GB');
                }
            }

            var product_id = item.row.id,
                item_type = item.row.type,
                combo_items = item.combo_items,
                item_price = item.row.price,
                item_cost = item.row.cost_price,
                item_sale_price = item.row.price,
                item_qty = item.row.qty,
                item_aqty = item.row.quantity,
                item_tax_method = item.row.tax_method,
                item_ds = item.row.discount,
                item_discount = 0,
                item_option = item.row.option,
                item_code = item.row.code,
                item_serial = item.row.serial,
                item_expiry = item.row.expiry,
                item_batchno = item.row.batch_no,
                item_serialno = item.row.serial_number,
                item_bonus = 0;//item.row.bonus,
                item_dis1 = item.row.discount1,
                item_dis2 = item.row.discount2,
                item_batchQuantity = item.row.batchQuantity,
                item_name = item.row.name.replace(/"/g, '&#034;').replace(/'/g, '&#039;');
            var product_unit = item.row.unit,
                base_quantity = item.row.base_quantity;

             var cost_price  = item.row.net_cost;  
             var batch_no    = item.row.batch_no;
             var bonus       = item.row.bonus;
             var expiry       = item_expiry_date;//item.row.expiry;
             var discount1       = item.row.discount1;
             var discount2       = item.row.discount2;


            var unit_price = item.row.real_unit_price;
            if (item.units && item.row.fup != 1 && product_unit != item.row.base_unit) {
                $.each(item.units, function () {
                    if (this.id == product_unit) {
                        base_quantity = formatDecimal(unitToBaseQty(item.row.qty, this), 4);
                        unit_price = formatDecimal(parseFloat(item.row.base_unit_price) * unitToBaseQty(1, this), 4);
                    }
                });
            }
            var sel_opt = '';
            if (item.options !== false) {
                $.each(item.options, function () {
                    if (this.id == item_option) {
                        sel_opt = this.name;
                        if (this.price != 0 && this.price != '' && this.price != null) {
                            // item_price = unit_price + parseFloat(this.price) * parseFloat(base_quantity);
                            item_price = parseFloat(unit_price) + parseFloat(this.price);
                            unit_price = item_price;
                        }
                    }
                });
            }

            var ds = item_ds ? item_ds : '0';
            if (ds.indexOf('%') !== -1) {
                var pds = ds.split('%');
                if (!isNaN(pds[0])) {
                    item_discount = formatDecimal((unit_price * parseFloat(pds[0])) / 100, 4);
                } else {
                    item_discount = formatDecimal(ds);
                }
            } else {
                item_discount = formatDecimal(ds);
            }
            product_discount += formatDecimal(item_discount * item_qty, 4);

            unit_price = formatDecimal(unit_price - item_discount);
            var pr_tax = item.tax_rate;
            var pr_tax_val = 0,
                pr_tax_rate = 0;
            if (site.settings.tax1 == 1) {
                if (pr_tax !== false && pr_tax != 0) {
                    if (pr_tax.type == 1) {
                        if (item_tax_method == '0') {
                            pr_tax_val = formatDecimal((item_sale_price * parseFloat(pr_tax.rate)) / (100 + parseFloat(pr_tax.rate)), 4);
                            pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                        } else {
                            pr_tax_val = formatDecimal((item_sale_price * parseFloat(pr_tax.rate)) / 100, 4);
                            pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                        }
                    } else if (pr_tax.type == 2) {
                        pr_tax_val = parseFloat(pr_tax.rate);
                        pr_tax_rate = pr_tax.rate;
                    }
                }
            }
            pr_tax_val = formatDecimal(pr_tax_val);
            product_tax += formatDecimal(pr_tax_val * item_qty);
            item_price = item_tax_method == 0 ? formatDecimal(unit_price - pr_tax_val, 4) : formatDecimal(unit_price);
            unit_price = formatDecimal(unit_price + item_discount, 4);

            var total_after_dis1 = 0.0;
            var total_after_dis2 = 0.0;

            var total_after_dis1_b = 0.0;
            var total_after_dis2_b = 0.0;

            var vat_15_a = 0.0;
            var vat_15_b = 0.0;
            var net_price_a = 0.0;
            var net_price_b = 0.0;

            var dis1_a = 0.0;
            var dis2_a = 0.0;
            var dis1_b = 0.0;
            var dis2_b = 0.0;

            var main_net = 0.0;

            var total_before_dis_vat = (parseFloat(item_sale_price)) * parseFloat(item_qty); //(parseFloat(item_cost) + parseFloat(pr_tax_val)) * parseFloat(item_qty); 

            dis1_a = total_before_dis_vat * parseFloat((item_dis1 / 100));
            total_after_dis1 =  total_before_dis_vat - dis1_a;
            dis2_a = total_after_dis1 *  parseFloat((item_dis2/100));
            total_after_dis2 =  total_after_dis1 - dis2_a;
            vat_15_a = total_after_dis2 * parseFloat(item.tax_rate.rate/100);//total_after_dis2 * parseFloat(15/100);
            net_price_a = vat_15_a + total_after_dis2;

            var total_purchases = (parseFloat(item_cost)) * parseFloat(item_qty);
            var total_sales = (parseFloat(item_sale_price)) * (parseFloat(item_qty) + parseFloat(item_bonus));
            //console.log(item_qty+' -- '+item_bonus+' -- '+item_sale_price+' -- '+total_sales);
            total_after_dis1 = total_sales * parseFloat((item_dis1 / 100));
            total_after_dis2 = (total_sales - total_after_dis1) * parseFloat((item_dis2 / 100));
            //main_net = net_price_a;// + net_price_b;
            main_net = total_sales - (total_after_dis1 + total_after_dis2);
            var new_unit_cost = parseFloat(main_net) / parseFloat(item_qty + item_bonus);

            var row_no = item.id;
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html =
                '<td><input name="product_id[]" type="hidden" class="rid" value="' +
                product_id +
                '"><input name="product_type[]" type="hidden" class="rtype" value="' +
                item_type +
                '"><input name="product_code[]" type="hidden" class="rcode" value="' +
                item_code +
                '"><input name="product_name[]" type="hidden" class="rname" value="' +
                item_name +
                '"><input name="product_option[]" type="hidden" class="roption" value="' +
                item_option +
                '"><span class="sname" id="name_' +
                row_no +
                '">' +
                item_code +
                ' - ' +
                item_name +
                (sel_opt != '' ? ' (' + sel_opt + ')' : '') +
                '</span> <i class="pull-right fa fa-edit tip pointer edit" id="' +
                row_no +
                '" data-item="' +
                item_id +
                '" title="Edit" style="cursor:pointer;"></i></td>';

                /*tr_html +=
                '<td class="text-right"><input class="form-control input-sm text-right rprice" name="net_sale_price[]" type="text" id="net_sale_price_' +
                row_no +
                '" value="' +
                item_price +
                '"></td>';

                tr_html +=
                '<td class="text-right"><input class="form-control input-sm text-right rprice" name="cost_price[]" type="text" id="cost_price_' +
                row_no +
                '" value="' +
                cost_price +
                '"></td>';*/

                tr_html +=
                    '<td class="text-right"><input class="rucost" name="unit_price[]" type="hidden" value="' +
                    unit_price +
                    '"><input class="form-control realucost" name="real_unit_price[]" type="hidden" value="' +
                    item.row.real_unit_price +
                    '"><input class="form-control input-sm text-center rprice" type="text" name="net_price[]" id="cost_' +
                    row_no +
                    '" value="' +
                    formatDecimal(item_sale_price, 2) +
                    '">';

                tr_html += 
                    '<input id="rreturn_' +
                    row_no +
                    '" class="form-control rcost text-center" name="net_cost[]" type="hidden" value="' +
                    formatDecimal(item.row.batchPurchaseCost, 2) +
                    '" data-id="' +
                    row_no +
                    '" data-item="' +
                    item_id +
                    '" id="rreturn_' +
                    row_no +
                    '"></td>';

                tr_html +=
                    '<td><input class="form-control rserialno" name="serial_no[]" type="text" value="' +
                    item_serialno +
                    '" data-id="' +
                    row_no +
                    '" data-item="' +
                    item_id +
                    '" id="serialno_' +
                    row_no +
                    '"></td>';

                // tr_html +=
                // '<td class="text-right"><input class="form-control input-sm text-right rbatchno" name="batch_no[]" type="text" id="batch_no_' +
                // row_no +
                // '" value="' +
                // batch_no +
                // '"></td>';

                var batchesOptions = '<option value="" data-batchExpiry="null" data-batchQty="0"  data-batchpurchasecost="0">--</option>';
                if (item.batches !== false) {
                    $.each(item.batches, function () {
                        batchSelected = "";
                        if (this.batchno == item_batchno) {
                            batchSelected = "selected";
                        }
                        batchesOptions += '<option data-batchExpiry="'+this.expiry+'" data-batchQty="'+this.quantity+'"  data-batchpurchasecost="'+this.purchase_cost+'" value="'+this.batchno+'" '+batchSelected+'>'+this.batchno+'</option>';
                    });
                }

                tr_html += '<td><select class="form-control rbatchno" name="batch_no[]" id="batch_no_' + row_no +'">'+batchesOptions+'</select></td>';


                tr_html +=
                '<td><input class="form-control date rexpiry" name="expiry[]" type="text" value="' +
                expiry +
                '" data-id="' +
                row_no +
                '" data-item="' +
                item_id +
                '" id="expiry_' +
                row_no +
                '"></td>';

                


            // if (site.settings.product_serial == 1) {
            //     tr_html +=
            //         '<td class="text-right"><input class="form-control input-sm rserial" name="serial[]" type="text" id="serial_' +
            //         row_no +
            //         '" value="' +
            //         item_serial +
            //         '"></td>';
            // }



         


            tr_html +=
                '<td><input class="form-control text-center rquantity" tabindex="' +
                (site.settings.set_focus == 1 ? an : an + 1) +
                '" name="quantity[]" type="text" value="' +
                formatQuantity2(item_qty) +
                '" data-id="' +
                row_no +
                '" data-item="' +
                item_id +
                '" id="quantity_' +
                row_no +
                '" onClick="this.select();"><input name="product_unit[]" type="hidden" class="runit" value="' +
                product_unit +
                '"><input name="product_base_quantity[]" type="hidden" class="rbase_quantity" value="' +
                base_quantity +
                '"></td>';

                /*tr_html +=
                '<td class="text-right"><input class="form-control input-sm text-right rbonus" name="bonus[]" type="text" id="bonus_' +
                row_no +
                '" value="' +
                bonus +
                '"></td>';*/

               
                /*tr_html +=
                    '<td class="text-right"><input class="form-control input-sm rdiscount" name="product_discount[]" type="text" id="discount_' +
                    row_no +
                    '" value="' +
                    discount1 +
                    '"></td>';*/
            
                // tr_html +=
                //     '<td class="text-right"><input class="form-control input-sm rdiscount1" name="dis1[]" type="text" id="discount_' +
                //     row_no +
                //     '" value="' +
                //     discount1 +
                //     '"><input class="form-control input-sm rdiscount" name="product_discount[]" type="hidden" id="discount_' +
                //     row_no +
                //     '" value="' +
                //     discount1 +
                //     '"></td>';
            
                // tr_html +=
                //     '<td class="text-right"><input class="form-control input-sm rdiscount2" name="dis2[]" type="text" id="discount2_' +
                //     row_no +
                //     '" value="' +
                //     discount2 +
                //     '"></td>';


                    tr_html +=
                    '<td><input class="form-control text-center rdiscount1" name="dis1[]" type="text" data-id="' +
                    row_no +
                    '" data-item="' +
                    item_id +
                    '" id="discount_' +
                    row_no +
                    '" value="'+formatDecimal(discount1)+'" onClick="this.select();"><span style="position:absolute;font-size:10px;margin-top:5px;">' +
                    formatMoney(total_after_dis1)
                    '</span></td>';
    
                tr_html +=
                    '<td><input class="form-control text-center rdiscount2" name="dis2[]" type="text" data-id="' +
                    row_no +
                    '" data-item="' +
                    item_id +
                    '" id="discount2_' +
                    row_no +
                    '" value="'+formatDecimal(item_dis2)+'" onClick="this.select();"><span style="position:absolute;font-size:10px;margin-top:5px;">' +
                    formatMoney(total_after_dis2)
                    '</span></td>';
    
            // <span class="text-right sdiscount text-danger" id="sdiscount_' +
            // row_no +
            // '">' +
            // formatMoney(0 - item_discount * item_qty) +
            // '</span>

            tr_html +=
                '<td class="text-right"><input class="form-control input-sm text-right rproduct_tax" name="product_tax[]" type="hidden" id="product_tax_' +
                    row_no +
                    '" value="' +
                    pr_tax.id +
                    '"><span class="text-right rvat15" id="vat15_' +
                row_no +
                '">'+formatMoney(vat_15_a)+'</span></td>';

            /*if (site.settings.tax1 == 1) {
                tr_html +=
                    '<td class="text-right"><input class="form-control input-sm text-right rproduct_tax" name="product_tax[]" type="hidden" id="product_tax_' +
                    row_no +
                    '" value="' +
                    pr_tax.id +
                    '"><span class="text-right sproduct_tax" id="sproduct_tax_' +
                    row_no +
                    '">' +
                    (parseFloat(pr_tax_rate) != 0 ? '(' + formatDecimal(pr_tax_rate) + ')' : '') +
                    ' ' +
                    formatMoney(pr_tax_val * item_qty) +
                    '</span></td>';
            }*/

            /*tr_html +=
            '<td class="text-right"><input class="form-control input-sm text-right rprice" name="cost_price[]" type="hidden" id="cost_price_' +
            row_no +
            '" value="' +
            item_price +
            '"><span class="text-right sprice" id="sprice_' +
            row_no +
            '">' +
            formatMoney(cost_price) +
            '</span></td>';


            tr_html +=
            '<td class="text-right"><input class="form-control input-sm text-right rprice" name="net_price[]" type="hidden" id="price1_' +
            row_no +
            '" value="' +
            item_price +
            '"><input class="ruprice" name="unit_price1[]" type="hidden" value="' +
            unit_price +
            '"><input class="realuprice" name="real_unit_price1[]" type="hidden" value="' +
            item.row.real_unit_price +
            '"><span class="text-right sprice" id="sprice_' +
            row_no +
            '">' +
            formatMoney(item_price) +
            '</span></td>';

            tr_html +=
                '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_' +
                row_no +
                '">' +
                formatMoney((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty)) +
                '</span></td>';

                tr_html +=
                '<td class="text-right"><input class="form-control input-sm text-right rprice" name="net_price[]" type="hidden" id="price2_' +
                row_no +
                '" value="' +
                item_price +
                '"><input class="ruprice" name="unit_price2[]" type="hidden" value="' +
                unit_price +
                '"><input class="realuprice" name="real_unit_price2[]" type="hidden" value="' +
                item.row.real_unit_price +
                '"><span class="text-right sprice" id="sprice_' +
                row_no +
                '">' +
                formatMoney(item_price) +
                '</span></td>';*/

            // tr_html +=
            //     '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_' +
            //     row_no +
            //     '">' +
            //     formatMoney(total_purchases) +
            //     '</span></td>';

            tr_html +=
                '<td class="text-right"><span class="text-right ssubtotal" id="total_sale_' +
                row_no +
                '">' +
                formatMoney(total_sales) +
                '</span></td>';

            tr_html +=
                '<td class="text-right"><span class="text-right rnet" id="net_' +
                row_no +
                '">'+formatMoney(main_net)+'</span></td>';

            tr_html +=
                '<td class="text-right"><span class="text-right ssubtotal" id="tes2_' +
                row_no +
                '">' +
                formatMoney(new_unit_cost) +
                '</span></td>'; 


            tr_html +=
                '<td class="text-center"><i class="fa fa-times tip pointer redel" id="' +
                row_no +
                '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.appendTo('#reTable');
            /// total += formatDecimal((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty), 4);

            total += formatDecimal(main_net, 4);
            grand_total_vat += formatDecimal(vat_15_a, 4);
            grand_total_purchases += formatDecimal(total_purchases, 4);
            grand_total_sales += formatDecimal(total_sales, 4);

            count += parseFloat(item_qty);
            an++;
        });

        // var col = 2;
        // if (site.settings.product_serial == 1) {
        //     col++;
        // }
        // var tfoot =
        //     '<tr id="tfoot" class="tfoot active"><th colspan="' +
        //     col +
        //     '">Total</th><th class="text-center">' +
        //     formatQty(parseFloat(count) - 1) +
        //     '</th>';
        // if ((site.settings.product_discount == 1 && allow_discount == 1) || product_discount) {
        //     tfoot += '<th class="text-right">' + formatMoney(product_discount) + '</th>';
        // }
        // if (site.settings.tax1 == 1) {
        //     tfoot += '<th class="text-right">' + formatMoney(product_tax) + '</th>';
        // }
        // tfoot +=
        //     '<th class="text-right">' +
        //     formatMoney(total) +
        //     '</th><th class="text-center"><i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i></th></tr>';
        // $('#reTable tfoot').html(tfoot);

        var col = 7;
        if (site.settings.product_serial == 1) {
            col++;
        }
        var tfoot =
            '<tr id="tfoot" class="tfoot active"><th colspan="' +
            col +
            '">Total</th><th class="text-center">' +
            formatMoney(grand_total_vat) +
            '</th>';

        //tfoot += '<th class="text-right">' + formatMoney(grand_total_purchases) + '</th>';

        tfoot += '<th class="text-right">' + formatMoney(grand_total_sales) + '</th>';
    
        tfoot +=
            '<th class="text-right">' +
            formatMoney(total) +
            '</th><th class="text-center"></th></tr>';
        $('#reTable tfoot').html(tfoot);

        if ((rediscount = localStorage.getItem('rediscount'))) {
            var ds = rediscount;
            if (ds.indexOf('%') !== -1) {
                var pds = ds.split('%');
                if (!isNaN(pds[0])) {
                    order_discount = formatDecimal((total * parseFloat(pds[0])) / 100, 4);
                } else {
                    order_discount = formatDecimal(ds);
                }
            } else {
                order_discount = formatDecimal(ds);
            }
        }

        if (site.settings.tax2 != 0) {
            if ((retax2 = localStorage.getItem('retax2'))) {
                $.each(tax_rates, function () {
                    if (this.id == retax2) {
                        if (this.type == 2) {
                            invoice_tax = formatDecimal(this.rate);
                        } else if (this.type == 1) {
                            invoice_tax = formatDecimal(((total - order_discount) * this.rate) / 100, 4);
                        }
                    }
                });
            }
        }

        var shipping = parseFloat(localStorage.getItem('reshipping'));
        total_discount = parseFloat(order_discount + product_discount);
        var gtotal = parseFloat(total + invoice_tax + shipping - order_discount);
        $('#total').text(formatMoney(total));
        $('#titems').text(an - 1 + ' (' + formatQty(parseFloat(count) - 1) + ')');
        $('#total_items').val(parseFloat(count) - 1);
        $('#tds').text(formatMoney(order_discount));
        if (site.settings.tax2 != 0) {
            $('#ttax2').text(formatMoney(invoice_tax));
        }
        $('#gtotal').text(formatMoney(gtotal));
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $('html, body').animate({ scrollTop: $('#sticker').offset().top }, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
        set_page_focus();
    }
}

/* -----------------------------
 * Add Sale Order Item Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */
function add_return_item(item) {
    if (count == 1) {
        reitems = {};
    }
    if (item == null) return;

    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (reitems[item_id]) {
        var new_qty = parseFloat(reitems[item_id].row.qty) + 1;
        reitems[item_id].row.base_quantity = new_qty;
        if (reitems[item_id].row.unit != reitems[item_id].row.base_unit) {
            $.each(reitems[item_id].units, function () {
                if (this.id == reitems[item_id].row.unit) {
                    reitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        reitems[item_id].row.qty = new_qty;
    } else {
        reitems[item_id] = item;
    }
    reitems[item_id].order = new Date().getTime();
    localStorage.setItem('reitems', JSON.stringify(reitems));
    loadItems();
    return true;
}

if (typeof Storage === 'undefined') {
    $(window).bind('beforeunload', function (e) {
        if (count > 1) {
            var message = 'You will loss data!';
            return message;
        }
    });
}
