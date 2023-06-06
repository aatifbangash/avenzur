$(document).ready(function () {

    //$('body a, body button').attr('tabindex', -1);
    check_add_item_val();
    if (site.settings.set_focus != 1) {
        $('#add_item').focus();
    }
    // Order level shipping and discoutn localStorage
    if ((podiscount = localStorage.getItem('podiscount'))) {
        $('#podiscount').val(podiscount);
    }
    $('#potax2').change(function (e) {
        localStorage.setItem('potax2', $(this).val());
    });
    if ((potax2 = localStorage.getItem('potax2'))) {
        $('#potax2').select2('val', potax2);
    }
    $('#postatus').change(function (e) {
        localStorage.setItem('postatus', $(this).val());
    });
    if ((postatus = localStorage.getItem('postatus'))) {
        $('#postatus').select2('val', postatus);
    }
    var old_shipping;
    $('#poshipping')
        .focus(function () {
            old_shipping = $(this).val();
        })
        .change(function () {
            var posh = $(this).val() ? $(this).val() : 0;
            if (!is_numeric(posh)) {
                $(this).val(old_shipping);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            shipping = parseFloat(posh);
            localStorage.setItem('poshipping', shipping);
            var gtotal = total + invoice_tax - order_discount + shipping;
            $('#gtotal').text(formatMoney(gtotal));
            $('#tship').text(formatMoney(shipping));
        });
    if ((poshipping = localStorage.getItem('poshipping'))) {
        shipping = parseFloat(poshipping);
        $('#poshipping').val(shipping);
    }

    $('#popayment_term').change(function (e) {
        localStorage.setItem('popayment_term', $(this).val());
    });
    if ((popayment_term = localStorage.getItem('popayment_term'))) {
        $('#popayment_term').val(popayment_term);
    }

    // If there is any item in localStorage
    if (localStorage.getItem('poitems')) {
        loadItems();
    }

    // clear localStorage and reload
    $('#reset').click(function (e) {
        bootbox.confirm(lang.r_u_sure, function (result) {
            if (result) {
                if (localStorage.getItem('poitems')) {
                    localStorage.removeItem('poitems');
                }
                if (localStorage.getItem('podiscount')) {
                    localStorage.removeItem('podiscount');
                }
                if (localStorage.getItem('potax2')) {
                    localStorage.removeItem('potax2');
                }
                if (localStorage.getItem('poshipping')) {
                    localStorage.removeItem('poshipping');
                }
                if (localStorage.getItem('poref')) {
                    localStorage.removeItem('poref');
                }
                if (localStorage.getItem('powarehouse')) {
                    localStorage.removeItem('powarehouse');
                }
                if (localStorage.getItem('ponote')) {
                    localStorage.removeItem('ponote');
                }
                if (localStorage.getItem('posupplier')) {
                    localStorage.removeItem('posupplier');
                }
                if (localStorage.getItem('pocurrency')) {
                    localStorage.removeItem('pocurrency');
                }
                if (localStorage.getItem('poextras')) {
                    localStorage.removeItem('poextras');
                }
                if (localStorage.getItem('podate')) {
                    localStorage.removeItem('podate');
                }
                if (localStorage.getItem('postatus')) {
                    localStorage.removeItem('postatus');
                }
                if (localStorage.getItem('popayment_term')) {
                    localStorage.removeItem('popayment_term');
                }

                $('#modal-loading').show();
                location.reload();
            }
        });
    });

    // save and load the fields in and/or from localStorage
    var $supplier = $('#posupplier'),
        $currency = $('#pocurrency');

    $('#poref').change(function (e) {
        localStorage.setItem('poref', $(this).val());
    });
    if ((poref = localStorage.getItem('poref'))) {
        $('#poref').val(poref);
    }
    $('#powarehouse').change(function (e) {
        localStorage.setItem('powarehouse', $(this).val());
    });
    if ((powarehouse = localStorage.getItem('powarehouse'))) {
        $('#powarehouse').select2('val', powarehouse);
    }

    $('#ponote').redactor('destroy');
    $('#ponote').redactor({
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
            localStorage.setItem('ponote', v);
        },
    });
    if ((ponote = localStorage.getItem('ponote'))) {
        $('#ponote').redactor('set', ponote);
    }
    $supplier.change(function (e) {
        localStorage.setItem('posupplier', $(this).val());
        $('#supplier_id').val($(this).val());
    });
    if ((posupplier = localStorage.getItem('posupplier'))) {
        $supplier.val(posupplier).select2({
            minimumInputLength: 1,
            data: [],
            initSelection: function (element, callback) {
                $.ajax({
                    type: 'get',
                    async: false,
                    url: site.base_url + 'suppliers/getSupplier/' + $(element).val(),
                    dataType: 'json',
                    success: function (data) {
                        callback(data[0]);
                    },
                });
            },
            ajax: {
                url: site.base_url + 'suppliers/suggestions',
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
    } else {
        nsSupplier();
    }

    /*$('.rexpiry').change(function (e) {
        var item_id = $(this).closest('tr').attr('data-item-id');
        poitems[item_id].row.expiry = $(this).val();
        localStorage.setItem('poitems', JSON.stringify(poitems));
    });*/
    if (localStorage.getItem('poextras')) {
        $('#extras').iCheck('check');
        $('#extras-con').show();
    }
    $('#extras').on('ifChecked', function () {
        localStorage.setItem('poextras', 1);
        $('#extras-con').slideDown();
    });
    $('#extras').on('ifUnchecked', function () {
        localStorage.removeItem('poextras');
        $('#extras-con').slideUp();
    });
    $(document).on('change', '.rexpiry', function () {
        var inputDate = $(this).val();
        var currentDate = new Date();

        var dateParts = inputDate.split("/");
        var inputDateObj = new Date(dateParts[2], dateParts[1] - 1, dateParts[0]);

        var yesterdayDate = new Date();
        yesterdayDate.setDate(currentDate.getDate() - 1);

        if (inputDateObj.getTime() <= yesterdayDate.getTime()) {
            $(this).val('');
            bootbox.alert('Expired product are not allowed');
            return;
        }else{
            var item_id = $(this).closest('tr').attr('data-item-id');
            poitems[item_id].row.expiry = $(this).val();
            localStorage.setItem('poitems', JSON.stringify(poitems));
        }
        
    });

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

    // Order tax calcuation
    if (site.settings.tax2 != 0) {
        $('#potax2').change(function () {
            localStorage.setItem('potax2', $(this).val());
            loadItems();
            return;
        });
    }

    // Order discount calcuation
    var old_podiscount;
    $('#podiscount')
        .focus(function () {
            old_podiscount = $(this).val();
        })
        .change(function () {
            var pod = $(this).val() ? $(this).val() : 0;
            if (is_valid_discount(pod)) {
                localStorage.removeItem('podiscount');
                localStorage.setItem('podiscount', pod);
                loadItems();
                return;
            } else {
                $(this).val(old_podiscount);
                bootbox.alert(lang.unexpected_value);
                return;
            }
        });

    /* ----------------------
     * Delete Row Method
     * ---------------------- */

    $(document).on('click', '.podel', function () {
        var row = $(this).closest('tr');
        var item_id = row.attr('data-item-id');
        delete poitems[item_id];
        row.remove();
        if (poitems.hasOwnProperty(item_id)) {
        } else {
            localStorage.setItem('poitems', JSON.stringify(poitems));
            loadItems();
            return;
        }
    });

    /* -----------------------
     * Edit Row Modal Hanlder
     ----------------------- */
    $(document).on('click', '.edit', function () {

        var trRowClas = localStorage.getItem('trRowClas');
        if(trRowClas != undefined && trRowClas !=""){
            $(".row_"+trRowClas).css("color", "black");
        }
        var row = $(this).closest('tr');
        var row_id = row.attr('id');
        item_id = row.attr('data-item-id');

        $(".row_"+item_id).css("color", "green");
        localStorage.setItem('trRowClas',item_id);

       
        item = poitems[item_id];
        var qty = row.children().children('.rquantity').val(),
            product_option = row.children().children('.roption').val(),
            unit_cost = formatDecimal(row.children().children('.rucost').val()),
            discount = row.children().children('.rdiscount').val();
        $('#prModalLabel').text(item.row.name + ' (' + item.row.code + ')');
        var real_unit_cost = item.row.real_unit_cost;
        var net_cost = real_unit_cost;
        if (site.settings.tax1) {
            $('#ptax').select2('val', item.row.tax_rate);
            $('#old_tax').val(item.row.tax_rate);
            var item_discount = 0,
                ds = discount ? discount : '0';
            if (ds.indexOf('%') !== -1) {
                var pds = ds.split('%');
                if (!isNaN(pds[0])) {
                    item_discount = parseFloat((real_unit_cost * parseFloat(pds[0])) / 100);
                } else {
                    item_discount = parseFloat(ds);
                }
            } else {
                item_discount = parseFloat(ds);
            }
            net_cost -= item_discount;
            var pr_tax = item.row.tax_rate,
                pr_tax_val = 0;
            if (pr_tax !== null && pr_tax != 0) {
                $.each(tax_rates, function () {
                    if (this.id == pr_tax) {
                        if (this.type == 1) {
                            if (poitems[item_id].row.tax_method == 0) {
                                pr_tax_val = formatDecimal(
                                    ((real_unit_cost - item_discount) * parseFloat(this.rate)) / (100 + parseFloat(this.rate)),
                                    4
                                );
                                pr_tax_rate = formatDecimal(this.rate) + '%';
                                net_cost -= pr_tax_val;
                            } else {
                                pr_tax_val = formatDecimal(((real_unit_cost - item_discount) * parseFloat(this.rate)) / 100, 4);
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
        }

        uopt = $('<select id="punit" name="punit" class="form-control select" />');
        $.each(item.units, function () {
            if (this.id == item.row.unit) {
                $('<option />', { value: this.id, text: this.name, selected: true }).appendTo(uopt);
            } else {
                $('<option />', { value: this.id, text: this.name }).appendTo(uopt);
            }
        });

        // Three Month average Sale
         var threeMonthSale = item.row.three_month_sale;
         $('#three_month_sale').text(threeMonthSale);
        // warehouse shelf 
        $('#warehouse_shelf').select2('val', item.row.warehouse_shelf); 


        $('#poptions-div').html(opt);
        $('#punits-div').html(uopt);
        $('select.select').select2({ minimumResultsForSearch: 7 });
        $('#pquantity').val(qty);
        $('#old_qty').val(qty);
        $('#pcost').val(unit_cost);
        $('#punit_cost').val(formatDecimal(parseFloat(unit_cost) + parseFloat(pr_tax_val)));
        $('#poption').select2('val', item.row.option);
        $('#old_cost').val(unit_cost);
        $('#row_id').val(row_id);
        $('#item_id').val(item_id);
        $('#pexpiry').val(row.children().children('.rexpiry').val());
        $('#pdiscount').val(discount);
        $('#net_cost').text(formatMoney(net_cost));
        $('#pro_tax').text(formatMoney(pr_tax_val));
        $('#psubtotal').val('');
        $('#prModal').appendTo('body').modal('show');
    });

    $('#prModal').on('shown.bs.modal', function (e) {
        if ($('#poption').select2('val') != '') {
            $('#poption').select2('val', product_variant);
            product_variant = 0;
        }
    });

    $(document).on('change', '#pcost, #ptax, #pdiscount', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var unit_cost = parseFloat($('#pcost').val());
        var item = poitems[item_id];
        var ds = $('#pdiscount').val() ? $('#pdiscount').val() : '0';
        if (ds.indexOf('%') !== -1) {
            var pds = ds.split('%');
            if (!isNaN(pds[0])) {
                item_discount = parseFloat((unit_cost * parseFloat(pds[0])) / 100);
            } else {
                item_discount = parseFloat(ds);
            }
        } else {
            item_discount = parseFloat(ds);
        }
        unit_cost -= item_discount;
        var pr_tax = $('#ptax').val(),
            item_tax_method = item.row.tax_method;
        var pr_tax_val = 0,
            pr_tax_rate = 0;
        if (pr_tax !== null && pr_tax != 0) {
            $.each(tax_rates, function () {
                if (this.id == pr_tax) {
                    if (this.type == 1) {
                        if (item_tax_method == 0) {
                            pr_tax_val = formatDecimal((unit_cost * parseFloat(this.rate)) / (100 + parseFloat(this.rate)), 4);
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                            unit_cost -= pr_tax_val;
                        } else {
                            pr_tax_val = formatDecimal((unit_cost * parseFloat(this.rate)) / 100, 4);
                            pr_tax_rate = formatDecimal(this.rate) + '%';
                        }
                    } else if (this.type == 2) {
                        pr_tax_val = parseFloat(this.rate);
                        pr_tax_rate = this.rate;
                    }
                }
            });
        }

        $('#net_cost').text(formatMoney(unit_cost));
        $('#pro_tax').text(formatMoney(pr_tax_val));
    });

    $(document).on('change', '#punit', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var item = poitems[item_id];
        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var unit = $('#punit').val();
        if (unit != poitems[item_id].row.base_unit) {
            $.each(item.units, function () {
                if (this.id == unit) {
                    $('#pcost')
                        .val(formatDecimal(parseFloat(item.row.base_unit_cost) * unitToBaseQty(1, this), 4))
                        .change();
                }
            });
        } else {
            $('#pcost').val(formatDecimal(item.row.base_unit_cost)).change();
        }
    });

    $(document).on('click', '#calculate_unit_price', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id');
        var item = poitems[item_id];
        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }
        var subtotal = parseFloat($('#psubtotal').val()),
            qty = parseFloat($('#pquantity').val());
        $('#pcost')
            .val(formatDecimal(subtotal / qty, 4))
            .change();
        return false;
    });

    /* -----------------------
     * Edit Row Method
     ----------------------- */
    $(document).on('click', '#editItem', function () {
        var row = $('#' + $('#row_id').val());
        var item_id = row.attr('data-item-id'),
            new_pr_tax = $('#ptax').val(),
            new_pr_tax_rate = {};
        if (new_pr_tax) {
            $.each(tax_rates, function () {
                if (this.id == new_pr_tax) {
                    new_pr_tax_rate = this;
                }
            });
        }

        if (!is_numeric($('#pquantity').val()) || parseFloat($('#pquantity').val()) < 0) {
            $(this).val(old_row_qty);
            bootbox.alert(lang.unexpected_value);
            return;
        }

        var unit = $('#punit').val();
        var base_quantity = parseFloat($('#pquantity').val());
        if (unit != poitems[item_id].row.base_unit) {
            $.each(poitems[item_id].units, function () {
                if (this.id == unit) {
                    base_quantity = unitToBaseQty($('#pquantity').val(), this);
                }
            });
        }

        (poitems[item_id].row.fup = 1),
            (poitems[item_id].row.qty = parseFloat($('#pquantity').val())),
            (poitems[item_id].row.base_quantity = parseFloat(base_quantity)),
            (poitems[item_id].row.unit = unit),
            (poitems[item_id].row.real_unit_cost = parseFloat($('#pcost').val())),
            (poitems[item_id].row.tax_rate = new_pr_tax),
            (poitems[item_id].tax_rate = new_pr_tax_rate),
            (poitems[item_id].row.discount = $('#pdiscount').val() ? $('#pdiscount').val() : '0'),
            (poitems[item_id].row.option = $('#poption').val()),
            (poitems[item_id].row.expiry = $('#pexpiry').val() ? $('#pexpiry').val() : '');
            (poitems[item_id].row.warehouse_shelf = $('#warehouse_shelf').val() ? $('#warehouse_shelf').val() : '');
        localStorage.setItem('poitems', JSON.stringify(poitems));
        $('#prModal').modal('hide');
        loadItems();
        if(ws_edit){
            $('#poTable input').attr('readonly','readonly');
        }
        return;
    });

    /* ------------------------------
     * Show manual item addition modal
     ------------------------------- */
    $(document).on('click', '#addManually', function (e) {
        $('#mModal').appendTo('body').modal('show');
        return false;
    });

           /* --------------------------
     * Edit Row BatchNo Method rbatchno
     -------------------------- */
    var old_row_batchno;
    var currTabIndex;
    $(document)
        .on('focus', '.rbatchno', function () {
            old_row_batchno = $(this).val();
            currTabIndex = $(this).prop('tabindex');
        })
        .on('blur', '.rbatchno', function () {
            var row = $(this).closest('tr');
            var new_batchno = $(this).val(),
            item_id = row.attr('data-item-id');
            var batchfound = findMatchingItemWithSameBatchNo(new_batchno, item_id, poitems);
            if(batchfound){
                $(this).val('');
                poitems[item_id].row.batchno = '';
                bootbox.alert("Cannot add same batch number for same product");
            }else{
                poitems[item_id].row.batchno = new_batchno;
                localStorage.setItem('poitems', JSON.stringify(poitems));
                //loadItems();
            }
            //$('[tabindex=' + (currTabIndex + 1) + ']').focus();
            
        })
        .on('change', '.rbatchno', function () {
            var row = $(this).closest('tr');
            /*if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
                $(this).val(old_row_batchno);
                bootbox.alert(lang.unexpected_value);
                return;
            }*/
            //var new_batchno = parseFloat($(this).val()),
            var new_batchno = $(this).val(),
                item_id = row.attr('data-item-id');
            /*poitems[item_id].row.batchno = new_batchno;
            localStorage.setItem('poitems', JSON.stringify(poitems));
            loadItems();*/
        });
           /* --------------------------
     * Edit Row Discount2 Method rdis2 rbatchno
     -------------------------- */
    var old_row_dis2;
    $(document)
        .on('focus', '.rdis2', function () {
            old_row_dis2 = $(this).val();
        })
        .on('change', '.rdis2', function () {
            var row = $(this).closest('tr');
            if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
                $(this).val(old_row_dis2);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            var new_dis2 = parseFloat($(this).val()),
                item_id = row.attr('data-item-id');
            poitems[item_id].row.dis2 = new_dis2;
            localStorage.setItem('poitems', JSON.stringify(poitems));
            loadItems();
        });

       /* --------------------------
     * Edit Row Discount1 Method rdis1
     -------------------------- */
    var old_row_dis1;
    $(document)
        .on('focus', '.rdis1', function () {
            old_row_dis1 = $(this).val();
        })
        .on('change', '.rdis1', function () {
            var row = $(this).closest('tr');
            if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
                $(this).val(old_row_dis1);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            var new_dis1 = parseFloat($(this).val()),
                item_id = row.attr('data-item-id');
            poitems[item_id].row.dis1 = new_dis1;
            localStorage.setItem('poitems', JSON.stringify(poitems));
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
            poitems[item_id].row.bonus = new_bonus;
            localStorage.setItem('poitems', JSON.stringify(poitems));
            loadItems();
        });

    /* --------------------------
     * Edit Row Quantity Method rbonus
     -------------------------- */
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
            poitems[item_id].row.base_quantity = new_qty;
            if (poitems[item_id].row.unit != poitems[item_id].row.base_unit) {
                $.each(poitems[item_id].units, function () {
                    if (this.id == poitems[item_id].row.unit) {
                        poitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                    }
                });
            }
            poitems[item_id].row.qty = new_qty;
            poitems[item_id].row.received = new_qty;
            localStorage.setItem('poitems', JSON.stringify(poitems));
            loadItems();
        });

    var old_received;
    $(document)
        .on('focus', '.received', function () {
            old_received = $(this).val();
        })
        .on('change', '.received', function () {
            var row = $(this).closest('tr');
            new_received = $(this).val() ? $(this).val() : 0;
            if (!is_numeric(new_received)) {
                $(this).val(old_received);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            var new_received = parseFloat($(this).val()),
                item_id = row.attr('data-item-id');
            if (new_received > poitems[item_id].row.qty) {
                $(this).val(old_received);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            (unit = formatDecimal(row.children().children('.runit').val())),
                $.each(poitems[item_id].units, function () {
                    if (this.id == unit) {
                        qty_received = formatDecimal(unitToBaseQty(new_received, this), 4);
                    }
                });
            poitems[item_id].row.unit_received = new_received;
            poitems[item_id].row.received = qty_received;
            localStorage.setItem('poitems', JSON.stringify(poitems));
            loadItems();
        });

    /* --------------------------
     * Edit Row Cost Method
     -------------------------- */
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
            poitems[item_id].row.cost = new_cost;
            localStorage.setItem('poitems', JSON.stringify(poitems));
            loadItems();
        });

    var old_sale_price;
    $(document)
        .on('focus', '.scost', function() {
            old_sale_price = $(this).val();
        })
        .on('change', '.scost', function () {
            var row = $(this).closest('tr');
            if (!is_numeric($(this).val())) {
                $(this).val(old_sale_price);
                bootbox.alert(lang.unexpected_value);
                return;
            }
            var new_sale_price = parseFloat($(this).val()),
                item_id = row.attr('data-item-id');
            poitems[item_id].row.sale_price = new_sale_price;
            localStorage.setItem('poitems', JSON.stringify(poitems));
            loadItems();
        });

    $(document).on('click', '#removeReadonly', function () {
        $('#posupplier').select2('readonly', false);
        return false;
    });

    if (po_edit) {
        $('#posupplier').select2('readonly', true);
    }
});
/* -----------------------
 * Misc Actions
 ----------------------- */

// hellper function for supplier if no localStorage value
function nsSupplier() {
    $('#posupplier').select2({
        minimumInputLength: 1,
        ajax: {
            url: site.base_url + 'suppliers/suggestions',
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
var first_load = 1;
function loadItems() {
   

    if (localStorage.getItem('poitems')) {
        total = 0;
        grand_total_vat = 0;
        grand_total_purchases = 0;
        grand_total_sales = 0;
        count = 1;
        an = 1;
        product_tax = 0;
        invoice_tax = 0;
        product_discount = 0;
        order_discount = 0;
        total_discount = 0;
        $('#poTable tbody').empty();
        poitems = JSON.parse(localStorage.getItem('poitems'));
        /*sortedItems =
            site.settings.item_addition == 1
                ? _.sortBy(poitems, function (o) {
                      return [parseInt(o.order)];
                  })
                : poitems;*/

        sortedItems = _.sortBy(poitems, function (o) {
                        return [parseInt(o.order)];
                    }).reverse();

        var order_no = new Date().getTime();
        $.each(sortedItems, function () {
            var item = this;
            var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
            item.order = item.order ? item.order : order_no++;
            var product_id = item.row.id,
                item_type = item.row.type,
                combo_items = item.combo_items,
                item_cost = item.row.cost,
                item_sale_price = item.row.sale_price,
                item_oqty = item.row.oqty,
                item_qty = item.row.qty,
                item_bqty = item.row.quantity_balance,
                item_expiry = item.row.expiry,
                item_batchno = item.row.batchno,
                item_tax_method = item.row.tax_method,
                item_ds = item.row.discount,
                item_discount = 0,
                item_option = item.row.option,
                item_code = item.row.code,
                item_bonus = item.row.bonus,
                item_dis1 = item.row.dis1,
                item_dis2 = item.row.dis2,
                item_supplier_dis = item.row.get_supplier_discount,
                warehouse_shelf = item.row.warehouse_shelf ? item.row.warehouse_shelf: '',
                item_name = item.row.name.replace(/"/g, '&#034;').replace(/'/g, '&#039;');

            var qty_received = item.row.received >= 0 ? item.row.received : item.row.qty;
            var item_supplier_part_no = item.row.supplier_part_no ? item.row.supplier_part_no : '';
            if (item.row.new_entry == 1) {
                item_bqty = item_qty;
                item_oqty = item_qty;
            }
            var unit_cost = item.row.cost;
            var product_unit = item.row.unit,
                base_quantity = item.row.base_quantity;
            var supplier = localStorage.getItem('posupplier'),
                belong = false;

            if (supplier == item.row.supplier1) {
                belong = true;
            } else if (supplier == item.row.supplier2) {
                belong = true;
            } else if (supplier == item.row.supplier3) {
                belong = true;
            } else if (supplier == item.row.supplier4) {
                belong = true;
            } else if (supplier == item.row.supplier5) {
                belong = true;
            }
            var unit_qty_received = qty_received;
            if (item.row.fup != 1 && product_unit != item.row.base_unit) {
                $.each(item.units, function () {
                    if (this.id == product_unit) {
                        base_quantity = formatDecimal(unitToBaseQty(item.row.qty, this), 4);
                        unit_qty_received = item.row.unit_received
                            ? item.row.unit_received
                            : formatDecimal(baseToUnitQty(qty_received, this), 4);
                        unit_cost = formatDecimal(parseFloat(item.row.base_unit_cost) * unitToBaseQty(1, this), 4);
                    }
                });
            }
            var ds = item_ds ? item_ds : '0';
            item_discount = calculateDiscount(ds, unit_cost);
            product_discount += parseFloat(item_discount * item_qty);

            unit_cost = formatDecimal(unit_cost - item_discount);
            var pr_tax = item.tax_rate;
            var pr_tax_val = (pr_tax_rate = 0);
            if (site.settings.tax1 == 1 && (ptax = calculateTax(pr_tax, unit_cost, item_tax_method))) {
                pr_tax_val = ptax[0];
                pr_tax_rate = ptax[1];
            }
            pr_tax_val = formatDecimal(pr_tax_val);
            product_tax += formatDecimal(pr_tax_val * item_qty);
            item_cost = item_tax_method == 0 ? formatDecimal(unit_cost - pr_tax_val, 4) : formatDecimal(unit_cost);
            unit_cost = formatDecimal(unit_cost + item_discount, 4);
            var sel_opt = '';
            $.each(item.options, function () {
                if (this.id == item_option) {
                    sel_opt = this.name;
                }
            });

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

            var total_before_dis_vat = (parseFloat(item_cost)) * parseFloat(item_qty); //(parseFloat(item_cost) + parseFloat(pr_tax_val)) * parseFloat(item_qty); 

                if(item_supplier_dis != item_dis1 && item_supplier_dis != 0)
                {
                    item_dis1 = item_supplier_dis;
                }
           

               dis1_a = total_before_dis_vat * parseFloat((item_dis1 / 100));
               total_after_dis1 =  total_before_dis_vat - dis1_a;
               dis2_a = total_after_dis1 *  parseFloat((item_dis2/100));
               total_after_dis2 =  total_after_dis1 - dis2_a;
               vat_15_a = total_after_dis2 * parseFloat(item.tax_rate.rate/100);//total_after_dis2 * parseFloat(15/100);
               net_price_a = vat_15_a + total_after_dis2;

               /*dis1_b = item_bonus * parseFloat(item_cost) * (item_dis1/100);
               total_after_dis1_b =  (item_bonus * parseFloat(item_cost))- dis1_b;
               dis2_b =  total_after_dis1_b * (item_dis2/100);
               total_after_dis2_b =  total_after_dis1_b - dis2_b;
               vat_15_b = total_after_dis2_b * (15/100);
               net_price_b = vat_15_b;*/

               
               var total_purchases = (parseFloat(item_cost)) * parseFloat(item_qty);
               var total_sales = (parseFloat(item_sale_price)) * parseFloat(item_qty + item_bonus);
               total_after_dis1 = total_purchases * parseFloat((item_dis1 / 100));
               total_after_dis2 = (total_purchases - total_after_dis1) * parseFloat((item_dis2 / 100));
               //main_net = net_price_a;// + net_price_b;
               main_net = total_purchases - (total_after_dis1 + total_after_dis2);
               var new_unit_cost = parseFloat(main_net) / parseFloat(item_qty + item_bonus);

            var row_no = item.id;

            
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');
            tr_html =
                '<td><input name="product_id[]" type="hidden" class="rid" value="' +
                product_id +
                '"><input name="product[]" type="hidden" class="rcode" value="' +
                item_code +
                '"><input name="product_name[]" type="hidden" class="rname" value="' +
                item_name +
                '"><input name="product_option[]" type="hidden" class="roption" value="' +
                item_option +
                '"><input name="part_no[]" type="hidden" class="rpart_no" value="' +
                item_supplier_part_no +
                '"><input name="totalbeforevat[]" type="hidden" class="totalbeforevat" value="' +
                total_after_dis2 +
                '"><input name="main_net[]" type="hidden" class="main_net" value="' +
                main_net +
                '"><input name="warehouse_shelf[]" type="hidden" class="warehouse_shelf" value="' +
                warehouse_shelf +
                '"><span class="sname" id="name_' +
                row_no +
                '">' +
                item_code +
                ' - ' +
                item_name +
                (sel_opt != '' ? ' (' + sel_opt + ')' : '') +
                ' <span class="label label-default">' +
                item_supplier_part_no +
                '</span></span> <i class="pull-right fa fa-edit tip edit" id="' +
                row_no +
                '" data-item="' +
                item_id +
                '" title="Edit" style="cursor:pointer;"></i></td>';
            
                
            //    tr_html += '<td><span class="text-right scost" id="ssale_' +
            //     row_no +
            //     '">' +
            //     formatMoney(item_sale_price) +
            //     '</span></td>';

            tr_html +=
                '<td><input class="form-control scost text-center" name="sale_price[]" type="text" value="' +
                formatDecimal(item_sale_price, 2) +
                '" data-id="' +
                row_no +
                '" data-item="' +
                item_id +
                '" id="ssale_' +
                row_no +
                '"></td>';


            tr_html +=
                '<td class="text-right"><input class="rucost" name="unit_cost[]" type="hidden" value="' +
                unit_cost +
                '"><input class="form-control realucost" name="real_unit_cost[]" type="hidden" value="' +
                item.row.real_unit_cost +
                '"><input class="form-control input-sm text-center rcost" type="text" name="net_cost[]" type="hidden" id="cost_' +
                row_no +
                '" value="' +
                formatDecimal(item.row.cost, 2) +
                '"></td>';

{/* <span class="text-right scost" id="scost_' +
                row_no +
                '">' +
                formatMoney(item_cost) +
                '</span> */}

            tr_html +=
                '<td><input class="form-control rbatchno" name="batchno[]" type="text" value="' +
                item_batchno +
                '" data-id="' +
                row_no +
                '" data-item="' +
                item_id +
                '" id="batchno_' +
                row_no +
                '"></td>';

            if (site.settings.product_expiry == 1) {
                tr_html +=
                    '<td><input class="form-control date rexpiry" name="expiry[]" type="text" value="' +
                    item_expiry +
                    '" data-id="' +
                    row_no +
                    '" data-item="' +
                    item_id +
                    '" id="expiry_' +
                    row_no +
                    '"></td>';
            }
            
            tr_html +=
                '<td><input name="quantity_balance[]" type="hidden" class="rbqty" value="' +
                item_bqty +
                '"><input class="form-control text-center rquantity" name="quantity[]" type="text" value="' +
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

            tr_html +=
                '<td><input class="form-control text-center rbonus" name="bonus[]" type="text" data-id="' +
                row_no +
                '" data-item="' +
                item_id +
                '" id="bonus_' +
                row_no +
                '" value="'+ formatDecimal(item_bonus)
                +'"onClick="this.select();"></td>';

            tr_html +=
                '<td><input class="form-control text-center rdis1" name="dis1[]" type="text" data-id="' +
                row_no +
                '" data-item="' +
                item_id +
                '" id="dis1_' +
                row_no +
                '" value="'+formatDecimal(item_dis1)+'" onClick="this.select();"><span style="position:absolute;font-size:10px;margin-top:5px;">' +
                formatMoney(total_after_dis1)
                '</span></td>';

            tr_html +=
                '<td><input class="form-control text-center rdis2" name="dis2[]" type="text" data-id="' +
                row_no +
                '" data-item="' +
                item_id +
                '" id="dis2_' +
                row_no +
                '" value="'+formatDecimal(item_dis2)+'" onClick="this.select();"><span style="position:absolute;font-size:10px;margin-top:5px;">' +
                formatMoney(total_after_dis2)
                '</span></td>';

            tr_html +=
                '<td class="text-right"><input class="form-control input-sm text-right rproduct_tax" name="product_tax[]" type="hidden" id="product_tax_' +
                    row_no +
                    '" value="' +
                    pr_tax.id +
                    '"><span class="text-right rvat15" id="vat15_' +
                row_no +
                '">'+formatMoney(vat_15_a)+'</span></td>';

            /*if (po_edit) {
                tr_html +=
                    '<td class="rec_con"><input name="ordered_quantity[]" type="hidden" class="oqty" value="' +
                    item_oqty +
                    '"><input class="form-control text-center received" name="received[]" type="text" value="' +
                    formatDecimal(unit_qty_received) +
                    '" data-id="' +
                    row_no +
                    '" data-item="' +
                    item_id +
                    '" id="received_' +
                    row_no +
                    '" onClick="this.select();"><input name="received_base_quantity[]" type="hidden" class="rrbase_quantity" value="' +
                    qty_received +
                    '"></td>';
            }*/
            /*if (site.settings.product_discount == 1) {
                tr_html +=
                    '<td class="text-right"><input class="form-control input-sm rdiscount" name="product_discount[]" type="hidden" id="discount_' +
                    row_no +
                    '" value="' +
                    item_ds +
                    '"><span class="text-right sdiscount text-danger" id="sdiscount_' +
                    row_no +
                    '">' +
                    formatMoney(0 - item_discount * item_qty) +
                    '</span></td>';
            }
            if (site.settings.tax1 == 1) {
                tr_html +=
                    '<td class="text-right"><input class="form-control input-sm text-right rproduct_tax" name="product_tax[]" type="hidden" id="product_tax_' +
                    row_no +
                    '" value="' +
                    pr_tax.id +
                    '"><span class="text-right sproduct_tax" id="sproduct_tax_' +
                    row_no +
                    '">' +
                    (pr_tax_rate ? '(' + pr_tax_rate + ')' : '') +
                    ' ' +
                    formatMoney(pr_tax_val * item_qty) +
                    '</span></td>';
            } formatMoney((parseFloat(item_cost) + parseFloat(pr_tax_val)) * parseFloat(item_qty))*/
            tr_html +=
                '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_' +
                row_no +
                '">' +
                formatMoney(total_purchases) +
                '</span></td>';

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
                '<td class="text-center"><i class="fa fa-times tip podel" id="' +
                row_no +
                '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.prependTo('#poTable');
            total += formatDecimal(main_net, 4);//formatDecimal((parseFloat(item_cost) + parseFloat(pr_tax_val)) * parseFloat(item_qty), 4);
            grand_total_vat += formatDecimal(vat_15_a, 4);
            grand_total_purchases += formatDecimal(total_purchases, 4);
            grand_total_sales += formatDecimal(total_sales, 4);
            count += parseFloat(item_qty);
            an++;
            if (!belong) $('#row_' + row_no).addClass('warning');
        });

       

        var trRowClas = localStorage.getItem('trRowClas');
        if(trRowClas != undefined && trRowClas !=""){
            $(".row_"+trRowClas).css("color", "green"); 
        }


        var col = 8;
        if (site.settings.product_expiry == 1) {
            col++;
        }
        var tfoot =
            '<tr id="tfoot" class="tfoot active"><th colspan="' +
            col +
            '">Total</th><th class="text-center">' +
            formatMoney(grand_total_vat) +
            '</th>';
        /*if (po_edit) {
            tfoot += '<th class="rec_con"></th>';
        }*/
       
        tfoot += '<th class="text-right">' + formatMoney(grand_total_purchases) + '</th>';
        
        tfoot += '<th class="text-right">' + formatMoney(grand_total_sales) + '</th>';
        
        tfoot +=
            '<th class="text-right">' +
            formatMoney(total) +
            '</th></tr>';
        $('#poTable tfoot').html(tfoot);

        // Order level discount calculations
        if ((podiscount = localStorage.getItem('podiscount'))) {
            var ds = podiscount;
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

        // Order level tax calculations
        if (site.settings.tax2 != 0) {
            if ((potax2 = localStorage.getItem('potax2'))) {
                $.each(tax_rates, function () {
                    if (this.id == potax2) {
                        if (this.type == 2) {
                            invoice_tax = formatDecimal(this.rate);
                        }
                        if (this.type == 1) {
                            invoice_tax = formatDecimal(((total - order_discount) * this.rate) / 100, 4);
                        }
                    }
                });
            }
        }
        total_discount = parseFloat(order_discount + product_discount);
        // Totals calculations after item addition
        var gtotal = total + invoice_tax - order_discount + shipping;
        $('#total').text(formatMoney(total));
        $('#titems').text(an - 1 + ' (' + formatQty(parseFloat(count) - 1) + ')');
        $('#tds').text(formatMoney(order_discount));
        if (site.settings.tax1) {
            $('#ttax1').text(formatMoney(product_tax));
        }
        if (site.settings.tax2 != 0) {
            $('#ttax2').text(formatMoney(invoice_tax));
        }
        $('#gtotal').text(formatMoney(gtotal));
        if (an > parseInt(site.settings.bc_fix) && parseInt(site.settings.bc_fix) > 0) {
            $('html, body').animate({ scrollTop: $('#sticker').offset().top }, 500);
            $(window).scrollTop($(window).scrollTop() + 1);
        }
        if(first_load == 1){
            first_load = 0;
            set_page_focus();
        }
    }

    // Reset all tabindexes to -1
    /*document.querySelectorAll('[tabindex]').forEach(function(el) {
        el.removeAttribute('tabindex');
    });
  
    // Assign tabindexes to valid input elements
    var inputEls = document.querySelectorAll('input:not([type="hidden"]):not([disabled])');
    inputEls.forEach(function(el, idx) {
        el.tabIndex = idx + 1;
    });

    currTabIndex = document.activeElement.tabIndex;*/
}

/* -----------------------------
 * Add Purchase Iten Function
 * @param {json} item
 * @returns {Boolean}
 ---------------------------- */
function add_purchase_item(item) {
    if (count == 1) {
        poitems = {};
        if ($('#posupplier').val()) {
            $('#posupplier').select2('readonly', true);
        } else {
            bootbox.alert(lang.select_above);
            item = null;
            return;
        }
    }
    if (item == null) return;

    var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
    if (poitems[item_id]) {
        var new_qty = parseFloat(poitems[item_id].row.qty) + 1;
        poitems[item_id].row.base_quantity = new_qty;
        if (poitems[item_id].row.unit != poitems[item_id].row.base_unit) {
            $.each(poitems[item_id].units, function () {
                if (this.id == poitems[item_id].row.unit) {
                    poitems[item_id].row.base_quantity = unitToBaseQty(new_qty, this);
                }
            });
        }
        poitems[item_id].row.qty = new_qty;
    } else {
        var foundItem = findMatchingPoItem(item, poitems);
        if(foundItem){
            if (isBatchNoEmpty(foundItem.item_id, poitems)) {
                bootbox.alert("No batch number entered for the same product");
            } else {
                poitems[item_id] = item;
            }
        }else{
            poitems[item_id] = item;
        }
    }
    poitems[item_id].order = new Date().getTime();
    localStorage.setItem('poitems', JSON.stringify(poitems));
    loadItems();
    return true;
}

function isBatchNoEmpty(item_id, poitems) {
    const poitemKeys = Object.keys(poitems);
    for (const key of poitemKeys) {
      const poitem = poitems[key];
      if (poitem.item_id === item_id && poitem.row.batchno === "") {
        return true;
      }
    }
    return false;
}

function findMatchingItemWithSameBatchNo(batchno, item_id, poitems) {
    var iitem_id = poitems[item_id].item_id;
    const poitemKeys = Object.keys(poitems);
    for (const key of poitemKeys) {
      const poitem = poitems[key];
      if (poitem.item_id === iitem_id && poitem.row.batchno == batchno && poitem.id != item_id) {
        return poitem;
      }
    }
    return null;
}

function findMatchingPoItem(item, poitems) {
    const poitemKeys = Object.keys(poitems);
    for (const key of poitemKeys) {
      const poitem = poitems[key];
      if (poitem.item_id === item.item_id) {
        return poitem;
      }
    }
    return null;
}

if (typeof Storage === 'undefined') {
    $(window).bind('beforeunload', function (e) {
        if (count > 1) {
            var message = 'You will loss data!';
            return message;
        }
    });
}
