<?php
// ─────────────────────────────────────────────────────────────────────────────
// WMS × Shopify Inventory Comparison  —  standalone, no CodeIgniter
// ─────────────────────────────────────────────────────────────────────────────

defined('BASEPATH') || define('BASEPATH', __DIR__ . '/app/');
$db = [];
include __DIR__ . '/app/config/database.php';
$db_config = $db['default'];
unset($db);

$db = new mysqli(
    $db_config['hostname'],
    $db_config['username'],
    $db_config['password'],
    $db_config['database']
);
if ($db->connect_error) {
    die('<p style="font-family:sans-serif;color:red;padding:20px;">DB Error: ' . htmlspecialchars($db->connect_error) . '</p>');
}
$db->set_charset('utf8');
$db->query("SET collation_connection = utf8_general_ci");

// ── INPUT ─────────────────────────────────────────────────────────────────────
$search   = trim($_GET['search'] ?? '');
$status_f = trim($_GET['status'] ?? '');
$sort     = in_array($_GET['sort'] ?? '', [
    'barcode','title','unit_cost','price','shopify_qty',
    'shelved_qty','damaged_qty','expiry_qty','reserved_qty','sellable_qty','variance'
]) ? $_GET['sort'] : 'variance';
$dir      = ($_GET['dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';
$page     = max(0, (int)($_GET['page'] ?? 0));
$per_page = 50;

// Damaged condition — rack E201*/E202*, box F101*
$damaged_cond = "
    UPPER(s.rack_number) LIKE 'E201%' OR
    UPPER(s.rack_number) LIKE 'E202%' OR
    UPPER(s.box_number)  LIKE 'F101%'
";

// ── SUMMARY METRICS ───────────────────────────────────────────────────────────
$met = $db->query("
    SELECT
        (SELECT COUNT(DISTINCT COALESCE(NULLIF(TRIM(LEADING '0' FROM product_code),''),'0'))
         FROM sma_purchase_order_items)                                        AS wms_purchased,

        (SELECT COUNT(DISTINCT COALESCE(NULLIF(TRIM(LEADING '0' FROM si.product_code),''),'0'))
         FROM sma_purchase_order_shelving_items si
         JOIN sma_purchase_order_shelving s ON s.id = si.shelving_id
         WHERE si.qty > 0)                                                     AS wms_shelved,

        (SELECT COUNT(DISTINCT barcode)
         FROM sma_shopify_location_products_inventory
         WHERE status = 'ACTIVE' AND barcode IS NOT NULL AND barcode != '')    AS shopify_active,

        (SELECT COALESCE(SUM(si.qty), 0)
         FROM sma_purchase_order_shelving_items si
         JOIN sma_purchase_order_shelving s ON s.id = si.shelving_id
         WHERE si.qty > 0
           AND NOT (UPPER(s.rack_number) LIKE 'E201%'
                 OR UPPER(s.rack_number) LIKE 'E202%'
                 OR UPPER(s.box_number)  LIKE 'F101%'))                        AS wms_total_units,

        (SELECT COALESCE(SUM(inventory_quantity), 0)
         FROM sma_shopify_location_products_inventory
         WHERE status = 'ACTIVE' AND barcode IS NOT NULL AND barcode != '')    AS shopify_total_units,

        (SELECT COALESCE(SUM(si.qty), 0)
         FROM sma_purchase_order_shelving_items si
         JOIN sma_purchase_order_shelving s ON s.id = si.shelving_id
         WHERE UPPER(s.rack_number) LIKE 'E201%'
            OR UPPER(s.rack_number) LIKE 'E202%'
            OR UPPER(s.box_number)  LIKE 'F101%')                             AS total_damaged,

        (SELECT COALESCE(SUM(si.qty), 0)
         FROM sma_purchase_order_shelving_items si
         JOIN sma_purchase_order_shelving s ON s.id = si.shelving_id
         WHERE s.bin_type = 'expiry')                                          AS total_expiry,

        (SELECT COALESCE(SUM(si.quantity), 0)
         FROM sma_sale_items si
         JOIN sma_sales s ON s.id = si.sale_id
         WHERE s.sale_status IN ('pending','processing','ordered'))            AS total_reserved,

        (SELECT DISTINCT location_id FROM sma_shopify_location_products_inventory LIMIT 1) AS location_id,

        NOW() AS synced_at
")->fetch_assoc();

// ── PRODUCT LIST (with barcode) ───────────────────────────────────────────────
// Pre-aggregate reserved qty once — avoids correlated subquery per row
$inner_sql = "
    SELECT
        sh.barcode,
        sh.location_id,
        sh.title,
        COALESCE(sh.unit_cost, 0)                                              AS unit_cost,
        sh.price,
        sh.inventory_quantity                                                  AS shopify_qty,
        GREATEST(COALESCE(SUM(si.qty), 0), 0)                                  AS shelved_qty,
        GREATEST(COALESCE(SUM(
            CASE WHEN UPPER(s.rack_number) LIKE 'E201%'
                   OR UPPER(s.rack_number) LIKE 'E202%'
                   OR UPPER(s.box_number)  LIKE 'F101%'
                 THEN si.qty ELSE 0 END
        ), 0), 0)                                                              AS damaged_qty,
        GREATEST(COALESCE(SUM(
            CASE WHEN s.bin_type = 'expiry'
                 THEN si.qty ELSE 0 END
        ), 0), 0)                                                              AS expiry_qty,
        COALESCE(res.reserved_qty, 0)                                          AS reserved_qty
    FROM sma_shopify_location_products_inventory sh
    LEFT JOIN sma_purchase_order_shelving_items si
           ON COALESCE(NULLIF(TRIM(LEADING '0' FROM si.product_code), ''), '0') = sh.barcode
    LEFT JOIN sma_purchase_order_shelving s ON s.id = si.shelving_id
    LEFT JOIN (
        SELECT p.code_clean, SUM(sli.quantity) AS reserved_qty
        FROM sma_sale_items sli
        JOIN sma_products p  ON p.id  = sli.product_id
        JOIN sma_sales    sl ON sl.id = sli.sale_id
        WHERE sl.sale_status IN ('pending','processing','ordered')
        GROUP BY p.code_clean
    ) res ON res.code_clean = sh.barcode
    WHERE sh.status = 'ACTIVE'
      AND sh.barcode IS NOT NULL AND sh.barcode != ''
    GROUP BY sh.id, sh.barcode, sh.location_id, sh.title, sh.unit_cost, sh.price, sh.inventory_quantity
";

$base_sql     = "FROM ({$inner_sql}) base";
$sellable_expr = "(base.shelved_qty - base.damaged_qty)";
$var_expr      = "({$sellable_expr} - base.shopify_qty)";

// filters
$conditions = [];
$params     = [];
$types      = '';

if ($search !== '') {
    $conditions[] = "(base.barcode LIKE ? OR base.title LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like; $params[] = $like;
    $types   .= 'ss';
}
if (isset($_GET['export']) && $_GET['export'] == '1') {

    ini_set('display_errors', 0);
    error_reporting(0);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=inventory_export.csv');

    $output = fopen('php://output', 'w');

    // Headers
    fputcsv($output, [
        'Barcode','Product','Cost Price','Sale Price',
        'On Hand','Damaged','Expiry','Reserved',
        'Sellable','Shopify Qty','Variance'
    ]);

    $export_sql = "
        SELECT base.*, {$var_expr} AS variance, {$sellable_expr} AS sellable_qty
        {$base_sql} {$where} {$order}
    ";

    $stmt = $db->prepare($export_sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['barcode'], 
            $row['title'],
            $row['unit_cost'],
            $row['price'],
            $row['shelved_qty'],
            $row['damaged_qty'],
            $row['expiry_qty'],
            $row['reserved_qty'],
            $row['sellable_qty'],
            $row['shopify_qty'],
            $row['variance']
        ]);
    }

    fclose($output);
    exit;
}
switch ($status_f) {
    case 'critical': $conditions[] = "{$var_expr} < 0";                                                               break;
    case 'low':      $conditions[] = "{$var_expr} >= 0 AND {$sellable_expr} > 0 AND {$sellable_expr} < 30";           break;
    case 'surplus':  $conditions[] = "{$var_expr} > 0 AND {$sellable_expr} >= 30";                                    break;
    case 'matched':  $conditions[] = "{$var_expr} = 0 AND base.shopify_qty > 0";                                      break;
    case 'oos':      $conditions[] = "{$sellable_expr} <= 0 AND base.shopify_qty <= 0";                               break;
}
$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

// sort
$sort_map = [
    'barcode'      => 'base.barcode',
    'title'        => 'base.title',
    'unit_cost'    => 'base.unit_cost',
    'price'        => 'base.price',
    'shopify_qty'  => 'base.shopify_qty',
    'shelved_qty'  => 'base.shelved_qty',
    'damaged_qty'  => 'base.damaged_qty',
    'expiry_qty'   => 'base.expiry_qty',
    'reserved_qty' => 'base.reserved_qty',
    'sellable_qty' => $sellable_expr,
    'variance'     => $var_expr,
];
$order = "ORDER BY " . ($sort_map[$sort] ?? $var_expr) . " {$dir}, base.title ASC";

// count
$stmt = $db->prepare("SELECT COUNT(*) AS c {$base_sql} {$where}");
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$total = (int)$stmt->get_result()->fetch_assoc()['c'];
$stmt->close();

// data
$data_params = array_merge($params, [$per_page, $page * $per_page]);
$data_types  = $types . 'ii';
$stmt = $db->prepare("
    SELECT base.*, {$var_expr} AS variance, {$sellable_expr} AS sellable_qty
    {$base_sql} {$where} {$order}
    LIMIT ? OFFSET ?
");
$stmt->bind_param($data_types, ...$data_params);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── EMPTY BARCODE PRODUCTS (bottom panel) ────────────────────────────────────
$empty_search = trim($_GET['esearch'] ?? '');
$empty_params = [];
$empty_types  = '';
$empty_where  = "WHERE (sh.barcode IS NULL OR sh.barcode = '') AND sh.status = 'ACTIVE'";
if ($empty_search !== '') {
    $empty_where .= " AND (sh.title LIKE ? OR sh.sku LIKE ?)";
    $elike = '%' . $empty_search . '%';
    $empty_params[] = $elike; $empty_params[] = $elike;
    $empty_types   .= 'ss';
}

// empty barcode summary ledger — count distinct sku+title groups
$empty_summary = $db->query("
    SELECT
        COUNT(*)                                                                          AS total,
        SUM(CASE WHEN sku IS NOT NULL AND sku != '' THEN 1 ELSE 0 END)                   AS has_sku,
        SUM(CASE WHEN sku IS NULL OR sku = ''       THEN 1 ELSE 0 END)                   AS no_sku,
        SUM(CASE WHEN total_qty > 0                 THEN 1 ELSE 0 END)                   AS has_stock,
        SUM(CASE WHEN total_qty <= 0                THEN 1 ELSE 0 END)                   AS zero_stock,
        SUM(CASE WHEN price > 0                     THEN 1 ELSE 0 END)                   AS has_price,
        SUM(total_qty)                                                                    AS total_units
    FROM (
        SELECT
            COALESCE(NULLIF(sku,''), title)           AS grp_key,
            MAX(sku)                                  AS sku,
            MAX(price)                                AS price,
            SUM(COALESCE(inventory_quantity, 0))      AS total_qty
        FROM sma_shopify_location_products_inventory
        WHERE (barcode IS NULL OR barcode = '') AND status = 'ACTIVE'
        GROUP BY COALESCE(NULLIF(sku,''), title)
    ) g
")->fetch_assoc();

$stmt = $db->prepare("
    SELECT
        sh.title,
        MAX(sh.sku)                              AS sku,
        MAX(sh.price)                            AS price,
        MAX(sh.unit_cost)                        AS unit_cost,
        SUM(sh.inventory_quantity)               AS inventory_quantity,
        MAX(sh.location_id)                      AS location_id
    FROM sma_shopify_location_products_inventory sh
    {$empty_where}
    GROUP BY COALESCE(NULLIF(sh.sku,''), sh.title), sh.title
    ORDER BY SUM(sh.inventory_quantity) DESC, sh.title ASC
");
if ($empty_params) $stmt->bind_param($empty_types, ...$empty_params);
$stmt->execute();
$empty_rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$empty_total = (int)($empty_summary['total'] ?? 0);

$db->close();

// ── HELPERS ───────────────────────────────────────────────────────────────────
function fmt($n)    { return $n === null ? '—' : number_format((float)$n, 0); }
function fmtSAR($n) { return (!$n || $n == 0) ? '<span class="sar-nil">—</span>' : 'SAR&nbsp;' . number_format((float)$n, 2); }

function statusInfo($row) {
    $v  = (float)$row['variance'];
    $sl = (float)$row['sellable_qty'];   // shelved − damaged
    $sq = (float)$row['shopify_qty'];
    if ($v < 0)               return ['badge-critical', 'Critical',     'row-critical'];
    if ($sl > 0 && $sl < 30)  return ['badge-low',      'Low Stock',    'row-warning'];
    if ($sl <= 0 && $sq <= 0) return ['badge-nodata',   'Out of Stock', ''];
    if ($v === 0.0 && $sq > 0)return ['badge-matched',  'Matched',      'row-ok'];
    if ($v > 0)               return ['badge-surplus',  'WMS Surplus',  ''];
    return                           ['badge-nodata',   'No Data',      ''];
}

function varBadge($v) {
    $v = (float)$v;
    if ($v < 0) return '<span class="var-badge var-neg"><i class="fa-solid fa-arrow-down"></i> ' . number_format($v,0) . '</span>';
    if ($v > 0) return '<span class="var-badge var-pos"><i class="fa-solid fa-arrow-up"></i> +' . number_format($v,0) . '</span>';
    return '<span class="var-badge var-zero">0</span>';
}

function qtyClass($n, $type = '') {
    $n = (float)$n;
    if (in_array($type, ['damaged','expiry','reserved']) && $n > 0) return 'qty qty-warn';
    if ($n <= 0) return 'qty qty-zero';
    return 'qty';
}

function thSort($col, $label, $cur, $dir, $extra = '') {
    $newDir = ($cur === $col && $dir === 'ASC') ? 'desc' : 'asc';
    $arrow  = $cur === $col ? ($dir === 'ASC' ? ' ▲' : ' ▼') : '';
    $q = http_build_query(array_merge($_GET, ['sort' => $col, 'dir' => $newDir, 'page' => 0]));
    return '<th ' . $extra . '><a href="?' . htmlspecialchars($q) . '">' . $label . $arrow . '</a></th>';
}

$total_pages  = (int)ceil($total / $per_page);
$offset_start = $page * $per_page + 1;
$offset_end   = min(($page + 1) * $per_page, $total);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>WMS × Shopify — Inventory Comparison</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
    --blue:#1d6fce;--blue-d:#1558a8;--blue-l:#e8f1fb;
    --teal:#0d9488;--teal-l:#e0f7f5;
    --purple:#7c3aed;--purple-l:#f0ebff;
    --orange:#ea580c;--orange-l:#fff1e8;
    --cyan:#0284c7;--cyan-l:#e0f4ff;
    --rose:#e11d48;--rose-l:#fff1f2;
    --red:#dc2626;--amber:#d97706;--green:#16a34a;
    --bg:#eef1f8;--card:#fff;--border:#e2e8f0;
    --text:#0f172a;--muted:#64748b;
    --radius:14px;--radius-sm:8px;
    --shadow:0 1px 3px rgba(0,0,0,.06),0 4px 14px rgba(0,0,0,.06);
    --shadow-md:0 4px 6px rgba(0,0,0,.07),0 10px 30px rgba(0,0,0,.08);
}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:13.5px;line-height:1.55;-webkit-font-smoothing:antialiased}

/* ── TOPBAR ─────────────────────────────────────────────────────────────── */
.topbar{
    position:sticky;top:0;z-index:200;height:64px;
    background:rgba(255,255,255,.88);
    backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);
    border-bottom:1px solid rgba(226,232,240,.8);
    display:flex;align-items:center;padding:0 28px;gap:14px;
    box-shadow:0 1px 0 rgba(0,0,0,.04),0 2px 12px rgba(0,0,0,.04);
}
.logo-box{
    width:36px;height:36px;flex-shrink:0;border-radius:10px;
    background:linear-gradient(135deg,#1d6fce 0%,#0ea5e9 100%);
    display:flex;align-items:center;justify-content:center;
    color:#fff;font-size:16px;
    box-shadow:0 2px 8px rgba(29,111,206,.35);
}
.topbar-brand{display:flex;align-items:center;gap:10px;font-weight:700;font-size:15px;color:var(--text);text-decoration:none;letter-spacing:-.2px}
.topbar-sep{width:1px;height:26px;background:var(--border);flex-shrink:0}
.topbar-title{font-size:13.5px;font-weight:600;color:var(--text)}
.topbar-sub{font-size:11px;color:var(--muted);margin-top:1px}
.loc-badge{
    display:inline-flex;align-items:center;gap:5px;
    padding:4px 12px;background:var(--blue-l);color:var(--blue);
    border-radius:20px;font-size:11.5px;font-weight:600;
    border:1px solid rgba(29,111,206,.15);
}
.topbar-right{margin-left:auto;display:flex;align-items:center;gap:10px}
.ts{font-size:11.5px;color:var(--muted);display:flex;align-items:center;gap:5px;padding:5px 10px;background:#f8fafc;border:1px solid var(--border);border-radius:8px}
.btn{display:inline-flex;align-items:center;gap:6px;padding:7px 16px;border-radius:9px;font-size:12.5px;font-weight:600;cursor:pointer;text-decoration:none;border:none;transition:all .15s;letter-spacing:-.1px}
.btn-primary{background:var(--blue);color:#fff;box-shadow:0 1px 3px rgba(29,111,206,.3)}.btn-primary:hover{background:var(--blue-d);box-shadow:0 2px 8px rgba(29,111,206,.4);transform:translateY(-1px)}
.btn-outline{background:#fff;border:1px solid var(--border);color:var(--text)}.btn-outline:hover{background:var(--bg);border-color:#c7d2e0}
.btn-danger{background:#fff;border:1px solid #fecaca;color:var(--red)}.btn-danger:hover{background:#fff5f5}

/* ── LAYOUT ──────────────────────────────────────────────────────────────── */
.page-wrap{width:100%;padding:22px 24px 56px}
.section-lbl{font-size:10.5px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.1em;margin-bottom:10px;display:flex;align-items:center;gap:8px}
.section-lbl::after{content:'';flex:1;height:1px;background:var(--border)}

/* ── METRICS ─────────────────────────────────────────────────────────────── */
.metrics-grid{display:grid;grid-template-columns:repeat(8,1fr);gap:10px;margin-bottom:18px}
@media(max-width:1500px){.metrics-grid{grid-template-columns:repeat(4,1fr)}}
@media(max-width:800px){.metrics-grid{grid-template-columns:repeat(2,1fr)}}
.mc{
    background:var(--card);border:1px solid var(--border);
    border-radius:var(--radius);padding:15px 16px;
    display:flex;flex-direction:column;gap:9px;
    box-shadow:var(--shadow);position:relative;overflow:hidden;
    transition:transform .18s,box-shadow .18s;
}
.mc:hover{transform:translateY(-2px);box-shadow:var(--shadow-md)}
.mc-stripe{position:absolute;top:0;left:0;width:4px;bottom:0;border-radius:var(--radius) 0 0 var(--radius)}
.mc-head{display:flex;align-items:center;justify-content:space-between;padding-left:10px}
.mc-icon{width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:14px}
.mc-tag{font-size:9.5px;font-weight:700;padding:2px 7px;border-radius:20px;text-transform:uppercase;letter-spacing:.05em}
.mc-body{padding-left:10px}
.mc-val{font-size:22px;font-weight:800;letter-spacing:-.8px;line-height:1;color:var(--text)}
.mc-lbl{font-size:11px;color:var(--muted);font-weight:500;margin-top:3px}

/* ── BANNER ──────────────────────────────────────────────────────────────── */
.banner{
    display:flex;align-items:flex-start;gap:12px;
    background:linear-gradient(135deg,#eff6ff 0%,#f0f9ff 100%);
    border:1px solid #bfdbfe;border-radius:var(--radius);
    padding:13px 18px;margin-bottom:18px;font-size:12.5px;color:#1e40af;
}
.banner i{color:#3b82f6;font-size:15px;flex-shrink:0;margin-top:2px}

/* ── SPLIT LAYOUT ────────────────────────────────────────────────────────── */
.split{display:grid;grid-template-columns:1fr 360px;gap:16px;align-items:start}
@media(max-width:1200px){.split{grid-template-columns:1fr}}

/* ── CARD ────────────────────────────────────────────────────────────────── */
.card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden}

/* ── TOOLBAR ─────────────────────────────────────────────────────────────── */
.toolbar{
    padding:12px 16px;border-bottom:1px solid var(--border);
    display:flex;align-items:center;gap:10px;flex-wrap:wrap;
    background:linear-gradient(to bottom,#fcfdff,#f8fafc);
}
.tbl-title{font-weight:700;font-size:13px;display:flex;align-items:center;gap:7px;letter-spacing:-.1px}
.tbl-title i{color:var(--blue)}
.cnt-badge{background:var(--blue-l);color:var(--blue);font-size:10.5px;font-weight:700;padding:2px 9px;border-radius:20px;border:1px solid rgba(29,111,206,.15)}
.tb-right{margin-left:auto;display:flex;align-items:center;gap:7px;flex-wrap:wrap}
.srch{position:relative}
.srch i{position:absolute;left:9px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:11.5px;pointer-events:none}
.srch input{
    padding:6px 10px 6px 30px;border:1px solid var(--border);
    border-radius:8px;font-size:12.5px;font-family:inherit;
    color:var(--text);width:200px;outline:none;background:#fff;
    transition:border-color .15s,box-shadow .15s;
}
.srch input:focus{border-color:var(--blue);box-shadow:0 0 0 3px rgba(29,111,206,.12)}
select.fs{
    padding:6px 10px;border:1px solid var(--border);border-radius:8px;
    font-size:12.5px;font-family:inherit;color:var(--text);
    background:#fff;outline:none;cursor:pointer;
    transition:border-color .15s;
}
select.fs:focus{border-color:var(--blue)}

/* ── TABLE ───────────────────────────────────────────────────────────────── */
.tw{overflow-x:auto;-webkit-overflow-scrolling:touch}
table{
    width:100%;border-collapse:separate;border-spacing:0;
    font-size:13px;table-layout:fixed;
}

/* column widths */
table colgroup col.col-num    {width:44px}
table colgroup col.col-barcode{width:130px}
table colgroup col.col-product{width:240px}
table colgroup col.col-price  {width:106px}
table colgroup col.col-qty    {width:82px}
table colgroup col.col-sell   {width:90px}
table colgroup col.col-var    {width:100px}
table colgroup col.col-status {width:120px}

thead th{
    padding:10px 14px;
    font-size:10.5px;font-weight:800;
    text-transform:uppercase;letter-spacing:.08em;
    color:#374151;
    background:#f1f5f9;
    border-top:1px solid var(--border);
    border-bottom:2px solid #cbd5e1;
    white-space:nowrap;
    text-align:center;
    position:sticky;
    top:0;
    z-index:30;
    box-shadow:0 2px 4px rgba(0,0,0,.07);
}
thead th.th-left{text-align:left}
thead th a{
    color:inherit;text-decoration:none;
    display:inline-flex;align-items:center;justify-content:center;gap:5px;
}
thead th.th-left a{justify-content:flex-start}
thead th a:hover{color:var(--blue)}

tbody tr{border-bottom:1px solid #f1f5f9;transition:background .12s}
tbody tr:last-child td{border-bottom:none}
tbody tr:hover{background:#f5f8ff}
tbody tr.row-critical{background:#fff5f5}
tbody tr.row-critical td:first-child{border-left:3px solid var(--red)}
tbody tr.row-critical:hover{background:#fee8e8}
tbody tr.row-warning{background:#fffdf0}
tbody tr.row-warning td:first-child{border-left:3px solid var(--amber)}
tbody tr.row-warning:hover{background:#fef9e0}
tbody tr.row-ok{background:#f6fef9}

tbody td{
    padding:10px 14px;
    vertical-align:middle;
    text-align:center;
    font-size:13px;
    line-height:1.45;
    overflow:hidden;
    white-space:nowrap;
    text-overflow:ellipsis;
    border-bottom:1px solid #f1f5f9;
    position:relative;
    z-index:1;
}
tbody td.td-left{text-align:left;white-space:normal}

/* # column */
.col-num-cell{color:#94a3b8;font-size:12px;font-weight:500}

/* barcode */
.bcode{
    font-family:'Courier New',monospace;font-size:11px;font-weight:700;
    color:#475569;background:#f1f5f9;
    padding:3px 8px;border-radius:5px;
    display:inline-block;white-space:nowrap;
    border:1px solid #e2e8f0;
}

/* product cell */
.prod-cell{min-width:0}
.prod-name{
    font-weight:600;font-size:13px;color:var(--text);
    white-space:normal;line-height:1.4;
    display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
}
.prod-loc{
    display:inline-flex;align-items:center;gap:3px;
    font-size:10.5px;color:var(--muted);font-weight:400;margin-top:3px;
}

/* price cells */
.price-wrap{display:flex;flex-direction:column;align-items:center;gap:1px}
.price-label{font-size:9px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.07em}
.price-val{font-size:12.5px;font-weight:700;color:var(--text);font-variant-numeric:tabular-nums;white-space:nowrap}
.sar-nil{color:#cbd5e1;font-weight:300;font-size:14px}

/* qty cells */
.qty{font-variant-numeric:tabular-nums;font-weight:700;font-size:13px}
.qty-zero{color:#cbd5e1;font-weight:400}
.qty-warn{color:var(--amber);font-weight:700}
.qty-ok{color:var(--green);font-weight:700}

/* badges */
.var-badge{
    display:inline-flex;align-items:center;gap:3px;
    padding:4px 10px;border-radius:20px;
    font-size:11.5px;font-weight:700;white-space:nowrap;
}
.var-pos{background:#dcfce7;color:#15803d}
.var-neg{background:#fee2e2;color:#b91c1c}
.var-zero{background:#f1f5f9;color:#64748b}
.sb{
    display:inline-flex;align-items:center;gap:5px;
    padding:4px 10px;border-radius:20px;
    font-size:11px;font-weight:600;white-space:nowrap;
}
.sb .d{width:6px;height:6px;border-radius:50%;flex-shrink:0}
.badge-matched {background:#dcfce7;color:#15803d;border:1px solid #bbf7d0}.badge-matched  .d{background:#16a34a}
.badge-critical{background:#fee2e2;color:#b91c1c;border:1px solid #fecaca}.badge-critical .d{background:#dc2626}
.badge-low     {background:#fef9c3;color:#854d0e;border:1px solid #fef08a}.badge-low      .d{background:#ca8a04}
.badge-surplus {background:#e0f2fe;color:#075985;border:1px solid #bae6fd}.badge-surplus  .d{background:#0284c7}
.badge-nodata  {background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0}.badge-nodata   .d{background:#94a3b8}

/* ── LEGEND ──────────────────────────────────────────────────────────────── */
.legend{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.li{display:flex;align-items:center;gap:4px;font-size:11px;color:var(--muted)}
.ld{width:7px;height:7px;border-radius:50%}

/* ── PAGINATION ──────────────────────────────────────────────────────────── */
.tbl-foot{
    padding:10px 16px;border-top:1px solid var(--border);
    display:flex;align-items:center;justify-content:space-between;
    background:#f8fafc;flex-wrap:wrap;gap:8px;
}
.tbl-foot-info{font-size:12px;color:var(--muted)}
.pag{display:flex;align-items:center;gap:3px}
.pb{
    width:30px;height:30px;display:flex;align-items:center;justify-content:center;
    border:1px solid var(--border);border-radius:8px;font-size:11.5px;
    color:var(--text);background:#fff;text-decoration:none;
    transition:all .12s;font-weight:500;
}
.pb:hover{background:var(--blue-l);border-color:var(--blue);color:var(--blue)}
.pb.active{background:var(--blue);border-color:var(--blue);color:#fff;font-weight:700;box-shadow:0 2px 6px rgba(29,111,206,.3)}
.pb.disabled{opacity:.35;pointer-events:none}

/* ── SIDE PANEL ──────────────────────────────────────────────────────────── */
.side-panel{
    position:sticky;top:80px;
    display:flex;flex-direction:column;
    max-height:calc(100vh - 96px);
    border-radius:var(--radius);
    overflow:hidden;
    box-shadow:var(--shadow-md);
}
.sp-header{
    padding:14px 16px;
    background:linear-gradient(135deg,#fff1f2 0%,#ffe4e6 100%);
    border-bottom:1px solid #fecdd3;
    display:flex;align-items:center;gap:10px;
    flex-shrink:0;
}
.sp-header-icon{
    width:34px;height:34px;border-radius:9px;
    background:var(--rose);display:flex;align-items:center;justify-content:center;
    color:#fff;font-size:14px;flex-shrink:0;
    box-shadow:0 2px 6px rgba(225,29,72,.3);
}
.sp-title{font-weight:700;font-size:12.5px;color:#9f1239;line-height:1.2}
.sp-sub{font-size:11px;color:#be185d;font-weight:500;margin-top:2px}
.sp-count{
    margin-left:auto;background:#fff;border:1px solid #fecdd3;
    color:#be185d;font-size:12px;font-weight:700;
    padding:3px 10px;border-radius:20px;flex-shrink:0;
}

/* ledger inside sidebar */
.sp-ledger{
    display:grid;grid-template-columns:1fr 1fr;gap:1px;
    background:var(--border);border-bottom:1px solid var(--border);
    flex-shrink:0;
}
.spl-cell{
    background:#fafbfc;padding:10px 14px;
    display:flex;flex-direction:column;gap:2px;
}
.spl-cell:nth-child(odd){background:#f8fafc}
.spl-val{font-size:18px;font-weight:800;letter-spacing:-.5px;line-height:1}
.spl-lbl{font-size:10px;color:var(--muted);font-weight:500;line-height:1.3}
.spl-red  .spl-val{color:var(--red)}
.spl-amber .spl-val{color:var(--amber)}
.spl-green .spl-val{color:var(--green)}
.spl-blue  .spl-val{color:var(--blue)}
.spl-muted .spl-val{color:#94a3b8}

/* why notice */
.sp-why{
    padding:10px 14px;font-size:11.5px;color:#92400e;
    background:#fffbeb;border-bottom:1px solid #fde68a;
    display:flex;align-items:flex-start;gap:8px;flex-shrink:0;
    line-height:1.5;
}
.sp-why i{color:var(--amber);flex-shrink:0;margin-top:2px;font-size:13px}

/* search inside sidebar */
.sp-search{
    padding:10px 12px;border-bottom:1px solid var(--border);
    background:#fff;flex-shrink:0;
}
.sp-search .srch input{width:100%;padding-right:10px;font-size:12px}

/* scrollable list */
.sp-list{overflow-y:auto;flex:1;background:#fff}
.sp-item{
    padding:11px 14px;border-bottom:1px solid #f1f5f9;
    transition:background .1s;cursor:default;
}
.sp-item:last-child{border-bottom:none}
.sp-item:hover{background:#fafbff}
.sp-item.has-stock{border-left:3px solid var(--amber);background:#fffdf0}
.sp-item.has-stock:hover{background:#fef9e0}
.sp-name{font-size:12px;font-weight:600;color:var(--text);line-height:1.35;margin-bottom:5px}
.sp-row{display:flex;align-items:center;flex-wrap:wrap;gap:5px;margin-top:4px}
.sp-sku{font-family:'Courier New',monospace;font-size:10.5px;color:#64748b;background:#f1f5f9;padding:1px 6px;border-radius:4px;font-weight:600}
.sp-no-sku{font-size:10.5px;color:#f87171;background:#fff5f5;padding:1px 6px;border-radius:4px;font-style:italic}
.sp-qty{font-size:11px;font-weight:700;color:var(--text);background:#f0fdf4;color:#15803d;padding:1px 7px;border-radius:4px;border:1px solid #bbf7d0}
.sp-qty.zero{background:#f1f5f9;color:#94a3b8;border-color:#e2e8f0}
.sp-price{font-size:11px;color:var(--muted);font-weight:500}
.sp-tag{font-size:10px;background:#fee2e2;color:#b91c1c;padding:1px 6px;border-radius:4px;font-weight:700;border:1px solid #fecaca}
.sp-cost{font-size:10.5px;color:#64748b;background:#f8fafc;padding:1px 6px;border-radius:4px;border:1px solid #e2e8f0}

/* ── EMPTY STATE ─────────────────────────────────────────────────────────── */
.empty{padding:48px 20px;text-align:center;color:var(--muted)}
.empty i{font-size:38px;margin-bottom:12px;display:block;opacity:.25}
.empty p{font-size:13px;font-weight:500}

::-webkit-scrollbar{width:5px;height:5px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:99px}
::-webkit-scrollbar-thumb:hover{background:#94a3b8}
</style>
</head>
<body>

<!-- ═══ TOPBAR ═══════════════════════════════════════════════════════════════ -->
<header class="topbar">
    <a href="#" class="topbar-brand">
        <div class="logo-box"><i class="fa-solid fa-boxes-stacked"></i></div>
        Avenzur WMS
    </a>
    <div class="topbar-sep"></div>
    <div>
        <div class="topbar-title">WMS × Shopify Inventory Comparison</div>
        <div class="topbar-sub">Live reconciliation &mdash; shelf stock vs Shopify</div>
    </div>
    <?php if (!empty($met['location_id'])): ?>
    <span class="loc-badge">
        <i class="fa-solid fa-location-dot"></i>
        Location <?= htmlspecialchars($met['location_id']) ?>
    </span>
    <?php endif; ?>
    <div class="topbar-right">
        <span class="ts">
            <i class="fa-regular fa-clock"></i>
            <?= date('d M Y, H:i', strtotime($met['synced_at'])) ?>
        </span>
        <a href="?" class="btn btn-primary">
            <i class="fa-solid fa-rotate"></i> Refresh
        </a>
    </div>
</header>

<!-- ═══ PAGE ══════════════════════════════════════════════════════════════════ -->
<div class="page-wrap">

    <!-- METRICS ─────────────────────────────────────────────────────────────── -->
    <div class="section-lbl">Overview</div>
    <div class="metrics-grid">
        <?php
        $cards = [
            ['blue',   'fa-cart-flatbed',        'WMS',     $met['wms_purchased'],    'Purchased SKUs'],
            ['teal',   'fa-layer-group',          'WMS',     $met['wms_shelved'],      'Shelved SKUs'],
            ['purple', 'fa-brands fa-shopify',    'Shopify', $met['shopify_active'],   'Active w/ Barcode'],
            ['orange', 'fa-warehouse',            'Units',   $met['wms_total_units'],  'WMS Sellable Units'],
            ['cyan',   'fa-chart-bar',            'Units',   $met['shopify_total_units'],'Shopify On-Hand'],
            ['rose',   'fa-circle-pause',         'Hold',    $met['total_reserved'],   'Reserved (Orders)'],
            ['amber',  'fa-clock-rotate-left',    'Zone',    $met['total_expiry'],     'Expiry Zone Units'],
            ['red',    'fa-box-archive',          'Damaged', $met['total_damaged'],    'Damaged Box Units'],
        ];
        $stripe_colors = [
            'blue'=>'#1d6fce','teal'=>'#0d9488','purple'=>'#7c3aed','orange'=>'#ea580c',
            'cyan'=>'#0284c7','rose'=>'#e11d48','amber'=>'#d97706','red'=>'#dc2626',
        ];
        $icon_colors = [
            'blue'=>['#e8f1fb','#1d6fce'],'teal'=>['#e0f7f5','#0d9488'],
            'purple'=>['#f0ebff','#7c3aed'],'orange'=>['#fff1e8','#ea580c'],
            'cyan'=>['#e0f4ff','#0284c7'],'rose'=>['#fff1f2','#e11d48'],
            'amber'=>['#fef9c3','#d97706'],'red'=>['#fee2e2','#dc2626'],
        ];
        foreach ($cards as [$color, $icon, $tag, $val, $lbl]):
            [$ibg, $ic] = $icon_colors[$color];
            $stripe = $stripe_colors[$color];
        ?>
        <div class="mc">
            <div class="mc-stripe" style="background:<?= $stripe ?>"></div>
            <div class="mc-head">
                <div class="mc-icon" style="background:<?= $ibg ?>;color:<?= $ic ?>">
                    <i class="fa-solid <?= $icon ?>"></i>
                </div>
                <span class="mc-tag" style="background:<?= $ibg ?>;color:<?= $ic ?>"><?= $tag ?></span>
            </div>
            <div class="mc-body">
                <div class="mc-val"><?= number_format((int)$val) ?></div>
                <div class="mc-lbl"><?= $lbl ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- BANNER ──────────────────────────────────────────────────────────────── -->
    <div class="banner">
        <i class="fa-solid fa-circle-info"></i>
        <span>
            <strong>Sellable</strong> = total WMS shelved − damaged (racks E201*, E202*, box F101*).
            &nbsp;&nbsp;<strong>Reserved</strong> = units on pending/processing orders.
            &nbsp;&nbsp;<strong>Variance</strong> = Sellable − Shopify Qty.
            &nbsp;&nbsp;<strong style="color:#b91c1c">Negative = Critical</strong> — Shopify reports more than WMS holds.
            &nbsp;&nbsp;<strong><?= $empty_total ?> Shopify products</strong> carry no barcode and appear in the side panel →
        </span>
    </div>

    <!-- SPLIT ───────────────────────────────────────────────────────────────── -->
    <div class="split">

        <!-- ── MAIN TABLE ────────────────────────────────────────────────── -->
        <div class="card">
            <form method="get" id="ff">
                <div class="toolbar">
                    <div class="tbl-title">
                        <i class="fa-solid fa-table-list"></i>
                        Product Inventory Breakdown
                        <span class="cnt-badge"><?= number_format($total) ?> products</span>
                    </div>
                    <div class="tb-right">
                        <div class="legend">
                            <div class="li"><div class="ld" style="background:#dc2626"></div>Critical</div>
                            <div class="li"><div class="ld" style="background:#ca8a04"></div>Low Stock</div>
                            <div class="li"><div class="ld" style="background:#0284c7"></div>Surplus</div>
                            <div class="li"><div class="ld" style="background:#16a34a"></div>Matched</div>
                        </div>
                        <div class="srch">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" name="search" placeholder="Barcode or product name…" value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <select name="status" class="fs" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="critical" <?= $status_f==='critical'?'selected':'' ?>>Critical</option>
                            <option value="low"      <?= $status_f==='low'     ?'selected':'' ?>>Low Stock</option>
                            <option value="surplus"  <?= $status_f==='surplus' ?'selected':'' ?>>WMS Surplus</option>
                            <option value="matched"  <?= $status_f==='matched' ?'selected':'' ?>>Matched</option>
                            <option value="oos"      <?= $status_f==='oos'     ?'selected':'' ?>>Out of Stock</option>
                        </select>
                        <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                        <input type="hidden" name="dir"  value="<?= htmlspecialchars(strtolower($dir)) ?>">
                        <input type="hidden" name="page" value="0">
                        <button type="submit" class="btn btn-primary" style="padding:6px 13px;font-size:12px">
                            <i class="fa-solid fa-magnifying-glass"></i> Search
                        </button>
                        <?php if ($search || $status_f): ?>
                        <a href="?" class="btn btn-danger" style="padding:6px 13px;font-size:12px">
                            <i class="fa-solid fa-xmark"></i> Clear
                        </a>
                        <?php endif; ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['export' => '1'])) ?>"
                           class="btn btn-outline" style="padding:6px 13px;font-size:12px">
                            <i class="fa-solid fa-file-excel" style="color:#16a34a"></i> Export
                        </a>
                    </div>
                </div>
            </form>

            <div class="tw">
                <table>
                    <colgroup>
                        <col class="col-num">
                        <col class="col-barcode">
                        <col class="col-product">
                        <col class="col-price"><!-- cost -->
                        <col class="col-price"><!-- sale -->
                        <col class="col-qty"><!-- on hand -->
                        <col class="col-qty"><!-- damaged -->
                        <col class="col-qty"><!-- expiry -->
                        <col class="col-qty"><!-- reserved -->
                        <col class="col-sell"><!-- sellable -->
                        <col class="col-qty"><!-- shopify -->
                        <col class="col-var"><!-- variance -->
                        <col class="col-status"><!-- status -->
                    </colgroup>
                    <thead>
                        <tr>
                            <th>#</th>
                            <?= thSort('barcode',      'Barcode',      $sort, $dir, 'class="th-left"') ?>
                            <?= thSort('title',        'Product',      $sort, $dir, 'class="th-left"') ?>
                            <th>Cost Price</th>
                            <th>Sale Price</th>
                            <?= thSort('shelved_qty',  'On Hand',      $sort, $dir) ?>
                            <?= thSort('damaged_qty',  'Damaged',      $sort, $dir) ?>
                            <?= thSort('expiry_qty',   'Expiry',       $sort, $dir) ?>
                            <?= thSort('reserved_qty', 'Reserved',     $sort, $dir) ?>
                            <?= thSort('sellable_qty', 'Sellable',     $sort, $dir) ?>
                            <?= thSort('shopify_qty',  'Shopify Qty',  $sort, $dir) ?>
                            <?= thSort('variance',     'Variance',     $sort, $dir) ?>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="13">
                            <div class="empty">
                                <i class="fa-solid fa-box-open"></i>
                                <p>No products found<?= $search ? ' for &ldquo;<strong>' . htmlspecialchars($search) . '</strong>&rdquo;' : '' ?>.</p>
                            </div>
                        </td></tr>
                    <?php else:
                        foreach ($rows as $i => $row):
                            [$bc, $bl, $rc] = statusInfo($row);
                    ?>
                        <tr class="<?= $rc ?>">
                            <td class="col-num-cell"><?= $offset_start + $i ?></td>
                            <td class="td-left">
                                <span class="bcode"><?= htmlspecialchars($row['barcode']) ?></span>
                            </td>
                            <td class="td-left prod-cell">
                                <div class="prod-name"><?= htmlspecialchars($row['title']) ?></div>
                                <div class="prod-loc">
                                    <i class="fa-solid fa-location-dot" style="font-size:9px;opacity:.5"></i>
                                    <?= htmlspecialchars($row['location_id']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="price-wrap">
                                    <span class="price-label">Cost</span>
                                    <span class="price-val"><?= fmtSAR($row['unit_cost']) ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="price-wrap">
                                    <span class="price-label">Sale</span>
                                    <span class="price-val"><?= fmtSAR($row['price']) ?></span>
                                </div>
                            </td>
                            <td class="<?= qtyClass($row['shelved_qty']) ?>"><?= fmt($row['shelved_qty']) ?></td>
                            <td class="<?= qtyClass($row['damaged_qty'],'damaged') ?>"><?= fmt($row['damaged_qty']) ?></td>
                            <td class="<?= qtyClass($row['expiry_qty'], 'expiry')  ?>"><?= fmt($row['expiry_qty'])  ?></td>
                            <td class="<?= qtyClass($row['reserved_qty'],'reserved') ?>"><?= fmt($row['reserved_qty']) ?></td>
                            <td class="<?= qtyClass($row['sellable_qty']) ?>"><strong><?= fmt($row['sellable_qty']) ?></strong></td>
                            <td class="qty"><?= fmt($row['shopify_qty']) ?></td>
                            <td><?= varBadge($row['variance']) ?></td>
                            <td><span class="sb <?= $bc ?>"><span class="d"></span><?= $bl ?></span></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="tbl-foot">
                <div class="tbl-foot-info">
                    <?php if ($total > 0): ?>
                        Showing <strong><?= number_format($offset_start) ?> – <?= number_format($offset_end) ?></strong>
                        of <strong><?= number_format($total) ?></strong> products
                    <?php else: ?>No products found<?php endif; ?>
                </div>
                <?php if ($total_pages > 1):
                    $sp = max(0, $page - 3); $ep = min($total_pages - 1, $page + 3);
                ?>
                <nav class="pag">
                    <a href="?<?= http_build_query(array_merge($_GET,['page'=>$page-1])) ?>"
                       class="pb <?= $page===0?'disabled':'' ?>" aria-label="Previous page">
                        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                    </a>
                    <?php
                    if ($sp > 0) {
                        echo '<a href="?'.http_build_query(array_merge($_GET,['page'=>0])).'" class="pb" aria-label="Page 1">1</a>';
                        if ($sp > 1) echo '<span style="padding:0 3px;color:var(--muted);font-size:12px">…</span>';
                    }
                    for ($p = $sp; $p <= $ep; $p++):
                    ?>
                        <a href="?<?= http_build_query(array_merge($_GET,['page'=>$p])) ?>"
                           class="pb <?= $p===$page?'active':'' ?>"
                           aria-label="Page <?= $p+1 ?>"><?= $p+1 ?></a>
                    <?php endfor;
                    if ($ep < $total_pages - 1) {
                        if ($ep < $total_pages - 2) echo '<span style="padding:0 3px;color:var(--muted);font-size:12px">…</span>';
                        echo '<a href="?'.http_build_query(array_merge($_GET,['page'=>$total_pages-1])).'" class="pb" aria-label="Page '.$total_pages.'">'.$total_pages.'</a>';
                    }
                    ?>
                    <a href="?<?= http_build_query(array_merge($_GET,['page'=>$page+1])) ?>"
                       class="pb <?= $page>=$total_pages-1?'disabled':'' ?>" aria-label="Next page">
                        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                    </a>
                </nav>
                <?php endif; ?>
            </div>
        </div><!-- /main table -->

        <!-- ── SIDE PANEL ─────────────────────────────────────────────────── -->
        <div class="side-panel card">

            <!-- Header -->
            <div class="sp-header">
                <div class="sp-header-icon"><i class="fa-solid fa-barcode"></i></div>
                <div>
                    <div class="sp-title">Empty Barcode Products</div>
                    <div class="sp-sub">Cannot match to WMS stock</div>
                </div>
                <span class="sp-count"><?= $empty_total ?></span>
            </div>

            <!-- Ledger -->
            <div class="sp-ledger">
                <div class="spl-cell spl-red">
                    <div class="spl-val"><?= number_format((int)$empty_summary['total']) ?></div>
                    <div class="spl-lbl">Total No-Barcode</div>
                </div>
                <div class="spl-cell spl-amber">
                    <div class="spl-val"><?= number_format((int)$empty_summary['has_stock']) ?></div>
                    <div class="spl-lbl">Has Live Stock</div>
                </div>
                <div class="spl-cell spl-blue">
                    <div class="spl-val"><?= number_format((int)$empty_summary['has_sku']) ?></div>
                    <div class="spl-lbl">Has SKU, No Barcode</div>
                </div>
                <div class="spl-cell spl-muted">
                    <div class="spl-val"><?= number_format((int)$empty_summary['no_sku']) ?></div>
                    <div class="spl-lbl">No SKU &amp; No Barcode</div>
                </div>
                <div class="spl-cell spl-green">
                    <div class="spl-val"><?= number_format((int)$empty_summary['has_price']) ?></div>
                    <div class="spl-lbl">Has Price Set</div>
                </div>
                <div class="spl-cell spl-blue">
                    <div class="spl-val"><?= number_format((int)$empty_summary['total_units']) ?></div>
                    <div class="spl-lbl">Total Shopify Units</div>
                </div>
            </div>

            <!-- Why -->
            <div class="sp-why">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <div>
                    <strong><?= number_format((int)$empty_summary['has_sku']) ?></strong> have a SKU but barcode not filled — fix by mapping to WMS code.
                    <strong><?= number_format((int)$empty_summary['no_sku']) ?></strong> are incomplete drafts.
                    <strong style="color:var(--red)"><?= number_format((int)$empty_summary['has_stock']) ?></strong> carry live inventory and need urgent attention.
                </div>
            </div>

            <!-- Search -->
            <div class="sp-search">
                <form method="get">
                    <?php foreach ($_GET as $k => $v):
                        if ($k === 'esearch') continue; ?>
                        <input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars($v) ?>">
                    <?php endforeach; ?>
                    <div class="srch" style="width:100%">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="esearch" placeholder="Search title or SKU…"
                               value="<?= htmlspecialchars($empty_search) ?>"
                               style="width:100%">
                    </div>
                </form>
            </div>

            <!-- List -->
            <div class="sp-list">
                <?php if (empty($empty_rows)): ?>
                    <div class="empty" style="padding:32px 16px">
                        <i class="fa-solid fa-check-circle" style="color:var(--green);opacity:1"></i>
                        <p>All Shopify products have barcodes.</p>
                    </div>
                <?php else:
                    foreach ($empty_rows as $ep):
                        $has_stock = (int)$ep['inventory_quantity'] > 0;
                ?>
                    <div class="sp-item <?= $has_stock ? 'has-stock' : '' ?>">
                        <div class="sp-name"><?= htmlspecialchars($ep['title']) ?></div>
                        <div class="sp-row">
                            <?php if (!empty($ep['sku'])): ?>
                                <span class="sp-sku"><?= htmlspecialchars($ep['sku']) ?></span>
                            <?php else: ?>
                                <span class="sp-no-sku">No SKU</span>
                            <?php endif; ?>
                            <span class="sp-qty <?= $has_stock ? '' : 'zero' ?>">
                                <?= number_format((int)$ep['inventory_quantity']) ?> units
                            </span>
                            <span class="sp-tag">No Barcode</span>
                        </div>
                        <div class="sp-row" style="margin-top:4px">
                            <?php if ($ep['unit_cost'] > 0): ?>
                                <span class="sp-cost">Cost: SAR <?= number_format((float)$ep['unit_cost'],2) ?></span>
                            <?php endif; ?>
                            <?php if ($ep['price'] > 0): ?>
                                <span class="sp-price">Sale: SAR <?= number_format((float)$ep['price'],2) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>

        </div><!-- /side panel -->

    </div><!-- /split -->

</div><!-- /page-wrap -->
</body>
</html>
