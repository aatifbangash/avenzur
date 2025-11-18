<!DOCTYPE html>
<html>
<head>
    <title>CSRF Test for Cost Center Management</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>CSRF Test for Cost Center Management</h1>
    
    <button id="testCsrf">Test CSRF Token</button>
    <button id="testAddCostCenter">Test Add Cost Center (should fail validation)</button>
    
    <div id="results"></div>

    <script>
        // Setup CSRF token for all AJAX requests (same as in cost center management)
        $.ajaxSetup({
            data: {
                'token': '<?php 
                    require_once("index.php");
                    $CI =& get_instance();
                    echo $CI->security->get_csrf_hash();
                ?>'
            }
        });

        $('#testCsrf').click(function() {
            $.ajax({
                url: '/avenzur/admin/cost_center/test_csrf',
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    $('#results').html('<div style="color: green;">CSRF Test Success: ' + JSON.stringify(response) + '</div>');
                },
                error: function(xhr, status, error) {
                    $('#results').html('<div style="color: red;">CSRF Test Failed: ' + xhr.status + ' ' + error + '</div>');
                }
            });
        });

        $('#testAddCostCenter').click(function() {
            $.ajax({
                url: '/avenzur/admin/cost_center/add_cost_center',
                type: 'POST',
                data: {
                    cost_center_code: 'TEST001',
                    cost_center_name: 'Test Cost Center',
                    cost_center_level: 1,
                    entity_id: 1
                },
                dataType: 'json',
                success: function(response) {
                    $('#results').html('<div style="color: green;">Add Cost Center Success: ' + JSON.stringify(response) + '</div>');
                },
                error: function(xhr, status, error) {
                    $('#results').html('<div style="color: red;">Add Cost Center Failed: ' + xhr.status + ' ' + error + '<br>Response: ' + xhr.responseText + '</div>');
                }
            });
        });
    </script>
</body>
</html>