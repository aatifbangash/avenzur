<section
  class="page-contents"
  id="thankyou-section"
  style="background: white !important"
>
  <div class="container container-max-width">
    <div class="row">
      <div class="col-md-12">
        <h3 class="fw-semibold" style="color: #9f3a38">Oops!!</h3>
        <div class="my-3 p-3 border rounded shadow">
          <h4 class="m-0 fs-5 ">Something wrong with receiving payment from your account. Please try again!</h4>
          <h4 class="mt-3 f fs-5">Transaction ID: <?= $_POST['Response_TransactionID']; ?></h4>
          <h4 class="m-0 fs-6 mt-2 fw-semibold">
          <span>Error: <?php echo $_POST['Response_StatusDescription'];?></span>
          </h4>
        </div>
      </div>
     
    </div>

  </div>
</section>
