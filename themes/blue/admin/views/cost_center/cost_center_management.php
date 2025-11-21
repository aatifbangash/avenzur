<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Cost Center Management - Modern Horizon UI Design
 * 
 * Features:
 * - Cost Center Management Interface
 * - Add/Edit/Delete Cost Centers for Pharmacies and Branches
 * - Hierarchical Cost Center Display (Level 1 and Level 2)
 * - Entity Selection (Pharmacy/Branch)
 * - Modern Card-based Layout
 * 
 * Design System:
 * - Primary Blue: #1a73e8
 * - Success Green: #05cd99
 * - Error Red: #f34235
 * - Warning Orange: #ff9a56
 * 
 * Date: 2025-11-10
 */
?>

<!-- Horizon UI Modern Cost Center Management -->
<style>
/* ============================================================================
   HORIZON UI Design System - CSS Variables & Global Styles
   ============================================================================ */

:root {
    --horizon-primary: #1a73e8;
    --horizon-success: #05cd99;
    --horizon-error: #f34235;
    --horizon-warning: #ff9a56;
    --horizon-secondary: #6c5ce7;
    --horizon-dark-text: #111111;
    --horizon-light-text: #7a8694;
    --horizon-bg-light: #f5f5f5;
    --horizon-bg-neutral: #e0e0e0;
    --horizon-border: #e0e0e0;
    --horizon-shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --horizon-shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --horizon-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.cost-center-management {
    background: #ffffff;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    padding: 0;
}

/* ============================================================================
   HEADER SECTION
   ============================================================================ */

.horizon-header {
    background: #ffffff;
    border-bottom: 1px solid var(--horizon-border);
    padding: 20px 24px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.horizon-header-title h1 {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
    color: var(--horizon-dark-text);
}

.horizon-header-title p {
    margin: 4px 0 0 0;
    font-size: 14px;
    color: var(--horizon-light-text);
}

.horizon-header-actions {
    display: flex;
    gap: 12px;
    align-items: center;
}

.btn-tree-control {
    background: var(--horizon-secondary);
    color: white;
    border: none;
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-tree-control:hover {
    background: #5a4fcf;
}

.btn-add-cost-center {
    background: var(--horizon-primary);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 12px 20px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-add-cost-center:hover {
    background: #1557b0;
}

/* ============================================================================
   FILTER BAR
   ============================================================================ */

.filter-bar {
    background: var(--horizon-bg-light);
    border-radius: 8px;
    padding: 16px 20px;
    margin-bottom: 20px;
    display: flex;
    gap: 16px;
    align-items: center;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 200px;
}

.filter-group label {
    font-size: 12px;
    font-weight: 600;
    color: var(--horizon-light-text);
    text-transform: uppercase;
}

.filter-group select {
    padding: 8px 12px;
    border: 1px solid var(--horizon-border);
    border-radius: 6px;
    background: white;
    font-size: 14px;
    cursor: pointer;
}

/* ============================================================================
   HIERARCHICAL TREE STRUCTURE
   ============================================================================ */

.entity-tree {
    margin-bottom: 20px;
}

.tree-node {
    background: white;
    border: 1px solid var(--horizon-border);
    border-radius: 8px;
    margin-bottom: 12px;
    transition: all 0.2s ease;
    overflow: hidden;
}

.tree-node:hover {
    border-color: var(--horizon-primary);
}

.tree-node-header {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.pharmacy-header {
    background: linear-gradient(135deg, #f8f9ff 0%, #e8efff 100%);
    border-bottom: 1px solid var(--horizon-border);
}

.branch-header {
    background: linear-gradient(135deg, #fff8f8 0%, #fff0f0 100%);
    border-bottom: 1px solid #f0f0f0;
    margin-left: 16px;
}

.tree-expand-icon {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
    background: var(--horizon-primary);
    color: white;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 11px;
}

.tree-expand-icon:hover {
    background: #1557b0;
    transform: scale(1.05);
}

.tree-expand-icon.expanded {
    background: var(--horizon-success);
}

.tree-expand-icon i {
    transition: transform 0.3s ease;
}

.tree-node-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    border-radius: 6px;
    font-size: 14px;
}

.pharmacy-node .tree-node-icon {
    background: linear-gradient(135deg, #1a73e8, #4285f4);
    color: white;
}

.branch-node .tree-node-icon {
    background: linear-gradient(135deg, #7b1fa2, #9c27b0);
    color: white;
}

.tree-node-info {
    flex: 1;
}

.tree-node-info h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: var(--horizon-dark-text);
}

.tree-node-info h4 {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    color: var(--horizon-dark-text);
}

.tree-node-info p {
    margin: 4px 0 0 0;
    font-size: 14px;
    color: var(--horizon-light-text);
}

.tree-node-badge {
    margin-right: 16px;
}

.tree-node-actions {
    display: flex;
    gap: 8px;
}

.btn-tree-action {
    padding: 8px 12px;
    border: none;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
}

.btn-tree-action:hover {
    opacity: 0.8;
}

.btn-add-cc {
    background: var(--horizon-success);
    color: white;
}

.btn-add-cc:hover {
    background: #04a085;
}

.btn-view-cc {
    background: var(--horizon-primary);
    color: white;
}

.btn-view-cc:hover {
    background: #1557b0;
}

/* Tree Children (Branches) */
.tree-children {
    padding: 0 16px 16px 16px;
    background: #fafbff;
}

.tree-connector {
    width: 28px;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
}

.connector-line {
    width: 2px;
    height: 32px;
    background: var(--horizon-border);
    position: relative;
}

.connector-line::before {
    content: '';
    position: absolute;
    top: 16px;
    right: -6px;
    width: 12px;
    height: 2px;
    background: var(--horizon-border);
}

/* Tree Cost Centers */
.tree-cost-centers {
    padding: 0 16px 16px 16px;
    background: #f8f9ff;
}

.entity-badge {
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.entity-badge.pharmacy {
    background: #e3f2fd;
    color: #1976d2;
}

.entity-badge.branch {
    background: #f3e5f5;
    color: #7b1fa2;
}

.entity-actions {
    display: flex;
    gap: 8px;
}

.btn-entity-action {
    padding: 6px 12px;
    border: none;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-add-cc {
    background: var(--horizon-success);
    color: white;
}

.btn-add-cc:hover {
    background: #04a085;
}

.btn-view-cc {
    background: var(--horizon-primary);
    color: white;
}

.btn-view-cc:hover {
    background: #1557b0;
}

/* ============================================================================
   COST CENTERS LIST
   ============================================================================ */

.cost-centers-list {
    margin-top: 12px;
}

.cost-center-item {
    display: flex;
    justify-content: between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.cost-center-item:last-child {
    border-bottom: none;
}

.cost-center-info {
    flex: 1;
}

.cost-center-info .name {
    font-weight: 600;
    color: var(--horizon-dark-text);
    font-size: 14px;
}

.cost-center-info .code {
    color: var(--horizon-light-text);
    font-size: 12px;
    margin-top: 2px;
}

.cost-center-level {
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    margin-right: 8px;
}

.cost-center-level.level-1 {
    background: #e8f5e8;
    color: #2e7d32;
}

.cost-center-level.level-2 {
    background: #e3f2fd;
    color: #1976d2;
}

.cost-center-actions {
    display: flex;
    gap: 4px;
}

.btn-cc-action {
    padding: 4px 8px;
    border: none;
    border-radius: 4px;
    font-size: 11px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-edit {
    background: var(--horizon-warning);
    color: white;
}

.btn-edit:hover {
    background: #e68900;
}

.btn-delete {
    background: var(--horizon-error);
    color: white;
}

.btn-delete:hover {
    background: #d32f2f;
}

/* ============================================================================
   MODAL STYLES
   ============================================================================ */

.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    display: none;
}

.modal-container {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 12px;
    padding: 24px;
    width: 90%;
    max-width: 500px;
    max-height: 80vh;
    overflow-y: auto;
    z-index: 1001;
    border: 1px solid var(--horizon-border);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--horizon-border);
}

.modal-header h3 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: var(--horizon-dark-text);
}

.btn-close {
    background: none;
    border: none;
    font-size: 24px;
    color: var(--horizon-light-text);
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
}

.btn-close:hover {
    background: var(--horizon-bg-light);
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: var(--horizon-dark-text);
    margin-bottom: 6px;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--horizon-border);
    border-radius: 6px;
    font-size: 14px;
    background: white;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--horizon-primary);
    border-width: 2px;
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid var(--horizon-border);
}

.btn-primary {
    background: var(--horizon-primary);
    color: white;
    border: none;
    border-radius: 6px;
    padding: 10px 20px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    flex: 1;
}

.btn-primary:hover {
    background: #1557b0;
}

.btn-secondary {
    background: var(--horizon-bg-light);
    color: var(--horizon-dark-text);
    border: 1px solid var(--horizon-border);
    border-radius: 6px;
    padding: 10px 20px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    flex: 1;
}

.btn-secondary:hover {
    background: var(--horizon-bg-neutral);
}

/* ============================================================================
   RESPONSIVE
   ============================================================================ */

@media (max-width: 768px) {
    .entity-tree {
        margin-bottom: 16px;
    }
    
    .horizon-header {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
    
    .horizon-header-actions {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .btn-tree-control {
        font-size: 11px;
        padding: 6px 10px;
    }
    
    .tree-node-header {
        padding: 10px 12px;
    }
    
    .branch-header {
        margin-left: 8px;
    }
    
    .tree-children {
        padding: 0 8px 12px 8px;
    }
    
    .tree-cost-centers {
        padding: 0 8px 12px 8px;
    }
    
    .tree-node-info h3 {
        font-size: 14px;
    }
    
    .tree-node-info h4 {
        font-size: 13px;
    }
    
    .btn-tree-action {
        width: 32px;
        height: 32px;
        font-size: 11px;
    }
    
    .filter-bar {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-group {
        min-width: auto;
    }
    
    .modal-container {
        width: 95%;
        margin: 20px;
        padding: 20px;
    }
}

.loading {
    opacity: 0.5;
    pointer-events: none;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: var(--horizon-light-text);
}

.empty-state.small {
    padding: 20px 10px;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
}

.empty-state.small i {
    font-size: 24px;
    margin-bottom: 8px;
}

.empty-state h3 {
    margin: 0 0 8px 0;
    font-size: 18px;
}

.empty-state p {
    margin: 0;
    font-size: 14px;
}

.empty-state.small p {
    font-size: 12px;
}
</style>

<div class="cost-center-management">
    <!-- Header Section -->
    <div class="horizon-header">
        <div class="horizon-header-title">
            <h1>Cost Center Management</h1>
            <p>Configure cost centers for existing pharmacies and branches</p>
        </div>
        <div class="horizon-header-actions">
            <button class="btn-tree-control" onclick="expandAllTrees()" title="Expand All">
                <i class="fa fa-expand-arrows-alt"></i>
                Expand All
            </button>
            <button class="btn-tree-control" onclick="collapseAllTrees()" title="Collapse All">
                <i class="fa fa-compress-arrows-alt"></i>
                Collapse All
            </button>
            <button class="btn-add-cost-center" onclick="openAddCostCenterModal()">
                <i class="fa fa-plus"></i>
                Add Cost Center
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="filter-group">
            <label>Filter by Entity Type</label>
            <select id="entityTypeFilter" onchange="filterEntities()">
                <option value="">All Entities</option>
                <option value="pharmacy">Pharmacies Only</option>
                <option value="branch">Branches Only</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Search Entity</label>
            <input type="text" id="searchEntity" placeholder="Search by name or code..." onkeyup="filterEntities()">
        </div>
    </div>

    <!-- Hierarchical Tree Structure -->
    <div class="entity-tree" id="entityTree">
        <?php if (!empty($pharmacies)): ?>
            <?php 
            // Group entities by hierarchy: pharmacies and their branches
            $pharmacies_list = [];
            $branches_by_pharmacy = [];
            
            // Debug: Let's see what we have
            $total_pharmacies = 0;
            $total_branches = 0;
            
            foreach ($pharmacies as $entity) {
                if ($entity['warehouse_type'] === 'pharmacy') {
                    $pharmacies_list[$entity['warehouse_id']] = $entity;
                    $total_pharmacies++;
                } else if ($entity['warehouse_type'] === 'branch' && $entity['parent_id']) {
                    $branches_by_pharmacy[$entity['parent_id']][] = $entity;
                    $total_branches++;
                }
            }
            ?>
            
            <?php foreach ($pharmacies_list as $pharmacy_id => $pharmacy): ?>
                <div class="tree-node pharmacy-node" data-entity-type="pharmacy" data-entity-name="<?= strtolower($pharmacy['warehouse_name']) ?>">
                    <!-- Pharmacy Header -->
                    <div class="tree-node-header pharmacy-header">
                        <?php 
                        $branch_count = isset($branches_by_pharmacy[$pharmacy_id]) ? count($branches_by_pharmacy[$pharmacy_id]) : 0;
                        ?>
                        <?php if ($branch_count > 0): ?>
                            <div class="tree-expand-icon" onclick="toggleTreeNode(<?= $pharmacy_id ?>)">
                                <i class="fa fa-chevron-right" id="expand_<?= $pharmacy_id ?>"></i>
                            </div>
                        <?php else: ?>
                            <div class="tree-expand-icon" style="background: #ccc; cursor: not-allowed;" title="No branches">
                                <i class="fa fa-circle"></i>
                            </div>
                        <?php endif; ?>
                        <div class="tree-node-icon">
                            <i class="fa fa-building"></i>
                        </div>
                        <div class="tree-node-info">
                            <h3><?= htmlspecialchars($pharmacy['warehouse_name']) ?></h3>
                            <p>Pharmacy • Code: <?= htmlspecialchars($pharmacy['warehouse_code']) ?>
                            <?php if ($branch_count > 0): ?>
                                • <?= $branch_count ?> branch<?= $branch_count != 1 ? 'es' : '' ?>
                            <?php else: ?>
                                • No branches
                            <?php endif; ?>
                            </p>
                        </div>
                        <div class="tree-node-badge">
                            <span class="entity-badge pharmacy">Pharmacy</span>
                        </div>
                        <div class="tree-node-actions">
                            <button class="btn-tree-action btn-add-cc" onclick="openAddCostCenterModal(<?= $pharmacy_id ?>, '<?= htmlspecialchars($pharmacy['warehouse_name']) ?>')" title="Add Cost Center">
                                <i class="fa fa-plus"></i>
                            </button>
                            <button class="btn-tree-action btn-view-cc" onclick="toggleCostCentersList(<?= $pharmacy_id ?>)" title="View Cost Centers">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Pharmacy Cost Centers -->
                    <div class="tree-cost-centers" id="costCentersList_<?= $pharmacy_id ?>" style="display: none;">
                        <?php 
                        $pharmacy_cost_centers = isset($cost_centers_by_entity[$pharmacy_id]) ? $cost_centers_by_entity[$pharmacy_id] : [];
                        if (!empty($pharmacy_cost_centers)): 
                        ?>
                            <?php foreach ($pharmacy_cost_centers as $cc): ?>
                                <div class="cost-center-item">
                                    <span class="cost-center-level level-<?= $cc['cost_center_level'] ?>">
                                        Level <?= $cc['cost_center_level'] ?>
                                    </span>
                                    <div class="cost-center-info">
                                        <div class="name"><?= htmlspecialchars($cc['cost_center_name']) ?></div>
                                        <div class="code"><?= htmlspecialchars($cc['cost_center_code']) ?></div>
                                    </div>
                                    <div class="cost-center-actions">
                                        <button class="btn-cc-action btn-edit" onclick="editCostCenter(<?= $cc['cost_center_id'] ?>)" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button class="btn-cc-action btn-delete" onclick="deleteCostCenter(<?= $cc['cost_center_id'] ?>)" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state small">
                                <i class="fa fa-folder-open"></i>
                                <p>No cost centers configured for this pharmacy.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Branches of this Pharmacy -->
                    <div class="tree-children" id="branches_<?= $pharmacy_id ?>" style="display: none;">
                        <?php if (isset($branches_by_pharmacy[$pharmacy_id])): ?>
                            <?php foreach ($branches_by_pharmacy[$pharmacy_id] as $branch): ?>
                                <div class="tree-node branch-node" data-entity-type="branch" data-entity-name="<?= strtolower($branch['warehouse_name']) ?>">
                                    <!-- Branch Header -->
                                    <div class="tree-node-header branch-header">
                                        <div class="tree-connector">
                                            <div class="connector-line"></div>
                                        </div>
                                        <div class="tree-node-icon">
                                            <i class="fa fa-store"></i>
                                        </div>
                                        <div class="tree-node-info">
                                            <h4><?= htmlspecialchars($branch['warehouse_name']) ?></h4>
                                            <p>Branch • Code: <?= htmlspecialchars($branch['warehouse_code']) ?></p>
                                        </div>
                                        <div class="tree-node-badge">
                                            <span class="entity-badge branch">Branch</span>
                                        </div>
                                        <div class="tree-node-actions">
                                            <button class="btn-tree-action btn-add-cc" onclick="openAddCostCenterModal(<?= $branch['warehouse_id'] ?>, '<?= htmlspecialchars($branch['warehouse_name']) ?>')" title="Add Cost Center">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                            <button class="btn-tree-action btn-view-cc" onclick="toggleCostCentersList(<?= $branch['warehouse_id'] ?>)" title="View Cost Centers">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Branch Cost Centers -->
                                    <div class="tree-cost-centers" id="costCentersList_<?= $branch['warehouse_id'] ?>" style="display: none;">
                                        <?php 
                                        $branch_cost_centers = isset($cost_centers_by_entity[$branch['warehouse_id']]) ? $cost_centers_by_entity[$branch['warehouse_id']] : [];
                                        if (!empty($branch_cost_centers)): 
                                        ?>
                                            <?php foreach ($branch_cost_centers as $cc): ?>
                                                <div class="cost-center-item">
                                                    <span class="cost-center-level level-<?= $cc['cost_center_level'] ?>">
                                                        Level <?= $cc['cost_center_level'] ?>
                                                    </span>
                                                    <div class="cost-center-info">
                                                        <div class="name"><?= htmlspecialchars($cc['cost_center_name']) ?></div>
                                                        <div class="code"><?= htmlspecialchars($cc['cost_center_code']) ?></div>
                                                    </div>
                                                    <div class="cost-center-actions">
                                                        <button class="btn-cc-action btn-edit" onclick="editCostCenter(<?= $cc['cost_center_id'] ?>)" title="Edit">
                                                            <i class="fa fa-edit"></i>
                                                        </button>
                                                        <button class="btn-cc-action btn-delete" onclick="deleteCostCenter(<?= $cc['cost_center_id'] ?>)" title="Delete">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="empty-state small">
                                                <i class="fa fa-folder-open"></i>
                                                <p>No cost centers configured for this branch.</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa fa-building"></i>
                <h3>No Entities Found</h3>
                <p>No pharmacies or branches found in the system.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add/Edit Cost Center Modal -->
<div class="modal-backdrop" id="costCenterModal">
    <div class="modal-container">
        <div class="modal-header">
            <h3 id="modalTitle">Add Cost Center</h3>
            <button class="btn-close" onclick="closeCostCenterModal()">&times;</button>
        </div>

        <form id="costCenterForm">
            <input type="hidden" id="costCenterId" name="cost_center_id">
            <input type="hidden" id="entityId" name="entity_id">

            <div class="form-group">
                <label for="entityName">Entity *</label>
                <input type="text" id="entityName" readonly>
            </div>

            <div class="form-group">
                <label for="costCenterCode">Cost Center Code *</label>
                <input type="text" id="costCenterCode" name="cost_center_code" placeholder="e.g., CC001" required>
            </div>

            <div class="form-group">
                <label for="costCenterName">Cost Center Name *</label>
                <input type="text" id="costCenterName" name="cost_center_name" placeholder="e.g., Administration" required>
            </div>

            <div class="form-group">
                <label for="costCenterLevel">Cost Center Level *</label>
                <select id="costCenterLevel" name="cost_center_level" required>
                    <option value="">Select Level</option>
                    <option value="1">Level 1 (Primary)</option>
                    <option value="2">Level 2 (Secondary)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="parentCostCenter">Parent Cost Center</label>
                <select id="parentCostCenter" name="parent_cost_center_id">
                    <option value="">None (Top Level)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" placeholder="Optional description..."></textarea>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeCostCenterModal()">Cancel</button>
                <button type="submit" class="btn-primary" id="submitBtn">Save Cost Center</button>
            </div>
        </form>
    </div>
</div>

<script>
// Global variables
let currentEntityId = null;
let currentCostCenterId = null;

// Initialize page
$(document).ready(function() {
    // Setup CSRF token for all AJAX requests
    $.ajaxSetup({
        data: {
            'token': '<?= $this->security->get_csrf_hash(); ?>'
        }
    });
    
    initializePage();
});

function initializePage() {
    // Initialize form validation
    $('#costCenterForm').on('submit', function(e) {
        e.preventDefault();
        saveCostCenter();
    });

    // Initialize cost center level change handler
    $('#costCenterLevel').on('change', function() {
        loadParentCostCenters();
    });
}

// Toggle tree node (expand/collapse branches)
function toggleTreeNode(pharmacyId) {
    const $branchesContainer = $('#branches_' + pharmacyId);
    const $expandIcon = $('#expand_' + pharmacyId);
    const $expandButton = $expandIcon.closest('.tree-expand-icon');
    
    if ($branchesContainer.is(':visible')) {
        // Collapse
        $branchesContainer.slideUp(300);
        $expandButton.removeClass('expanded');
        $expandIcon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
    } else {
        // Expand
        $branchesContainer.slideDown(300);
        $expandButton.addClass('expanded');
        $expandIcon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
    }
}

// Expand all pharmacy trees
function expandAllTrees() {
    $('.tree-children').slideDown(300);
    $('.tree-expand-icon').addClass('expanded');
    $('.tree-expand-icon i').removeClass('fa-chevron-right').addClass('fa-chevron-down');
}

// Collapse all pharmacy trees
function collapseAllTrees() {
    $('.tree-children').slideUp(300);
    $('.tree-expand-icon').removeClass('expanded');
    $('.tree-expand-icon i').removeClass('fa-chevron-down').addClass('fa-chevron-right');
}

// Filter entities (updated for tree structure)
function filterEntities() {
    const entityTypeFilter = $('#entityTypeFilter').val();
    const searchQuery = $('#searchEntity').val().toLowerCase();
    
    $('.tree-node').each(function() {
        const $node = $(this);
        const entityType = $node.data('entity-type');
        const entityName = $node.data('entity-name');
        
        let showNode = true;
        
        // Filter by entity type
        if (entityTypeFilter && entityType !== entityTypeFilter) {
            showNode = false;
        }
        
        // Filter by search query
        if (searchQuery && !entityName.includes(searchQuery)) {
            showNode = false;
        }
        
        if (showNode) {
            $node.show();
        } else {
            $node.hide();
        }
    });
}

// Toggle cost centers list
function toggleCostCentersList(entityId) {
    const $list = $('#costCentersList_' + entityId);
    if ($list.is(':visible')) {
        $list.slideUp();
    } else {
        $list.slideDown();
        // Refresh the list
        refreshCostCentersList(entityId);
    }
}

// Refresh cost centers list for an entity
function refreshCostCentersList(entityId) {
    $.ajax({
        url: '<?= admin_url('cost_center/get_entity_cost_centers/') ?>' + entityId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                updateCostCentersList(entityId, response.cost_centers);
            }
        },
        error: function() {
            showNotification('Error loading cost centers', 'error');
        }
    });
}

// Update cost centers list HTML
function updateCostCentersList(entityId, costCenters) {
    const $container = $('#costCentersList_' + entityId);
    
    if (costCenters.length === 0) {
        $container.html(`
            <div class="empty-state">
                <i class="fa fa-folder-open"></i>
                <h3>No Cost Centers</h3>
                <p>No cost centers configured for this entity.</p>
            </div>
        `);
        return;
    }
    
    let html = '';
    costCenters.forEach(function(cc) {
        html += `
            <div class="cost-center-item">
                <span class="cost-center-level level-${cc.cost_center_level}">
                    Level ${cc.cost_center_level}
                </span>
                <div class="cost-center-info">
                    <div class="name">${escapeHtml(cc.cost_center_name)}</div>
                    <div class="code">${escapeHtml(cc.cost_center_code)}</div>
                </div>
                <div class="cost-center-actions">
                    <button class="btn-cc-action btn-edit" onclick="editCostCenter(${cc.cost_center_id})">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn-cc-action btn-delete" onclick="deleteCostCenter(${cc.cost_center_id})">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    
    $container.html(html);
}

// Open add cost center modal
function openAddCostCenterModal(entityId = null, entityName = '') {
    currentEntityId = entityId;
    currentCostCenterId = null;
    
    // Reset form
    $('#costCenterForm')[0].reset();
    $('#costCenterId').val('');
    $('#entityId').val(entityId || '');
    $('#entityName').val(entityName);
    
    // Update modal title
    $('#modalTitle').text('Add Cost Center');
    $('#submitBtn').text('Save Cost Center');
    
    // Show modal
    $('#costCenterModal').fadeIn(300);
    
    // Load parent cost centers if entity is selected
    if (entityId) {
        loadParentCostCenters();
    }
}

// Edit cost center
function editCostCenter(costCenterId) {
    currentCostCenterId = costCenterId;
    
    // Load cost center data
    $.ajax({
        url: '<?= admin_url('cost_center/get_cost_center_by_id/') ?>' + costCenterId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                populateCostCenterForm(response.cost_center);
            } else {
                showNotification('Error loading cost center data', 'error');
            }
        },
        error: function() {
            showNotification('Error loading cost center data', 'error');
        }
    });
}

// Populate form with cost center data
function populateCostCenterForm(costCenter) {
    $('#costCenterId').val(costCenter.cost_center_id);
    $('#entityId').val(costCenter.entity_id);
    $('#entityName').val(costCenter.entity_name);
    $('#costCenterCode').val(costCenter.cost_center_code);
    $('#costCenterName').val(costCenter.cost_center_name);
    $('#costCenterLevel').val(costCenter.cost_center_level);
    $('#description').val(costCenter.description);
    
    currentEntityId = costCenter.entity_id;
    
    // Update modal title
    $('#modalTitle').text('Edit Cost Center');
    $('#submitBtn').text('Update Cost Center');
    
    // Show modal
    $('#costCenterModal').fadeIn(300);
    
    // Load parent cost centers and select current parent
    loadParentCostCenters(costCenter.parent_cost_center_id);
}

// Load parent cost centers
function loadParentCostCenters(selectedParentId = null) {
    if (!currentEntityId) return;
    
    const currentLevel = $('#costCenterLevel').val();
    if (!currentLevel || currentLevel == '1') {
        $('#parentCostCenter').html('<option value="">None (Top Level)</option>');
        return;
    }
    
    // For level 2, load level 1 cost centers of the same entity
    $.ajax({
        url: '<?= admin_url('cost_center/get_parent_cost_centers/') ?>' + currentEntityId + '/1',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let options = '<option value="">None (Top Level)</option>';
                response.cost_centers.forEach(function(cc) {
                    const selected = (cc.cost_center_id == selectedParentId) ? 'selected' : '';
                    options += `<option value="${cc.cost_center_id}" ${selected}>${cc.cost_center_name} (${cc.cost_center_code})</option>`;
                });
                $('#parentCostCenter').html(options);
            }
        },
        error: function() {
            showNotification('Error loading parent cost centers', 'error');
        }
    });
}

// Save cost center
function saveCostCenter() {
    const formData = $('#costCenterForm').serialize();
    const url = currentCostCenterId ? 
        '<?= admin_url('cost_center/update_cost_center') ?>' : 
        '<?= admin_url('cost_center/add_cost_center') ?>';
    
    // Disable form
    $('#costCenterForm').addClass('loading');
    $('#submitBtn').prop('disabled', true).text('Saving...');
    
    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showNotification(response.message, 'success');
                closeCostCenterModal();
                // Refresh the page to show updated data
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showNotification(response.message, 'error');
            }
        },
        error: function() {
            showNotification('Error saving cost center', 'error');
        },
        complete: function() {
            $('#costCenterForm').removeClass('loading');
            $('#submitBtn').prop('disabled', false).text(currentCostCenterId ? 'Update Cost Center' : 'Save Cost Center');
        }
    });
}

// Delete cost center
function deleteCostCenter(costCenterId) {
    if (!confirm('Are you sure you want to delete this cost center?')) {
        return;
    }
    
    $.ajax({
        url: '<?= admin_url('cost_center/delete_cost_center') ?>',
        type: 'POST',
        data: { cost_center_id: costCenterId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showNotification(response.message, 'success');
                // Refresh the page to show updated data
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showNotification(response.message, 'error');
            }
        },
        error: function() {
            showNotification('Error deleting cost center', 'error');
        }
    });
}

// Close modal
function closeCostCenterModal() {
    $('#costCenterModal').fadeOut(300);
    currentEntityId = null;
    currentCostCenterId = null;
}

// Utility functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showNotification(message, type = 'info') {
    // Use SMA's existing notification system if available
    if (typeof window.sma !== 'undefined' && window.sma.alert) {
        window.sma.alert(message, type);
    } else {
        // Fallback to browser alert
        alert(message);
    }
}

// Close modal on backdrop click
$(document).on('click', '.modal-backdrop', function(e) {
    if (e.target === this) {
        closeCostCenterModal();
    }
});

// Close modal on Escape key
$(document).on('keydown', function(e) {
    if (e.key === 'Escape' && $('#costCenterModal').is(':visible')) {
        closeCostCenterModal();
    }
});
</script>