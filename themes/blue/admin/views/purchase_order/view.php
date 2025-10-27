<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
  .status-flow {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: nowrap;
    overflow-x: auto;
    padding: 10px 0;
  }

  .status-step {
    position: relative;
    flex: 1;
    min-width: 120px;
  }

  .circle {
    width: 50px;
    height: 50px;
    margin: 0 auto;
    border-radius: 50%;
    background-color: #dee2e6;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    transition: all 0.3s ease;
  }

  .status-line {
    flex: 1;
    height: 3px;
    background-color: #dee2e6;
    margin: 0 5px;
  }

  .step-active {
    background-color: #fd850d;
    color: #fff;
    box-shadow: 0 0 10px rgba(13, 110, 253, 0.3);
  }

  .step-completed {
    background-color: #198754 !important;
    color: #fff !important;
  }

  .step-completed+.status-line {
    background-color: #198754 !important;
  }

  .status-label {
    font-size: 14px;
    color: #495057;
    font-weight: 500;
  }

  /* Make it responsive */
  @media (max-width: 768px) {
    .status-flow {
      flex-wrap: nowrap;
      overflow-x: scroll;
    }

    .status-step {
      min-width: 150px;
    }
  }
</style>
<div class="box">
  <div class="box-header">
    <h2 class="blue"><i class="fa-fw fa fa-file"></i><?= lang('purchase_no') . '. ' . $inv->id; ?></h2>


  </div>
  <div class="box-content">
    <div class="row">
      <div class="col-lg-12">


        <?php
        // Your current status
        echo $currentStatus = $inv->status;

        // Define the status flow order
        $statuses = [
          ['key' => 'created', 'label' => 'PO Created', 'icon' => 'fa-file-text-o'],
          ['key' => 'sent_to_supplier', 'label' => 'Sent to Supplier', 'icon' => 'fa-paper-plane-o'],
          ['key' => 'goods_received', 'label' => 'Goods Received', 'icon' => 'fa-truck'],
          ['key' => 'invoiced', 'label' => 'Invoiced', 'icon' => 'fa-money']
        ];

        ?>

        <div class="container mt-5">
          <div class="status-flow d-flex justify-content-between align-items-center position-relative">

            <?php foreach ($statuses as $status):
              // Determine color
              if ($currentStatus == 'invoiced') {
                $colorClass = 'text-success text-white';
                $iconColor = 'text-white';
              } elseif ($status['key'] == $currentStatus) {
                $colorClass = 'text-warning text-white';
                $iconColor = 'text-white';
              } elseif (array_search($status['key'], array_column($statuses, 'key')) < array_search($currentStatus, array_column($statuses, 'key'))) {
                $colorClass = 'text-success text-white';
                $iconColor = 'text-white';
              } else {
                $colorClass = 'bg-secondary text-white';
                $iconColor = 'text-white';
              }
            ?>
              <div class="status-step text-center">
                <div class="circle <?= $colorClass ?>">
                  <i class="fa <?= $status['icon'] ?> <?= $iconColor ?>"></i>
                </div>
                <div class="status-label mt-2"><?= $status['label'] ?></div>




              </div>
              <!-- Connector line (except last step) -->
              <?php if ($status !== end($statuses)): ?>
                <div class="status-line" style="width:100%; background: <?= ($colorClass == 'bg-secondary text-white') ? '#6c757d' : (($colorClass == 'bg-warning text-white') ? '#ffc107' : '#198754') ?>;"></div>
              <?php endif; ?>
            <?php endforeach; ?>



          </div>
        </div>



        <div class="shadow-sm border-0 rounded-3 mb-4 p-3">
          <div class="d-flex justify-content-between align-items-center flex-wrap">

            <!-- Supplier Info -->
            <div class="d-flex align-items-center mb-2 mb-md-0">
              <div style="float: left; margin-right: 10px;">
                <h6 class="mb-1 fw-bold text-dark">
                  Supplier: <span class="text-primary">Al Noor Traders</span>
                </h6>
                <small class="text-muted d-block">Supplier Code: SUP-1023</small>
              </div>

              <div class="d-flex flex-wrap justify-content-end" style="float: right; margin-left: auto;">


                <a href="<?php echo admin_url('purchase_order/download/'.$inv->id); ?>" class="btn btn-outline-success btn-sm mb-2 text-primary">
                  <i class="fa fa-download me-1"></i> Download PO
                </a>

                <?php if ($currentStatus == "invoiced" || $currentStatus == "goods_received") { ?>
                  <button class="btn btn-outline-secondary btn-sm me-2 mb-2 text-primary">
                    <i class="fa fa-info me-1"></i> Already Sent to Supplier
                  </button>
                  <button class="btn btn-outline-secondary btn-sm me-2 mb-2 text-primary">
                    <i class="fa fa-check me-1"></i> GRN Created
                  </button>
                <?php } else { ?>
                  <button class="btn btn-outline-primary btn-sm me-2 mb-2 text-primary" data-toggle="modal" data-target="#sendToSupplierModal">
                    <i class="fa fa-paper-plane-o me-1"></i> Send to Supplier
                  </button>

                  <!-- <button class="btn btn-outline-warning btn-sm me-2 mb-2 text-primary" data-toggle="modal" data-target="#addGrnModal">
        <i class="fa fa-truck me-1"></i> Add GRN
      </button> -->
                  <a href="<?= admin_url('purchase_order/add_grn/' . $inv->id); ?>" class="btn btn-outline-warning btn-sm me-2 mb-2 text-primary">
                    <i class="fa fa-truck me-1"></i> Add GRN
                  </a>
                <?php  }
                if ($currentStatus == "invoiced") { ?>
                  <button class="btn btn-outline-success btn-sm mb-2 text-primary">
                    <i class="fa fa-money me-1"></i> Invoiced
                  </button>
                <?php } else { ?>
                  <a href="<?php echo admin_url("purchases/add?action=create_invoice&po_number=" . base64_encode($inv->id)); ?>" class="btn btn-outline-success btn-sm mb-2 text-primary">
                    <i class="fa fa-money me-1"></i> Create Invoice
                  </a>
                <?php } ?>
              </div>

            </div>

            <!-- Action Buttons -->

          </div>
        </div>





        <?php if (!empty($inv->return_purchase_ref) && $inv->return_id) {
          echo '<div class="alert alert-info no-print"><p>' . lang('purchase_is_returned') . ': ' . $inv->return_purchase_ref;
          echo ' <a data-target="#myModal2" data-toggle="modal" href="' . admin_url('purchases/modal_view/' . $inv->return_id) . '"><i class="fa fa-external-link no-print"></i></a><br>';
          echo '</p></div>';
        } ?>
        <div class="clearfix"></div>
        <div class="print-only col-xs-12">
          <img src="<?= base_url() . 'assets/uploads/logos/' . $Settings->logo; ?>"
            alt="<?= $Settings->site_name; ?>">
        </div>

        <?php $total_col = 5; ?>
        <div class="table-responsive">
          <table class="table table-bordered table-hover table-striped print-table order-table">
            <thead>
              <tr>
                <th><?= lang('no.'); ?></th>
                <th><?= lang('description'); ?></th>
                <?php if ($Settings->indian_gst) {
                  $total_col += 1;
                ?>
                  <th><?= lang('hsn_sac_code'); ?></th>
                <?php
                } ?>
                <th><?= lang('quantity'); ?></th>
                <?php
                if ($inv->status == 'partial') {
                  $total_col += 1;
                  echo '<th>' . lang('received') . '</th>';
                }
                ?>
                <th style="padding-right:20px;"><?= lang('unit_cost'); ?></th>
                <?php
                if ($Settings->tax1 && $inv->product_tax > 0) {
                  $total_col += 1;
                  echo '<th style="padding-right:20px; text-align:center; vertical-align:middle;">' . lang('tax') . '</th>';
                }
                if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                  $total_col += 1;
                  echo '<th style="padding-right:20px; text-align:center; vertical-align:middle;">' . lang('discount1') . '</th>';
                }
                if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                  $total_col += 1;
                  echo '<th style="padding-right:20px; text-align:center; vertical-align:middle;">' . lang('discount2') . '</th>';
                }
                ?>
                <th style="padding-right:20px;"><?= lang('subtotal'); ?></th>
              </tr>
            </thead>
            <tbody>
              <?php $r = 1;
              //  echo '<pre>'; print_r($rows); exit; 
              foreach ($rows as $row):
              ?>
                <tr>
                  <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                  <td style="vertical-align:middle;">
                    <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                    <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                    <?= $row->supplier_part_no ? '<br>' . lang('supplier_part_no') . ': ' . $row->supplier_part_no : ''; ?>
                    <?= $row->details ? '<br>' . $row->details : ''; ?>
                    <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' . lang('expiry') . ': ' . $this->sma->hrsd($row->expiry) : ''; ?>
                  </td>
                  <?php if ($Settings->indian_gst) {
                  ?>
                    <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $row->hsn_code ?: ''; ?></td>
                  <?php
                  } ?>
                  <td style="width: 120px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->quantity); ?></td>
                  <?php
                  if ($inv->status == 'partial') {
                    echo '<td style="text-align:center;vertical-align:middle;width:120px;">' . $this->sma->formatQuantity($row->quantity_received)  . '</td>';
                  }
                  ?>
                  <td style="text-align:right; width:120px; padding-right:10px;">
                    <?= $row->unit_cost != $row->real_unit_cost && $row->item_discount > 0 ? '<del>' . $this->sma->formatMoney($row->real_unit_cost) . '</del>' : ''; ?>
                    <?= $this->sma->formatMoney($row->unit_cost); ?>
                  </td>
                  <?php
                  if ($Settings->tax1 && $inv->product_tax > 0) {
                    echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small> ' : '') . $this->sma->formatMoney($row->item_tax) . '</td>';
                  }
                  if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                    echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_discount) . '</td>';
                  }

                  if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                    $unit_cost = $row->unit_cost;
                    $pr_discount      = $this->site->calculateDiscount($row->discount1 . '%', $row->unit_cost);
                    $amount_after_dis1 = $unit_cost - $pr_discount;
                    $pr_discount2      = $this->site->calculateDiscount($row->discount2 . '%', $amount_after_dis1);
                    $pr_item_discount2 = $this->sma->formatDecimal($pr_discount2 * $row->quantity);
                    $row->discount2 = $this->sma->formatNumber($row->discount2, null);
                    echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->discount2 != 0 ? '<small>(' . $row->discount2 . ')</small>' : '') . ' ' . $this->sma->formatMoney($pr_item_discount2) . '</td>';
                  }

                  ?>
                  <td style="text-align:right; width:120px; padding-right:10px;"><?= $this->sma->formatMoney($row->subtotal); ?></td>
                </tr>
                <?php
                $r++;
              endforeach;
              if ($return_rows) {
                echo '<tr class="warning"><td colspan="100%" class="no-border"><strong>' . lang('returned_items') . '</strong></td></tr>';
                foreach ($return_rows as $row):
                ?>
                  <tr class="warning">
                    <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                    <td style="vertical-align:middle;">
                      <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                      <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                      <?= $row->supplier_part_no ? '<br>' . lang('supplier_part_no') . ': ' . $row->supplier_part_no : ''; ?>
                      <?= $row->details ? '<br>' . $row->details : ''; ?>
                      <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' . lang('expiry') . ': ' . $this->sma->hrsd($row->expiry) : ''; ?>
                    </td>
                    <?php if ($Settings->indian_gst) {
                    ?>
                      <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $row->hsn_code ?: ''; ?></td>
                    <?php
                    } ?>
                    <td style="width: 120px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->unit_quantity) . ' ' . $row->product_unit_code; ?></td>
                    <?php
                    if ($inv->status == 'partial') {
                      echo '<td style="text-align:center;vertical-align:middle;width:120px;">' . $this->sma->formatQuantity($row->quantity_received) . ' ' . $row->product_unit_code . '</td>';
                    } ?>
                    <td style="text-align:right; width:120px; padding-right:10px;"><?= $this->sma->formatMoney($row->unit_cost); ?></td>
                    <?php
                    if ($Settings->tax1 && $inv->product_tax > 0) {
                      echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small> ' : '') . $this->sma->formatMoney($row->item_tax) . '</td>';
                    }
                    if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                      echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_discount) . '</td>';
                    }
                    if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                      $unit_cost = $row->unit_cost;
                      $pr_discount      = $this->site->calculateDiscount($row->discount1 . '%', $row->unit_cost);
                      $amount_after_dis1 = $unit_cost - $pr_discount;
                      $pr_discount2      = $this->site->calculateDiscount($row->discount2 . '%', $amount_after_dis1);
                      $pr_item_discount2 = $this->sma->formatDecimal($pr_discount2 * $row->quantity);
                      $row->discount2 = $this->sma->formatNumber($row->discount2, null);
                      echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->discount2 != 0 ? '<small>(' . $row->discount2 . ')</small>' : '') . ' ' . $this->sma->formatMoney($pr_item_discount2) . '</td>';
                    } ?>
                    <td style="text-align:right; width:120px; padding-right:10px;"><?= $this->sma->formatMoney($row->subtotal); ?></td>
                  </tr>
              <?php
                  $r++;
                endforeach;
              }
              ?>
            </tbody>
            <tfoot>
              <?php
              $col = $Settings->indian_gst ? 6 : 5;
              if ($inv->status == 'partial') {
                $col++;
              }
              if ($Settings->product_discount && $inv->product_discount != 0) {
                $col++;
              }
              if ($Settings->tax1 && $inv->product_tax > 0) {
                $col++;
              }
              if ($Settings->tax1 && $inv->product_tax > 0 && $inv->product_discount == 0) {
                $col = $col - 1;
              }
              if ($Settings->tax1 && $inv->product_tax == 0  && $inv->product_discount == 0) {
                $col = $col - 1;
              }

              if (($Settings->product_discount && $inv->product_discount != 0) && ($Settings->tax1 && $inv->product_tax > 0)) {
                $tcol = $col - 2;
              } elseif ($Settings->product_discount && $inv->product_discount != 0) {
                $tcol = $col - 1;
              } elseif ($Settings->tax1 && $inv->product_tax > 0) {
                $tcol = $col - 1;
              } elseif ($Settings->tax1 && $inv->product_tax == 0  && $inv->product_discount == 0) {
                $tcol = $col - 1;
              } else {
                $tcol = $col;
              }
              $colspan = $total_col - 1;
              ?>
              <?php if ($inv->grand_total != $inv->total) {
              ?>
                <tr>
                  <td colspan="<?= $tcol; ?>"
                    style="text-align:right; padding-right:10px;"><?= lang('total'); ?>
                    (<?= $default_currency->code; ?>)
                  </td>
                  <?php
                  if ($Settings->tax1 && $inv->product_tax > 0) {
                    echo '<td style="text-align:right;">' . $this->sma->formatMoney($return_purchase ? ($inv->product_tax + $return_purchase->product_tax) : $inv->product_tax) . '</td>';
                  }
                  if ($Settings->product_discount && $inv->product_discount != 0) {
                    echo '<td style="text-align:right;">' . $this->sma->formatMoney($return_purchase ? ($inv->product_discount + $return_purchase->product_discount) : $inv->product_discount) . '</td>';
                  } ?>
                  <td style="text-align:right; padding-right:10px;"><?= $this->sma->formatMoney($return_purchase ? (($inv->total + $inv->product_tax) + ($return_purchase->total + $return_purchase->product_tax)) : ($inv->total + $inv->product_tax)); ?></td>
                </tr>
              <?php
              } ?>
              <?php
              if ($return_purchase) {
                echo '<tr><td colspan="' . $colspan . '" style="text-align:right; padding-right:10px;;">' . lang('return_total') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($return_purchase->grand_total) . '</td></tr>';
              }
              if ($inv->surcharge != 0) {
                echo '<tr><td colspan="' . $colspan . '" style="text-align:right; padding-right:10px;;">' . lang('return_surcharge') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($inv->surcharge) . '</td></tr>';
              }
              ?>
              <?php if ($Settings->indian_gst) {
                if ($inv->cgst > 0) {
                  $cgst = $return_purchase ? $inv->cgst + $return_purchase->cgst : $inv->cgst;
                  echo '<tr><td colspan="' . $colspan . '" class="text-right">' . lang('cgst') . ' (' . $default_currency->code . ')</td><td class="text-right">' . ($Settings->format_gst ? $this->sma->formatMoney($cgst) : $cgst) . '</td></tr>';
                }
                if ($inv->sgst > 0) {
                  $sgst = $return_purchase ? $inv->sgst + $return_purchase->sgst : $inv->sgst;
                  echo '<tr><td colspan="' . $colspan . '" class="text-right">' . lang('sgst') . ' (' . $default_currency->code . ')</td><td class="text-right">' . ($Settings->format_gst ? $this->sma->formatMoney($sgst) : $sgst) . '</td></tr>';
                }
                if ($inv->igst > 0) {
                  $igst = $return_purchase ? $inv->igst + $return_purchase->igst : $inv->igst;
                  echo '<tr><td colspan="' . $colspan . '" class="text-right">' . lang('igst') . ' (' . $default_currency->code . ')</td><td class="text-right">' . ($Settings->format_gst ? $this->sma->formatMoney($igst) : $igst) . '</td></tr>';
                }
              } ?>
              <?php if ($inv->order_discount != 0) {
                echo '<tr><td colspan="' . $colspan . '" style="text-align:right; padding-right:10px;;">' . lang('order_discount') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . ($inv->order_discount_id ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->sma->formatMoney($return_purchase ? ($inv->order_discount + $return_purchase->order_discount) : $inv->order_discount) . '</td></tr>';
              }
              ?>
              <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                echo '<tr><td colspan="' . $colspan . '" style="text-align:right; padding-right:10px;">' . lang('order_tax') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($return_purchase ? ($inv->order_tax + $return_purchase->order_tax) : $inv->order_tax) . '</td></tr>';
              }
              ?>
              <?php if ($inv->shipping != 0) {
                echo '<tr><td colspan="' . $colspan . '" style="text-align:right; padding-right:10px;;">' . lang('shipping') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($inv->shipping) . '</td></tr>';
              }
              ?>
              <tr>
                <td colspan="<?= $colspan; ?>"
                  style="text-align:right; font-weight:bold;"><?= lang('total_amount'); ?>
                  (<?= $default_currency->code; ?>)
                </td>
                <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->sma->formatMoney($return_purchase ? ($inv->grand_total + $return_purchase->grand_total) : $inv->grand_total); ?></td>
              </tr>
              <tr>
                <td colspan="<?= $colspan; ?>"
                  style="text-align:right; font-weight:bold;"><?= lang('paid'); ?>
                  (<?= $default_currency->code; ?>)
                </td>
                <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney($return_purchase ? ($inv->paid + $return_purchase->paid) : $inv->paid); ?></td>
              </tr>
              <tr>
                <td colspan="<?= $colspan; ?>"
                  style="text-align:right; font-weight:bold;"><?= lang('balance'); ?>
                  (<?= $default_currency->code; ?>)
                </td>
                <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney(($return_purchase ? ($inv->grand_total + $return_purchase->grand_total) : $inv->grand_total) - ($return_purchase ? ($inv->paid + $return_purchase->paid) : $inv->paid)); ?></td>
              </tr>

            </tfoot>
          </table>

        </div>

        <div class="row">
          <div class="col-xs-7">
            <?= $Settings->invoice_view > 0 ? $this->gst->summary($rows, $return_rows, ($return_purchase ? $inv->product_tax + $return_purchase->product_tax : $inv->product_tax), true) : ''; ?>
            <?php if ($inv->note || $inv->note != '') {
            ?>
              <div class="well well-sm">
                <p class="bold"><?= lang('note'); ?>:</p>

                <div><?= $this->sma->decode_html($inv->note); ?></div>
              </div>
            <?php
            } ?>
          </div>

          <div class="col-xs-4 col-xs-offset-1">
            <div class="well well-sm">
              <p><?= lang('created_by'); ?>
                : <?= $created_by->first_name . ' ' . $created_by->last_name; ?> </p>

              <p><?= lang('date'); ?>: <?= $this->sma->hrld($inv->date); ?></p>
              <?php if ($inv->updated_by) {
              ?>
                <p><?= lang('updated_by'); ?>
                  : <?= $updated_by->first_name . ' ' . $updated_by->last_name; ?></p>
                <p><?= lang('update_at'); ?>: <?= $this->sma->hrld($inv->updated_at); ?></p>
              <?php
              } ?>
            </div>

          </div>
        </div>

      </div>
    </div>

    <?php if (!empty($payments)) {
    ?>
      <div class="row">
        <div class="col-xs-12">
          <div class="table-responsive">
            <table class="table table-bordered table-striped table-condensed">
              <thead>
                <tr>
                  <th><?= lang('date') ?></th>
                  <th><?= lang('payment_reference') ?></th>
                  <th><?= lang('paid_by') ?></th>
                  <th><?= lang('amount') ?></th>
                  <th><?= lang('created_by') ?></th>
                  <th><?= lang('type') ?></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($payments as $payment) {
                ?>
                  <tr>
                    <td><?= $this->sma->hrld($payment->date) ?></td>
                    <td><?= $payment->reference_no; ?></td>
                    <td><?= $payment->paid_by; ?></td>
                    <td><?= $payment->amount; ?></td>
                    <td><?= $payment->first_name . ' ' . $payment->last_name; ?></td>
                    <td><?= $payment->type; ?></td>
                  </tr>
                <?php
                } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php
    } ?>

    <?php include(dirname(__FILE__) . '/../partials/attachments.php'); ?>
    <?php if (!$Supplier || !$Customer) {
    ?>
      <div class="buttons">
        <div class="btn-group btn-group-justified">
          <div class="btn-group">
            <a href="<?= admin_url('purchases/payments/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal" class="tip btn btn-primary tip" title="<?= lang('view_payments') ?>">
              <i class="fa fa-money"></i> <span class="hidden-sm hidden-xs"><?= lang('view_payments') ?></span>
            </a>
          </div>
          <div class="btn-group">
            <a href="<?= admin_url('purchases/add_payment/' . $inv->id) ?>" class="tip btn btn-primary tip" title="<?= lang('add_payment') ?>" data-target="#myModal" data-toggle="modal">
              <i class="fa fa-money"></i> <span class="hidden-sm hidden-xs"><?= lang('add_payment') ?></span>
            </a>
          </div>
          <div class="btn-group">
            <a href="<?= admin_url('purchases/email/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal" class="tip btn btn-primary tip" title="<?= lang('email') ?>">
              <i class="fa fa-envelope-o"></i> <span class="hidden-sm hidden-xs"><?= lang('email') ?></span>
            </a>
          </div>
          <div class="btn-group">
            <a href="<?= admin_url('purchases/pdf/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('download_pdf') ?>">
              <i class="fa fa-download"></i> <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
            </a>
          </div>
          <div class="btn-group">
            <a href="<?= admin_url('purchases/edit/' . $inv->id) ?>" class="tip btn btn-warning tip" title="<?= lang('edit') ?>">
              <i class="fa fa-edit"></i> <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
            </a>
          </div>
          <div class="btn-group">
            <a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line('delete_purchase') ?></b>"
              data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= admin_url('purchases/delete/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
              data-html="true" data-placement="top">
              <i class="fa fa-trash-o"></i> <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
            </a>
          </div>
        </div>
      </div>
    <?php
    } ?>
  </div>
</div>


<!-- Modal -->
<div class="modal fade" id="sendToSupplierModal" tabindex="-1" aria-labelledby="sendToSupplierModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header bg-primary text-white rounded-top-4">
        <h5 class="modal-title" id="sendToSupplierModalLabel">
          <i class="fa fa-paper-plane-o me-2"></i> Send to Supplier
        </h5>
      </div>

      <form id="sendToSupplierForm">
        <div class="modal-body">
          <div class="mb-3">
            <label for="sendingNotes" class="form-label fw-semibold text-secondary">Sending Notes</label>
            <textarea class="form-control rounded-3" id="sendingNotes" name="sendingNotes" rows="4" placeholder="Write notes or instructions for the supplier..." required></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">
            Cancel
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-check me-1"></i> Send
          </button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- Button -->
<!-- <button class="btn btn-outline-warning btn-sm me-2 mb-2" data-bs-toggle="modal" data-bs-target="#addGrnModal">
    <i class="fa fa-truck me-1"></i> Add GRN
</button> -->

<!-- Modal -->
<div class="modal fade" id="addGrnModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header bg-warning text-white rounded-top-4">
        <h5 class="modal-title">
          <i class="fa fa-truck me-2"></i> Add Goods Received Note
        </h5>

      </div>

      <form id="addGrnForm" enctype="multipart/form-data">
        <div class="modal-body">

          <div class="mb-3">
            <label class="form-label fw-semibold">Received Date</label>
            <input type="date" class="form-control" name="received_date" value="<?= date('Y-m-d') ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Received By</label>
            <input type="text" class="form-control" name="received_by" value="<?= $this->session->userdata('username') ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">General Notes</label>
            <textarea class="form-control" name="grn_notes" rows="3" placeholder="Any additional notes..."></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Total Items</label>
            <input class="form-control" name="total_items" value="" />
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Total Quantity</label>
            <input class="form-control" name="total_quantity" value="" />
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Attachment (PDF/Image)</label>
            <input type="file" class="form-control" name="grn_attachment" accept=".pdf, .jpg, .png, .jpeg">
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">
            <i class="fa fa-check me-1"></i> Save GRN
          </button>
        </div>
      </form>
    </div>
  </div>
</div>


<script>
  $.ajaxSetup({
    data: {
      '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
    }
  });

  $('#sendToSupplierForm').on('submit', function(e) {
    e.preventDefault();

    const notes = $('#sendingNotes').val();

    $.ajax({
      url: '<?= admin_url("purchase_order/send_to_supplier") ?>',
      type: 'POST',
      data: {
        purchase_order_id: <?= $inv->id; ?>,
        notes: notes
      },
      success: function(response) {
        $('#sendToSupplierModal').modal('hide');
        alert('Purchase Order sent successfully!');
        location.reload();
      },
      error: function() {
        alert('Something went wrong. Please try again.');
      }
    });
  });


  $('#addGrnForm').on('submit', function(e) {
    e.preventDefault();

    var formData = new FormData(this); // Handles files automatically
    formData.append('purchase_order_id', <?= $inv->id ?>);
    formData.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');


    $.ajax({
      url: '<?= admin_url("purchase_order/add_grn") ?>',
      type: 'POST',
      data: formData,
      contentType: false, // required for FormData
      processData: false, // required for FormData
      success: function(response) {
        $('#addGrnModal').modal('hide');
        alert('GRN saved successfully!');
        location.reload(); // Refresh to show updated status
      },
      error: function(xhr, status, error) {
        alert('Error: ' + xhr.responseText);
      }
    });
  });
</script>