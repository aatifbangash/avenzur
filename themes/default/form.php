<!DOCTYPE html>
<html>
<head>
    <title>Supplier Response Form</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .table th { background-color: #f8f9fa; }
        input[type="number"] { width: 100px; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Purchase Requisition Response Form</h4>
                    </div>
                    <div class="card-body">
                        <?php if (validation_errors()): ?>
                            <div class="alert alert-danger">
                                <?php echo validation_errors(); ?>
                            </div>
                        <?php endif; ?>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>PR Details</h5>
                                <p><strong>PR Number:</strong> <?php echo $pr->pr_number; ?></p>
                                <p><strong>Date Created:</strong> <?php echo date('Y-m-d', strtotime($pr->created_at)); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h5>Supplier Details</h5>
                                <p><strong>Company:</strong> <?php echo $supplier->name; ?></p>
                                <p><strong>Email:</strong> <?php echo $supplier->email; ?></p>
                            </div>
                        </div>

                        <?php echo form_open('supplier_response/submit/'.$token); ?>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Quantity</th>
                                            <th>Unit Price</th>
                                            <th>Dis 1</th>
                                            <th>Dis 2</th>
                                            <th>Dis 3</th>
                                            <th>Deal %</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td>
                                                <?php echo $item->product_name; ?>
                                                <input type="hidden" name="pr_item_id[]" value="<?php echo $item->id; ?>">
                                            </td>
                                            <td><?php echo $item->quantity; ?></td>
                                            <td>
                                                <input type="number" step="0.0001" name="unit_price[]" class="form-control" required>
                                            </td>
                                            <td>
                                                <input type="number" step="0.00001" name="dis1[]" class="form-control" value="0">
                                            </td>
                                            <td>
                                                <input type="number" step="0.00001" name="dis2[]" class="form-control" value="0">
                                            </td>
                                            <td>
                                                <input type="number" step="0.00001" name="dis3[]" class="form-control" value="0">
                                            </td>
                                            <td>
                                                <input type="number" step="0.00001" name="deal[]" class="form-control" value="0">
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="form-group">
                                <label for="remarks">General Remarks</label>
                                <textarea name="remarks" class="form-control" rows="3"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">Submit Response</button>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>