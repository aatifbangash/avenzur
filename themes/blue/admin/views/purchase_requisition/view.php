
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
        th { background: #007bff; color: #fff; font-weight: 500; }
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
    <div class="header">
        <h1>Purchase Request Details</h1>
        <button id="" class="btn btn-primary" style="float:right; margin-top:-35px">Create PO</button>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <div class="tab active" data-tab="info">PR Info</div>
        <div class="tab" data-tab="items">Items</div>
        <div class="tab" data-tab="logs">Audit Logs</div>
        <div class="tab" data-tab="suppliers">Suppliers Sent</div>
        <div class="tab" data-tab="send-supplier">Send to Supplier</div>
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
        <div class="logs">
            <?php foreach($logs as $log): ?>
                <p>[<?= $log->created_at; ?>] <?= $log->action; ?> by user <?= $log->done_by_name; ?></p>

            <?php endforeach; ?>

        </div>
    </div>

    <div class="tab-content" id="suppliers">
        <ul>
            <?php 



            foreach($suppliers as $key => $supplier): ?>
                <li><?= $supplier->name; ?> (<?= $supplier->id; ?>)</li>
            <?php endforeach; ?>
        </ul>
    </div>

      <div class="tab-content" id="send-supplier">
         <?= form_open('admin/purchase_requisition/create', ['class' => 'needs-validation', 'novalidate' => true]); ?>

            <div class="row mb-3">
                 <div class="col-md-4">
                    <?= form_label('supplier', 'supplier_id'); 
                  $supplier_options = [];
        foreach ($suppliers as $wh) {
            $supplier_options[$wh->id] = $wh->name;
        }
                    ?>
                    <?= form_dropdown('supplier_id', $supplier_options, set_value('warehouse_id'), ['class' => 'form-control', 'id' => 'warehouse_id']); ?>
                </div>
            

                <div class="col-md-4">
                    <?= form_label('Supplier Email', 'requested_by'); ?>
                    <input type="text" name="supplier_email" class="form-control" 
       value=""  />
                </div>

                
            </div>

            <div class="row mb-3">

            <div class="col-md-4">
                    <?= form_label('Subject', 'subject'); ?>
                    <input type="text" name="requested_by" class="form-control" 
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

