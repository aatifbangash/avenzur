<section
  class="page-contents"
  id="thankyou-section"
  style="background: white !important"
>
  <div class="container container-max-width">
    <div class="row">
      <div class="col-md-6">
        <h2 class="purpColor">Thank you for your Order</h2>
        <h4 class="mt-3 fw-semibold fs-4">Order no: <?= $inv->id; ?></h4>

        <div class="my-3 p-3 border">
          <h4 class="m-0 fs-3">Your order is confirmed</h4>

          <h4 class="m-0 fs-6 mt-2 fw-semibold">
            You will receive a confirmation email with your order number shortly
          </h4>
        </div>

        <div class="mt-3 p-3 px-4 border">
          <h4 class="m-0 fs-4">Customer information</h4>

          <h4 class="m-0 fs-6 mt-2 py-2 fw-semibold">Contact: <?= $customer->phone; ?></h4>
          <h4 class="m-0 fs-6 mt-1 fw-semibold">Email: <?= $customer->email; ?></h4>
          <div class="my-4 pt-3">
            <h4 class="m-0 fs-4">Shipping address</h4>

            <h4 class="m-0 fs-6 mt-2 py-2 fw-semibold">
              <?= $address->first_name. ' '. $address->last_name; ?> <br />
              <?= $address->line1; ?>
            </h4>
          </div>
          <div class="my-4 pt-3">
            <h4 class="m-0 fs-4">Billing address</h4>

            <h4 class="m-0 fs-6 mt-2 py-2 fw-semibold">
            <?= $customer->address. ', '.$customer->city.' '.$customer->country; ?>
            </h4>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="d-flex flex-column justify-content-between h-100">
          <img
            src="<?= base_url('assets/uploads/logos/avenzur-logov2-024.png') ?>"
            alt="logo"
            class="w-auto mt-3"
          />

          <button
            type="button"
            class="btn text-white continueBtn px-5"
            style="width: fit-content"
          >
            Continue
          </button>
        </div>
      </div>
    </div>
  </div>
</section>
