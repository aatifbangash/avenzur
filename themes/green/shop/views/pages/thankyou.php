<section
  class="page-contents"
  id="thankyou-section"
  style="background: white !important"
>
  <div class="container container-max-width">
    <div class="row">
      <div class="col-md-6">
        <h3 class="purpColor fw-semibold">Thank you for your Order</h3>
        <h4 class="mt-3 fw-semibold fs-5">Order no: <?= $inv->id; ?></h4>

        <div class="my-3 p-3 border rounded">
          <h4 class="m-0 fs-5 fw-semibold">Your order is confirmed</h4>

          <h4 class="m-0 fs-6 mt-2 fw-semibold" style="font-weight:normal !important;">
            You will receive a confirmation email with your order number shortly
          </h4>
        </div>

        <div class="mt-3 p-3 px-4 border rounded">
          <h4 class="m-0 fs-5 fw-semibold">Customer information</h4>

          <h4 class="m-0 fs-6 mt-1 py-1 fw-semibold" style="font-weight:normal !important;">Contact: <?= $customer->phone; ?></h4>
          <h4 class="m-0 fs-6 mt-1 fw-semibold" style="font-weight:normal !important;">Email: <?= $customer->email; ?></h4>
          <div class="my-2 pt-3">
            <h4 class="m-0 fs-5 fw-semibold">Shipping address</h4>

            <h4 class="m-0 fs-6 mt-1 py-1 fw-semibold" style="font-weight:normal !important;">
              <?php if($inv->address_id > 0) {?>
              <?= $address->first_name. ' '. $address->last_name; ?> <br />
              <?= $address->line1; } else {?>
                <?= $customer->address. ', '.$customer->city.' '.$customer->country; } ?>
               
            </h4>
          </div>
          <div class="my-2 pt-3">
            <h4 class="m-0 fs-5 fw-semibold">Billing address</h4>

            <h4 class="m-0 fs-6 mt-1 py-1 fw-semibold" style="font-weight:normal !important;">
            <?= $customer->address. ', '.$customer->city.' '.$customer->country; ?>
            </h4>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="d-flex flex-column justify-content-between h-100 ">
          <img
            src="<?= base_url('assets/uploads/logos/avenzur-logov2-024.png') ?>"
            alt="logo"
            class=" mt-3 mx-md-0 mx-auto"
            style="width: fit-content;"
          />

          <div class=" border rounded p-3  mb-4 products-pay">
              <h3 class=" fw-bold pb-2 order-summary-title">Order Summary</h3><hr />
              <div class="">
              <h4 class="m-0 fw-semibold mb-1">Order Details</h4>
              <div class="d-flex justify-content-between">
                  <div>
                      <h4 class="m-0 my-2">Sub total</h4>
                      <h4 class="m-0 my-2">Shipping Fee</h4>
                      <h4 class="m-0 my-2">Discount</h4>
                  </div>
                  <div class="text-end">
                      <h4 class="m-0 my-2" id="sub-total-amt"> <?= $this->sma->formatMoney($inv->total + $inv->total_discount, $selected_currency->symbol); ?>
                                          </h4>
                      <h4 class="text-success m-0 my-2" id="shipping-price"> <?= $this->sma->formatNumber($inv->shipping); ?></span><?= $selected_currency->symbol ?></h4>
                      <h4 class="text-success m-0 my-2" id="discount-amt"> <?= $this->sma->formatNumber($inv->total_discount); ?></span><?= $selected_currency->symbol ?></h4>
                  </div>
              </div>
              
              <hr class="mb-0 mt-2">
              <div class="d-flex justify-content-between">
                  <div>
                      <h4 class="mt-3"><span class="fw-semibold">Total</span> Incl. VAT</h4>
                  </div>
                  <div>
                      <h4 class="mt-3"><span class="fw-semibold"  id="grand-total-price">SAR <?= $this->sma->formatDecimal(($this->sma->formatDecimal($inv->total))); ?></span><?= $selected_currency->symbol ?></span> </h4>
                  </div>
              </div>   
              </div>
          </div>

          <button
            type="button"
            class="btn text-white continueBtn px-5 mb-1 mx-md-0 mx-auto"
            style="width: fit-content"
            onclick="redirectToCheckout('<?= site_url(); ?>')"
          >
            Continue
          </button>
        </div>
      </div>
    </div>
  </div>
</section>
