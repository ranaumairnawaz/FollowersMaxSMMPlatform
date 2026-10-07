<?php
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_DATABASE') ?: 'followersmax_db';
$db_user = getenv('DB_USERNAME') ?: 'root';
$db_pass = getenv('DB_PASSWORD') ?: '';

try {
    // First connect without DB to create it if it doesn't exist
    $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db_name`");
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper functions can go here
function get_setting($pdo, $key, $default = '')
{
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $res = $stmt->fetchColumn();
    return $res !== false ? $res : $default;
}

// Global API Request Helper
function api_request($pdo, $data)
{
    $api_url = get_setting($pdo, 'api_url');
    $api_key = get_setting($pdo, 'api_key');

    if (empty($api_url) || empty($api_key)) {
        return false;
    }

    $data['key'] = $api_key;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return ['error' => 'cURL Error: ' . $err];
    }

    $decoded = json_decode($response, true);
    if ($decoded === null) {
        return ['error' => 'Invalid or non-JSON response from API: ' . substr(strip_tags($response), 0, 150)];
    }

    return $decoded;
}
?>