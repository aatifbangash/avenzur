<?php defined('BASEPATH') or exit('No direct script access allowed');

$groupUserCount = 0;
$activeGroupUserCount = 0;
$groupUsers = [];

if (!empty($id)) {
    $groupUserCount = (int) $this->db->where('group_id', (int) $id)->count_all_results('users');
    $groupUsers = $this->db
        ->select('id, first_name, last_name, email, active')
        ->from('users')
        ->where('group_id', (int) $id)
        ->order_by('active', 'DESC')
        ->order_by('first_name', 'ASC')
        ->limit(8)
        ->get()
        ->result();

    foreach ($groupUsers as $groupUser) {
        if ((int) $groupUser->active === 1) {
            $activeGroupUserCount++;
        }
    }
}
?>
<style>
    .permission-page {
        display: grid;
        gap: 18px;
    }

    .permission-overview {
        display: grid;
        grid-template-columns: minmax(0, 1.6fr) minmax(320px, 0.9fr);
        gap: 18px;
    }

    .permission-hero,
    .permission-impact,
    .permission-toolbar,
    .permission-table-wrap,
    .permission-actions {
        background: #fff;
        border: 1px solid #d9e2ec;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
    }

    .permission-hero,
    .permission-impact,
    .permission-toolbar,
    .permission-actions {
        padding: 20px;
    }

    .permission-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border-radius: 999px;
        background: #eff8ff;
        color: #175cd3;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .permission-title-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        margin: 14px 0 16px;
    }

    .permission-title-row h3 {
        margin: 0;
        font-size: 24px;
        line-height: 1.2;
        font-weight: 700;
        color: #101828;
    }

    .permission-note {
        margin: 0;
        color: #475467;
        line-height: 1.6;
    }

    .permission-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 16px;
    }

    .permission-meta span,
    .permission-stat {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 42px;
        padding: 10px 12px;
        border-radius: 12px;
        background: #f8fafc;
        color: #344054;
        font-weight: 600;
    }

    .permission-stat {
        flex-direction: column;
        align-items: flex-start;
        min-width: 150px;
        background: linear-gradient(180deg, #f8fbff 0%, #eef4ff 100%);
        color: #1d4ed8;
    }

    .permission-stat strong {
        font-size: 22px;
        line-height: 1;
        color: #101828;
    }

    .permission-impact h4 {
        margin: 0 0 8px;
        font-size: 17px;
        font-weight: 700;
        color: #101828;
    }

    .permission-user-list {
        display: grid;
        gap: 10px;
        margin-top: 16px;
    }

    .permission-user-pill {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        border: 1px solid #e4e7ec;
        border-radius: 14px;
        background: #f8fafc;
    }

    .permission-user-pill strong,
    .permission-user-pill span {
        display: block;
    }

    .permission-user-pill strong {
        color: #101828;
    }

    .permission-user-pill span {
        color: #667085;
        font-size: 12px;
    }

    .permission-status {
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .permission-status.is-active {
        background: #ecfdf3;
        color: #027a48;
    }

    .permission-status.is-inactive {
        background: #fef3f2;
        color: #b42318;
    }

    .permission-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .permission-toolbar-left,
    .permission-toolbar-right {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .permission-search {
        min-width: 280px;
        max-width: 360px;
    }

    .permission-search .form-control {
        height: 44px;
        border-radius: 12px;
        border-color: #d0d5dd;
        box-shadow: none;
    }

    .permission-search .form-control:focus {
        border-color: #98a2b3;
        box-shadow: none;
    }

    .permission-toolbar-right .permission-note {
        max-width: 420px;
    }

    .permission-table-wrap {
        padding: 18px;
        overflow: hidden;
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
    }

    .permission-section-stack {
        display: grid;
        gap: 18px;
    }

    .permission-section {
        display: grid;
        gap: 14px;
    }

    .permission-section-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        padding: 18px 20px;
        border: 1px solid #d9e2ec;
        border-radius: 18px;
        background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%);
    }

    .permission-section-header h4 {
        margin: 0 0 4px;
        font-size: 18px;
        font-weight: 700;
        color: #101828;
    }

    .permission-section-header p {
        margin: 0;
        color: #475467;
        line-height: 1.5;
    }

    .permission-section-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        height: 38px;
        padding: 0 12px;
        border-radius: 999px;
        background: #fff;
        border: 1px solid #d0d5dd;
        color: #175cd3;
        font-weight: 700;
    }

    .permission-matrix {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 12px;
        margin: 0;
        background: transparent;
    }

    .permission-matrix thead {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        border: 0;
    }

    .permission-matrix tbody tr {
        display: grid;
        grid-template-columns: minmax(220px, 1.05fr) minmax(420px, 1.2fr) minmax(320px, 1fr);
        gap: 12px;
        align-items: stretch;
        padding: 14px;
        border: 1px solid #d9e2ec;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
    }

    .permission-matrix tbody td {
        border: 0 !important;
        background: transparent !important;
        padding: 0 !important;
        vertical-align: top;
    }

    .permission-matrix tbody td:first-child {
        display: flex;
        align-items: flex-start;
        flex-direction: column;
        gap: 12px;
        min-width: 0;
        padding-right: 10px !important;
        font-weight: 700;
        color: #101828;
    }

    .permission-module-name {
        display: block;
        font-size: 16px;
        line-height: 1.35;
    }

    .permission-module-copy {
        display: grid;
        gap: 8px;
    }

    .permission-module-note {
        margin: 0;
        font-size: 13px;
        font-weight: 500;
        line-height: 1.5;
        color: #667085;
    }

    .permission-module-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 10px;
        border-radius: 999px;
        background: #f5f8ff;
        color: #3538cd;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .permission-row-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 28px;
        padding: 0 10px;
        border-radius: 999px;
        background: #eef2ff;
        color: #3538cd;
        font-size: 12px;
        font-weight: 700;
    }

    .permission-module-metric {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .permission-crud-panel,
    .permission-extra-panel,
    .permission-report-panel,
    .permission-utility-panel {
        display: grid;
        gap: 10px;
        min-height: 100%;
        padding: 12px !important;
        border: 1px solid #e4e7ec;
        border-radius: 14px;
        background: #fcfcfd !important;
    }

    .permission-crud-panel {
        border-top: 3px solid #2563eb;
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%) !important;
    }

    .permission-extra-panel {
        position: relative;
        border-top: 3px solid #f59e0b;
        border-left: 1px solid #f4d9a6;
        background: linear-gradient(180deg, #fffaf0 0%, #ffffff 100%) !important;
    }

    .permission-extra-panel::before {
        content: '';
        position: absolute;
        left: -8px;
        top: 8px;
        bottom: 8px;
        width: 1px;
        background: #d0d5dd;
    }

    .permission-report-panel {
        border-top: 3px solid #7c3aed;
    }

    .permission-utility-panel {
        border-top: 3px solid #0f766e;
    }

    .permission-panel-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
        padding-bottom: 6px;
        border-bottom: 1px solid #e4e7ec;
    }

    .permission-panel-header strong {
        color: #101828;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }

    .permission-panel-header span {
        color: #667085;
        font-size: 11px;
        line-height: 1.35;
    }

    .permission-crud-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 6px;
    }

    .permission-toggle-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        min-height: 0;
        padding: 7px 9px;
        border: 1px solid #d9e2ec;
        border-radius: 9px;
        background: #fff;
    }

    .permission-toggle-card strong,
    .permission-report-card strong {
        display: block;
        color: #101828;
        font-size: 12px;
        line-height: 1.15;
    }

    .permission-toggle-card > div:first-child {
        min-width: 0;
        flex: 1 1 auto;
    }

    .permission-toggle-card input[type="checkbox"],
    .permission-report-card input[type="checkbox"],
    .permission-utility-card input[type="checkbox"],
    .permission-extra-item input[type="checkbox"] {
        margin: 0;
        flex: 0 0 auto;
        transform: scale(1.15);
    }

    .permission-report-card p {
        margin: 4px 0 0;
        color: #667085;
        font-size: 12px;
        line-height: 1.5;
    }

    .permission-extra-items,
    .permission-utility-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-content: flex-start;
    }

    .permission-extra-item,
    .permission-utility-card {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 0;
        padding: 7px 9px;
        border: 1px solid #d9e2ec;
        border-radius: 9px;
        background: #fff;
    }

    .permission-extra-item {
        border-color: #f4d9a6;
        background: #fffdf7;
    }

    .permission-extra-item label,
    .permission-utility-card label {
        margin: 0;
        color: #344054;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.25;
    }

    .permission-report-groups {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 8px;
        align-items: start;
    }

    .permission-report-group {
        display: grid;
        gap: 6px;
        padding: 10px;
        border: 1px solid #d9e2ec;
        border-radius: 11px;
        background: #fff;
    }

    .permission-report-group-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
    }

    .permission-report-group-header > div {
        display: grid;
        gap: 2px;
        min-width: 0;
    }

    .permission-report-group-header strong {
        font-size: 13px;
        color: #101828;
        line-height: 1.2;
    }

    .permission-report-group-header span {
        display: block;
        color: #667085;
        font-size: 11px;
        line-height: 1.3;
    }

    .permission-report-items {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 6px;
    }

    .permission-report-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 7px 9px;
        border: 1px solid #eaecf0;
        border-radius: 9px;
        background: #f8fafc;
        min-height: 0;
    }

    .permission-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .permission-actions p {
        margin: 0;
        color: #475467;
    }

    @media (max-width: 1200px) {
        .permission-matrix tbody tr {
            grid-template-columns: minmax(220px, 1fr) minmax(280px, 1fr);
        }

        .permission-crud-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .permission-extra-panel,
        .permission-report-panel,
        .permission-utility-panel {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 991px) {
        .permission-overview {
            grid-template-columns: 1fr;
        }

        .permission-title-row {
            flex-direction: column;
        }

        .permission-search {
            min-width: 100%;
            max-width: 100%;
        }

        .permission-matrix tbody tr {
            grid-template-columns: 1fr;
        }

        .permission-crud-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .permission-report-groups,
        .permission-report-items {
            grid-template-columns: 1fr;
        }

        .permission-extra-panel::before {
            display: none;
        }

        .permission-matrix tbody td:first-child,
        .permission-extra-panel,
        .permission-report-panel,
        .permission-utility-panel {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 640px) {
        .permission-toolbar,
        .permission-actions,
        .permission-toolbar-left,
        .permission-toolbar-right {
            align-items: stretch;
        }

        .permission-toolbar-left .btn,
        .permission-actions .btn {
            width: 100%;
        }

        .permission-toggle-card,
        .permission-report-card,
        .permission-section-header,
        .permission-panel-header {
            flex-direction: column;
        }
    }
</style>
<script>
    $(function () {
        var $matrix = $('.permission-matrix');
        var $tbody = $matrix.find('tbody');
        var $wrap = $('.permission-table-wrap');
        var $search = $('#permission-module-search');
        var $checkedCount = $('#permission-checked-count');
        var crudLabels = [
            { key: 'View', note: 'Open and inspect records in this module.' },
            { key: 'Add', note: 'Create new records in this module.' },
            { key: 'Edit', note: 'Change existing records in this module.' },
            { key: 'Delete', note: 'Remove records from this module.' }
        ];
        var moduleNotes = {
            products: 'Manage catalog records and product-level maintenance tools.',
            sales: 'Control core sales workflow access and related sale actions.',
            deliveries: 'Handle shipment creation and delivery record changes.',
            gift_cards: 'Maintain gift card records and issued balances.',
            quotes: 'Work with quotations before they become confirmed sales.',
            purchases: 'Control vendor purchasing flow and financial follow-up actions.',
            transfers: 'Move stock between warehouses and export transfer records.',
            returns: 'Process return records and related customer communication.',
            customers: 'Manage customer master data and deposit handling.',
            suppliers: 'Maintain supplier master data without operational extras.',
            reports: 'Report access is separated by topic so users can find the exact analysis screens they need.',
            misc: 'These are cross-module utilities that affect workflow behavior.'
        };
        var reportGroups = [
            { key: 'inventory', title: 'Inventory & Products', note: 'Stock visibility, catalog analysis, and expiry monitoring.' },
            { key: 'sales', title: 'Sales Performance', note: 'Revenue movement, sales trends, and payment summaries.' },
            { key: 'purchases', title: 'Purchases & Costs', note: 'Supplier buying activity, spending, and tax totals.' },
            { key: 'people', title: 'People & Partners', note: 'Customer, supplier, and staff reporting access.' }
        ];

        function slugify(text) {
            return (text || '').toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
        }

        function cloneHtml($node) {
            return $('<div>').append($node.clone()).html();
        }

        function escapeHtml(text) {
            return $('<div>').text(text || '').html();
        }

        function buildModuleCell(titleText, tagText, noteText) {
            return '' +
                '<td>' +
                    '<div class="permission-module-copy">' +
                        '<span class="permission-module-tag"><i class="fa fa-folder-open-o"></i> ' + escapeHtml(tagText) + '</span>' +
                        '<span class="permission-module-name">' + escapeHtml(titleText) + '</span>' +
                        '<p class="permission-module-note">' + escapeHtml(noteText) + '</p>' +
                    '</div>' +
                    '<div class="permission-module-metric">' +
                        '<span class="permission-row-count">0</span>' +
                    '</div>' +
                '</td>';
        }

        function classifyReport(name, label) {
            var value = ((name || '') + ' ' + (label || '')).toLowerCase();
            if (value.indexOf('quantity') !== -1 || value.indexOf('expiry') !== -1 || value.indexOf('product') !== -1) {
                return 'inventory';
            }
            if (value.indexOf('purchase') !== -1 || value.indexOf('expense') !== -1 || value.indexOf('tax') !== -1) {
                return 'purchases';
            }
            if (value.indexOf('customer') !== -1 || value.indexOf('supplier') !== -1 || value.indexOf('staff') !== -1) {
                return 'people';
            }
            return 'sales';
        }

        $tbody.find('tr').each(function () {
            var $row = $(this);
            var $cells = $row.children('td');
            var titleText = $.trim($cells.eq(0).text());
            var slug = slugify(titleText);
            var noteText = moduleNotes[slug] || 'Review which team members can access this area and what they can do inside it.';

            if (slug === 'reports') {
                var groupedReports = {
                    inventory: [],
                    sales: [],
                    purchases: [],
                    people: []
                };

                $cells.eq(1).find('span').each(function () {
                    var $item = $(this);
                    var $input = $item.find('input').first();
                    var $label = $item.find('label').first();
                    if (!$input.length || !$label.length) {
                        return;
                    }
                    var labelText = $.trim($label.text());
                    var groupKey = classifyReport($input.attr('name'), labelText);
                    groupedReports[groupKey].push(
                        '<div class="permission-report-card">' +
                            '<div>' +
                                '<strong>' + escapeHtml(labelText) + '</strong>' +
                            '</div>' +
                            cloneHtml($input) +
                        '</div>'
                    );
                });

                var reportsMarkup = '';
                $.each(reportGroups, function (_, group) {
                    if (!groupedReports[group.key].length) {
                        return;
                    }
                    reportsMarkup +=
                        '<div class="permission-report-group">' +
                            '<div class="permission-report-group-header">' +
                                '<div>' +
                                    '<strong>' + escapeHtml(group.title) + '</strong>' +
                                    '<span>' + escapeHtml(group.note) + '</span>' +
                                '</div>' +
                                '<span>' + groupedReports[group.key].length + ' items</span>' +
                            '</div>' +
                            '<div class="permission-report-items">' + groupedReports[group.key].join('') + '</div>' +
                        '</div>';
                });

                $row.attr('data-section', 'reports').html(
                    buildModuleCell(titleText, 'Report Access', noteText) +
                    '<td class="permission-report-panel" colspan="2">' +
                        '<div class="permission-panel-header">' +
                            '<strong>Reports are grouped by purpose</strong>' +
                            '<span>Keep operational users out of analytics they do not need.</span>' +
                        '</div>' +
                        '<div class="permission-report-groups">' + reportsMarkup + '</div>' +
                    '</td>'
                );
                return;
            }

            if (slug === 'misc') {
                var utilityItems = [];
                $cells.eq(1).find('span').each(function () {
                    var $item = $(this);
                    var $input = $item.find('input').first();
                    var $label = $item.find('label').first();
                    if (!$input.length || !$label.length) {
                        return;
                    }
                    utilityItems.push(
                        '<div class="permission-utility-card">' +
                            cloneHtml($input) +
                            '<label for="' + escapeHtml($input.attr('id') || '') + '">' + escapeHtml($.trim($label.text())) + '</label>' +
                        '</div>'
                    );
                });

                $row.attr('data-section', 'utilities').html(
                    buildModuleCell(titleText, 'Workflow Utilities', noteText) +
                    '<td class="permission-utility-panel" colspan="2">' +
                        '<div class="permission-panel-header">' +
                            '<strong>Global utilities</strong>' +
                            '<span>Separate from CRUD because these affect broader workflow behavior.</span>' +
                        '</div>' +
                        '<div class="permission-utility-grid">' + utilityItems.join('') + '</div>' +
                    '</td>'
                );
                return;
            }

            var crudMarkup = [];
            $.each(crudLabels, function (index, item) {
                var $input = $cells.eq(index + 1).find('input').first();
                crudMarkup.push(
                    '<div class="permission-toggle-card">' +
                        '<div>' +
                            '<strong>' + item.key + '</strong>' +
                        '</div>' +
                        ($input.length ? cloneHtml($input) : '') +
                    '</div>'
                );
            });

            var extraItems = [];
            $cells.eq(5).find('span').each(function () {
                var $item = $(this);
                var $input = $item.find('input').first();
                var $label = $item.find('label').first();
                if (!$input.length || !$label.length) {
                    return;
                }
                extraItems.push(
                    '<div class="permission-extra-item">' +
                        cloneHtml($input) +
                        '<label for="' + escapeHtml($input.attr('id') || '') + '">' + escapeHtml($.trim($label.text())) + '</label>' +
                    '</div>'
                );
            });

            $row.attr('data-section', 'modules').html(
                buildModuleCell(titleText, 'Module Access', noteText) +
                '<td class="permission-crud-panel">' +
                    '<div class="permission-panel-header">' +
                        '<strong>Core CRUD permissions</strong>' +
                        '<span>Separate from the extra module actions.</span>' +
                    '</div>' +
                    '<div class="permission-crud-grid">' + crudMarkup.join('') + '</div>' +
                '</td>' +
                '<td class="permission-extra-panel">' +
                    '<div class="permission-panel-header">' +
                        '<strong>Additional actions</strong>' +
                        '<span>Separate tools beyond add, edit, and delete.</span>' +
                    '</div>' +
                    (extraItems.length ? '<div class="permission-extra-items">' + extraItems.join('') + '</div>' : '<div class="permission-empty-state">No extra actions in this module.</div>') +
                '</td>'
            );
        });

        var sections = [
            {
                key: 'modules',
                title: 'Module Permissions',
                note: 'Each row keeps CRUD operations separate from the extra actions tied to that module.'
            },
            {
                key: 'reports',
                title: 'Report Permissions',
                note: 'Reports are split by business purpose so the list is easier to scan and safer to assign.'
            },
            {
                key: 'utilities',
                title: 'Workflow Utilities',
                note: 'These are cross-cutting tools and special actions that do not belong inside a single CRUD block.'
            }
        ];

        var $sectionStack = $('<div class="permission-section-stack"></div>');
        $.each(sections, function (_, section) {
            var $rows = $tbody.find('tr[data-section="' + section.key + '"]');
            if (!$rows.length) {
                return;
            }

            var $sectionTable = $matrix.clone();
            $sectionTable.find('thead').remove();
            $sectionTable.find('tbody').html($rows.clone());

            var $section = $(
                '<div class="permission-section" data-group="' + section.key + '">' +
                    '<div class="permission-section-header">' +
                        '<div>' +
                            '<h4>' + escapeHtml(section.title) + '</h4>' +
                            '<p>' + escapeHtml(section.note) + '</p>' +
                        '</div>' +
                        '<span class="permission-section-count">' + $rows.length + '</span>' +
                    '</div>' +
                '</div>'
            );

            $section.append($sectionTable);
            $sectionStack.append($section);
        });

        $matrix.remove();
        $wrap.append($sectionStack);

        function updateCheckedCount() {
            $checkedCount.text($wrap.find('input[type="checkbox"]:checked').length);
            $wrap.find('tbody tr').each(function () {
                var count = $(this).find('input[type="checkbox"]:checked').length;
                $(this).find('.permission-row-count').text(count);
            });
        }

        function filterModules() {
            var query = ($search.val() || '').toLowerCase();
            $wrap.find('.permission-section').each(function () {
                var sectionVisible = false;
                $(this).find('tbody tr').each(function () {
                    var rowText = $(this).text().toLowerCase();
                    var matched = rowText.indexOf(query) !== -1;
                    $(this).toggle(matched);
                    if (matched) {
                        sectionVisible = true;
                    }
                });
                $(this).toggle(sectionVisible || !query);
            });
        }

        $('#permission-toggle-visible').on('click', function (e) {
            e.preventDefault();
            $wrap.find('tbody tr:visible input[type="checkbox"]').prop('checked', true);
            updateCheckedCount();
        });

        $('#permission-clear-visible').on('click', function (e) {
            e.preventDefault();
            $wrap.find('tbody tr:visible input[type="checkbox"]').prop('checked', false);
            updateCheckedCount();
        });

        $search.on('input', filterModules);
        $wrap.on('change', 'input[type="checkbox"]', updateCheckedCount);
        updateCheckedCount();
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-folder-open"></i><?= lang('group_permissions'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('set_permissions'); ?></p>

                <?php if (!empty($p)) {
    if ($p->group_id != 1) {
        echo admin_form_open('system_settings/permissions/' . $id); ?>
                        <div class="permission-page">
                            <div class="permission-overview">
                                <div class="permission-hero">
                                    <span class="permission-eyebrow"><i class="fa fa-shield"></i> Shared Permission Set</span>
                                    <div class="permission-title-row">
                                        <div>
                                            <h3><?= $group->description . ' (' . $group->name . ')'; ?></h3>
                                            <p class="permission-note">Edit the default access for everyone assigned to this group. This page controls the shared permission set used by those users.</p>
                                        </div>
                                        <div class="permission-stat">
                                            <i class="fa fa-check-square-o"></i>
                                            <span><strong id="permission-checked-count">0</strong> enabled flags</span>
                                        </div>
                                    </div>
                                    <div class="permission-meta">
                                        <span><i class="fa fa-users"></i> <?= $groupUserCount; ?> users in this group</span>
                                        <span><i class="fa fa-user-circle-o"></i> <?= $activeGroupUserCount; ?> active users shown</span>
                                        <span><i class="fa fa-link"></i> Group ID: <?= (int) $id; ?></span>
                                    </div>
                                </div>
                                <div class="permission-impact">
                                    <h4>Who Gets These Permissions</h4>
                                    <p class="permission-note">Anyone assigned to this group receives these defaults. If you need per-user overrides, that needs a backend feature, not just a screen change.</p>
                                    <div class="permission-user-list">
                                        <?php if (!empty($groupUsers)) { ?>
                                            <?php foreach ($groupUsers as $groupUser) { ?>
                                                <div class="permission-user-pill">
                                                    <div>
                                                        <strong><?= trim($groupUser->first_name . ' ' . $groupUser->last_name) ?: $groupUser->email; ?></strong>
                                                        <span><?= $groupUser->email; ?></span>
                                                    </div>
                                                    <span class="permission-status <?= (int) $groupUser->active === 1 ? 'is-active' : 'is-inactive'; ?>">
                                                        <?= (int) $groupUser->active === 1 ? 'Active' : 'Inactive'; ?>
                                                    </span>
                                                </div>
                                            <?php } ?>
                                            <?php if ($groupUserCount > count($groupUsers)) { ?>
                                                <p class="permission-note">Showing <?= count($groupUsers); ?> of <?= $groupUserCount; ?> users assigned to this group.</p>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <p class="permission-note">No users are currently assigned to this group.</p>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>

                            <div class="permission-toolbar">
                                <div class="permission-toolbar-left">
                                    <div class="permission-search">
                                        <input type="text" id="permission-module-search" class="form-control" placeholder="Search modules or permission labels">
                                    </div>
                                    <a href="#" class="btn btn-default" id="permission-toggle-visible"><i class="fa fa-check-square-o"></i> Enable visible</a>
                                    <a href="#" class="btn btn-default" id="permission-clear-visible"><i class="fa fa-square-o"></i> Clear visible</a>
                                </div>
                                <div class="permission-toolbar-right">
                                    <span class="permission-note">Search a module, then enable or clear only the items you can see.</span>
                                </div>
                            </div>

                            <div class="table-responsive permission-table-wrap">
                                <table class="table table-bordered table-hover table-striped reports-table permission-matrix">

                                <thead>
                                <tr>
                                    <th colspan="6"
                                        class="text-center"><?php echo $group->description . ' ( ' . $group->name . ' ) ' . $this->lang->line('group_permissions'); ?></th>
                                </tr>
                                <tr>
                                    <th rowspan="2" class="text-center"><?= lang('module_name'); ?>
                                    </th>
                                    <th colspan="5" class="text-center"><?= lang('permissions'); ?></th>
                                </tr>
                                <tr>
                                    <th class="text-center"><?= lang('view'); ?></th>
                                    <th class="text-center"><?= lang('add'); ?></th>
                                    <th class="text-center"><?= lang('edit'); ?></th>
                                    <th class="text-center"><?= lang('delete'); ?></th>
                                    <th class="text-center"><?= lang('misc'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td><?= lang('products'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="products-index" <?php echo $p->{'products-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="products-add" <?php echo $p->{'products-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="products-edit" <?php echo $p->{'products-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="products-delete" <?php echo $p->{'products-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <!--<span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="products-cost" class="checkbox" name="products-cost" <?php echo $p->{'products-cost'} ? 'checked' : ''; ?>>
                                            <label for="products-cost" class="padding05"><?= lang('product_cost') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="products-price" class="checkbox" name="products-price" <?php echo $p->{'products-price'} ? 'checked' : ''; ?>>
                                            <label for="products-price" class="padding05"><?= lang('product_price') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="products-adjustments" class="checkbox" name="products-adjustments" <?php echo $p->{'products-adjustments'} ? 'checked' : ''; ?>>
                                            <label for="products-adjustments" class="padding05"><?= lang('adjustments') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="products-barcode" class="checkbox" name="products-barcode" <?php echo $p->{'products-barcode'} ? 'checked' : ''; ?>>
                                            <label for="products-barcode" class="padding05"><?= lang('print_barcodes') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="products-stock_count" class="checkbox" name="products-stock_count" <?php echo $p->{'products-stock_count'} ? 'checked' : ''; ?>>
                                            <label for="products-stock_count" class="padding05"><?= lang('stock_counts') ?></label>
                                        </span>-->
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('sales'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-index" <?php echo $p->{'sales-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-add" <?php echo $p->{'sales-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-edit" <?php echo $p->{'sales-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-delete" <?php echo $p->{'sales-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-email" class="checkbox" name="sales-email" <?php echo $p->{'sales-email'} ? 'checked' : ''; ?>>
                                            <label for="sales-email" class="padding05"><?= lang('email') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-pdf" class="checkbox" name="sales-pdf" <?php echo $p->{'sales-pdf'} ? 'checked' : ''; ?>>
                                            <label for="sales-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <?php if (POS) {
            ?>
                                            <input type="checkbox" value="1" id="pos-index" class="checkbox" name="pos-index" <?php echo $p->{'pos-index'} ? 'checked' : ''; ?>>
                                            <label for="pos-index" class="padding05"><?= lang('pos') ?></label>
                                            <?php
        } ?>
                                        </span>

                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-add-label" class="checkbox" name="sales-add-label" <?php echo $p->{'sales-add-label'} ? 'checked' : ''; ?>>
                                            <label for="sales-add-label" class="padding05"><?= lang('Add Label') ?></label>
                                        </span>

                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-verify-label" class="checkbox" name="sales-verify-label" <?php echo $p->{'sales-verify-label'} ? 'checked' : ''; ?>>
                                            <label for="sales-verify-label" class="padding05"><?= lang('Verify Label') ?></label>
                                        </span>

                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-rsd" class="checkbox" name="sales-rsd" <?php echo $p->{'sales-rsd'} ? 'checked' : ''; ?>>
                                            <label for="sales-rsd" class="padding05"><?= lang('Send To RSD') ?></label>
                                        </span>

                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-create-invoice" class="checkbox" name="sales-create-invoice" <?php echo $p->{'sales-create-invoice'} ? 'checked' : ''; ?>>
                                            <label for="sales-create-invoice" class="padding05"><?= lang('Create Invoice') ?></label>
                                        </span>

                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-view-invoice" class="checkbox" name="sales-view-invoice" <?php echo $p->{'sales-view-invoice'} ? 'checked' : ''; ?>>
                                            <label for="sales-view-invoice" class="padding05"><?= lang('View Invoice') ?></label>
                                        </span>

                                        <!--<span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-payments" class="checkbox" name="sales-payments" <?php echo $p->{'sales-payments'} ? 'checked' : ''; ?>>
                                            <label for="sales-payments" class="padding05"><?= lang('payments') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-return_sales" class="checkbox" name="sales-return_sales" <?php echo $p->{'sales-return_sales'} ? 'checked' : ''; ?>>
                                            <label for="sales-return_sales" class="padding05"><?= lang('return_sales') ?></label>
                                        </span>
                                          <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-coordinator" class="checkbox" name="sales-coordinator" <?php echo $p->{'sales-coordinator'} ? 'checked' : ''; ?>>
                                            <label for="sales-coordinator" class="padding05"><?= lang('Sales Coordinator') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-warehouse_supervisor" class="checkbox" name="sales-warehouse_supervisor" <?php echo $p->{'sales-warehouse_supervisor'} ? 'checked' : ''; ?>>
                                            <label for="sales-warehouse_supervisor" class="padding05"><?= lang('Warehouse Supervisor') ?></label>
                                        </span>
                                        
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-warehouse_supervisor_shipping" class="checkbox" name="sales-warehouse_supervisor_shipping" <?php echo $p->{'sales-warehouse_supervisor_shipping'} ? 'checked' : ''; ?>>
                                            <label for="sales-warehouse_supervisor_shipping" class="padding05"><?= lang('Warehouse Supervisor Shipping') ?></label>
                                        </span>

                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-accountant" class="checkbox" name="sales-accountant" <?php echo $p->{'sales-accountant'} ? 'checked' : ''; ?>>
                                            <label for="sales-accountant" class="padding05"><?= lang('Accountant') ?></label>
                                        </span>

                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-quality_supervisor" class="quality_supervisor" name="sales-quality_supervisor" <?php echo $p->{'sales-quality_supervisor'} ? 'checked' : ''; ?>>
                                            <label for="sales-quality_supervisor" class="padding05"><?= lang('Quality Supervisor') ?></label>
                                        </span>-->
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('deliveries'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-deliveries" <?php echo $p->{'sales-deliveries'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-add_delivery" <?php echo $p->{'sales-add_delivery'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-edit_delivery" <?php echo $p->{'sales-edit_delivery'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-delete_delivery" <?php echo $p->{'sales-delete_delivery'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="sales-pdf" class="checkbox" name="sales-pdf_delivery" <?php echo $p->{'sales-pdf_delivery'} ? 'checked' : ''; ?>>
                                            <label for="sales-pdf_delivery" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                    </td>
                                </tr>
                                <!--<tr>
                                    <td><?= lang('gift_cards'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-gift_cards" <?php echo $p->{'sales-gift_cards'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-add_gift_card" <?php echo $p->{'sales-add_gift_card'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-edit_gift_card" <?php echo $p->{'sales-edit_gift_card'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="sales-delete_gift_card" <?php echo $p->{'sales-delete_gift_card'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>

                                    </td>
                                </tr>-->

                                <tr>
                                    <td><?= lang('quotes'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="quotes-index" <?php echo $p->{'quotes-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="quotes-add" <?php echo $p->{'quotes-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="quotes-edit" <?php echo $p->{'quotes-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="quotes-delete" <?php echo $p->{'quotes-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="quotes-pdf" class="checkbox" name="quotes-pdf" <?php echo $p->{'quotes-pdf'} ? 'checked' : ''; ?>>
                                            <label for="quotes-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('Contract Deals'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="contract-deals-index" <?php echo $p->{'contract-deals-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="contract-deals-add" <?php echo $p->{'contract-deals-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="contract-deals-edit" <?php echo $p->{'contract-deals-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="contract-deals-delete" <?php echo $p->{'contract-deals-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="contract-deals-email" class="checkbox" name="contract-deals-email" <?php echo $p->{'contract-deals-email'} ? 'checked' : ''; ?>>
                                            <label for="contract-deals-email" class="padding05"><?= lang('email') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="contract-deals-pdf" class="checkbox" name="contract-deals-pdf" <?php echo $p->{'contract-deals-pdf'} ? 'checked' : ''; ?>>
                                            <label for="po-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('Purchase Requisition'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="pr-index" <?php echo $p->{'pr-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="pr-add" <?php echo $p->{'pr-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="pr-edit" <?php echo $p->{'pr-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="pr-delete" <?php echo $p->{'pr-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="pr-email" class="checkbox" name="pr-email" <?php echo $p->{'pr-email'} ? 'checked' : ''; ?>>
                                            <label for="pr-email" class="padding05"><?= lang('email') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="pr-pdf" class="checkbox" name="pr-pdf" <?php echo $p->{'pr-pdf'} ? 'checked' : ''; ?>>
                                            <label for="pr-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('Purchase Order'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="po-index" <?php echo $p->{'po-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="po-add" <?php echo $p->{'po-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="po-edit" <?php echo $p->{'po-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="po-delete" <?php echo $p->{'po-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="po-approve" class="checkbox" name="po-approve" <?php echo $p->{'po-approve'} ? 'checked' : ''; ?>>
                                            <label for="po-approve" class="padding05"><?= lang('Approve PO') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="po-create-invoice" class="checkbox" name="po-create-invoice" <?php echo $p->{'po-create-invoice'} ? 'checked' : ''; ?>>
                                            <label for="po-create-invoice" class="padding05"><?= lang('Create Purchase Invoice') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="grn-add" class="checkbox" name="grn-add" <?php echo $p->{'grn-add'} ? 'checked' : ''; ?>>
                                            <label for="grn-add" class="padding05"><?= lang('Add GRN') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="po-email" class="checkbox" name="po-email" <?php echo $p->{'po-email'} ? 'checked' : ''; ?>>
                                            <label for="po-email" class="padding05"><?= lang('email') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="po-pdf" class="checkbox" name="po-pdf" <?php echo $p->{'po-pdf'} ? 'checked' : ''; ?>>
                                            <label for="po-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('purchases'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="purchases-index" <?php echo $p->{'purchases-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="purchases-add" <?php echo $p->{'purchases-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="purchases-edit" <?php echo $p->{'purchases-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="purchases-delete" <?php echo $p->{'purchases-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="purchases-email" class="checkbox" name="purchases-email" <?php echo $p->{'purchases-email'} ? 'checked' : ''; ?>>
                                            <label for="purchases-email" class="padding05"><?= lang('email') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="purchases-pdf" class="checkbox" name="purchases-pdf" <?php echo $p->{'purchases-pdf'} ? 'checked' : ''; ?>>
                                            <label for="purchases-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                        <!--<span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="purchases-payments" class="checkbox" name="purchases-payments" <?php echo $p->{'purchases-payments'} ? 'checked' : ''; ?>>
                                            <label for="purchases-payments" class="padding05"><?= lang('payments') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="purchases-expenses" class="checkbox" name="purchases-expenses" <?php echo $p->{'purchases-expenses'} ? 'checked' : ''; ?>>
                                            <label for="purchases-expenses" class="padding05"><?= lang('expenses') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="purchases-return_purchases" class="checkbox" name="purchases-return_purchases" <?php echo $p->{'purchases-return_purchases'} ? 'checked' : ''; ?>>
                                            <label for="purchases-return_purchases" class="padding05"><?= lang('return_purchases') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="purchase_supervisor" class="checkbox" name="purchase_supervisor" <?php echo $p->{'purchase_supervisor'} ? 'checked' : ''; ?>>
                                            <label for="purchase_supervisor" class="padding05"><?= lang('Purchase Supervisor') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="purchase_manager" class="checkbox" name="purchase_manager" <?php echo $p->{'purchase_manager'} ? 'checked' : ''; ?>>
                                            <label for="purchase_manager" class="padding05"><?= lang('Purchase Manager') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="receiving_supervisor" class="checkbox" name="purchase_receiving_supervisor" <?php echo $p->{'purchase_receiving_supervisor'} ? 'checked' : ''; ?>>
                                            <label for="receiving_supervisor" class="padding05"><?= lang('Receiving Supervisor') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="warehouse_supervisor" class="checkbox" name="purchase_warehouse_supervisor" <?php echo $p->{'purchase_warehouse_supervisor'} ? 'checked' : ''; ?>>
                                            <label for="warehouse_supervisor" class="padding05"><?= lang('Warehouse Supervisor') ?></label>
                                        </span>-->
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('transfers'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="transfers-index" <?php echo $p->{'transfers-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="transfers-add" <?php echo $p->{'transfers-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="transfers-edit" <?php echo $p->{'transfers-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="transfers-delete" <?php echo $p->{'transfers-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="transfers-email" class="checkbox" name="transfers-email" <?php echo $p->{'transfers-email'} ? 'checked' : ''; ?>>
                                            <label for="transfers-email" class="padding05"><?= lang('email') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="transfers-pdf" class="checkbox" name="transfers-pdf" <?php echo $p->{'transfers-pdf'} ? 'checked' : ''; ?>>
                                            <label for="transfers-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>

                                        <!--<span style="display:inline-block;">
                                        <input type="checkbox" value="1" class="checkbox" id="transfer_pharmacist"
                                        name="transfer_pharmacist" <?php echo $p->transfer_pharmacist ? 'checked' : ''; ?>>
                                        <label for="transfer_pharmacist" class="padding05"><?= lang('Pharmacist') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                        <input type="checkbox" value="1" class="checkbox" id="transfer_warehouse_supervisor"
                                        name="transfer_warehouse_supervisor" <?php echo $p->transfer_warehouse_supervisor ? 'checked' : ''; ?>>
                                        <label for="transfer_warehouse_supervisor" class="padding05"><?= lang('Warehouse Supervisor') ?></label>
                                        </span>-->

                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('Customer Returns'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="returns-index" <?php echo $p->{'returns-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="returns-add" <?php echo $p->{'returns-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="returns-edit" <?php echo $p->{'returns-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="returns-delete" <?php echo $p->{'returns-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="returns-approve" class="checkbox" name="returns-approve" <?php echo $p->{'returns-approve'} ? 'checked' : ''; ?>>
                                            <label for="returns-approve" class="padding05"><?= lang('Approve') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="returns-email" class="checkbox" name="returns-email" <?php echo $p->{'returns-email'} ? 'checked' : ''; ?>>
                                            <label for="returns-email" class="padding05"><?= lang('email') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="returns-pdf" class="checkbox" name="returns-pdf" <?php echo $p->{'returns-pdf'} ? 'checked' : ''; ?>>
                                            <label for="returns-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('Supplier Returns'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="supplier-returns-index" <?php echo $p->{'supplier-returns-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="supplier-returns-add" <?php echo $p->{'supplier-returns-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="supplier-returns-edit" <?php echo $p->{'supplier-returns-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="supplier-returns-delete" <?php echo $p->{'supplier-returns-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="supplier-returns-approve" class="checkbox" name="supplier-returns-approve" <?php echo $p->{'supplier-returns-approve'} ? 'checked' : ''; ?>>
                                            <label for="supplier-returns-approve" class="padding05"><?= lang('Approve') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="supplier-returns-email" class="checkbox" name="supplier-returns-email" <?php echo $p->{'supplier-returns-email'} ? 'checked' : ''; ?>>
                                            <label for="supplier-returns-email" class="padding05"><?= lang('email') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="supplier-returns-pdf" class="checkbox" name="supplier-returns-pdf" <?php echo $p->{'supplier-returns-pdf'} ? 'checked' : ''; ?>>
                                            <label for="supplier-returns-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('customers'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="customers-index" <?php echo $p->{'customers-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="customers-add" <?php echo $p->{'customers-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="customers-edit" <?php echo $p->{'customers-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="customers-delete" <?php echo $p->{'customers-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <!--<span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="customers-deposits" class="checkbox" name="customers-deposits" <?php echo $p->{'customers-deposits'} ? 'checked' : ''; ?>>
                                            <label for="customers-deposits" class="padding05"><?= lang('deposits') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="customers-delete_deposit" class="checkbox" name="customers-delete_deposit" <?php echo $p->{'customers-delete_deposit'} ? 'checked' : ''; ?>>
                                            <label for="customers-delete_deposit" class="padding05"><?= lang('delete_deposit') ?></label>
                                        </span>-->
                                    </td>
                                </tr>

                                 <tr>
                                    <td><?= lang('Customer Payment'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="customer-payment-index" <?php echo $p->{'customer-payment-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="customer-payment-add" <?php echo $p->{'customer-payment-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="customer-payment-edit" <?php echo $p->{'customer-payment-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="customer-payment-delete" <?php echo $p->{'customer-payment-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="customer-payment-email" class="checkbox" name="customer-payment-email" <?php echo $p->{'customer-payment-email'} ? 'checked' : ''; ?>>
                                            <label for="customer-payment-email" class="padding05"><?= lang('email') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="customer-payment-pdf" class="checkbox" name="customer-payment-pdf" <?php echo $p->{'customer-payment-pdf'} ? 'checked' : ''; ?>>
                                            <label for="customer-payment-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('suppliers'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="suppliers-index" <?php echo $p->{'suppliers-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="suppliers-add" <?php echo $p->{'suppliers-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="suppliers-edit" <?php echo $p->{'suppliers-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="suppliers-delete" <?php echo $p->{'suppliers-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('Supplier Payment'); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="supplier-payment-index" <?php echo $p->{'supplier-payment-index'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="supplier-payment-add" <?php echo $p->{'supplier-payment-add'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="supplier-payment-edit" <?php echo $p->{'supplier-payment-edit'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="supplier-payment-delete" <?php echo $p->{'supplier-payment-delete'} ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="supplier-payment-email" class="checkbox" name="supplier-payment-email" <?php echo $p->{'supplier-payment-email'} ? 'checked' : ''; ?>>
                                            <label for="supplier-payment-email" class="padding05"><?= lang('email') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="supplier-payment-pdf" class="checkbox" name="supplier-payment-pdf" <?php echo $p->{'supplier-payment-pdf'} ? 'checked' : ''; ?>>
                                            <label for="supplier-payment-pdf" class="padding05"><?= lang('pdf') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('Finance'); ?></td>
                                    <td colspan="5">
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="finance-view" name="finance-view" <?php echo $p->{'finance-view'} ? 'checked' : ''; ?>>
                                            <label for="finance-view" class="padding05"><?= lang(' View') ?></label>
                                        </span>

                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="finance-view-reports" name="finance-view-reports" <?php echo $p->{'finance-view-reports'} ? 'checked' : ''; ?>>
                                            <label for="finance-view-reports" class="padding05"><?= lang('Reports') ?></label>
                                        </span>
                                        
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="finance-chart-accounts" name="finance-chart-accounts" <?php echo $p->{'finance-chart-accounts'} ? 'checked' : ''; ?>>
                                            <label for="finance-chart-accounts" class="padding05"><?= lang('Chart of Accounts') ?></label>
                                        </span>

                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="finance-jv" name="finance-jv" <?php echo $p->{'finance-jv'} ? 'checked' : ''; ?>>
                                            <label for="finance-jv" class="padding05"><?= lang('Journal Voucher') ?></label>
                                        </span>

                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="finance-jv-templates" name="finance-jv-templates" <?php echo $p->{'finance-jv-templates'} ? 'checked' : ''; ?>>
                                            <label for="finance-jv-templates" class="padding05"><?= lang('JV Templates') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('reports'); ?></td>
                                    <td colspan="5">
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="report-stock" name="report-stock" <?php echo $p->{'report-stock'} ? 'checked' : ''; ?>>
                                            <label for="report-stock" class="padding05"><?= lang('Stock Report') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-item-movement" name="reports-item-movement" <?php echo $p->{'reports-item-movement'} ? 'checked' : ''; ?>>
                                            <label for="reports-item-movement" class="padding05"><?= lang('Item Movement Report') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-revenue"
                                            name="reports-revenue" <?php echo $p->{'reports-revenue'} ? 'checked' : ''; ?>><label for="reports-revenue" class="padding05"><?= lang('Revenue Report') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-purchase" name="reports-purchase" <?php echo $p->{'reports-purchase'} ? 'checked' : ''; ?>>
                                            <label for="daily_sales" class="padding05"><?= lang('Purchase Report') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-transfer" name="reports-transfer" <?php echo $p->{'reports-transfer'} ? 'checked' : ''; ?>>
                                            <label for="reports-transfer" class="padding05"><?= lang('Transfer Report') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-inventory-tb" name="reports-inventory-tb" <?php echo $p->{'reports-inventory-tb'} ? 'checked' : ''; ?>>
                                            <label for="reports-inventory-tb" class="padding05"><?= lang('Inventory TB') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-customer-tb" name="reports-customer-tb" <?php echo $p->{'reports-customer-tb'} ? 'checked' : ''; ?>>
                                            <label for="reports-customer-tb" class="padding05"><?= lang('Customer TB') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-customer-statement" name="reports-customer-statement" <?php echo $p->{'reports-customer-statement'} ? 'checked' : ''; ?>>
                                            <label for="reports-customer-statement" class="padding05"><?= lang('Customer Statement') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-customer-aging" name="reports-customer-aging" <?php echo $p->{'reports-customer-aging'} ? 'checked' : ''; ?>>
                                            <label for="reports-customer-aging" class="padding05"><?= lang('Customer Aging') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-unpaid-invoices-ar" name="reports-unpaid-invoices-ar" <?php echo !empty($p->{'reports-unpaid-invoices-ar'}) ? 'checked' : ''; ?>>
                                            <label for="reports-unpaid-invoices-ar" class="padding05"><?= lang('Unpaid Invoices AR') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-supplier-tb" name="reports-supplier-tb" <?php echo $p->{'reports-supplier-tb'} ? 'checked' : ''; ?>>
                                            <label for="reports-supplier-tb" class="padding05"><?= lang('Supplier TB') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-supplier-statement" name="reports-supplier-statement" <?php echo $p->{'reports-supplier-statement'} ? 'checked' : ''; ?>>
                                            <label for="reports-supplier-statement" class="padding05"><?= lang('Supplier Statement') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-supplier-aging" name="reports-supplier-aging" <?php echo $p->{'reports-supplier-aging'} ? 'checked' : ''; ?>>
                                            <label for="reports-supplier-aging" class="padding05"><?= lang('Supplier Aging') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-unpaid-invoices-ap" name="reports-unpaid-invoices-ap" <?php echo !empty($p->{'reports-unpaid-invoices-ap'}) ? 'checked' : ''; ?>>
                                            <label for="reports-unpaid-invoices-ap" class="padding05"><?= lang('Unpaid Invoices AP') ?></label>
                                        </span>

                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-consumption" name="reports-consumption" <?php echo $p->{'reports-consumption'} ? 'checked' : ''; ?>>
                                            <label for="reports-consumption" class="padding05"><?= lang('Consumption') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-purchase-per-item" name="reports-purchase-per-item" <?php echo $p->{'reports-purchase-per-item'} ? 'checked' : ''; ?>>
                                            <label for="reports-purchase-per-item" class="padding05"><?= lang('Purchase Per Item') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-purchase-per-invoice" name="reports-purchase-per-invoice" <?php echo $p->{'reports-purchase-per-invoice'} ? 'checked' : ''; ?>>
                                            <label for="reports-purchase-per-invoice" class="padding05"><?= lang('Purchase Per Invoice') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-collections-by-location" name="reports-collections-by-location" <?php echo $p->{'reports-collections-by-location'} ? 'checked' : ''; ?>>
                                            <label for="reports-collections-by-location" class="padding05"><?= lang('Collections By Location') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-invoice-status" name="reports-invoice-status" <?php echo $p->{'reports-invoice-status'} ? 'checked' : ''; ?>>
                                            <label for="reports-invoice-status" class="padding05"><?= lang('SalesInvoice Status') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-sales-per-invoice" name="reports-sales-per-invoice" <?php echo $p->{'reports-sales-per-invoice'} ? 'checked' : ''; ?>>
                                            <label for="reports-sales-per-invoice" class="padding05"><?= lang('Sales Per Invoice') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-sales-per-item" name="reports-sales-per-item" <?php echo $p->{'reports-sales-per-item'} ? 'checked' : ''; ?>>
                                            <label for="reports-sales-per-item" class="padding05"><?= lang('Sales Per Item') ?></label>
                                        </span>
                                        
                                    </td>
                                </tr>

                                <tr>
                                    <td><?= lang('misc'); ?></td>
                                    <td colspan="5">
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="bulk_actions"
                                            name="bulk_actions" <?php echo $p->bulk_actions ? 'checked' : ''; ?>>
                                            <label for="bulk_actions" class="padding05"><?= lang('bulk_actions') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="edit_price"
                                            name="edit_price" <?php echo $p->edit_price ? 'checked' : ''; ?>>
                                            <label for="edit_price" class="padding05"><?= lang('edit_price_on_sale') ?></label>
                                        </span>
                                    </td>
                                </tr>
                                <!--<tr>
                                    <td><?= lang('Stock Requests'); ?></td>
                                     <td colspan="5">
                                    <span style="display:inline-block;">
                                        <input type="checkbox" value="1" class="checkbox" id="bulk_actions"
                                            name="stock_request_view" <?php echo $p->stock_request_view ? 'checked' : ''; ?>>
                                            <label for="bulk_actions" class="padding05"><?= lang('View') ?></label>
                                    </span>
                                    <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="edit_price"
                                            name="stock_request_approval" <?php echo $p->stock_request_approval ? 'checked' : ''; ?>>
                                            <label for="edit_price" class="padding05"><?= lang('Approval') ?></label>
                                    </span>
                                    <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="stock_pharmacist"
                                            name="stock_pharmacist" <?php echo $p->stock_pharmacist ? 'checked' : ''; ?>>
                                            <label for="stock_pharmacist" class="padding05"><?= lang('Pharmacist') ?></label>
                                    </span>
                                    <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="stock_warehouse_supervisor"
                                            name="stock_warehouse_supervisor" <?php echo $p->stock_warehouse_supervisor ? 'checked' : ''; ?>>
                                            <label for="stock_warehouse_supervisor" class="padding05"><?= lang('Warehouse Supervisor') ?></label>
                                    </span>
                                     </td>
                                </tr>-->
                                <tr>
                                    <td><?= lang('Truck Registration'); ?></td>
                                     <td colspan="5">
                                    <span style="display:inline-block;">
                                        <input type="checkbox" value="1" class="checkbox" id="bulk_actions"
                                            name="truck_registration_view" <?php echo $p->truck_registration_view ? 'checked' : ''; ?>>
                                            <label for="bulk_actions" class="padding05"><?= lang('View') ?></label>
                                    </span>
                                     </td>
                                </tr>

                                <tr>
                                    <td><?= lang('Warehouse Management'); ?></td>
                                     <td colspan="5">
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="bulk_actions"
                                                name="inventory-check" <?php echo $p->{'inventory-check'} ? 'checked' : ''; ?>>
                                                <label for="bulk_actions" class="padding05"><?= lang('Inventory Check') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="bulk_actions"
                                                name="inventory-requests" <?php echo $p->{'inventory-requests'} ? 'checked' : ''; ?>>
                                                <label for="bulk_actions" class="padding05"><?= lang('Inventory Requests') ?></label>
                                        </span>
                                     </td>
                                </tr>

                                <!--<tr>
                                    <td><?= lang('Accountant'); ?></td>
                                     <td colspan="5">
                                    <span style="display:inline-block;">
                                        <input type="checkbox" value="1" class="checkbox" id="bulk_actions"
                                            name="accountant" <?php echo $p->accountant ? 'checked' : ''; ?>>
                                            <label for="bulk_actions" class="padding05"><?= lang('Accounts') ?></label>
                                    </span>
                                     </td>
                                </tr>-->

                                </tbody>
                            </table>
                            </div>

                            <div class="permission-actions">
                                <p>Save this shared permission set for the group.</p>
                                <button type="submit" class="btn btn-primary"><?= lang('update') ?></button>
                            </div>
                        </div>
                        <?php echo form_close();
    } else {
        echo $this->lang->line('group_x_allowed');
    }
} else {
    echo $this->lang->line('group_x_allowed');
} ?>


            </div>
        </div>
    </div>
</div>
