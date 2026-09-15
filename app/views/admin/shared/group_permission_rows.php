<?php defined('BASEPATH') or exit('No direct script access allowed');

$permissionCatalog = !empty($permission_catalog) && is_array($permission_catalog) ? $permission_catalog : [];
$groupPermissions = [];
$permissionsReadonly = !empty($permissions_readonly);
if (!empty($p)) {
    $groupPermissions = is_object($p) ? get_object_vars($p) : (array) $p;
}

$labelOverrides = [
    'api' => 'API',
    'crm' => 'CRM',
    'grn' => 'GRN',
    'jv'  => 'JV',
    'po'  => 'PO',
    'pos' => 'POS',
    'pr'  => 'PR',
    'rsd' => 'RSD',
];

$formatLabel = static function ($value) use ($labelOverrides) {
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    if (isset($labelOverrides[strtolower($value)])) {
        return $labelOverrides[strtolower($value)];
    }

    $words = preg_split('/[-_\s]+/', $value);
    $words = array_values(array_filter($words, static function ($word) {
        return $word !== '';
    }));

    $formatted = array_map(static function ($word) use ($labelOverrides) {
        $lower = strtolower($word);
        if (isset($labelOverrides[$lower])) {
            return $labelOverrides[$lower];
        }

        return ucfirst($lower);
    }, $words);

    return implode(' ', $formatted);
};

$modules = [];
foreach ($permissionCatalog as $permissionDefinition) {
    $permissionKey = isset($permissionDefinition['permission_key']) ? trim((string) $permissionDefinition['permission_key']) : '';
    if ($permissionKey === '') {
        continue;
    }

    $moduleKey = isset($permissionDefinition['module']) ? trim((string) $permissionDefinition['module']) : '';
    if ($moduleKey === '') {
        $moduleKey = strpos($permissionKey, '-') !== false ? strtok($permissionKey, '-') : $permissionKey;
    }

    if (!isset($modules[$moduleKey])) {
        $modules[$moduleKey] = [
            'label'  => $formatLabel($moduleKey),
            'view'   => null,
            'add'    => null,
            'edit'   => null,
            'delete' => null,
            'misc'   => [],
        ];
    }

    $permissionLabel = isset($permissionDefinition['name']) ? trim((string) $permissionDefinition['name']) : '';
    if ($permissionLabel === '') {
        $permissionLabel = $formatLabel($permissionKey);
    }

    $entry = [
        'key'   => $permissionKey,
        'label' => $permissionLabel,
    ];

    if (preg_match('/^' . preg_quote($moduleKey, '/') . '-(index|add|edit|delete)$/', $permissionKey, $matches)) {
        $modules[$moduleKey][$matches[1]] = $entry;
        continue;
    }

    $modules[$moduleKey]['misc'][] = $entry;
}

$renderCheckbox = static function ($entry, $groupPermissions, $permissionsReadonly = false) {
    if (empty($entry['key'])) {
        return '&nbsp;';
    }

    $permissionKey = (string) $entry['key'];
    $permissionLabel = isset($entry['label']) ? (string) $entry['label'] : $permissionKey;
    $checked = !empty($groupPermissions[$permissionKey]) ? ' checked' : '';
    $disabled = $permissionsReadonly ? ' disabled' : '';
    $inputId = 'perm-' . preg_replace('/[^a-zA-Z0-9_-]+/', '-', $permissionKey);

    return '<label class="sr-only" for="' . html_escape($inputId) . '">' . html_escape($permissionLabel) . '</label>'
        . '<input type="checkbox" value="1" class="checkbox" id="' . html_escape($inputId) . '" name="permissions[' . html_escape($permissionKey) . ']"' . $checked . ($permissionsReadonly ? ' disabled' : '') . '>';
};
?>
<tbody>
<?php if (!empty($modules)) { ?>
    <?php foreach ($modules as $module) { ?>
        <tr>
            <td><?= html_escape($module['label']); ?></td>
            <td class="text-center"><?= $renderCheckbox($module['view'], $groupPermissions, $permissionsReadonly); ?></td>
            <td class="text-center"><?= $renderCheckbox($module['add'], $groupPermissions, $permissionsReadonly); ?></td>
            <td class="text-center"><?= $renderCheckbox($module['edit'], $groupPermissions, $permissionsReadonly); ?></td>
            <td class="text-center"><?= $renderCheckbox($module['delete'], $groupPermissions, $permissionsReadonly); ?></td>
            <td>
                <?php if (!empty($module['misc'])) { ?>
                    <?php foreach ($module['misc'] as $entry) { ?>
                        <span style="display:inline-block;">
                            <?php $inputId = 'perm-' . preg_replace('/[^a-zA-Z0-9_-]+/', '-', $entry['key']); ?>
                            <input type="checkbox" value="1" id="<?= html_escape($inputId); ?>" class="checkbox" name="permissions[<?= html_escape($entry['key']); ?>]" <?= !empty($groupPermissions[$entry['key']]) ? 'checked' : ''; ?> <?= $permissionsReadonly ? 'disabled' : ''; ?>>
                            <label for="<?= html_escape($inputId); ?>" class="padding05"><?= html_escape($entry['label']); ?></label>
                        </span>
                    <?php } ?>
                <?php } else { ?>
                    <span class="text-muted">&nbsp;</span>
                <?php } ?>
            </td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="6" class="text-center text-muted">No permission catalog entries are available.</td>
    </tr>
<?php } ?>
</tbody>
