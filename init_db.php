<?php
require_once 'config.php';

$queries = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(150) UNIQUE NOT NULL,
        phone VARCHAR(20) DEFAULT NULL,
        profile_pic VARCHAR(255) DEFAULT NULL,
        password VARCHAR(255) NOT NULL,
        balance DECIMAL(10,4) DEFAULT 0.0000,
        is_admin TINYINT(1) DEFAULT 0,
        reset_token VARCHAR(64) DEFAULT NULL,
        reset_expires DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value TEXT
    )",
    "CREATE TABLE IF NOT EXISTS services (
        id INT AUTO_INCREMENT PRIMARY KEY,
        api_service_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        category VARCHAR(255) NOT NULL,
        rate DECIMAL(10,4) NOT NULL,
        min INT NOT NULL,
        max INT NOT NULL,
        is_active TINYINT(1) DEFAULT 1
    )",
    "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        service_id INT NOT NULL,
        api_order_id INT,
        link TEXT NOT NULL,
        quantity INT NOT NULL,
        charge DECIMAL(10,4) NOT NULL,
        status VARCHAR(50) DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        amount DECIMAL(10,4) NOT NULL,
        screenshot_path VARCHAR(255) NOT NULL,
        status VARCHAR(50) DEFAULT 'Pending', /* Pending, Approved, Cancelled */
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )"
];

foreach ($queries as $query) {
    try {
        $pdo->exec($query);
        echo "<p>Successfully executed setup query.</p>";
    } catch (PDOException $e) {
        echo "<p>Error executing query: " . $e->getMessage() . "</p>";
    }
}

// Insert default admin if not exists
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
$stmt->execute();
if ($stmt->fetchColumn() == 0) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT INTO users (username, email, phone, password, is_admin) VALUES ('admin', 'admin@example.com', '0000000000', '$hash', 1)");
    echo "<p>Default admin created. Username: admin, Password: admin123</p>";
}

// Insert default settings
$default_settings = [
    'api_url' => '',
    'api_key' => '',
    'profit_margin' => '150', // 150%
    'bank_name' => 'Bank Name',
    'account_title' => 'Account Title',
    'account_number' => '1234567890'
];

foreach ($default_settings as $key => $val) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
    $stmt->execute([$key, $val]);
}

echo "<h3>Database initialization complete!</h3>";
?>