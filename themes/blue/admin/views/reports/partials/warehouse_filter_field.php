<?php defined('BASEPATH') or exit('No direct script access allowed');
$wh_field = $wh_field ?? 'warehouse_id';
$wh_val = $wh_val ?? ($warehouse_id ?? ($warehouse ?? ($pharmacy_id ?? '')));
if (($wh_val === '' || $wh_val === null)
    && !array_key_exists($wh_field, $_GET)
    && !array_key_exists($wh_field, $_POST)
    && !$this->site->listingShowsAllLocalWarehouses()) {
    $wh_val = $this->site->getDefaultListingWarehouseId();
}
$wh_col = $wh_col ?? 'col-md-3';
$wh_label = $wh_label ?? (!empty($canAccessOverseas) ? 'All Local Warehouses' : lang('all_warehouses'));
if (empty($warehouses)) {
    return;
}
?>
<div class="<?= $wh_col ?>">
    <div class="form-group">
        <?= lang('warehouse', $wh_field); ?>
        <?php
        $opts = ['' => $wh_label];
        foreach ($warehouses as $wh) {
            $opts[$wh->id] = $wh->name;
        }
        echo form_dropdown(
            $wh_field,
            $opts,
            $wh_val,
            'id="' . $wh_field . '" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('warehouse') . '" style="width:100%;"'
        );
        ?>
    </div>
</div>
