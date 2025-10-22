
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        /* Reset & Base */
        * 
        /* Container */
        /* Header */
        

        /* Table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #eee; color: #000; font-weight: bold; }
        tr:hover { background: #f1f1f1; }

        /* Tabs */
        .tabs { display: flex; border-bottom: 2px solid #ddd; margin-bottom: 20px; }
        .tab { padding: 10px 20px; cursor: pointer; border-top-left-radius: 5px; border-top-right-radius: 5px; background: #e9ecef; margin-right: 5px; transition: background 0.3s; }
        .tab.active { background: #fff; border-bottom: 2px solid #fff; font-weight: 500; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* Forms */
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; }

        /* Audit logs */
        .logs { max-height: 200px; overflow-y: auto; background: #f9f9f9; padding: 15px; border-radius: 5px; border: 1px solid #ddd; font-size: 14px; }
        .logs p { margin-bottom: 8px; }

        /* Responsive */
        @media(max-width: 768px) {
            .header { flex-direction: column; align-items: flex-start; }
            .header button { margin-top: 10px; }
            table th, table td { font-size: 14px; }
        }
    </style>

<!-- <div class="container"> -->
    <!-- Header -->
    <!-- <div class="header">
        <h1 style="font-size: larger">Purchase Request Details</h1>
        <a href="<?= admin_url('purchase_order/add?action=create_po&id='.base64_encode($id)) ?>" id="" class="btn btn-primary" style="float:right; margin-top:-35px">Create PO</a>
    </div> -->

    <div class="header" style="position: relative; margin-bottom: 10px;">
    <div style="font-size: larger; display: inline-block;">Purchase Request Details</div>
    
    <!-- Status Labels (centered) -->
    <div style="position: absolute; left: 50%; transform: translateX(-50%); top: 12px;">
        <span class="label label-success" style="margin:0 5px;"><?= $requisition->status; ?></span>
        
    </div>
    
    <!-- Create PO button -->
    <a href="<?= admin_url('purchase_order/add?action=create_po&id='.base64_encode($id)) ?>" 
       class="btn btn-primary" 
       style="float:right; margin-top:0;">Create PO</a>
</div>


    <!-- Tabs -->
    <div class="tabs">
        <div class="tab active" data-tab="info">PR Info</div>
        <div class="tab" data-tab="items">Items</div>
        <div class="tab" data-tab="logs">Audit Logs</div>
         <div class="tab" data-tab="send-supplier">Send to Supplier</div>
        <div class="tab" data-tab="suppliers">Suppliers Sent</div>
       
    </div>

    <!-- Tab Content -->
    <div class="tab-content active" id="info">
        <table>
            <tr><th>PR Number</th><td><?= $requisition->pr_number ?? 'N/A'; ?></td></tr>
            <tr><th>Date</th><td><?= $requisition->created_at ?? 'N/A'; ?></td></tr>
            <tr><th>Status</th><td><?= $requisition->status ?? 'Pending'; ?></td></tr>
        </table>
    </div>

    <div class="tab-content" id="items">
        <table>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
            
            </tr>
            <?php 
            //print_r($items);exit;
            foreach($items as $key => $item): ?>
            <tr>
                <td><?= $item->product_name; ?></td>
                <td><?= $item->quantity; ?></td>
         
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="tab-content" id="logs">
        <div class="">
<style>
.timeline {
  position: relative;
  padding: 20px 0;
  list-style: none;
}
.timeline:before {
  content: "";
  position: absolute;
  top: 0;
  bottom: 0;
  left: 40px;
  width: 3px;
  background: #e6e6e6;
}

.timeline-item {
  position: relative;
  margin-left: 80px;
  margin-bottom: 25px;
  background: #f9f9f9;
  border-radius: 6px;
  padding: 15px 20px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.timeline-badge {
  position: absolute;
  left: 15px;
  top: 15px;
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: #fff;
  border: 3px solid #dcdcdc;
  text-align: center;
  line-height: 44px;
  font-size: 18px;
}

.timeline-time {
  float: right;
  color: #999;
  font-size: 12px;
}

.timeline-title {
  margin-top: 0;
  font-size: 15px;
  font-weight: 600;
}

.timeline-meta {
  font-size: 13px;
  color: #555;
}

.timeline-note {
  font-size: 13px;
  color: #666;
  margin-top: 5px;
}

/* Status Labels */
.label-status {
  display: inline-block;
  padding: 3px 6px;
  border-radius: 3px;
  font-size: 11px;
  vertical-align: middle;
}
.label-pending  { background: #f0ad4e; color: #fff; }
.label-sent     { background: #337ab7; color: #fff; }
.label-received { background: #5cb85c; color: #fff; }
.label-invoice  { background: #5bc0de; color: #fff; }

/* Responsive */
@media (max-width: 767px) {
  .timeline:before { left: 25px; }
  .timeline-item { margin-left: 60px; }
  .timeline-badge { left: -2px; top: 12px; width: 40px; height: 40px; line-height: 34px; font-size: 16px; }
}
</style>

<ul class="timeline">

  <li class="timeline-item">
    <div class="timeline-time">2025-10-19 09:20</div>
    <h4 class="timeline-title">
      Purchase Request Created
      <span class="label-status label-pending">Pending</span>
    </h4>
    <div class="timeline-meta">Supplier: <strong>Medicare Supplies</strong> | PR #: PR-00098</div>
    <div class="timeline-note">Request generated by <strong>Ali</strong> for 200 units of Paracetamol 500 mg.</div>
  </li>

  <li class="timeline-item">
  
    <div class="timeline-time">2025-10-19 11:40</div>
    <h4 class="timeline-title">
      Goods Dispatched
      <span class="label-status label-sent">Sent</span>
    </h4>
    <div class="timeline-meta">Supplier: <strong>Medicare Supplies</strong> | AWB: <strong>AWB-2025-7742</strong></div>
    <div class="timeline-note">Partial shipment (120 units) dispatched via courier service.</div>
  </li>

  <li class="timeline-item">

    <div class="timeline-time">2025-10-19 14:10</div>
    <h4 class="timeline-title">
      Goods Received
      <span class="label-status label-received">Received</span>
    </h4>
    <div class="timeline-meta">Warehouse: <strong>Jeddah Central</strong> | GRN: <strong>GRN-3345</strong></div>
    <div class="timeline-note">Received by <strong>Ahmed</strong>. All items passed quality check.</div>
  </li>

  <li class="timeline-item">
  
    <div class="timeline-time">2025-10-19 15:05</div>
    <h4 class="timeline-title">
      Invoice Generated
      <span class="label-status label-invoice">Invoiced</span>
    </h4>
    <div class="timeline-meta">Invoice #: <strong>INV-2025-412</strong> | Amount: <strong>SAR 9,750</strong></div>
    <div class="timeline-note">Invoice created with 30-day payment term.</div>
  </li>

</ul>


        </div>
    </div>

    <div class="tab-content" id="suppliers">
       <style>
/* ===== Supplier List (consistent with timeline theme) ===== */
.supplier-list {
  background: #f9f9f9;
  border-radius: 6px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  margin-top: 15px;
}

.supplier-list table {
  width: 100%;
  border-collapse: collapse;
}

.supplier-list th, 
.supplier-list td {
  padding: 10px 12px;
  border-bottom: 1px solid #e6e6e6;
  vertical-align: middle;
}

.supplier-list th {
  background: #f2f2f2;
  font-weight: 600;
  font-size: 13px;
  color: #555;
}

.supplier-list td {
  font-size: 13px;
  color: #444;
}

.supplier-name {
  font-weight: 600;
  color: #333;
}

.file-list i {
  margin-right: 4px;
  color: #777;
}

.file-list a {
  margin-right: 10px;
  color: #337ab7;
  text-decoration: none;
}

.file-list a:hover {
  text-decoration: underline;
}

.btn-upload {
  padding: 5px 10px;
  font-size: 12px;
  border-radius: 4px;
}

.no-files {
  color: #aaa;
  font-style: italic;
  font-size: 12px;
}

/* small responsive tweak */
@media (max-width: 767px) {
  .supplier-list table, 
  .supplier-list thead, 
  .supplier-list tbody, 
  .supplier-list th, 
  .supplier-list td, 
  .supplier-list tr {
    display: block;
  }
  .supplier-list tr {
    border: 1px solid #e6e6e6;
    margin-bottom: 10px;
    border-radius: 5px;
    background: #fff;
    padding: 10px;
  }
  .supplier-list td {
    border: none;
    padding: 6px 0;
  }
  .supplier-list td:before {
    content: attr(data-label);
    font-weight: 600;
    display: block;
    color: #777;
  }
}
</style>


 <!-- ========== SUPPLIER LIST (same as before) ========== -->
<div class="supplier-list">

  <table class="table table-hover">
    <thead>
      <tr>
        <th style="text-align: left">Supplier Name</th>
        <th style="text-align: left">Contact</th>
        <th style="text-align: left">PR Doc</th>
        <th style="text-align: left">Supplier Docs
        <th style="width:120px;">Action</th>
      </tr>
    </thead>
    <tbody>
    <?php if(!empty( $requisition_suppliers)) {
         foreach($requisition_suppliers as $supplier) { 
            
            //print_r($supplier);?>
        
      <tr>
        <td><span class="supplier-name"><?= $supplier->supplier_name; ?></span></td>
        <td><?= $supplier->supplier_email;?> / <?= $supplier->supplier_phone;?></td>
        <td class="file-list">
          <a target="_blank" href="<?= base_url().$supplier->pdf_path; ?>"><i class="fa fa-file-pdf-o fa-2x"></i> </a>
        </td>

         <td class="file-list">

         <?php if (!empty($supplier->responses)): ?>
            <?php foreach($supplier->responses as $res): ?>
              <a href="<?= base_url().'assets/uploads/pr_pdfs/'.$res->pdf_path; ?>" target="_blank">
                <i class="fa fa-file-pdf-o text-danger"></i> <?= $res->doc_name; ?>
              </a><br>
            <?php endforeach; ?>
          <?php else: ?>
            <span class="text-muted">No documents</span>
          <?php endif; ?>
           </td>
        <td>
          <button class="btn btn-md btn-primary btn-upload"
                  data-pr-id="<?= $supplier->pr_id; ?>"
                  data-supplier-id="<?= $supplier->supplier_id; ?>"
                  data-supplier-name="<?= $supplier->supplier_name; ?>">
            <i class="fa fa-upload"></i> Upload
          </button>
        </td>
      </tr>
        <?php } } else { ?>

      <tr>
        <td>No supplier list found. Please send PR to supplier.</td>
        
      </tr>
        <?php } ?>
    </tbody>
  </table>


<!-- ========== UPLOAD MODAL ========== -->
<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="uploadForm" enctype="multipart/form-data">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title"><i class="fa fa-upload"></i> Upload Document</h4>
        </div>
        <div class="modal-body">
          <input type="hidden" id="supplierId" name="supplier_id">
          <input type="hidden" id="prId" name="pr_id">
          <div class="form-group">
            <label>Supplier Name</label>
            <input type="text" id="supplierName" name="supplier_name" class="form-control" readonly>
          </div>
          <div class="form-group">
            <label>Document Name</label>
            <input type="text" name="doc_name" id="docName" class="form-control" placeholder="e.g. Contract, License, Quotation" required>
          </div>
          <div class="form-group">
            <label>Select File</label>
            <input type="file" name="file" id="fileInput" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Upload</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ========== JAVASCRIPT (AJAX Upload) ========== -->
<script>
$(document).ready(function() {

  // Open modal and fill supplier info
  $('.btn-upload').on('click', function() {
    var supplierId = $(this).data('supplier-id');
     var prId = $(this).data('pr-id');
    var supplierName = $(this).data('supplier-name');
    $('#supplierId').val(supplierId);
     $('#prId').val(prId);
    $('#supplierName').val(supplierName);
    $('#docName').val('');
    $('#fileInput').val('');
    $('#uploadModal').modal('show');
  });

  // Handle AJAX upload
  $('#uploadForm').on('submit', function(e) {
    e.preventDefault();


    var formData = new FormData(this);

    formData.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');

    $.ajax({
      url: '<?= admin_url("purchase_requisition/upload_supplier_docs") ?>',  // <-- Change to your backend endpoint
      type: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      beforeSend: function() {
        $('#uploadForm button[type=submit]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Uploading...');
      },
      success: function(response) {
        $('#uploadForm button[type=submit]').prop('disabled', false).html('<i class="fa fa-check"></i> Upload');
        $('#uploadModal').modal('hide');

        // Optional: show success message
        alert('File uploaded successfully!');
        location.reload();
        // You can also refresh the supplier list dynamically here
      },
      error: function() {
        $('#uploadForm button[type=submit]').prop('disabled', false).html('<i class="fa fa-check"></i> Upload');
        alert('Error uploading file. Please try again.');
      }
    });
  });

});
</script>

</div>

    </div>

      <div class="tab-content" id="send-supplier">
         <?= form_open('admin/purchase_requisition/send_to_supplier', ['class' => 'needs-validation', 'novalidate' => true]); ?>

         <input type="hidden" name="pr_id" value="<?= $id; ?>">
            <div class="row mb-3">
                 <div class="col-md-4">
                    <?= form_label('Supplier', 'supplier_id'); 
                        $supplier_options = [];
                        foreach ($suppliers as $wh) {
                            $supplier_options[$wh->id] = $wh->name;
                        }
                    ?>
                    <?= form_multiselect(
                        'supplier_id[]',           // name must be an array for multiple selection
                        $supplier_options,         // options
                        set_value('supplier_id[]'),// selected value(s)
                        ['class' => 'form-control select2', 'id' => 'supplier_id', 'multiple' => 'multiple']
                    ); ?>
                </div>

               
            </div>

            <div class="row mb-3">

            <div class="col-md-4">
                    <?= form_label('Subject', 'subject'); ?>
                    <input type="text" name="subject" class="form-control" 
       value=""  />
                </div>
    </div>

            <div class="mb-3">
                <?= form_label('Body', 'remarks'); ?>
                <?= form_textarea([
                    'name' => 'remarks',
                    'id' => 'remarks',
                    'rows' => 3,
                    'class' => 'form-control',
                    'placeholder' => 'Enter any additional details or notes',
                    'value' => set_value('remarks')
                ]); ?>
            </div>

            <hr>

             <a href="<?= admin_url('purchase_requisition/download_pdf/' . $id); ?>" 
                class="btn btn-outline-success">
                    <i class="fa fa-download"></i> Download PDF
            </a>
         

            <div class="text-end">
                <button type="submit" class="btn btn-success">Send To Supplier</button>
            </div>

            <?= form_close(); ?>
    </div>

<!-- </div> -->

<!-- JS for Tabs -->
<script>
    const tabs = document.querySelectorAll('.tab');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));

            tab.classList.add('active');
            document.getElementById(tab.dataset.tab).classList.add('active');
        });
    });

  
</script>

