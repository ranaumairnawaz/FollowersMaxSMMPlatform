<?php
require_once '../includes/header.php';

if (!$user['is_admin']) {
    header("Location: ../dashboard.php");
    exit;
}

$message = '';
$msg_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'api_url', 'api_key', 'profit_margin', 'usd_rate',
        'bank_name', 'account_title', 'account_number', 'whatsapp_number'
    ];
    
    try {
        $pdo->beginTransaction();
        foreach ($settings as $key) {
            if (isset($_POST[$key])) {
                $val = trim($_POST[$key]);
                $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->execute([$key, $val, $val]);
            }
        }
        $pdo->commit();
        $message = "Settings updated successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Failed to update settings.";
        $msg_type = 'error';
    }
}

// Fetch current
$settings_map = [];
$stmt = $pdo->query("SELECT * FROM settings");
while ($row = $stmt->fetch()) {
    $settings_map[$row['setting_key']] = $row['setting_value'];
}
?>

<div class="mb-8">
    <h2 class="text-2xl font-bold text-gray-800"><i class="fa-solid fa-gear text-indigo-500 mr-2"></i> Global Settings</h2>
    <p class="text-gray-500 text-sm mt-1">Configure API Connection and Bank Transfer Details.</p>
</div>

<?php if ($message): ?>
    <div class="p-4 mb-6 rounded-xl text-sm flex gap-3 items-center <?php echo $msg_type === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
        <i class="fa-solid <?php echo $msg_type === 'success' ? 'fa-check-circle' : 'fa-circle-exclamation'; ?> text-lg"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<form method="POST" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- API Settings -->
    <div class="glass-panel p-6 md:p-8 rounded-3xl border border-gray-100 shadow-sm relative overflow-hidden">
        <h3 class="text-lg font-bold text-gray-800 mb-6 border-b border-gray-100 pb-4">SMM Panel API Connection</h3>
        <div class="space-y-5">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">API URL</label>
                <div class="relative">
                    <i class="fa-solid fa-link absolute left-4 top-3.5 text-gray-400"></i>
                    <input type="url" name="api_url" value="<?php echo htmlspecialchars($settings_map['api_url'] ?? ''); ?>" required class="w-full bg-gray-50 border border-gray-200 text-gray-800 py-3 pl-11 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium text-sm">
                </div>
                <p class="text-xs text-gray-400 mt-1 ml-1">Example: https://provider.com/api/v2</p>
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">API Key</label>
                <div class="relative">
                    <i class="fa-solid fa-key absolute left-4 top-3.5 text-gray-400"></i>
                    <input type="password" name="api_key" value="<?php echo htmlspecialchars($settings_map['api_key'] ?? ''); ?>" required class="w-full bg-gray-50 border border-gray-200 text-gray-800 py-3 pl-11 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium text-sm">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-100">
                    <label class="block text-sm font-bold text-indigo-900 mb-1.5 flex items-center gap-1">Exchange Rate (USD to PKR)</label>
                    <div class="relative">
                        <span class="absolute right-4 top-3.5 text-indigo-400 font-bold">PKR</span>
                        <input type="number" step="0.01" name="usd_rate" value="<?php echo htmlspecialchars($settings_map['usd_rate'] ?? '280'); ?>" required class="w-full bg-white border border-indigo-200 text-indigo-800 py-3 pl-4 pr-12 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-bold text-lg">
                    </div>
                    <p class="text-[10px] text-indigo-600/80 mt-2 font-medium uppercase tracking-wider">e.g. 1 USD = 280 PKR</p>
                </div>
                
                <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-100">
                    <label class="block text-sm font-bold text-indigo-900 mb-1.5">Profit Margin (%)</label>
                    <div class="relative">
                        <span class="absolute right-4 top-3.5 text-indigo-400 font-bold">%</span>
                        <input type="number" name="profit_margin" value="<?php echo htmlspecialchars($settings_map['profit_margin'] ?? '150'); ?>" required class="w-full bg-white border border-indigo-200 text-indigo-800 py-3 pl-4 pr-10 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-bold text-lg">
                    </div>
                    <p class="text-[10px] text-indigo-600/80 mt-2 font-medium uppercase tracking-wider">100% = Cost Price, 200% = 2x price</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bank Details Settings -->
    <div class="glass-panel p-6 md:p-8 rounded-3xl border border-gray-100 shadow-sm relative overflow-hidden">
        <h3 class="text-lg font-bold text-gray-800 mb-6 border-b border-gray-100 pb-4">Bank Transfer & Support Info</h3>
        <div class="space-y-5">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">Bank Name</label>
                <input type="text" name="bank_name" value="<?php echo htmlspecialchars($settings_map['bank_name'] ?? ''); ?>" required class="w-full bg-gray-50 border border-gray-200 text-gray-800 py-3 px-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium text-sm">
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">Account Title</label>
                <input type="text" name="account_title" value="<?php echo htmlspecialchars($settings_map['account_title'] ?? ''); ?>" required class="w-full bg-gray-50 border border-gray-200 text-gray-800 py-3 px-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium text-sm">
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">Account / IBAN Number</label>
                <input type="text" name="account_number" value="<?php echo htmlspecialchars($settings_map['account_number'] ?? ''); ?>" required class="w-full bg-gray-50 border border-gray-200 text-gray-800 py-3 px-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-bold font-mono tracking-wider">
            </div>
            
            <div class="pt-2 border-t border-gray-100 mt-4">
                <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">WhatsApp Support Number</label>
                <div class="relative">
                    <i class="fa-brands fa-whatsapp absolute left-4 top-3.5 text-green-500 text-lg"></i>
                    <input type="text" name="whatsapp_number" value="<?php echo htmlspecialchars($settings_map['whatsapp_number'] ?? ''); ?>" placeholder="+92 300 0000000" class="w-full bg-gray-50 border border-gray-200 text-gray-800 py-3 pl-11 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 font-medium text-sm">
                </div>
                <p class="text-[10px] text-gray-400 mt-1 ml-1 uppercase tracking-wider">Include country code. Enables floating icon.</p>
            </div>
            
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-indigo-500/30 transform transition-all hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mt-4">
                Save All Settings <i class="fa-solid fa-floppy-disk ml-2"></i>
            </button>
        </div>
    </div>
</form>

<?php require_once '../includes/footer.php'; ?>
