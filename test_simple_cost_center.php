<?php
// Include CI
require_once('index.php');
$CI =& get_instance();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple Cost Center Test</title>
</head>
<body>
    <h1>Simple Cost Center Test Form</h1>
    
    <form action="admin/cost_center/add_cost_center" method="POST">
        <input type="hidden" name="token" value="<?= $CI->security->get_csrf_hash(); ?>">
        
        <p>
            <label>Cost Center Code:</label><br>
            <input type="text" name="cost_center_code" value="TEST001" required>
        </p>
        
        <p>
            <label>Cost Center Name:</label><br>
            <input type="text" name="cost_center_name" value="Test Cost Center" required>
        </p>
        
        <p>
            <label>Cost Center Level:</label><br>
            <select name="cost_center_level" required>
                <option value="">Select Level</option>
                <option value="1" selected>Level 1</option>
                <option value="2">Level 2</option>
            </select>
        </p>
        
        <p>
            <label>Entity ID:</label><br>
            <input type="number" name="entity_id" value="1" required>
        </p>
        
        <p>
            <label>Description:</label><br>
            <textarea name="description">Test description</textarea>
        </p>
        
        <p>
            <button type="submit">Add Cost Center</button>
        </p>
    </form>
    
    <hr>
    
    <h2>Debug Info:</h2>
    <ul>
        <li><strong>CSRF Protection:</strong> <?= $CI->config->item('csrf_protection') ? 'ENABLED' : 'DISABLED' ?></li>
        <li><strong>CSRF Token Name:</strong> <?= $CI->config->item('csrf_token_name') ?></li>
        <li><strong>CSRF Hash:</strong> <?= $CI->security->get_csrf_hash() ?></li>
        <li><strong>Logged In:</strong> <?= property_exists($CI, 'loggedIn') && $CI->loggedIn ? 'YES' : 'NO' ?></li>
    </ul>
    
    <h2>Test Links:</h2>
    <ul>
        <li><a href="admin/cost_center/management">Cost Center Management</a></li>
        <li><a href="admin/cost_center/dashboard">Cost Center Dashboard</a></li>
        <li><a href="admin/cost_center/test_csrf" onclick="testAjax(); return false;">Test CSRF AJAX</a></li>
    </ul>

    <div id="ajax-result"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function testAjax() {
            $.ajax({
                url: 'admin/cost_center/test_csrf',
                type: 'POST',
                data: {
                    'token': '<?= $CI->security->get_csrf_hash(); ?>',
                    'test': 'data'
                },
                dataType: 'json',
                success: function(response) {
                    $('#ajax-result').html('<div style="color: green; padding: 10px; border: 1px solid green;">SUCCESS: ' + JSON.stringify(response) + '</div>');
                },
                error: function(xhr, status, error) {
                    $('#ajax-result').html('<div style="color: red; padding: 10px; border: 1px solid red;">ERROR: ' + xhr.status + ' ' + error + '<br>Response: ' + xhr.responseText + '</div>');
                }
            });
        }
    </script>
</body>
</html>