<?php
/**
 * Quick CSRF Token Test
 * Test URL: http://localhost:8080/avenzur/test_csrf_token.php
 */

// Include CI bootstrap
require_once('index.php');

$CI =& get_instance();

echo "<h1>CSRF Token Test</h1>";
echo "<p><strong>CSRF Protection:</strong> " . ($CI->config->item('csrf_protection') ? 'ENABLED' : 'DISABLED') . "</p>";
echo "<p><strong>CSRF Token Name:</strong> " . $CI->config->item('csrf_token_name') . "</p>";
echo "<p><strong>CSRF Cookie Name:</strong> " . $CI->config->item('csrf_cookie_name') . "</p>";

if ($CI->config->item('csrf_protection')) {
    echo "<p><strong>Current CSRF Token:</strong> " . $CI->security->get_csrf_hash() . "</p>";
    echo "<p><strong>Current CSRF Token Name:</strong> " . $CI->security->get_csrf_token_name() . "</p>";
    
    echo "<h2>Test AJAX Setup Code:</h2>";
    echo "<pre>";
    echo htmlspecialchars("$.ajaxSetup({\n");
    echo htmlspecialchars("    data: {\n");
    echo htmlspecialchars("        '" . $CI->security->get_csrf_token_name() . "': '" . $CI->security->get_csrf_hash() . "'\n");
    echo htmlspecialchars("    }\n");
    echo htmlspecialchars("});");
    echo "</pre>";
} else {
    echo "<p>CSRF protection is disabled.</p>";
}

echo "<h2>Test Cost Center Management Access</h2>";
echo "<p><a href='" . base_url('admin/cost_center/management') . "' target='_blank'>Open Cost Center Management</a></p>";
?>