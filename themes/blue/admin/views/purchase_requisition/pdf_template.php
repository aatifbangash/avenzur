<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Requisition <?= $pr->pr_number ?></title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; font-weight: bold; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 6px; text-align: left; }
        .footer { margin-top: 30px; text-align: right; font-size: 11px; color: #555; }
    </style>
</head>
<body>

<div class="header">
    <h2>Purchase Requisition</h2>
    <p><strong>PR Number:</strong> <?= $pr['pr_number'] ?></p>
    <p><strong>Date:</strong> <?= date('d M Y', strtotime($pr['created_date'])) ?></p>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Item</th>
            <th>Qty</th>
            
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $i => $item): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($item['product_name']) ?></td>
            <td><?= $item['quantity'] ?></td>
           
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="footer">
    Generated on <?= date('d M Y H:i') ?>
</div>

</body>
</html>
