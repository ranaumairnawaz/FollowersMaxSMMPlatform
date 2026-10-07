<?php
/**
 * FollowersMax - Database and Configuration Sample
 */

$db_host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_DATABASE') ?: 'followersmax_db';
$db_user = getenv('DB_USERNAME') ?: 'root';
$db_pass = getenv('DB_PASSWORD') ?: '';

try {
    $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db_name`");
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
