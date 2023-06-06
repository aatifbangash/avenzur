<style>
        .select2-container--default.select2-container--focus,.select2-selection.select2-container--focus,.select2-container--default:focus,.select2-selection:focus,.select2-container--default:active,.select2-selection:active {
        outline: none
    }

    .select2-container--default .select2-selection--single,.select2-selection .select2-selection--single {
        border: 1px solid #d2d6de;
        border-radius: 0;
        padding: 6px 12px;
        height: 34px
    }

    .select2-container--default.select2-container--open {
        border-color: #3c8dbc
    }

    .select2-dropdown {
        border: 1px solid #d2d6de;
        border-radius: 0
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #3c8dbc;
        color: white
    }

    .select2-results__option {
        padding: 6px 12px;
        user-select: none;
        -webkit-user-select: none
    }

    .select2-container .select2-selection--single .select2-selection__rendered {
        padding-left: 0;
        padding-right: 0;
        height: auto;
        margin-top: -4px
    }

    .select2-container[dir="rtl"] .select2-selection--single .select2-selection__rendered {
        padding-right: 6px;
        padding-left: 20px
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 28px;
        right: 3px
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow b {
        margin-top: 0
    }

    .select2-dropdown .select2-search__field,.select2-search--inline .select2-search__field {
        border: 1px solid #d2d6de
    }

    .select2-dropdown .select2-search__field:focus,.select2-search--inline .select2-search__field:focus {
        outline: none;
        border: 1px solid #3c8dbc
    }

    .select2-container--default .select2-results__option[aria-disabled=true] {
        color: #999
    }

    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #ddd
    }

    .select2-container--default .select2-results__option[aria-selected=true],.select2-container--default .select2-results__option[aria-selected=true]:hover {
        color: #444
    }

    .select2-container--default .select2-selection--multiple {
        border: 1px solid #d2d6de;
        border-radius: 0
    }

    .select2-container--default .select2-selection--multiple:focus {
        border-color: #3c8dbc
    }

    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #d2d6de
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #3c8dbc;
        border-color: #367fa9;
        padding: 1px 10px;
        color: #fff
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        margin-right: 5px;
        color: rgba(255,255,255,0.7)
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #fff
    }

    .select2-container .select2-selection--single .select2-selection__rendered {
        padding-right: 10px
    }
    .select2-container--default .select2-results>.select2-results__options {
        max-height: 200px;
        overflow-y: auto;
    }
    .select2-container .select2-search--inline {
        float: left
    }

    .select2-container .select2-search--inline .select2-search__field {
        box-sizing: border-box;
        border: none;
        font-size: 100%;
        margin-top: 5px;
        padding: 0
    }

    .select2-container .select2-search--inline .select2-search__field::-webkit-search-cancel-button {
        -webkit-appearance: none
    }

    .select2-dropdown {
        background-color: white;
        border: 1px solid #aaa;
        border-radius: 4px;
        box-sizing: border-box;
        display: block;
        position: absolute;
        left: -100000px;
        width: 100%;
        z-index: 1051
    }

    .select2-results {
        display: block
    }

    .select2-results__options {
        list-style: none;
        margin: 0;
        padding: 0
    }

    .select2-results__option {
        padding: 6px;
        user-select: none;
        -webkit-user-select: none
    }

    .select2-results__option[aria-selected] {
        cursor: pointer
    }

    .select2-container--open .select2-dropdown {
        left: 0
    }

    .select2-container--open .select2-dropdown--above {
        border-bottom: none;
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0
    }

    .select2-container--open .select2-dropdown--below {
        border-top: none;
        border-top-left-radius: 0;
        border-top-right-radius: 0
    }

    .select2-search--dropdown {
        display: block;
        padding: 4px
    }

    .select2-search--dropdown .select2-search__field {
        padding: 4px;
        width: 100%;
        box-sizing: border-box
    }

    .select2-search--dropdown .select2-search__field::-webkit-search-cancel-button {
        -webkit-appearance: none
    }

    .select2-search--dropdown.select2-search--hide {
        display: none
    }

    .select2-close-mask {
        border: 0;
        margin: 0;
        padding: 0;
        display: block;
        position: fixed;
        left: 0;
        top: 0;
        min-height: 100%;
        min-width: 100%;
        height: auto;
        width: auto;
        opacity: 0;
        z-index: 99;
        background-color: #fff;
        filter: alpha(opacity=0)
    }

    .select2-hidden-accessible {
        border: 0 !important;
        clip: rect(0 0 0 0) !important;
        height: 1px !important;
        margin: -1px !important;
        overflow: hidden !important;
        padding: 0 !important;
        position: absolute !important;
        width: 1px !important
    }

    .select2-container--default .select2-selection--single {
        background-color: #fff;
        border: 1px solid #aaa;
        border-radius: 4px
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #444;
        line-height: 28px
    }

    .select2-container--default .select2-selection--single .select2-selection__clear {
        cursor: pointer;
        float: right;
        font-weight: bold
    }

    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #999
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 26px;
        position: absolute;
        top: 1px;
        right: 1px;
        width: 20px
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: #888 transparent transparent transparent;
        border-style: solid;
        border-width: 5px 4px 0 4px;
        height: 0;
        left: 50%;
        margin-left: -4px;
        margin-top: -2px;
        position: absolute;
        top: 50%;
        width: 0
    }
    .select2 {
        min-width: 420px;
    }

    .select2-container--default .select2-results__option[aria-disabled="true"] {
        font-weight: bold;
        color: #000;
    }
</style>
<script type="text/javascript">
$(document).ready(function() {
    /* javascript floating point operations */
    var jsFloatOps = function(param1, param2, op) {
        <?php if ($this->mAccountSettings->decimal_places == 2) { ?>
            param1 = param1 * 100;
            param2 = param2 * 100;
        <?php } else if ($this->mAccountSettings->decimal_places == 3) { ?>
            param1 = param1 * 1000;
            param2 = param2 * 1000;
        <?php } ?>
        param1 = param1.toFixed(0);
        param2 = param2.toFixed(0);
        param1 = Math.floor(param1);
        param2 = Math.floor(param2);
        var result = 0;
        if (op == '+') {
            result = param1 + param2;
            <?php if ($this->mAccountSettings->decimal_places == 2) { ?>
                result = result/100;
            <?php } else if ($this->mAccountSettings->decimal_places == 3) { ?>
                result = result/1000;
            <?php } ?>
            return result;
        }
        if (op == '-') {
            result = param1 - param2;
            <?php if ($this->mAccountSettings->decimal_places == 2) { ?>
                result = result/100;
            <?php } else if ($this->mAccountSettings->decimal_places == 3) { ?>
                result = result/1000;
            <?php } ?>
            return result;
        }
        if (op == '!=') {
            if (param1 != param2)
                return true;
            else
                return false;
        }
        if (op == '==') {
            if (param1 == param2)
                return true;
            else
                return false;
        }
        if (op == '>') {
            if (param1 > param2)
                return true;
            else
                return false;
        }
        if (op == '<') {
            if (param1 < param2)
                return true;
            else
                return false;
        }
    }

    /* Calculating Dr and Cr total */
    $(document).on('change', '.dr-item', function() {
        var drTotal = 0;
        $("table tr .dr-item").each(function() {
            var curDr = $(this).prop('value');
            curDr = parseFloat(curDr);
            if (isNaN(curDr))
                curDr = 0;
            drTotal = jsFloatOps(drTotal, curDr, '+');
            console.log($(this));
            // console.log(curDr);
            // console.log(drTotal);
        });
        $("table tr #dr-total").text(drTotal);
        var crTotal = 0;
        $("table tr .cr-item").each(function() {
            var curCr = $(this).prop('value');
            curCr = parseFloat(curCr);
            if (isNaN(curCr))
                curCr = 0;
            crTotal = jsFloatOps(crTotal, curCr, '+');
        });
        $("table tr #cr-total").text(crTotal);

        if (jsFloatOps(drTotal, crTotal, '==')) {
            $("table tr #dr-total").css("background-color", "#FFFF99");
            $("table tr #cr-total").css("background-color", "#FFFF99");
            $("table tr #dr-diff").text("-");
            $("table tr #cr-diff").text("");
        } else {
            $("table tr #dr-total").css("background-color", "#FFE9E8");
            $("table tr #cr-total").css("background-color", "#FFE9E8");
            if (jsFloatOps(drTotal, crTotal, '>')) {
                $("table tr #dr-diff").text("");
                $("table tr #cr-diff").text(jsFloatOps(drTotal, crTotal, '-'));
            } else {
                $("table tr #dr-diff").text(jsFloatOps(crTotal, drTotal, '-'));
                $("table tr #cr-diff").text("");
            }
        }
    });

    $(document).on('change', '.cr-item', function() {
        var drTotal = 0;
        $("table tr .dr-item").each(function() {
            var curDr = $(this).prop('value')
            curDr = parseFloat(curDr);
            if (isNaN(curDr))
                curDr = 0;
            drTotal = jsFloatOps(drTotal, curDr, '+');
        });
        $("table tr #dr-total").text(drTotal);
        var crTotal = 0;
        $("table tr .cr-item").each(function() {
            var curCr = $(this).prop('value')
            curCr = parseFloat(curCr);
            if (isNaN(curCr))
                curCr = 0;
            crTotal = jsFloatOps(crTotal, curCr, '+');
        });
        $("table tr #cr-total").text(crTotal);

        if (jsFloatOps(drTotal, crTotal, '==')) {
            $("table tr #dr-total").css("background-color", "#FFFF99");
            $("table tr #cr-total").css("background-color", "#FFFF99");
            $("table tr #dr-diff").text("-");
            $("table tr #cr-diff").text("");
        } else {
            $("table tr #dr-total").css("background-color", "#FFE9E8");
            $("table tr #cr-total").css("background-color", "#FFE9E8");
            if (jsFloatOps(drTotal, crTotal, '>')) {
                $("table tr #dr-diff").text("");
                $("table tr #cr-diff").text(jsFloatOps(drTotal, crTotal, '-'));
            } else {
                $("table tr #dr-diff").text(jsFloatOps(crTotal, drTotal, '-'));
                $("table tr #cr-diff").text("");
            }
        }
    });

    /* Dr - Cr dropdown changed */
    $(document).on('change', '.dc-dropdown', function() {
        var drValue = $(this).parent().parent().next().next().children().children().prop('value');
        var crValue = $(this).parent().parent().next().next().next().children().children().prop('value');

        if ($(this).parent().parent().next().children().children().val() == "0") {
            return;
        }

        drValue = parseFloat(drValue);
        if (isNaN(drValue))
            drValue = 0;

        crValue = parseFloat(crValue);
        if (isNaN(crValue))
            crValue = 0;

        if ($(this).prop('value') == "D") {
            if (drValue == 0 && crValue != 0) {
                $(this).parent().parent().next().next().children().children().prop('value', crValue);
            }
            $(this).parent().parent().next().next().next().children().children().prop('value', "");
            $(this).parent().parent().next().next().next().children().children().prop('disabled', 'disabled');
            $(this).parent().parent().next().next().children().children().prop('disabled', '');
        } else {
            if (crValue == 0 && drValue != 0) {
                $(this).parent().parent().next().next().next().children().prop('value', drValue);
            }
            $(this).parent().parent().next().next().children().children().prop('value', "");
            $(this).parent().parent().next().next().children().children().prop('disabled', 'disabled');
            $(this).parent().parent().next().next().next().children().children().prop('disabled', '');
        }
        /* Recalculate Total */
        $('.dr-item:first').trigger('change');
        $('.cr-item:first').trigger('change');
    });

    /* Ledger dropdown changed */
    $(document).on('change', '.ledger-dropdown', function() {
        if ($(this).val() == "0") {
            /* Reset and diable dr and cr amount */
            $(this).parent().parent().next().children().children().prop('value', "");
            $(this).parent().parent().next().next().children().children().prop('value', "");
            $(this).parent().parent().next().children().children().prop('disabled', 'disabled');
            $(this).parent().parent().next().next().children().children().prop('disabled', 'disabled');
        } else {
            /* Enable dr and cr amount and trigger Dr/Cr change */
            $(this).parent().parent().next().children().children().prop('disabled', '');
            $(this).parent().parent().next().next().children().children().prop('disabled', '');
            $(this).parent().parent().prev().children().children().trigger('change');
        }
        /* Trigger dr and cr change */
        $(this).parent().parent().next().children().children().trigger('change');
        $(this).parent().parent().next().next().children().children().trigger('change');

        var ledgerid = $(this).val();
        var rowid = $(this);
        if (ledgerid > 0) {
            $.ajax({
                url: '<?=admin_url("ledgers/cl"); ?>',
                data: 'id=' + ledgerid,
                dataType: 'json',
                success: function(data)
                {
                    var ledger_bal = parseFloat(data['cl']['amount']);

                    var prefix = '';
                    var suffix = '';
                    if (data['cl']['status'] == 'neg') {
                        prefix = '<span class="error-text">';
                        suffix = '</span>';
                    }

                    if (data['cl']['dc'] == 'D') {
                        rowid.parent().parent().next().next().next().next().children().html(prefix + "Dr " + ledger_bal + suffix);
                    } else if (data['cl']['dc'] == 'C') {
                        rowid.parent().parent().next().next().next().next().children().html(prefix + "Cr " + ledger_bal + suffix);
                    } else {
                        rowid.parent().parent().next().next().next().next().children().html("");
                    }
                }
            });
        } else {
            rowid.parent().parent().next().next().next().next().children().text("");
        }
    });

    /* Recalculate Total */
    $(document).on('click', 'table td .recalculate', function() {
        /* Recalculate Total */
        $('.dr-item:first').trigger('change');
        $('.cr-item:first').trigger('change');
    });

    /* Delete ledger row */
    $(document).on('click', '.deleterow', function() {
        $(this).parent().parent().remove();
        /* Recalculate Total */
        $('.dr-item:first').trigger('change');
        $('.cr-item:first').trigger('change');
    });

    

    /* Add ledger row */
    $(document).on('click', '.addrow', function() {
        var cur_obj = this;
        $.ajax({
            url: '<?=admin_url("entries/addrow/").$entrytype["restriction_bankcash"]; ?>',
            success: function(data) {
                console.log(data);
                $(cur_obj).parent().parent().before(data);
                /* Trigger ledger item change */
                $(cur_obj).trigger('change');
                $("tr.ajax-add .ledger-dropdown").select2({
                    width:'100%',
                    ajax: {
                        url: "<?= admin_url("entries/ledgerList/"); ?><?=$entrytypeLabel?>",
                        dataType: 'json',
                        type: "post",
                        delay: 250,
                        data: function (params) {
                            return {
                                searchTerm: params.term,
                                "<?= $this->security->get_csrf_token_name() ?>":"<?= $this->security->get_csrf_hash() ?>" 
                            };
                        },
                        processResults: function (response) {
                            console.log(response);
                            return {
                                results: response
                            };
                        },
                        cache: true
                    },
                    placeholder: '<?= lang('please_select_ledger'); ?>'
                });
            }
        });
    });
    
    /* On page load initiate all triggers */
    $('.dc-dropdown').trigger('change');
    $('.ledger-dropdown').val(null).trigger('change');
    $('.dr-item:first').trigger('change');
    $('.cr-item:first').trigger('change');

    /* Calculate date range in javascript */
    startDate = new Date(<?php echo strtotime($this->mAccountSettings->fy_start) * 1000; ?>  + (new Date().getTimezoneOffset() * 60 * 1000));
    endDate = new Date(<?php echo strtotime($this->mAccountSettings->fy_end) * 1000; ?>  + (new Date().getTimezoneOffset() * 60 * 1000));

    /* Setup jQuery datepicker ui */
    $('#EntryDate').datepicker({
        minDate: startDate,
        maxDate: endDate,
        dateFormat: '<?= $this->mDateArray[1]; ?>',
        numberOfMonths: 1,
    });

    
});

</script>
<div class="box">
	<div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-barcode"></i><?= $title; ?>
        </h2>

        <div class="box-icon">
            
        </div>
    </div>
    <div class="box-content">
     	<div class="row">
            <div class="col-xs-12">
          <div class="box">
            
            <!-- /.box-header -->
            <div class="box-body">
                <div class="entry add form">
                <?php
                    if ($this->mSettings->drcr_toby == 'toby') {
                        $dc_options = array(
                            'D' => lang('entries_views_addrow_label_dc_toby_D'),
                            'C' => lang('entries_views_addrow_label_dc_toby_C'),
                        );
                    } else {
                        $dc_options = array(
                            'D' => lang('entries_views_addrow_label_dc_drcr_D'),
                            'C' => lang('entries_views_addrow_label_dc_drcr_C'),
                        );
                    }

                    echo form_open();

                    $prefixNumber = '';
                    $suffixNumber = '';

                    if ( ($entrytype['prefix'] != '') && ($entrytype['suffix'] != '')) {
                        $prefixNumber = "<div class='input-group'><span class='input-group-addon'>" . $entrytype['prefix'] . '</span>';
                        $suffixNumber = "<span class='input-group-addon'>" . $entrytype['suffix'] . '</span></div>';
                    } else if ($entrytype['prefix'] != '') {
                        $prefixNumber = "<div class='input-group'><span class='input-group-addon'>" . $entrytype['prefix'] . '</span>';
                        $suffixNumber = '</div>';
                    } else if ($entrytype['suffix'] != '') {
                        $prefixNumber = "<div class='input-group'>";
                        $suffixNumber = "<span class='input-group-addon'>" . $entrytype['suffix'] . '</span></div>';
                    }
                    
                    echo '<div class="row">';
                    echo '<div class="col-xs-4">';
                    echo '<div class="form-group">';
                    echo form_label(lang('entries_views_add_label_number'), 'number');
                    $data = array(
                        'id' => "number",
                        'type' => "text",
                        'name' => "number",
                        'beforeInput' =>  $prefixNumber,
                        'afterInput' => $suffixNumber,
                        'class' => "form-control",
                        'value' => set_value('number'),
                    );
                    echo form_input($data);
                    echo "</div>";
                    echo "</div>";
                    echo '<div class="col-xs-4">';
                    echo '<div class="form-group">';
                    echo form_label(lang('entries_views_add_label_date'), 'date');
                    $data = array(
                        'id' => "EntryDate",
                        'type' => "text",
                        'name' => "date",
                        'class' => "form-control",
                        'value' => set_value('date'),
                    );
                    echo form_input($data);
                    echo "</div>";
                    echo "</div>";
                    echo '<div class="col-xs-4">';
                    echo '<div class="form-group">';
                    echo form_label(lang('entries_views_add_label_tag'), 'tag_id');
                    ?>
                        <select name="tag_id" class="form-control">
                            <option value="0"><?= lang('entries_views_add_tag_first_option'); ?></option>
                            <?php foreach ($tag_options as $tag): ?>
                                <option value="<?= $tag['id']; ?>"><?= $tag['title']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";

                    echo '<table class="table items table-striped table-bordered table-condensed ">';
                    /* Header */
                    echo '<tr>';
                    if ($this->mSettings->drcr_toby == 'toby') {
                        echo '<th>' . (lang('entries_views_add_items_th_toby')) . '</th>';
                    } else {
                        echo '<th>' . (lang('entries_views_add_items_th_drcr')) . '</th>';
                    }
                    echo '<th>' . (lang('entries_views_add_items_th_ledger')) . '</th>';
                    echo '<th>' . (lang('entries_views_add_items_th_dr_amount')) . ' (' . $this->mAccountSettings->currency_symbol . ')' . '</th>';
                    echo '<th>' . (lang('entries_views_add_items_th_cr_amount')) . ' (' . $this->mAccountSettings->currency_symbol . ')' . '</th>';
                    echo '<th>' . (lang('entries_views_add_items_th_narration')) . '</th>';
                    echo '<th>' . (lang('entries_views_add_items_th_cur_balance')) . ' (' . $this->mAccountSettings->currency_symbol . ')' . '</th>';
                    echo '<th>' . (lang('entries_views_add_items_th_actions')) . '</th>';
                    echo '</tr>';

                    /* Intial rows */
                    foreach ($curEntryitems as $row => $entryitem) {
                        echo '<tr>';
                        if (empty($entryitem['dc'])) {
                            echo '<td><div class="form-group-entryitem">' . form_dropdown('Entryitem[' . $row . '][dc]', $dc_options, "", array('class' => 'dc-dropdown form-control')) . '</div></td>';
                        } else {
                            echo '<td><div class="form-group-entryitem">' . form_dropdown('Entryitem[' . $row . '][dc]', $dc_options, $entryitem['dc'], array('class' => 'dc-dropdown form-control')) . '</div></td>';
                        }
                        if (empty($entryitem['ledger_id'])) {
                        ?>
                            <td>
                                <div class="form-group-entryitem">
                                    <select class="ledger-dropdown form-control" name="<?= 'Entryitem[' . $row . '][ledger_id]'; ?>">
                                        <?php // foreach ($ledger_options as $id => $ledger): ?>
                                            <!-- <option value="<?php // $id; ?>" <?php // ($id < 0) ? 'disabled' : "" ?> ><?php // $ledger; ?></option> -->
                                        <?php // endforeach; ?>
                                    </select>
                                </div>
                            </td>
                            <?php
                        } else {
                            ?>
                            <td>
                                <div class="form-group-entryitem">
                                    <select class="ledger-dropdown form-control" name="<?= 'Entryitem[' . $row . '][ledger_id]'; ?>">
                                        <?php //foreach ($ledger_options as $id => $ledger): ?>
                                            <!-- <option value="<?php // $id; ?>" <?php // ($entryitem['ledger_id'] == $id) ? 'selected' : "" ?> <?php // ($id < 0) ? 'disabled' : "" ?> ><?php // $ledger; ?></option> -->
                                        <?php //endforeach; ?>
                                    </select>
                                </div>
                            </td>
                            <?php
                        }

                        if (empty($entryitem['dr_amount'])) {
                            $data = array(
                                'type' => "text",
                                'name' => 'Entryitem[' . $row . '][dr_amount]',
                                'class' =>  'dr-item form-control',
                            );
                            echo "<td><div class='form-group-entryitem'>";
                            echo form_input($data);
                            echo "</div></td>";
                        } else {
                            $data = array(
                                'value' => $entryitem['dr_amount'],
                                'type' => "text",
                                'name' => 'Entryitem[' . $row . '][dr_amount]',
                                'class' =>  'dr-item form-control',
                            );
                            echo "<td><div class='form-group-entryitem'>";
                            echo form_input($data);
                            echo "</div></td>";
                        }

                        if (empty($entryitem['cr_amount'])) {
                            $data = array(
                                'type' => "text",
                                'name' => 'Entryitem[' . $row . '][cr_amount]',
                                'class' =>  'cr-item form-control',
                            );
                            echo "<td><div class='form-group-entryitem'>";
                            echo form_input($data);
                            echo "</div></td>";

                        } else {
                            $data = array(
                                'value' => $entryitem['cr_amount'],
                                'type' => "text",
                                'name' => 'Entryitem[' . $row . '][cr_amount]',
                                'class' =>  'cr-item form-control',
                            );
                            echo "<td><div class='form-group-entryitem'>";
                            echo form_input($data);
                            echo "</div></td>";
                        }
                        $data = array(
                            'type'  => "text",
                            'name'  => 'Entryitem[' . $row . '][narration]',
                            'class' => 'form-control',
                            'value' => set_value('Entryitem[' . $row . '][narration]'),

                        );
                        echo "<td><div class='form-group-entryitem'>";
                        echo form_input($data);
                        echo "</div></td>";                     
                        echo '<td class="ledger-balance"><div></div></td>';
                        echo '<td>';
                        echo '<span class="deleterow fa fa-trash" escape="false"></span>';
                        echo '</td>';
                        echo '</tr>';
                    }                   
                    /* Total and difference */
                    echo '<tr class="bold-text">' . '<td>' . lang('entries_views_add_items_td_total') . '</td>' . '<td>' . '</td>' . '<td id="dr-total">' . '</td>' . '<td id="cr-total">' . '</td>' . '<td >' . '<span class="recalculate" escape="false"><i class="fa fa-refresh"></i></span>' . '</td>' . '<td>' . '</td>' . '<td>' . '<span class="addrow" escape="false" style="padding-left: 5px;"><i class="fa fa-plus"></i></span>' . '</td>' . '</tr>';
                    echo '<tr class="bold-text">' . '<td>' . lang('entries_views_add_items_td_diff') . '</td>' . '<td>' . '</td>' . '<td id="dr-diff">' . '</td>' . '<td id="cr-diff">' . '</td>' . '<td>' . '</td>' . '<td>' . '</td>' . '<td>' . '</td>' . '</tr>';

                    echo '</table>';

                    echo '<br />';
                    echo '<div class="form-group">';
                    echo form_label(lang('entries_views_add_label_note'), 'note');
                    echo "<textarea name='notes' class='form-control' rows='3'></textarea>";
                    echo "</div>";
                    echo '<div class="form-group">';
                    echo form_submit('submit', lang('entries_views_add_label_submit_btn'), array('class'=>'btn btn-success'));
                    echo '<span class="link-pad"></span>';
                    echo anchor('entries/index', lang('entries_views_add_label_cancel_btn'), array('class' => 'btn btn-default'));
                    echo '<a></span>';
                    echo '</div>';
                    echo form_close();
                ?>
                </div>
            </div>
          </div>
      </div>
     		
     	</div>
     </div>
</div>