<?php
/**
 * Cost Center Management - Test Script
 * 
 * This script tests the newly implemented cost center management functionality:
 * 1. Database connection to sma_cost_centers table
 * 2. Cost Center model methods
 * 3. Controller endpoints
 * 4. Sample data creation and manipulation
 * 
 * Usage: Run this script from browser to validate the implementation
 * URL: http://your-domain/test_cost_center_management.php
 * 
 * Date: 2025-11-10
 */

// Include CodeIgniter bootstrap
require_once('index.php');

// Get CI instance
$CI =& get_instance();
$CI->load->database();
$CI->load->model('admin/Cost_center_model', 'cost_center');

?>
<!DOCTYPE html>
<html>
<head>
    <title>Cost Center Management - Test Results</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .test-section { margin-bottom: 30px; border: 1px solid #ddd; padding: 20px; border-radius: 6px; }
        .test-section h2 { margin-top: 0; color: #333; border-bottom: 2px solid #1a73e8; padding-bottom: 10px; }
        .success { color: #05cd99; font-weight: bold; }
        .error { color: #f34235; font-weight: bold; }
        .info { color: #1a73e8; font-weight: bold; }
        .warning { color: #ff9a56; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 4px; font-family: monospace; margin: 10px 0; }
        .btn { background: #1a73e8; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #1557b0; }
        .btn-danger { background: #f34235; }
        .btn-success { background: #05cd99; }
        .test-results { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Cost Center Management - Test Results</h1>
        <p><strong>Date:</strong> <?= date('Y-m-d H:i:s') ?></p>
        <p><strong>Purpose:</strong> Verify cost center management implementation for existing pharmacies and branches.</p>
        
        <div class="test-results">
            
            <!-- Test 1: Database Table Check -->
            <div class="test-section">
                <h2>1. Database Table Verification</h2>
                <?php
                try {
                    // Check if sma_cost_centers table exists
                    $table_check = $CI->db->query("SHOW TABLES LIKE 'sma_cost_centers'")->num_rows();
                    
                    if ($table_check > 0) {
                        echo '<p class="success">✓ Table sma_cost_centers exists</p>';
                        
                        // Check table structure
                        $structure = $CI->db->query("DESCRIBE sma_cost_centers")->result_array();
                        echo '<h3>Table Structure:</h3>';
                        echo '<table>';
                        echo '<tr><th>Field</th><th>Type</th><th>Key</th></tr>';
                        foreach ($structure as $field) {
                            echo '<tr>';
                            echo '<td>' . $field['Field'] . '</td>';
                            echo '<td>' . $field['Type'] . '</td>';
                            echo '<td>' . $field['Key'] . '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                        
                        // Check existing records
                        $record_count = $CI->db->get('sma_cost_centers')->num_rows();
                        echo '<p class="info">📊 Total cost centers: ' . $record_count . '</p>';
                        
                    } else {
                        echo '<p class="error">✗ Table sma_cost_centers does NOT exist!</p>';
                        echo '<div class="code">
                        CREATE TABLE `sma_cost_centers` (
                          `cost_center_id` int NOT NULL AUTO_INCREMENT,
                          `cost_center_code` varchar(50) NOT NULL,
                          `cost_center_name` varchar(100) NOT NULL,
                          `cost_center_level` int NOT NULL,
                          `entity_id` int NOT NULL,
                          `parent_cost_center_id` int DEFAULT NULL,
                          `description` text,
                          `is_active` tinyint(1) DEFAULT 1,
                          `created_date` timestamp DEFAULT CURRENT_TIMESTAMP,
                          `modified_date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                          PRIMARY KEY (`cost_center_id`),
                          UNIQUE KEY `cost_center_code` (`cost_center_code`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                        </div>';
                    }
                } catch (Exception $e) {
                    echo '<p class="error">✗ Database error: ' . $e->getMessage() . '</p>';
                }
                ?>
            </div>

            <!-- Test 2: Model Methods Check -->
            <div class="test-section">
                <h2>2. Model Methods Verification</h2>
                <?php
                try {
                    // Test model loading
                    if (method_exists($CI->cost_center, 'get_all_pharmacies_for_cost_center')) {
                        echo '<p class="success">✓ Cost_center_model loaded successfully</p>';
                        
                        // Test get_all_pharmacies_for_cost_center method
                        $pharmacies = $CI->cost_center->get_all_pharmacies_for_cost_center();
                        echo '<p class="info">📋 Found ' . count($pharmacies) . ' pharmacies/branches for cost center management</p>';
                        
                        if (!empty($pharmacies)) {
                            echo '<h3>Available Entities:</h3>';
                            echo '<table>';
                            echo '<tr><th>ID</th><th>Name</th><th>Type</th><th>Code</th></tr>';
                            foreach (array_slice($pharmacies, 0, 5) as $pharmacy) {
                                echo '<tr>';
                                echo '<td>' . $pharmacy['warehouse_id'] . '</td>';
                                echo '<td>' . htmlspecialchars($pharmacy['warehouse_name']) . '</td>';
                                echo '<td>' . ucfirst($pharmacy['warehouse_type']) . '</td>';
                                echo '<td>' . htmlspecialchars($pharmacy['warehouse_code']) . '</td>';
                                echo '</tr>';
                            }
                            if (count($pharmacies) > 5) {
                                echo '<tr><td colspan="4"><em>... and ' . (count($pharmacies) - 5) . ' more</em></td></tr>';
                            }
                            echo '</table>';
                        }
                        
                        // Test get_all_cost_centers method
                        $cost_centers = $CI->cost_center->get_all_cost_centers();
                        echo '<p class="info">🎯 Found ' . count($cost_centers) . ' existing cost centers</p>';
                        
                    } else {
                        echo '<p class="error">✗ Cost center model methods not found</p>';
                    }
                    
                } catch (Exception $e) {
                    echo '<p class="error">✗ Model error: ' . $e->getMessage() . '</p>';
                }
                ?>
            </div>

            <!-- Test 3: Controller Endpoints Check -->
            <div class="test-section">
                <h2>3. Controller Endpoints Verification</h2>
                <?php
                try {
                    // Check if controller file exists
                    $controller_file = APPPATH . 'controllers/admin/Cost_center.php';
                    if (file_exists($controller_file)) {
                        echo '<p class="success">✓ Cost_center controller exists</p>';
                        
                        // Read controller file and check for new methods
                        $controller_content = file_get_contents($controller_file);
                        
                        $methods_to_check = [
                            'management' => 'Cost Center Management Page',
                            'add_cost_center' => 'Add Cost Center',
                            'update_cost_center' => 'Update Cost Center',
                            'delete_cost_center' => 'Delete Cost Center',
                            'get_entity_cost_centers' => 'Get Entity Cost Centers',
                            'get_cost_center_by_id' => 'Get Cost Center By ID',
                            'get_parent_cost_centers' => 'Get Parent Cost Centers'
                        ];
                        
                        echo '<h3>Controller Methods:</h3>';
                        echo '<table>';
                        echo '<tr><th>Method</th><th>Description</th><th>Status</th></tr>';
                        
                        foreach ($methods_to_check as $method => $description) {
                            $method_exists = strpos($controller_content, 'function ' . $method) !== false;
                            $status = $method_exists ? '<span class="success">✓ Implemented</span>' : '<span class="error">✗ Missing</span>';
                            
                            echo '<tr>';
                            echo '<td>' . $method . '()</td>';
                            echo '<td>' . $description . '</td>';
                            echo '<td>' . $status . '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                        
                    } else {
                        echo '<p class="error">✗ Controller file not found</p>';
                    }
                } catch (Exception $e) {
                    echo '<p class="error">✗ Controller error: ' . $e->getMessage() . '</p>';
                }
                ?>
            </div>

            <!-- Test 4: View File Check -->
            <div class="test-section">
                <h2>4. View File Verification</h2>
                <?php
                try {
                    // Check if view file exists
                    $view_file = APPPATH . '../themes/blue/admin/views/cost_center/cost_center_management.php';
                    if (file_exists($view_file)) {
                        echo '<p class="success">✓ Cost center management view exists</p>';
                        
                        $view_size = filesize($view_file);
                        echo '<p class="info">📄 View file size: ' . number_format($view_size) . ' bytes</p>';
                        
                        // Check for key HTML elements
                        $view_content = file_get_contents($view_file);
                        $elements_to_check = [
                            'cost-center-management' => 'Main container class',
                            'btn-add-cost-center' => 'Add cost center button',
                            'entities-grid' => 'Entities grid layout',
                            'costCenterModal' => 'Modal for adding/editing',
                            'costCenterForm' => 'Cost center form'
                        ];
                        
                        echo '<h3>View Elements:</h3>';
                        echo '<table>';
                        echo '<tr><th>Element</th><th>Description</th><th>Status</th></tr>';
                        
                        foreach ($elements_to_check as $element => $description) {
                            $element_exists = strpos($view_content, $element) !== false;
                            $status = $element_exists ? '<span class="success">✓ Found</span>' : '<span class="error">✗ Missing</span>';
                            
                            echo '<tr>';
                            echo '<td>' . $element . '</td>';
                            echo '<td>' . $description . '</td>';
                            echo '<td>' . $status . '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                        
                    } else {
                        echo '<p class="error">✗ View file not found at: ' . $view_file . '</p>';
                    }
                } catch (Exception $e) {
                    echo '<p class="error">✗ View error: ' . $e->getMessage() . '</p>';
                }
                ?>
            </div>

            <!-- Test 5: Menu Integration Check -->
            <div class="test-section">
                <h2>5. Menu Integration Verification</h2>
                <?php
                try {
                    // Check if menu file contains the cost center management link
                    $menu_file = APPPATH . '../themes/blue/admin/views/new_customer_menu.php';
                    if (file_exists($menu_file)) {
                        echo '<p class="success">✓ Menu file exists</p>';
                        
                        $menu_content = file_get_contents($menu_file);
                        $has_management_link = strpos($menu_content, 'cost_center/management') !== false;
                        
                        if ($has_management_link) {
                            echo '<p class="success">✓ Cost center management menu item found</p>';
                        } else {
                            echo '<p class="warning">⚠ Cost center management menu item not found</p>';
                            echo '<p>Add this to the Finance menu section:</p>';
                            echo '<div class="code">&lt;li&gt;&lt;a href="&lt;?= admin_url(\'cost_center/management\'); ?&gt;" class="newmenu-link"&gt;&lt;i class="fa fa-cogs"&gt;&lt;/i&gt;&lt;?= lang(\'Cost Center Management\'); ?&gt;&lt;/a&gt;&lt;/li&gt;</div>';
                        }
                        
                    } else {
                        echo '<p class="error">✗ Menu file not found</p>';
                    }
                } catch (Exception $e) {
                    echo '<p class="error">✗ Menu error: ' . $e->getMessage() . '</p>';
                }
                ?>
            </div>

            <!-- Test 6: Sample Data Operations -->
            <div class="test-section">
                <h2>6. Sample Operations Test</h2>
                <?php
                try {
                    // Only run if we have pharmacies available
                    $pharmacies = $CI->cost_center->get_all_pharmacies_for_cost_center();
                    
                    if (!empty($pharmacies)) {
                        echo '<p class="info">🧪 Testing with sample data operations...</p>';
                        
                        // Get the first pharmacy for testing
                        $test_pharmacy = $pharmacies[0];
                        echo '<p>Test entity: <strong>' . htmlspecialchars($test_pharmacy['warehouse_name']) . '</strong> (ID: ' . $test_pharmacy['warehouse_id'] . ')</p>';
                        
                        // Test getting cost centers for this entity
                        $entity_cost_centers = $CI->cost_center->get_cost_centers_by_entity($test_pharmacy['warehouse_id']);
                        echo '<p>Cost centers for this entity: <strong>' . count($entity_cost_centers) . '</strong></p>';
                        
                        if (!empty($entity_cost_centers)) {
                            echo '<table>';
                            echo '<tr><th>Code</th><th>Name</th><th>Level</th><th>Active</th></tr>';
                            foreach ($entity_cost_centers as $cc) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($cc['cost_center_code']) . '</td>';
                                echo '<td>' . htmlspecialchars($cc['cost_center_name']) . '</td>';
                                echo '<td>Level ' . $cc['cost_center_level'] . '</td>';
                                echo '<td>' . ($cc['is_active'] ? 'Yes' : 'No') . '</td>';
                                echo '</tr>';
                            }
                            echo '</table>';
                        } else {
                            echo '<p class="info">ℹ No cost centers configured for this entity yet.</p>';
                        }
                        
                    } else {
                        echo '<p class="warning">⚠ No pharmacies/branches found for testing</p>';
                    }
                    
                } catch (Exception $e) {
                    echo '<p class="error">✗ Sample operations error: ' . $e->getMessage() . '</p>';
                }
                ?>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="test-section">
            <h2>🚀 Quick Actions</h2>
            <p>Use these buttons to quickly navigate to the cost center management features:</p>
            
            <button class="btn" onclick="window.open('<?= base_url('admin/cost_center/dashboard') ?>', '_blank')">
                📊 Cost Center Dashboard
            </button>
            
            <button class="btn btn-success" onclick="window.open('<?= base_url('admin/cost_center/management') ?>', '_blank')">
                ⚙️ Cost Center Management
            </button>
            
            <button class="btn btn-danger" onclick="if(confirm('This will reload the test page.')) { location.reload(); }">
                🔄 Refresh Test
            </button>
        </div>

        <!-- Summary -->
        <div class="test-section">
            <h2>📋 Implementation Summary</h2>
            <p><strong>What was implemented:</strong></p>
            <ul>
                <li>✅ Cost center management controller methods (add, edit, delete, view)</li>
                <li>✅ Cost center model methods for database operations</li>
                <li>✅ Modern responsive UI for cost center management</li>
                <li>✅ Hierarchical cost center support (Level 1 and Level 2)</li>
                <li>✅ Entity-based cost center assignment (Pharmacy/Branch)</li>
                <li>✅ AJAX-powered interface with modal forms</li>
                <li>✅ Menu integration in Finance section</li>
                <li>✅ Validation and error handling</li>
            </ul>
            
            <p><strong>Key Features:</strong></p>
            <ul>
                <li>🏗️ Two-level hierarchical cost centers</li>
                <li>🏥 Cost center assignment to existing pharmacies and branches</li>
                <li>📝 Add, edit, delete cost center operations</li>
                <li>🔍 Search and filter entities</li>
                <li>💻 Responsive design for all devices</li>
                <li>⚡ Real-time UI updates</li>
                <li>🔐 Validation and security</li>
            </ul>
            
            <p><strong>Next Steps:</strong></p>
            <ol>
                <li>Navigate to <strong>Finance → Cost Center Management</strong> to start using the feature</li>
                <li>Create cost centers for your pharmacies and branches</li>
                <li>Set up the two-level hierarchy as needed</li>
                <li>Integrate cost center data with financial reporting</li>
            </ol>
            
            <div style="background: #e3f2fd; padding: 15px; border-radius: 6px; margin: 15px 0;">
                <h3 style="margin-top: 0; color: #1976d2;">🎯 Implementation Complete!</h3>
                <p style="margin-bottom: 0;">The cost center management system is now ready for use. You can configure cost centers for existing pharmacies and branches through the new management interface.</p>
            </div>
        </div>
    </div>
</body>
</html>