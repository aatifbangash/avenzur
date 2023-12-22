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

        <div class="my-3 p-3 border rounded shadow">
          <h4 class="m-0 fs-5 fw-semibold">Your order is confirmed</h4>

          <h4 class="m-0 fs-6 mt-2 fw-semibold">
            You will receive a confirmation email with your order number shortly
          </h4>
        </div>

        <div class="mt-3 p-3 px-4 border rounded shadow">
          <h4 class="m-0 fs-5 fw-semibold">Customer information</h4>

          <h4 class="m-0 fs-6 mt-1 py-1 fw-semibold">Contact: <?= $customer->phone; ?></h4>
          <h4 class="m-0 fs-6 mt-1 fw-semibold">Email: <?= $customer->email; ?></h4>
          <div class="my-2 pt-3">
            <h4 class="m-0 fs-5 fw-semibold">Shipping address</h4>

            <h4 class="m-0 fs-6 mt-1 py-1 fw-semibold">
              <?php if($inv->address_id > 0) {?>
              <?= $address->first_name. ' '. $address->last_name; ?> <br />
              <?= $address->line1; } else {?>
                <?= $customer->address. ', '.$customer->city.' '.$customer->country; } ?>
               
            </h4>
          </div>
          <div class="my-2 pt-3">
            <h4 class="m-0 fs-5 fw-semibold">Billing address</h4>

            <h4 class="m-0 fs-6 mt-1 py-1 fw-semibold">
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

          <button
            type="button"
            class="btn text-white continueBtn px-5 mb-1 mx-md-0 mx-auto"
            style="width: fit-content"
          >
            Continue
          </button>
        </div>
      </div>
    </div>
  </div>
</section>
