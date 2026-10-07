<?php
require_once '../includes/header.php';

if (!$user['is_admin']) {
    header("Location: ../dashboard");
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'smtp_host', 'smtp_port', 'smtp_user',
        'smtp_pass', 'smtp_from', 'smtp_name'
    ];
    
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    
    foreach ($settings as $key) {
        if (isset($_POST[$key])) {
            $val = trim($_POST[$key]);
            $stmt->execute([$key, $val, $val]);
        }
    }
    $pdo->commit();
    $message = "SMTP settings updated perfectly!";
}

// Fetch current
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'smtp_%'");
$settings_map = [];
while ($row = $stmt->fetch()) {
    $settings_map[$row['setting_key']] = $row['setting_value'];
}
?>

<div class="mb-8">
    <h2 class="text-2xl font-bold text-gray-800"><i class="fa-solid fa-envelope text-indigo-500 mr-2"></i> Mail Server Configuration</h2>
    <p class="text-gray-500 text-sm mt-1">Setup SMTP to automatically deliver Order Receipts, Password Resets, and Funds Approvals.</p>
</div>

<?php if ($message): ?>
    <div class="p-4 mb-6 bg-green-50 text-green-700 border border-green-200 rounded-xl text-sm flex gap-3 items-center">
        <i class="fa-solid fa-check-circle text-lg"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="flex flex-col xl:flex-row gap-8">
    <div class="flex-1 glass-panel p-6 sm:p-8 rounded-3xl border border-gray-100 shadow-sm h-fit">
        <form method="POST" class="space-y-6">
            
            <h3 class="text-lg font-bold text-gray-800 mb-6 border-b border-gray-100 pb-4">SMTP Credentials</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">SMTP Host</label>
                    <div class="relative">
                        <i class="fa-solid fa-server absolute left-4 top-3.5 text-gray-400"></i>
                        <input type="text" name="smtp_host" value="<?php echo htmlspecialchars($settings_map['smtp_host'] ?? ''); ?>" placeholder="smtp.gmail.com" class="w-full bg-gray-50 border border-gray-200 text-gray-800 py-3 pl-11 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium text-sm">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">SMTP Port</label>
                    <div class="relative">
                        <i class="fa-solid fa-network-wired absolute left-4 top-3.5 text-gray-400"></i>
                        <input type="number" name="smtp_port" value="<?php echo htmlspecialchars($settings_map['smtp_port'] ?? '587'); ?>" placeholder="587 or 465" class="w-full bg-gray-50 border border-gray-200 text-gray-800 py-3 pl-11 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium text-sm">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">SMTP Username (Email)</label>
                    <div class="relative">
                        <i class="fa-solid fa-user absolute left-4 top-3.5 text-gray-400"></i>
                        <input type="text" name="smtp_user" value="<?php echo htmlspecialchars($settings_map['smtp_user'] ?? ''); ?>" placeholder="admin@domain.com" class="w-full bg-gray-50 border border-gray-200 text-gray-800 py-3 pl-11 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium text-sm">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">SMTP Password</label>
                    <div class="relative">
                        <i class="fa-solid fa-key absolute left-4 top-3.5 text-gray-400"></i>
                        <input type="password" name="smtp_pass" value="<?php echo htmlspecialchars($settings_map['smtp_pass'] ?? ''); ?>" placeholder="••••••••" class="w-full bg-gray-50 border border-gray-200 text-gray-800 py-3 pl-11 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium text-sm">
                    </div>
                    <p class="text-xs text-gray-400 mt-1 ml-1">Use App Password if Gmail.</p>
                </div>
            </div>
            
            <h3 class="text-lg font-bold text-gray-800 mt-8 mb-6 border-b border-gray-100 pb-4">Sender Branding</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">From Name</label>
                    <div class="relative">
                        <i class="fa-solid fa-signature absolute left-4 top-3.5 text-gray-400"></i>
                        <input type="text" name="smtp_name" value="<?php echo htmlspecialchars($settings_map['smtp_name'] ?? 'FollowersMax System'); ?>" class="w-full bg-gray-50 border border-gray-200 text-gray-800 py-3 pl-11 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium text-sm">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">From Email</label>
                    <div class="relative">
                        <i class="fa-solid fa-at absolute left-4 top-3.5 text-gray-400"></i>
                        <input type="email" name="smtp_from" value="<?php echo htmlspecialchars($settings_map['smtp_from'] ?? 'noreply@yourdomain.com'); ?>" class="w-full bg-gray-50 border border-gray-200 text-gray-800 py-3 pl-11 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium text-sm">
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 mt-8">
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-indigo-500/30 transform transition-all hover:-translate-y-0.5 focus:outline-none">
                    Save Email Configuration <i class="fa-solid fa-paper-plane ml-2"></i>
                </button>
            </div>
        </form>
    </div>
    
    <div class="w-full xl:w-80">
        <div class="glass-panel p-6 rounded-3xl border border-gray-100 shadow-sm sticky top-8">
            <h3 class="font-bold text-gray-800 mb-4 border-b border-gray-100 pb-3">Email Template</h3>
            <p class="text-xs text-gray-600 mb-4 leading-relaxed">
                We've built a sleek, dynamic HTML template that automatically adopts your active <a href="appearance" class="text-indigo-500 underline font-bold">Appearance</a> colors.
            </p>
            <div class="border border-gray-200 rounded-lg overflow-hidden flex flex-col items-center">
                <div class="bg-gray-800 w-full h-8 flex items-center justify-center p-2 mb-2"><span class="text-orange-500 text-[10px] font-bold">FollowersMax Receipt</span></div>
                <div class="w-3/4 h-2 bg-gray-200 rounded-full mb-2"></div>
                <div class="w-1/2 h-2 bg-gray-200 rounded-full mb-4"></div>
                <div class="w-24 h-6 bg-orange-500 rounded-md mb-4 shadow"></div>
                <div class="bg-gray-100 w-full h-8 border-t border-gray-200"></div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
