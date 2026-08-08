<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Arial, sans-serif; font-size: 11px; }
    h2 { text-align: center; margin-bottom: 4px; }
    p.subtitle { text-align: center; color: #555; margin-top: 0; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th { background-color: #2d6a9f; color: #fff; padding: 5px 4px; text-align: left; }
    td { padding: 4px; border-bottom: 1px solid #ddd; }
    tr:nth-child(even) td { background-color: #f5f5f5; }
    .active  { color: green; font-weight: bold; }
    .restock { color: #e67e00; font-weight: bold; }
</style>
</head>
<body>
<h2>Warehouse Shelving Report</h2>
<p class="subtitle">Generated: <?= date('d M Y H:i') ?></p>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>PO Date</th>
            <th>Product Code</th>
            <th>Product Name</th>
            <th>Batch #</th>
            <th>Expiry Date</th>
            <th>Qty</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($reportData)): ?>
            <?php foreach ($reportData as $i => $row): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($row['po_date']) ?></td>
                    <td><?= htmlspecialchars($row['product_code']) ?></td>
                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                    <td><?= htmlspecialchars($row['batch_no']) ?></td>
                    <td><?= htmlspecialchars($row['expiry_date']) ?></td>
                    <td><?= htmlspecialchars($row['qty']) ?></td>
                    <td class="<?= in_array($row['status'], ['active','restock']) ? $row['status'] : '' ?>">
                        <?= htmlspecialchars($row['status']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="8" style="text-align:center;">No records found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
</body>
</html>
