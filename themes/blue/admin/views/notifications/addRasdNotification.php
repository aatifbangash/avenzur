<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('Accept Dispatch'); ?></h4>
        </div>
        <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
        echo admin_form_open_multipart('notifications/addRasdNotification', $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                <?php echo lang('Notification Id*', 'notification_id'); ?>
                <div class="controls">
                    <?php echo form_input('notification_id', '', 'class="form-control" id="notification_id" required="required"'); ?>
                </div>
            </div>

              <div class="form-group">
                <div class="col-md-12">
                            <div class="panel panel-warning">
                                <!-- <div
                                    class="panel-heading"><?= lang('please_select_these_before_adding_product') ?></div> -->
                                <div>
                                    
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <?= lang('warehouse', 'powarehouse'); ?>
                                            <?php
                                            $wh[''] = '';
                                                foreach ($warehouses as $warehouse) {
                                                    $wh[$warehouse->id] = $warehouse->name.' ('.$warehouse->code.')';
                                                }
                                                echo form_dropdown('warehouse', $wh, ($_POST['warehouse'] ?? $Settings->default_warehouse), 'id="powarehouse" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('warehouse') . '" required="required" style="width:100%;" '); 
                                                ?>
                                        </div>
                                    </div> 
                                      <div class="clearfix"></div>
                                </div>
                                <div class="panel-body" style="padding: 5px;">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?= lang('Parent Supplier', 'posupplier'); ?>
                                            <?php if ($Owner || $Admin || $GP['suppliers-add'] || $GP['suppliers-index']) {
                                                ?><div class="input-group"><?php
                                            } ?>
                                                <input type="hidden" name="supplier" value="" id="posupplier"
                                                       class="form-control" style="width:100%;"
                                                       placeholder="<?= lang('select') . ' ' . lang('supplier') ?>">
                                                <input type="hidden" name="supplier_id" value="" id="supplier_id"
                                                       class="form-control">
                                                <?php if ($Owner || $Admin || $GP['suppliers-index']) {
                                                ?>
                                                    <div class="input-group-addon no-print" style="padding: 2px 5px; border-left: 0;">
                                                        <a href="#" id="view-supplier" class="external" data-toggle="modal" data-target="#myModal">
                                                            <i class="fa fa-2x fa-user" id="addIcon"></i>
                                                        </a>
                                                    </div>
                                                <?php
                                                } ?>
                                                <?php if ($Owner || $Admin || $GP['suppliers-add']) {
                                                ?>
                                                <div class="input-group-addon no-print" style="padding: 2px 5px;">
                                                    <a href="<?= admin_url('suppliers/add'); ?>" id="add-supplier" class="external" data-toggle="modal" data-target="#myModal">
                                                        <i class="fa fa-2x fa-plus-circle" id="addIcon"></i>
                                                    </a>
                                                </div>
                                                <?php
                                                } ?>
                                                <?php if ($Owner || $Admin || $GP['suppliers-add'] || $GP['suppliers-index']) {
                                                ?></div><?php
                                                } ?>
                                        </div>
                                    </div>
                                                 
                                    <!-- Child Suppliers -->

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?= lang('Child Supplier', 'posupplier'); ?>
                                            <?php
                                            $childSupArr[''] = '';
                                            
                                            echo form_dropdown('childsupplier', $childSupArr, $_POST['childsupplier'], 'id="childsupplier" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('child supplier') . '" required="required" style="width:100%;" '); ?>
                                        </div>
                                    </div>

                                </div>
                            </div>
              </div>
                
                            <div class="clearfix"></div>
                        </div>

        </div>
        <div class="modal-footer">
            <div class="btn  btn-primary" id="submitAcceptDispatch" >Accept Dispatch</div>
            <!-- <?php echo form_submit('add_notification', lang('Accept Dispatch'), 'class="btn btn-primary"'); ?> -->
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<?= $modal_js ?>
<!-- <script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" charset="UTF-8">
    $.fn.datetimepicker.dates['sma'] = <?=$dp_lang?>;
</script> -->
<script>
    $(document).ready(function () {
        var $submit = $("#submitAcceptDispatch");
        $submit.click(function(e){
            const parentSupplier = $("#posupplier").val();
            const childSupplier = $("#childsupplier").val();
            const warehouseId = $("#powarehouse").val();
            const notificationId = $("#notification_id").val();
            console.log('Supplier id:'+parentSupplier);
            if(!parentSupplier  && !childSupplier){
                return;
            }

            $.ajax({
                url: "<?= admin_url('Notifications/acceptDispatch/'); ?>",
                type: "POST",
                dataType: "json",
                data: {
                    notificationId: notificationId,
                    supplierId: parentSupplier,
                    warehouseId: warehouseId,
                    childSupplierId: childSupplier,
                    "<?= $this->security->get_csrf_token_name() ?>": "<?= $this->security->get_csrf_hash() ?>"
                },
                success: function (response) {
                    console.log("Response received:", response);
                    // Handle the successful response here
                },
                error: function (xhr, status, error) {
                    console.error("Error occurred:", error);
                    // Handle errors here
                },
                cache: true
            });
            
        }) 
        
        var $supplier = $("#posupplier"),
		$currency = $("#pocurrency");

        var $childsupplierselectbox = $("#childsupplier");
        $supplier.change(function (e) {
            localStorage.setItem("posupplier", $(this).val());
            localStorage.setItem("childsupplier", null);
            $("#supplier_id").val($(this).val());

            //localStorage.removeItem('childsupplier');
            //$childsupplierselectbox.empty();
            //$childsupplierselectbox.val();
            populateChildSuppliers($(this).val());
	    });
        $childsupplierselectbox.change(function (e) {
            localStorage.setItem("childsupplier", $(this).val());
            $("#child_supplier_id").val($(this).val());
        });

        function populateChildSuppliers(pid) {
            $.ajax({
                url: site.base_url + "suppliers/getChildById",
                data: { term: "", limit: 10, pid: pid },
                dataType: "json",
                success: function (data) {
                    $childsupplierselectbox.empty();
                    $.each(data.results, function (index, value) {
                        $childsupplierselectbox.append(new Option(value.text, value.id));
                    });

                    if (localStorage.getItem("childsupplier")) {
                        $childsupplierselectbox
                            .val(localStorage.getItem("childsupplier"))
                            .trigger("change");
                    }
                },
            });
	    }
        if ((posupplier = localStorage.getItem("posupplier"))) {
                $supplier.val(posupplier).select2({
                    minimumInputLength: 1,
                    data: [],
                    initSelection: function (element, callback) {
                        $.ajax({
                            type: "get",
                            async: false,
                            url: site.base_url + "suppliers/getSupplier/" + $(element).val(),
                            dataType: "json",
                            success: function (data) {
                                callback(data[0]);
                            },
                        });
                    },
                    ajax: {
                        url: site.base_url + "suppliers/suggestions",
                        dataType: "json",
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
                                return { results: [{ id: "", text: "No Match Found" }] };
                            }
                        },
                    },
                });

                populateChildSuppliers(posupplier);
            } else {
                nsSupplier();
                //nsChildSupplierByParentId($(this).val());
        }
        function nsChildSupplierByParentId(pid) {
            $("#childsupplier").select2({
                ajax: {
                    url: site.base_url + "suppliers/getChildById",
                    dataType: "json",
                    quietMillis: 15,
                    data: function (term, page) {
                        return {
                            pid: pid,
                            search: term,
                        };
                    },
                    results: function (data, page) {
                        if (data.results != null) {
                            return { results: data.results };
                        } else {
                            return { results: [{ id: "", text: "No Match Found" }] };
                        }
                    },
                },
            });
        }

        function nsChildSupplier() {
            $("#childsupplier").select2({
                minimumInputLength: 1,
                ajax: {
                    url: site.base_url + "suppliers/childsuggestions",
                    dataType: "json",
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
                            return { results: [{ id: "", text: "No Match Found" }] };
                        }
                    },
                },
            });
        }

        function nsSupplier() {
            $("#posupplier").select2({
                minimumInputLength: 1,
                ajax: {
                    url: site.base_url + "suppliers/suggestions",
                    dataType: "json",
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
                            return { results: [{ id: "", text: "No Match Found" }] };
                        }
                    },
                },
            });
        }
    })
</script>
