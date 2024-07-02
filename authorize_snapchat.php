<?php
session_start();

function authorize_snapchat() {
    $client_id = '7bcc6ae7-1685-42af-8bff-6ef9e4ec71a2';
    $redirect_uri = 'https://avenzur.com/snapchat_callback.php';
    $oauth_url = 'https://accounts.snapchat.com/login/oauth2';

    $auth_url = "{$oauth_url}/authorize?client_id={$client_id}&redirect_uri={$redirect_uri}&response_type=code&scope=snapchat-marketing-api";
    var_dump($auth_url);
    header('Location: ' . $auth_url);
    exit;
}
// Check if we already have an access token
if (!isset($_SESSION['access_token'])) {
    authorize_snapchat();
} else {
    echo "Access token already exists.";
}
?>
