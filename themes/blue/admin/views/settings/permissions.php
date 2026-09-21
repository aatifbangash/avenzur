<?php defined('BASEPATH') or exit('No direct script access allowed');
$permission_mode = isset($permission_mode) ? $permission_mode : 'group';
$has_custom_permissions = !empty($has_custom_permissions);
$form_action = isset($form_action) ? $form_action : ('system_settings/permissions/' . $id);
$page_heading = isset($page_heading) ? $page_heading : lang('group_permissions');

$subject_title = '';
$subject_sub = '';
if ($permission_mode === 'user' && !empty($user)) {
    $subject_title = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
    $subject_sub = $user->email ?? '';
    if (!empty($group)) {
        $subject_sub .= ($subject_sub ? ' · ' : '') . ($group->description ?: $group->name);
    }
} elseif (!empty($group)) {
    $subject_title = $group->description ?: $group->name;
    $subject_sub = $group->name ? ('Group: ' . $group->name) : '';
}
?>
<style>
    .perm-page {
        --perm-accent: #428bca;
        --perm-border: #e3e8ef;
        --perm-muted: #6b7280;
        --perm-bg: #f7f9fc;
    }

    .perm-page .introtext {
        color: var(--perm-muted);
        margin-bottom: 16px;
    }

    .perm-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px 16px;
        background: linear-gradient(135deg, #f0f6fc 0%, #ffffff 55%);
        border: 1px solid var(--perm-border);
        border-radius: 8px;
        padding: 16px 18px;
        margin-bottom: 16px;
    }

    .perm-hero-main {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }

    .perm-hero-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        background: #428bca;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .perm-hero-text h3 {
        margin: 0 0 4px;
        font-size: 16px;
        font-weight: 700;
        color: #1f2937;
    }

    .perm-hero-text p {
        margin: 0;
        color: var(--perm-muted);
        font-size: 12px;
    }

    .perm-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    .perm-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 11px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.2;
    }

    .perm-badge-custom {
        background: #fff7e6;
        color: #9a6700;
        border: 1px solid #ffe0a3;
    }

    .perm-badge-group {
        background: #ecf8ef;
        color: #1b7a3d;
        border: 1px solid #c6e8d0;
    }

    .perm-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        margin-bottom: 14px;
        padding: 12px;
        background: var(--perm-bg);
        border: 1px solid var(--perm-border);
        border-radius: 8px;
    }

    .perm-toolbar .perm-search-wrap {
        position: relative;
        flex: 1 1 260px;
        max-width: 380px;
    }

    .perm-toolbar .perm-search-wrap i {
        position: absolute;
        left: 11px;
        top: 50%;
        transform: translateY(-50%);
        color: #9aa3af;
        pointer-events: none;
    }

    .perm-toolbar .perm-search {
        width: 100%;
        padding-left: 32px;
        height: 34px;
        border-radius: 6px;
        border: 1px solid #d5dbe5;
        box-shadow: none;
    }

    .perm-toolbar .perm-search:focus {
        border-color: #8bb8e0;
        outline: none;
        box-shadow: 0 0 0 3px rgba(66, 139, 202, 0.15);
    }

    .perm-toolbar .btn {
        border-radius: 6px;
    }

    .perm-layout {
        display: flex;
        gap: 14px;
        align-items: flex-start;
        margin-bottom: 70px;
    }

    .perm-nav {
        flex: 0 0 210px;
        position: sticky;
        top: 12px;
        background: #1f2937;
        color: #f3f4f6;
        border-radius: 8px;
        padding: 10px 0;
        max-height: calc(100vh - 140px);
        overflow-y: auto;
    }

    .perm-nav-title {
        padding: 6px 16px 10px;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #9ca3af;
        font-weight: 600;
    }

    .perm-nav a {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 9px 16px;
        color: #d1d5db;
        text-decoration: none;
        font-size: 13px;
        border-left: 3px solid transparent;
        transition: background 0.15s ease, color 0.15s ease;
    }

    .perm-nav a i {
        width: 16px;
        text-align: center;
        opacity: 0.85;
    }

    .perm-nav a:hover {
        background: #134dab;
        color: #fff;
        text-decoration: none;
    }

    .perm-nav a.active {
        background: #0b4476;
        color: #fff;
        border-left-color: #60a5fa;
        font-weight: 600;
    }

    .perm-nav a.perm-nav-hidden {
        display: none;
    }

    .perm-main {
        flex: 1 1 auto;
        min-width: 0;
    }

    .perm-table-wrap {
        border: 1px solid var(--perm-border);
        border-radius: 8px;
        overflow: auto;
        max-height: calc(100vh - 260px);
        background: #fff;
        -webkit-overflow-scrolling: touch;
    }

    #permissions-table {
        margin-bottom: 0 !important;
        border-collapse: separate;
        border-spacing: 0;
    }

    #permissions-table thead th {
        position: sticky;
        top: 0;
        z-index: 12;
        background: #f3f6fa !important;
        color: #374151;
        border-bottom: 1px solid var(--perm-border) !important;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        vertical-align: middle !important;
        padding: 10px 8px !important;
        box-shadow: 0 1px 0 rgba(15, 23, 42, 0.06);
    }

    #permissions-table tbody td {
        vertical-align: middle !important;
        padding: 10px 8px !important;
        border-color: #eef2f7 !important;
    }

    #permissions-table tbody tr.perm-module:nth-child(even) td {
        background: #fafbfc;
    }

    #permissions-table tbody tr.perm-module:hover td {
        background: #f0f7fc !important;
    }

    #permissions-table td:first-child {
        font-weight: 600;
        color: #1f2937;
        white-space: nowrap;
        min-width: 160px;
    }

    #permissions-table td.text-center {
        width: 72px;
    }

    #permissions-table input.checkbox {
        width: 16px;
        height: 16px;
        cursor: pointer;
        accent-color: #428bca;
    }

    #permissions-table label.padding05 {
        margin: 0 12px 0 4px;
        font-weight: 500;
        color: #4b5563;
        cursor: pointer;
    }

    #permissions-table td span[style*="inline-block"] {
        margin: 3px 6px 3px 0;
        padding: 4px 8px;
        background: #f5f7fa;
        border: 1px solid #e8edf3;
        border-radius: 5px;
    }

    #permissions-table tr.perm-section td {
        background: #0b4476 !important;
        color: #fff !important;
        font-weight: 700;
        font-size: 13px;
        letter-spacing: 0.02em;
        padding: 11px 12px !important;
        border-color: #0b4476 !important;
        cursor: pointer;
        user-select: none;
    }

    #permissions-table tr.perm-section td i.perm-section-icon {
        margin-right: 8px;
        opacity: 0.9;
    }

    #permissions-table tr.perm-section td .perm-section-toggle {
        float: right;
        opacity: 0.8;
        font-size: 12px;
    }

    #permissions-table tr.perm-section.collapsed td .perm-section-toggle {
        transform: rotate(-90deg);
    }

    #permissions-table tr.perm-section-hidden,
    #permissions-table tr.perm-row-hidden {
        display: none !important;
    }

    .perm-empty {
        display: none;
        text-align: center;
        padding: 28px 16px;
        color: var(--perm-muted);
        border: 1px dashed var(--perm-border);
        border-radius: 8px;
        margin-top: 10px;
        background: var(--perm-bg);
    }

    .perm-footer {
        position: sticky;
        bottom: 0;
        z-index: 20;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        padding: 12px 16px;
        margin: 0 -15px -15px;
        background: rgba(255, 255, 255, 0.96);
        border-top: 1px solid var(--perm-border);
        box-shadow: 0 -6px 18px rgba(15, 23, 42, 0.06);
    }

    .perm-footer .perm-footer-hint {
        color: var(--perm-muted);
        font-size: 12px;
        margin: 0;
    }

    .perm-footer .btn-primary {
        min-width: 140px;
        border-radius: 6px;
        font-weight: 600;
    }

    @media (max-width: 991px) {
        .perm-layout {
            flex-direction: column;
        }
        .perm-nav {
            flex: 1 1 auto;
            width: 100%;
            position: static;
            max-height: none;
            display: flex;
            flex-wrap: wrap;
            padding: 8px;
            gap: 4px;
        }
        .perm-nav-title {
            width: 100%;
            padding: 4px 8px 6px;
        }
        .perm-nav a {
            border-left: 0;
            border-radius: 6px;
            padding: 7px 10px;
            font-size: 12px;
        }
    }
</style>
<script>
    $(document).ready(function () {
        function syncSectionVisibility() {
            var q = ($('#perm-search').val() || '').toLowerCase();
            $('#permissions-table tbody tr.perm-section').each(function () {
                var section = $(this).data('section');
                var modules = $('#permissions-table tbody tr.perm-module[data-section="' + section + '"]');
                var visibleModules = modules.filter(function () {
                    return !$(this).hasClass('perm-row-hidden');
                });
                var hideSection = q !== '' && visibleModules.length === 0;
                $(this).toggleClass('perm-row-hidden', hideSection);
                $('.perm-nav a[data-section="' + section + '"]').toggleClass('perm-nav-hidden', hideSection);
            });
        }

        function refreshEmptyState() {
            var visible = $('#permissions-table tbody tr.perm-module:not(.perm-row-hidden):not(.perm-section-hidden)').length;
            $('#perm-empty').toggle(visible === 0);
            if (visible === 0 && ($('#perm-search').val() || '') !== '') {
                $('#perm-table-wrap').hide();
            } else {
                $('#perm-table-wrap').show();
            }
        }

        function filterRows() {
            var q = ($('#perm-search').val() || '').toLowerCase();
            $('#permissions-table tbody tr.perm-module').each(function () {
                var text = $(this).text().toLowerCase();
                var sectionLabel = String($(this).data('section-label') || '').toLowerCase();
                var match = q === '' || text.indexOf(q) !== -1 || sectionLabel.indexOf(q) !== -1;
                $(this).toggleClass('perm-row-hidden', !match);
            });
            syncSectionVisibility();
            refreshEmptyState();
        }

        $('#perm-search').on('keyup', filterRows);

        $('#perm-select-visible').on('click', function (e) {
            e.preventDefault();
            var $boxes = $('#permissions-table tbody tr.perm-module:not(.perm-row-hidden):not(.perm-section-hidden) input.checkbox');
            if ($.fn.iCheck) {
                $boxes.iCheck('check');
            } else {
                $boxes.prop('checked', true);
            }
        });

        $('#perm-clear-visible').on('click', function (e) {
            e.preventDefault();
            var $boxes = $('#permissions-table tbody tr.perm-module:not(.perm-row-hidden):not(.perm-section-hidden) input.checkbox');
            if ($.fn.iCheck) {
                $boxes.iCheck('uncheck');
            } else {
                $boxes.prop('checked', false);
            }
        });

        // Keep iCheck state in sync with native inputs before save
        $('form').on('submit', function () {
            $('#permissions-table input.checkbox').each(function () {
                var $el = $(this);
                if ($el.data('iCheck')) {
                    // native checked is already maintained by iCheck; force update just in case
                    $el.iCheck('update');
                }
            });
        });

        // Re-apply iCheck after page scripts for any late-rendered boxes
        if ($.fn.iCheck) {
            $('#permissions-table input.checkbox').each(function () {
                if (!$(this).data('iCheck')) {
                    $(this).iCheck({
                        checkboxClass: 'icheckbox_square-blue',
                        radioClass: 'iradio_square-blue',
                        increaseArea: '20%'
                    });
                }
            });
        }

        $('.perm-nav a').on('click', function (e) {
            e.preventDefault();
            var section = $(this).data('section');
            $('.perm-nav a').removeClass('active');
            $(this).addClass('active');
            var $header = $('#permissions-table tbody tr.perm-section[data-section="' + section + '"]');
            if ($header.hasClass('collapsed')) {
                $header.trigger('click');
            }
            if ($header.length) {
                var $wrap = $('#perm-table-wrap');
                var theadH = $('#permissions-table thead').outerHeight() || 0;
                var top = $header.offset().top - $wrap.offset().top + $wrap.scrollTop() - theadH - 4;
                $wrap.stop(true).animate({ scrollTop: Math.max(0, top) }, 250);
            }
        });

        $('#permissions-table').on('click', 'tr.perm-section', function () {
            var section = $(this).data('section');
            $(this).toggleClass('collapsed');
            var collapsed = $(this).hasClass('collapsed');
            $('#permissions-table tbody tr.perm-module[data-section="' + section + '"]').toggleClass('perm-section-hidden', collapsed);
        });

        $('.perm-nav a').first().addClass('active');
    });
</script>
<div class="box perm-page">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-key"></i><?= $page_heading; ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('set_permissions'); ?></p>

                <?php if (!empty($p)) {
    if ($permission_mode === 'user' || (isset($p->group_id) && $p->group_id != 1)) {
        echo admin_form_open($form_action); ?>
                        <?php if ($permission_mode === 'user') { ?>
                            <input type="hidden" name="user_id" value="<?= (int) $id; ?>">
                        <?php } else { ?>
                            <input type="hidden" name="group" value="<?= (int) $id; ?>">
                        <?php } ?>

                        <div class="perm-hero">
                            <div class="perm-hero-main">
                                <div class="perm-hero-icon"><i class="fa <?= $permission_mode === 'user' ? 'fa-user' : 'fa-users'; ?>"></i></div>
                                <div class="perm-hero-text">
                                    <h3><?= htmlspecialchars($subject_title ?: $page_heading); ?></h3>
                                    <p><?= htmlspecialchars($subject_sub ?: (lang('set_permissions') ?: '')); ?></p>
                                </div>
                            </div>
                            <div class="perm-hero-actions">
                                <?php if ($permission_mode === 'user') { ?>
                                    <?php if ($has_custom_permissions) { ?>
                                        <span class="perm-badge perm-badge-custom"><i class="fa fa-pencil"></i> <?= lang('custom_permissions') ?: 'Custom permissions active'; ?></span>
                                        <a href="<?= admin_url('auth/user_permissions/' . $id . '?reset=1'); ?>" class="btn btn-warning btn-sm"
                                           onclick="return confirm('<?= lang('r_u_sure') ?: 'Are you sure?'; ?>');">
                                            <i class="fa fa-undo"></i> <?= lang('reset_to_group') ?: 'Reset to group defaults'; ?>
                                        </a>
                                    <?php } else { ?>
                                        <span class="perm-badge perm-badge-group"><i class="fa fa-check"></i> <?= lang('using_group_defaults') ?: 'Showing group defaults (save to create custom permissions)'; ?></span>
                                    <?php } ?>
                                    <a href="<?= admin_url('users'); ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> <?= lang('users'); ?></a>
                                <?php } else { ?>
                                    <span class="perm-badge perm-badge-group"><i class="fa fa-users"></i> <?= lang('group_permissions'); ?></span>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="perm-layout">
                            <nav class="perm-nav" aria-label="Permission sections">
                                <div class="perm-nav-title">Menu sections</div>
                                <a href="#perm-sec-master" data-section="master"><i class="fa fa-archive"></i> <?= lang('Master Data'); ?></a>
                                <a href="#perm-sec-ar" data-section="ar"><i class="fa fa-hand-o-up"></i> <?= lang('Account Receivable'); ?></a>
                                <a href="#perm-sec-ap" data-section="ap"><i class="fa fa-hand-o-down"></i> <?= lang('Account Payable'); ?></a>
                                <a href="#perm-sec-inventory" data-section="inventory"><i class="fa fa-archive"></i> <?= lang('Inventory'); ?></a>
                                <a href="#perm-sec-finance" data-section="finance"><i class="fa fa-money"></i> <?= lang('Finance'); ?></a>
                                <a href="#perm-sec-warehouse" data-section="warehouse"><i class="fa fa-building"></i> <?= lang('Warehouse Management'); ?></a>
                                <a href="#perm-sec-services" data-section="services"><i class="fa fa-cogs"></i> <?= lang('Services'); ?></a>
                            </nav>
                            <div class="perm-main">
                        <div class="perm-toolbar">
                            <div class="perm-search-wrap">
                                <i class="fa fa-search"></i>
                                <input type="text" id="perm-search" class="form-control perm-search" placeholder="<?= lang('search') ?: 'Search modules / permissions...'; ?>">
                            </div>
                            <a href="#" id="perm-select-visible" class="btn btn-default btn-sm"><i class="fa fa-check-square-o"></i> <?= lang('select_all') ?: 'Select visible'; ?></a>
                            <a href="#" id="perm-clear-visible" class="btn btn-default btn-sm"><i class="fa fa-square-o"></i> <?= lang('clear') ?: 'Clear visible'; ?></a>
                        </div>

                        <div id="perm-empty" class="perm-empty">
                            <i class="fa fa-search" style="font-size:20px; margin-bottom:8px; display:block;"></i>
                            No modules match your search.
                        </div>

                        <div class="perm-table-wrap table-responsive" id="perm-table-wrap">
                            <table id="permissions-table" class="table table-bordered table-hover reports-table">

                                <thead>
                                <tr>
                                    <th class="text-center"><?= lang('module_name'); ?></th>
                                    <th class="text-center"><?= lang('view'); ?></th>
                                    <th class="text-center"><?= lang('add'); ?></th>
                                    <th class="text-center"><?= lang('edit'); ?></th>
                                    <th class="text-center"><?= lang('delete'); ?></th>
                                    <th class="text-center"><?= lang('misc'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr class="perm-section" id="perm-sec-master" data-section="master">
                                    <td colspan="6"><i class="fa fa-archive perm-section-icon"></i><?= lang('Master Data'); ?><i class="fa fa-chevron-down perm-section-toggle"></i></td>
                                </tr>

                                <tr class="perm-module" data-section="master" data-section-label="Master Data">
                                <td>
                                    <input type="checkbox" value="1" class="checkbox" name="products-module" <?php echo $p->{'products-module'} ? 'checked' : ''; ?>>
                                    <?= lang('products'); ?>
                                </td>
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
                                <span style="display:inline-block;">
                                <input type="checkbox" value="1" id="products-export" class="checkbox" name="products-export" <?php echo $p->{'products-export'} ? 'checked' : ''; ?>>
                                <label for="products-export" class="padding05"><?= lang('Export') ?></label>
                                </span>
                                </td>
                                </tr>

                                <tr class="perm-section" id="perm-sec-ar" data-section="ar">
                                    <td colspan="6"><i class="fa fa-hand-o-up perm-section-icon"></i><?= lang('Account Receivable'); ?><i class="fa fa-chevron-down perm-section-toggle"></i></td>
                                </tr>

                                <tr class="perm-module" data-section="ar" data-section-label="Account Receivable">
                                <td><input type="checkbox" value="1" class="checkbox" name="quotes-module" <?php echo $p->{'quotes-module'} ? 'checked' : ''; ?>>
                                <?= lang('quotes'); ?></td>
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
                                <label for="quotes-pdf" class="padding05"><?= lang('Export') ?></label>
                                </span>
                                </td>
                                </tr>

                                <tr class="perm-module" data-section="ar" data-section-label="Account Receivable">
                                <td>
                                <input type="checkbox" value="1" class="checkbox" name="sales-module" <?php echo $p->{'sales-module'} ? 'checked' : ''; ?>>
                                <?= lang('sales'); ?></td>
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
                                <!--<span style="display:inline-block;">
                                <input type="checkbox" value="1" id="sales-email" class="checkbox" name="sales-email" <?php echo $p->{'sales-email'} ? 'checked' : ''; ?>>
                                <label for="sales-email" class="padding05"><?= lang('email') ?></label>
                                </span>-->
                                <span style="display:inline-block;">
                                <input type="checkbox" value="1" id="sales-pdf" class="checkbox" name="sales-pdf" <?php echo $p->{'sales-pdf'} ? 'checked' : ''; ?>>
                                <label for="sales-pdf" class="padding05"><?= lang('Export') ?></label>
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

                                <tr class="perm-module" data-section="ar" data-section-label="Account Receivable">
                                <td>
                                <input type="checkbox" value="1" class="checkbox" name="customers-module" <?php echo $p->{'customers-module'} ? 'checked' : ''; ?>>
                                    <?= lang('customers'); ?>
                                </td>
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

                                <tr class="perm-module" data-section="ar" data-section-label="Account Receivable">
                                <td>
                                    <input type="checkbox" value="1" class="checkbox" name="customer-payment-module" <?php echo $p->{'customer-payment-module'} ? 'checked' : ''; ?>>
                                    <?= lang('Customer Payment'); ?></td>
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
                                <!--<span style="display:inline-block;">
                                <input type="checkbox" value="1" id="customer-payment-email" class="checkbox" name="customer-payment-email" <?php echo $p->{'customer-payment-email'} ? 'checked' : ''; ?>>
                                <label for="customer-payment-email" class="padding05"><?= lang('email') ?></label>
                                </span>-->
                                <span style="display:inline-block;">
                                <input type="checkbox" value="1" id="customer-payment-pdf" class="checkbox" name="customer-payment-pdf" <?php echo $p->{'customer-payment-pdf'} ? 'checked' : ''; ?>>
                                <label for="customer-payment-pdf" class="padding05"><?= lang('Export') ?></label>
                                </span>
                                </td>
                                </tr>

                                <tr class="perm-module" data-section="ar" data-section-label="Account Receivable">
                                <td>
                                    <input type="checkbox" value="1" class="checkbox" name="customermemo-module" <?php echo $p->{'customermemo-module'} ? 'checked' : ''; ?>>
                                    <?= lang('Customer Memo'); ?></td>
                                <td class="text-center">
                                <input type="checkbox" value="1" class="checkbox" name="customermemo-index" <?php echo $p->{'customermemo-index'} ? 'checked' : ''; ?>>
                                </td>
                                <td class="text-center">
                                <input type="checkbox" value="1" class="checkbox" name="customermemo-add" <?php echo $p->{'customermemo-add'} ? 'checked' : ''; ?>>
                                </td>
                                <td class="text-center">
                                <input type="checkbox" value="1" class="checkbox" name="customermemo-edit" <?php echo $p->{'customermemo-edit'} ? 'checked' : ''; ?>>
                                </td>
                                <td class="text-center">
                                <input type="checkbox" value="1" class="checkbox" name="customermemo-delete" <?php echo $p->{'customermemo-delete'} ? 'checked' : ''; ?>>
                                </td>
                                <td>
                                <!--<span style="display:inline-block;">
                                <input type="checkbox" value="1" id="customer-payment-email" class="checkbox" name="customer-payment-email" <?php echo $p->{'customer-payment-email'} ? 'checked' : ''; ?>>
                                <label for="customer-payment-email" class="padding05"><?= lang('email') ?></label>
                                </span>-->
                                <span style="display:inline-block;">
                                <input type="checkbox" value="1" id="customermemo-export" class="checkbox" name="customermemo-export" <?php echo $p->{'customermemo-export'} ? 'checked' : ''; ?>>
                                <label for="customermemo-export" class="padding05"><?= lang('Export') ?></label>
                                </span>
                                </td>
                                </tr>

                                <tr class="perm-module" data-section="ar" data-section-label="Account Receivable">
                                <td>
                                    <input type="checkbox" value="1" class="checkbox" name="customersi-module" <?php echo $p->{'customersi-module'} ? 'checked' : ''; ?>>
                                    <?= lang('Customer Service Invoice'); ?></td>
                                <td class="text-center">
                                <input type="checkbox" value="1" class="checkbox" name="customersi-index" <?php echo $p->{'customersi-index'} ? 'checked' : ''; ?>>
                                </td>
                                <td class="text-center">
                                <input type="checkbox" value="1" class="checkbox" name="customersi-add" <?php echo $p->{'customersi-add'} ? 'checked' : ''; ?>>
                                </td>
                                <td class="text-center">
                                <input type="checkbox" value="1" class="checkbox" name="customersi-edit" <?php echo $p->{'customersi-edit'} ? 'checked' : ''; ?>>
                                </td>
                                <td class="text-center">
                                <input type="checkbox" value="1" class="checkbox" name="customersi-delete" <?php echo $p->{'customersi-delete'} ? 'checked' : ''; ?>>
                                </td>
                                <td>
                                <!--<span style="display:inline-block;">
                                <input type="checkbox" value="1" id="customer-payment-email" class="checkbox" name="customer-payment-email" <?php echo $p->{'customer-payment-email'} ? 'checked' : ''; ?>>
                                <label for="customer-payment-email" class="padding05"><?= lang('email') ?></label>
                                </span>-->
                                <span style="display:inline-block;">
                                <input type="checkbox" value="1" id="customersi-export" class="checkbox" name="customersi-export" <?php echo $p->{'customersi-export'} ? 'checked' : ''; ?>>
                                <label for="customersi-export" class="padding05"><?= lang('Export') ?></label>
                                </span>
                                </td>
                                </tr>

                                <tr class="perm-module" data-section="ar" data-section-label="Account Receivable">
                                <td>
                                <input type="checkbox" value="1" class="checkbox" name="customer-returns-module" <?php echo $p->{'customer-returns-module'} ? 'checked' : ''; ?>>
                                <?= lang('Customer Returns'); ?></td>
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
                                <!--<span style="display:inline-block;">
                                <input type="checkbox" value="1" id="returns-email" class="checkbox" name="returns-email" <?php echo $p->{'returns-email'} ? 'checked' : ''; ?>>
                                <label for="returns-email" class="padding05"><?= lang('email') ?></label>
                                </span>-->
                                <span style="display:inline-block;">
                                <input type="checkbox" value="1" id="returns-pdf" class="checkbox" name="returns-pdf" <?php echo $p->{'returns-pdf'} ? 'checked' : ''; ?>>
                                <label for="returns-pdf" class="padding05"><?= lang('Export') ?></label>
                                </span>
                                </td>
                                </tr>

                                <tr class="perm-module" data-section="ar" data-section-label="Account Receivable">
                                    <td>
                                    <input type="checkbox" value="1" class="checkbox" name="receiveable-reports" <?php echo $p->{'receiveable-reports'} ? 'checked' : ''; ?>>
                                    <?= lang('Reports'); ?></td>
                                    <td colspan="5">
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-customer-tb" name="reports-customer-tb" <?php echo $p->{'reports-customer-tb'} ? 'checked' : ''; ?>>
                                            <label for="reports-customer-tb" class="padding05"><?= lang('Customer TB') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-customer-advances" name="reports-customer-advances" <?php echo $p->{'reports-customer-advances'} ? 'checked' : ''; ?>>
                                            <label for="reports-customer-advances" class="padding05"><?= lang('Customer Advances') ?></label>
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
                                            <input type="checkbox" value="1" class="checkbox" id="reports-collections-by-location" name="reports-collections-by-location" <?php echo $p->{'reports-collections-by-location'} ? 'checked' : ''; ?>>
                                            <label for="reports-collections-by-location" class="padding05"><?= lang('Collection Per Invoice') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-customer-collections-report" name="reports-customer-collections-report" <?php echo $p->{'reports-customer-collections-report'} ? 'checked' : ''; ?>>
                                            <label for="reports-customer-collections-report" class="padding05"><?= lang('Customer Collections') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-onhold-sales" name="reports-onhold-sales" <?php echo $p->{'reports-onhold-sales'} ? 'checked' : ''; ?>>
                                            <label for="reports-onhold-sales" class="padding05"><?= lang('Onhold Sales') ?></label>
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

                                <tr class="perm-section" id="perm-sec-ap" data-section="ap">
                                    <td colspan="6"><i class="fa fa-hand-o-down perm-section-icon"></i><?= lang('Account Payable'); ?><i class="fa fa-chevron-down perm-section-toggle"></i></td>
                                </tr>

                                <tr class="perm-module" data-section="ap" data-section-label="Account Payable">
                                <td>
                                <input type="checkbox" value="1" class="checkbox" name="contract-deals-module" <?php echo $p->{'contract-deals-module'} ? 'checked' : ''; ?>>    
                                <?= lang('Contract Deals'); ?></td>
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
                                <!--<span style="display:inline-block;">
                                <input type="checkbox" value="1" id="contract-deals-email" class="checkbox" name="contract-deals-email" <?php echo $p->{'contract-deals-email'} ? 'checked' : ''; ?>>
                                <label for="contract-deals-email" class="padding05"><?= lang('email') ?></label>
                                </span>-->
                                <span style="display:inline-block;">
                                <input type="checkbox" value="1" id="contract-deals-pdf" class="checkbox" name="contract-deals-pdf" <?php echo $p->{'contract-deals-pdf'} ? 'checked' : ''; ?>>
                                <label for="po-pdf" class="padding05"><?= lang('Export') ?></label>
                                </span>
                                </td>
                                </tr>

                                <tr class="perm-module" data-section="ap" data-section-label="Account Payable">
                                <td>
                                <input type="checkbox" value="1" class="checkbox" name="pr-module" <?php echo $p->{'pr-module'} ? 'checked' : ''; ?>>    
                                <?= lang('Purchase Requisition'); ?></td>
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
                                <!--<span style="display:inline-block;">
                                <input type="checkbox" value="1" id="pr-email" class="checkbox" name="pr-email" <?php echo $p->{'pr-email'} ? 'checked' : ''; ?>>
                                <label for="pr-email" class="padding05"><?= lang('email') ?></label>
                                </span>-->
                                <span style="display:inline-block;">
                                <input type="checkbox" value="1" id="pr-pdf" class="checkbox" name="pr-pdf" <?php echo $p->{'pr-pdf'} ? 'checked' : ''; ?>>
                                <label for="pr-pdf" class="padding05"><?= lang('Export') ?></label>
                                </span>
                                </td>
                                </tr>

                                <tr class="perm-module" data-section="ap" data-section-label="Account Payable">
                                <td>
                                <input type="checkbox" value="1" class="checkbox" name="po-module" <?php echo $p->{'po-module'} ? 'checked' : ''; ?>>    
                                <?= lang('Purchase Order'); ?></td>
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
                                <!--<span style="display:inline-block;">
                                <input type="checkbox" value="1" id="po-email" class="checkbox" name="po-email" <?php echo $p->{'po-email'} ? 'checked' : ''; ?>>
                                <label for="po-email" class="padding05"><?= lang('email') ?></label>
                                </span>-->
                                <span style="display:inline-block;">
                                <input type="checkbox" value="1" id="po-pdf" class="checkbox" name="po-pdf" <?php echo $p->{'po-pdf'} ? 'checked' : ''; ?>>
                                <label for="po-pdf" class="padding05"><?= lang('Export') ?></label>
                                </span>
                                </td>
                                </tr>

                                <tr class="perm-module" data-section="ap" data-section-label="Account Payable">
                                <td>
                                <input type="checkbox" value="1" class="checkbox" name="purchases-module" <?php echo $p->{'purchases-module'} ? 'checked' : ''; ?>>    
                                <?= lang('purchases'); ?></td>
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
                                <!--<span style="display:inline-block;">
                                <input type="checkbox" value="1" id="purchases-email" class="checkbox" name="purchases-email" <?php echo $p->{'purchases-email'} ? 'checked' : ''; ?>>
                                <label for="purchases-email" class="padding05"><?= lang('email') ?></label>
                                </span>-->
                                <span style="display:inline-block;">
                                <input type="checkbox" value="1" id="purchases-pdf" class="checkbox" name="purchases-pdf" <?php echo $p->{'purchases-pdf'} ? 'checked' : ''; ?>>
                                <label for="purchases-pdf" class="padding05"><?= lang('Export') ?></label>
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

                                <tr class="perm-module" data-section="ap" data-section-label="Account Payable">
                                <td>
                                    <input type="checkbox" value="1" class="checkbox" name="suppliers-module" <?php echo $p->{'suppliers-module'} ? 'checked' : ''; ?>>    
                                    <?= lang('suppliers'); ?></td>
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

                                <tr class="perm-module" data-section="ap" data-section-label="Account Payable">
                                <td>
                                    <input type="checkbox" value="1" class="checkbox" name="supplier-payment-module" <?php echo $p->{'supplier-payment-module'} ? 'checked' : ''; ?>>    
                                    <?= lang('Supplier Payment'); ?></td>
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
                                <!--<span style="display:inline-block;">
                                <input type="checkbox" value="1" id="supplier-payment-email" class="checkbox" name="supplier-payment-email" <?php echo $p->{'supplier-payment-email'} ? 'checked' : ''; ?>>
                                <label for="supplier-payment-email" class="padding05"><?= lang('email') ?></label>
                                </span>-->
                                <span style="display:inline-block;">
                                <input type="checkbox" value="1" id="supplier-payment-pdf" class="checkbox" name="supplier-payment-pdf" <?php echo $p->{'supplier-payment-pdf'} ? 'checked' : ''; ?>>
                                <label for="supplier-payment-pdf" class="padding05"><?= lang('Export') ?></label>
                                </span>
                                </td>
                                </tr>

                                <tr class="perm-module" data-section="ap" data-section-label="Account Payable">
                                <td>
                                    <input type="checkbox" value="1" class="checkbox" name="suppliermemo-module" <?php echo $p->{'suppliermemo-module'} ? 'checked' : ''; ?>>    
                                    <?= lang('Supplier Memo'); ?></td>
                                <td class="text-center">
                                <input type="checkbox" value="1" class="checkbox" name="suppliermemo-index" <?php echo $p->{'suppliermemo-index'} ? 'checked' : ''; ?>>
                                </td>
                                <td class="text-center">
                                <input type="checkbox" value="1" class="checkbox" name="suppliermemo-add" <?php echo $p->{'suppliermemo-add'} ? 'checked' : ''; ?>>
                                </td>
                                <td class="text-center">
                                <input type="checkbox" value="1" class="checkbox" name="suppliermemo-edit" <?php echo $p->{'suppliermemo-edit'} ? 'checked' : ''; ?>>
                                </td>
                                <td class="text-center">
                                <input type="checkbox" value="1" class="checkbox" name="suppliermemo-delete" <?php echo $p->{'suppliermemo-delete'} ? 'checked' : ''; ?>>
                                </td>
                                <td>
                                <!--<span style="display:inline-block;">
                                <input type="checkbox" value="1" id="supplier-payment-email" class="checkbox" name="supplier-payment-email" <?php echo $p->{'supplier-payment-email'} ? 'checked' : ''; ?>>
                                <label for="supplier-payment-email" class="padding05"><?= lang('email') ?></label>
                                </span>-->
                                <span style="display:inline-block;">
                                <input type="checkbox" value="1" id="suppliermemo-export" class="checkbox" name="suppliermemo-export" <?php echo $p->{'suppliermemo-export'} ? 'checked' : ''; ?>>
                                <label for="suppliermemo-export" class="padding05"><?= lang('Export') ?></label>
                                </span>
                                </td>
                                </tr>

                                <tr class="perm-module" data-section="ap" data-section-label="Account Payable">
                                <td>
                                    <input type="checkbox" value="1" class="checkbox" name="suppliersi-module" <?php echo $p->{'suppliersi-module'} ? 'checked' : ''; ?>>    
                                    <?= lang('Supplier Service Invoice'); ?></td>
                                <td class="text-center">
                                <input type="checkbox" value="1" class="checkbox" name="suppliersi-index" <?php echo $p->{'suppliersi-index'} ? 'checked' : ''; ?>>
                                </td>
                                <td class="text-center">
                                <input type="checkbox" value="1" class="checkbox" name="suppliersi-add" <?php echo $p->{'suppliersi-add'} ? 'checked' : ''; ?>>
                                </td>
                                <td class="text-center">
                                <input type="checkbox" value="1" class="checkbox" name="suppliersi-edit" <?php echo $p->{'suppliersi-edit'} ? 'checked' : ''; ?>>
                                </td>
                                <td class="text-center">
                                <input type="checkbox" value="1" class="checkbox" name="suppliersi-delete" <?php echo $p->{'suppliersi-delete'} ? 'checked' : ''; ?>>
                                </td>
                                <td>
                                <!--<span style="display:inline-block;">
                                <input type="checkbox" value="1" id="supplier-payment-email" class="checkbox" name="supplier-payment-email" <?php echo $p->{'supplier-payment-email'} ? 'checked' : ''; ?>>
                                <label for="supplier-payment-email" class="padding05"><?= lang('email') ?></label>
                                </span>-->
                                <span style="display:inline-block;">
                                <input type="checkbox" value="1" id="suppliersi-export" class="checkbox" name="suppliersi-export" <?php echo $p->{'suppliersi-export'} ? 'checked' : ''; ?>>
                                <label for="suppliersi-export" class="padding05"><?= lang('Export') ?></label>
                                </span>
                                </td>
                                </tr>

                                <tr class="perm-module" data-section="ap" data-section-label="Account Payable">
                                <td>
                                    <input type="checkbox" value="1" class="checkbox" name="supplier-pettycash-module" <?php echo $p->{'supplier-pettycash-module'} ? 'checked' : ''; ?>>    
                                    <?= lang('Supplier Petty Cash'); ?></td>
                                <td class="text-center">
                                <input type="checkbox" value="1" class="checkbox" name="supplier-pettycash-index" <?php echo $p->{'supplier-pettycash-index'} ? 'checked' : ''; ?>>
                                </td>
                                <td class="text-center">
                                <input type="checkbox" value="1" class="checkbox" name="supplier-pettycash-add" <?php echo $p->{'supplier-pettycash-add'} ? 'checked' : ''; ?>>
                                </td>
                                <td class="text-center">
                                <input type="checkbox" value="1" class="checkbox" name="supplier-pettycash-edit" <?php echo $p->{'supplier-pettycash-edit'} ? 'checked' : ''; ?>>
                                </td>
                                <td class="text-center">
                                <input type="checkbox" value="1" class="checkbox" name="supplier-pettycash-delete" <?php echo $p->{'supplier-pettycash-delete'} ? 'checked' : ''; ?>>
                                </td>
                                <td>
                                <!--<span style="display:inline-block;">
                                <input type="checkbox" value="1" id="supplier-payment-email" class="checkbox" name="supplier-payment-email" <?php echo $p->{'supplier-payment-email'} ? 'checked' : ''; ?>>
                                <label for="supplier-payment-email" class="padding05"><?= lang('email') ?></label>
                                </span>-->
                                <span style="display:inline-block;">
                                <input type="checkbox" value="1" id="supplier-pettycash-export" class="checkbox" name="supplier-pettycash-export" <?php echo $p->{'supplier-pettycash-export'} ? 'checked' : ''; ?>>
                                <label for="supplier-pettycash-export" class="padding05"><?= lang('Export') ?></label>
                                </span>
                                </td>
                                </tr>

                                <tr class="perm-module" data-section="ap" data-section-label="Account Payable">
                                <td>
                                    <input type="checkbox" value="1" class="checkbox" name="supplier-returns-module" <?php echo $p->{'supplier-returns-module'} ? 'checked' : ''; ?>>    
                                    <?= lang('Supplier Returns'); ?></td>
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
                                <!--<span style="display:inline-block;">
                                <input type="checkbox" value="1" id="supplier-returns-email" class="checkbox" name="supplier-returns-email" <?php echo $p->{'supplier-returns-email'} ? 'checked' : ''; ?>>
                                <label for="supplier-returns-email" class="padding05"><?= lang('email') ?></label>
                                </span>-->
                                <span style="display:inline-block;">
                                <input type="checkbox" value="1" id="supplier-returns-pdf" class="checkbox" name="supplier-returns-pdf" <?php echo $p->{'supplier-returns-pdf'} ? 'checked' : ''; ?>>
                                <label for="supplier-returns-pdf" class="padding05"><?= lang('Export') ?></label>
                                </span>
                                </td>
                                </tr>

                                <tr class="perm-module" data-section="ap" data-section-label="Account Payable">
                                <td>
                                    <input type="checkbox" value="1" class="checkbox" name="payable-reports" <?php echo $p->{'payable-reports'} ? 'checked' : ''; ?>>
                                    <?= lang('Reports'); ?></td>
                                    <td colspan="5">
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-supplier-tb" name="reports-supplier-tb" <?php echo $p->{'reports-supplier-tb'} ? 'checked' : ''; ?>>
                                            <label for="reports-supplier-tb" class="padding05"><?= lang('Supplier TB') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-supplier-tb" name="reports-supplier-advances" <?php echo $p->{'reports-supplier-advances'} ? 'checked' : ''; ?>>
                                            <label for="reports-supplier-advances" class="padding05"><?= lang('Supplier Advances') ?></label>
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
                                            <input type="checkbox" value="1" class="checkbox" id="reports-payment-by_invoice" name="reports-payment-by_invoice" <?php echo !empty($p->{'reports-payment-by_invoice'}) ? 'checked' : ''; ?>>
                                            <label for="reports-payment-by_invoice" class="padding05"><?= lang('Payment By Invoice') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" class="checkbox" id="reports-supplier-payments" name="reports-supplier-payments" <?php echo !empty($p->{'reports-supplier-payments'}) ? 'checked' : ''; ?>>
                                            <label for="reports-supplier-payments" class="padding05"><?= lang('Supplier Payments') ?></label>
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
                                    </td>
                                </tr>

                                <tr class="perm-section" id="perm-sec-inventory" data-section="inventory">
                                    <td colspan="6"><i class="fa fa-archive perm-section-icon"></i><?= lang('Inventory'); ?><i class="fa fa-chevron-down perm-section-toggle"></i></td>
                                </tr>

                                <tr class="perm-module" data-section="inventory" data-section-label="Inventory">
                                <td>
                                <input type="checkbox" value="1" class="checkbox" name="transfers-module" <?php echo !empty($p->{'transfers-module'}) ? 'checked' : ''; ?>>
                                <?= lang('transfers'); ?>
                                </td>
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
                                <!--<span style="display:inline-block;">
                                <input type="checkbox" value="1" id="transfers-email" class="checkbox" name="transfers-email" <?php echo $p->{'transfers-email'} ? 'checked' : ''; ?>>
                                <label for="transfers-email" class="padding05"><?= lang('email') ?></label>
                                </span>-->
                                <span style="display:inline-block;">
                                <input type="checkbox" value="1" id="transfers-pdf" class="checkbox" name="transfers-pdf" <?php echo $p->{'transfers-pdf'} ? 'checked' : ''; ?>>
                                <label for="transfers-pdf" class="padding05"><?= lang('Export') ?></label>
                                </span>

                                <span style="display:inline-block;">
                                <input type="checkbox" value="1" id="inventory-fix" class="checkbox" name="inventory-fix" <?php echo $p->{'inventory-fix'} ? 'checked' : ''; ?>>
                                <label for="inventory-fix" class="padding05"><?= lang('Inventory Fix') ?></label>
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

                                <tr class="perm-module" data-section="inventory" data-section-label="Inventory">
                                    <td>
                                    <input type="checkbox" value="1" class="checkbox" name="inventory-reports" <?php echo !empty($p->{'inventory-reports'}) ? 'checked' : ''; ?>>
                                    <?= lang('Reports'); ?></td>
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
                                    </td>
                                </tr>

                                <tr class="perm-section" id="perm-sec-finance" data-section="finance">
                                    <td colspan="6"><i class="fa fa-money perm-section-icon"></i><?= lang('Finance'); ?><i class="fa fa-chevron-down perm-section-toggle"></i></td>
                                </tr>

                                <tr class="perm-module" data-section="finance" data-section-label="Finance">
                                    <td>
                                        <input type="checkbox" value="1" class="checkbox" name="finance-chart-accounts-module" <?php echo !empty($p->{'finance-chart-accounts-module'}) ? 'checked' : ''; ?>>
                                        <?= lang('Charts Of Accounts') ?: lang('Chart of Accounts'); ?>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="finance-chart-accounts" <?php echo !empty($p->{'finance-chart-accounts'}) ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="finance-chart-accounts-add" <?php echo !empty($p->{'finance-chart-accounts-add'}) ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="finance-chart-accounts-edit" <?php echo !empty($p->{'finance-chart-accounts-edit'}) ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="finance-chart-accounts-delete" <?php echo !empty($p->{'finance-chart-accounts-delete'}) ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="finance-chart-accounts-export" class="checkbox" name="finance-chart-accounts-export" <?php echo !empty($p->{'finance-chart-accounts-export'}) ? 'checked' : ''; ?>>
                                            <label for="finance-chart-accounts-export" class="padding05"><?= lang('Export') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                <tr class="perm-module" data-section="finance" data-section-label="Finance">
                                    <td>
                                        <input type="checkbox" value="1" class="checkbox" name="finance-jv-module" <?php echo !empty($p->{'finance-jv-module'}) ? 'checked' : ''; ?>>
                                        <?= lang('JL Entries'); ?>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="finance-jv" <?php echo !empty($p->{'finance-jv'}) ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="finance-jv-add" <?php echo !empty($p->{'finance-jv-add'}) ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="finance-jv-edit" <?php echo !empty($p->{'finance-jv-edit'}) ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="finance-jv-delete" <?php echo !empty($p->{'finance-jv-delete'}) ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="finance-jv-export" class="checkbox" name="finance-jv-export" <?php echo !empty($p->{'finance-jv-export'}) ? 'checked' : ''; ?>>
                                            <label for="finance-jv-export" class="padding05"><?= lang('Export') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                <tr class="perm-module" data-section="finance" data-section-label="Finance">
                                    <td>
                                        <input type="checkbox" value="1" class="checkbox" name="finance-jv-templates-module" <?php echo !empty($p->{'finance-jv-templates-module'}) ? 'checked' : ''; ?>>
                                        <?= lang('JV Templates'); ?>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="finance-jv-templates" <?php echo !empty($p->{'finance-jv-templates'}) ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="finance-jv-templates-add" <?php echo !empty($p->{'finance-jv-templates-add'}) ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="finance-jv-templates-edit" <?php echo !empty($p->{'finance-jv-templates-edit'}) ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="finance-jv-templates-delete" <?php echo !empty($p->{'finance-jv-templates-delete'}) ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <span style="display:inline-block;">
                                            <input type="checkbox" value="1" id="finance-jv-templates-export" class="checkbox" name="finance-jv-templates-export" <?php echo !empty($p->{'finance-jv-templates-export'}) ? 'checked' : ''; ?>>
                                            <label for="finance-jv-templates-export" class="padding05"><?= lang('Export') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                <tr class="perm-module" data-section="finance" data-section-label="Finance">
                                    <td>
                                    <input type="hidden" name="finance-view-reports" value="0">
                                    <input type="checkbox" value="1" class="checkbox" name="finance-view-reports" <?php echo !empty($p->{'finance-view-reports'}) ? 'checked' : ''; ?>>    
                                    <?= lang('Reports'); ?></td>
                                    <td colspan="5">
                                        <span style="display:inline-block;">
                                            <input type="hidden" name="finance-report-gl-statement" value="0">
                                            <input type="checkbox" value="1" class="checkbox" id="finance-report-gl-statement" name="finance-report-gl-statement" <?php echo !empty($p->{'finance-report-gl-statement'}) ? 'checked' : ''; ?>>
                                            <label for="finance-report-gl-statement" class="padding05"><?= lang('GL Statement') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="hidden" name="finance-report-trial-balance" value="0">
                                            <input type="checkbox" value="1" class="checkbox" id="finance-report-trial-balance" name="finance-report-trial-balance" <?php echo !empty($p->{'finance-report-trial-balance'}) ? 'checked' : ''; ?>>
                                            <label for="finance-report-trial-balance" class="padding05"><?= lang('Trial Balance') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="hidden" name="finance-report-general-ledger" value="0">
                                            <input type="checkbox" value="1" class="checkbox" id="finance-report-general-ledger" name="finance-report-general-ledger" <?php echo !empty($p->{'finance-report-general-ledger'}) ? 'checked' : ''; ?>>
                                            <label for="finance-report-general-ledger" class="padding05"><?= lang('General Ledger Report') ?></label>
                                        </span>
                                        <span style="display:inline-block;">
                                            <input type="hidden" name="finance-report-vat" value="0">
                                            <input type="checkbox" value="1" class="checkbox" id="finance-report-vat" name="finance-report-vat" <?php echo !empty($p->{'finance-report-vat'}) ? 'checked' : ''; ?>>
                                            <label for="finance-report-vat" class="padding05"><?= lang('Vat Report') ?></label>
                                        </span>
                                    </td>
                                </tr>

                                <tr class="perm-module" data-section="finance" data-section-label="Finance">
                                    <td><?= lang('Comparison Reports'); ?></td>
                                    <td class="text-center" colspan="5" style="text-align:left !important;padding-left:12px !important;">
                                        <span class="text-muted" style="font-size:12px;"><i class="fa fa-users"></i> <?= lang('Controlled by financemanager group') ?: 'Controlled by financemanager group membership (not a permission flag)'; ?></span>
                                    </td>
                                </tr>

                                <tr class="perm-section" id="perm-sec-warehouse" data-section="warehouse">
                                    <td colspan="6"><i class="fa fa-building perm-section-icon"></i><?= lang('Warehouse Management'); ?><i class="fa fa-chevron-down perm-section-toggle"></i></td>
                                </tr>

                                <tr class="perm-module" data-section="warehouse" data-section-label="Warehouse Management">
                                <td>
                                <input type="checkbox" value="1" class="checkbox" name="sales-deliveries-module" <?php echo !empty($p->{'sales-deliveries-module'}) ? 'checked' : ''; ?>>
                                <?= lang('deliveries'); ?>
                                </td>
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
                                <label for="sales-pdf_delivery" class="padding05"><?= lang('Export') ?></label>
                                </span>
                                </td>
                                </tr>

                                <!--<tr class="perm-module" data-section="warehouse" data-section-label="Warehouse Management">
                                <td><?= lang('Truck Registration'); ?></td>
                                <td colspan="5">
                                <span style="display:inline-block;">
                                <input type="checkbox" value="1" class="checkbox" id="bulk_actions"
                                name="truck_registration_view" <?php echo $p->truck_registration_view ? 'checked' : ''; ?>>
                                <label for="bulk_actions" class="padding05"><?= lang('View') ?></label>
                                </span>
                                </td>
                                </tr>-->

                                <tr class="perm-module" data-section="warehouse" data-section-label="Warehouse Management">
                                <td>
                                <input type="checkbox" value="1" class="checkbox" name="truck_registration_module" <?php echo $p->{'truck_registration_module'} ? 'checked' : ''; ?>>    
                                <?= lang('Truck Registration'); ?></td>
                                
                                <td class="text-center">
                                <input type="checkbox" value="1" class="checkbox" name="truck_registration_view" <?php echo $p->{'truck_registration_view'} ? 'checked' : ''; ?>>
                                </td>
                                <td class="text-center">
                                <input type="checkbox" value="1" class="checkbox" name="truck_registration_add" <?php echo $p->{'truck_registration_add'} ? 'checked' : ''; ?>>
                                </td>
                                <td class="text-center">
                                <input type="checkbox" value="1" class="checkbox" name="truck_registration_edit" <?php echo $p->{'truck_registration_edit'} ? 'checked' : ''; ?>>
                                </td>
                                <td class="text-center">
                                <input type="checkbox" value="1" class="checkbox" name="truck_registration_delete" <?php echo $p->{'truck_registration_delete'} ? 'checked' : ''; ?>>
                                </td>
                                </tr>

                                <tr class="perm-module" data-section="warehouse" data-section-label="Warehouse Management">
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

                                <tr class="perm-section" id="perm-sec-services" data-section="services">
                                    <td colspan="6"><i class="fa fa-cogs perm-section-icon"></i><?= lang('Services'); ?><i class="fa fa-chevron-down perm-section-toggle"></i></td>
                                </tr>

                                <tr class="perm-module" data-section="services" data-section-label="Services">
                                    <td>
                                        <input type="checkbox" value="1" class="checkbox" name="rasd-notifications-module" <?php echo !empty($p->{'rasd-notifications-module'}) ? 'checked' : ''; ?>>
                                        <?= lang('Rasd Notifications'); ?>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="rasd-notifications" <?php echo !empty($p->{'rasd-notifications'}) ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="rasd-notifications-add" <?php echo !empty($p->{'rasd-notifications-add'}) ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="rasd-notifications-edit" <?php echo !empty($p->{'rasd-notifications-edit'}) ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" value="1" class="checkbox" name="rasd-notifications-delete" <?php echo !empty($p->{'rasd-notifications-delete'}) ? 'checked' : ''; ?>>
                                    </td>
                                    <td></td>
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
                            </div>
                        </div>

                        <div class="perm-footer form-actions">
                            <p class="perm-footer-hint"><i class="fa fa-info-circle"></i> Changes apply after you save.</p>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> <?= lang('update') ?></button>
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
