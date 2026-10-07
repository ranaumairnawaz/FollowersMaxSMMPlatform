<?php
require_once '../includes/header.php';

// Ensure admin
if (!$user['is_admin']) {
    header("Location: ../dashboard.php");
    exit;
}

// Get stats
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 0")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_spent = $pdo->query("SELECT SUM(charge) FROM orders")->fetchColumn() ?: 0;
$pending_funds = $pdo->query("SELECT COUNT(*) FROM transactions WHERE status = 'Pending'")->fetchColumn();

// Fetch API Balance
$api_balance = 'Not Configured';
$api_url = get_setting($pdo, 'api_url');
$api_key = get_setting($pdo, 'api_key');

if (!empty($api_url) && !empty($api_key)) {
    $data = api_request($pdo, ['action' => 'balance']);
    if (isset($data['balance'])) {
        $usd_rate = (float)get_setting($pdo, 'usd_rate', 280);
        $bal_usd = (float)$data['balance'];
        
        // If API natively returns PKR, don't convert. But user said "api pricing is in usd, convert it".
        // Let's check currency string or assume it is USD.
        $currency_code = strtoupper($data['currency'] ?? 'USD');
        if ($currency_code !== 'PKR') {
            $bal_pkr = $bal_usd * $usd_rate;
            $api_balance = "PKR " . number_format($bal_pkr, 2) . " <span class='text-xs font-normal text-gray-500'>(\$" . number_format($bal_usd, 2) . ")</span>";
        } else {
            $api_balance = "PKR " . number_format($bal_usd, 2);
        }
    } else if (isset($data['error'])) {
        $api_balance = "API Error: " . htmlspecialchars($data['error']);
    } else {
        $api_balance = "Unknown API Error";
    }
}
?>

<div class="mb-8 flex justify-between items-end">
    <div>
        <h2 class="text-2xl font-bold text-gray-800"><i class="fa-solid fa-shield-halved text-indigo-500 mr-2"></i> Admin Dashboard</h2>
        <p class="text-gray-500 text-sm mt-1">Overview of system operations and API status.</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-8">
    <div class="glass-panel p-6 rounded-2xl border-l-4 border-l-cyan-500 shadow-sm relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 text-cyan-500/10"><i class="fa-solid fa-server text-8xl"></i></div>
        <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Live API Balance</p>
        <h3 class="text-xl md:text-2xl font-extrabold text-gray-800"><?php echo $api_balance; ?></h3>
    </div>
    <div class="glass-panel p-6 rounded-2xl border-l-4 border-l-indigo-500 shadow-sm relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 text-indigo-500/10"><i class="fa-solid fa-users text-8xl"></i></div>
        <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Total Users</p>
        <h3 class="text-3xl font-extrabold text-gray-800"><?php echo number_format($total_users); ?></h3>
    </div>
    <div class="glass-panel p-6 rounded-2xl border-l-4 border-l-orange-500 shadow-sm relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 text-orange-500/10"><i class="fa-solid fa-cart-shopping text-8xl"></i></div>
        <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Total Orders</p>
        <h3 class="text-3xl font-extrabold text-gray-800"><?php echo number_format($total_orders); ?></h3>
    </div>
    <div class="glass-panel p-6 rounded-2xl border-l-4 border-l-green-500 shadow-sm relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 text-green-500/10"><i class="fa-solid fa-money-bill-trend-up text-8xl"></i></div>
        <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Total Revenue</p>
        <h3 class="text-3xl font-extrabold text-gray-800">PKR <?php echo number_format($total_spent, 2); ?></h3>
    </div>
    <div class="glass-panel p-6 rounded-2xl border-l-4 border-l-amber-500 shadow-sm relative overflow-hidden <?php echo $pending_funds > 0 ? 'bg-amber-50 animate-pulse' : ''; ?>">
        <div class="absolute -right-4 -bottom-4 text-amber-500/10"><i class="fa-solid fa-bell text-8xl"></i></div>
        <p class="text-xs text-amber-600 font-bold uppercase tracking-wider mb-1">Pending Funds</p>
        <h3 class="text-3xl font-extrabold text-amber-800"><?php echo number_format($pending_funds); ?></h3>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <div class="glass-panel p-6 rounded-3xl border border-gray-100 shadow-sm text-center">
        <i class="fa-solid fa-money-bill-transfer text-6xl text-indigo-200 mb-4 block"></i>
        <h3 class="font-bold text-gray-800 mb-2">Review Fund Requests</h3>
        <p class="text-sm text-gray-500 mb-4">You have <?php echo $pending_funds; ?> user transaction(s) waiting for approval.</p>
        <a href="funds.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-xl inline-block transition-colors">Go to Funds</a>
    </div>
    <div class="glass-panel p-6 rounded-3xl border border-gray-100 shadow-sm text-center">
        <i class="fa-solid fa-server text-6xl text-indigo-200 mb-4 block"></i>
        <h3 class="font-bold text-gray-800 mb-2">Sync API Services</h3>
        <p class="text-sm text-gray-500 mb-4">Pull latest services from API and update local pricing.</p>
        <a href="services.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-xl inline-block transition-colors">Go to Services</a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
