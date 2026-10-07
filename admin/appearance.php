<?php
require_once '../includes/header.php';

if (!$user['is_admin']) {
    header("Location: ../dashboard");
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'theme_font', 'theme_primary', 'theme_primary_dark',
        'theme_dark_bg', 'theme_card_bg'
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
    $message = "Appearance settings updated successfully!";
}

// Fetch current
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'theme_%'");
$settings_map = [];
while ($row = $stmt->fetch()) {
    $settings_map[$row['setting_key']] = $row['setting_value'];
}
?>

<div class="mb-8">
    <h2 class="text-2xl font-bold text-gray-800"><i class="fa-solid fa-palette text-indigo-500 mr-2"></i> Appearance Settings</h2>
    <p class="text-gray-500 text-sm mt-1">Customize website colors, branding, and typography.</p>
</div>

<?php if ($message): ?>
    <div class="p-4 mb-6 bg-green-50 text-green-700 border border-green-200 rounded-xl text-sm flex gap-3 items-center">
        <i class="fa-solid fa-check-circle text-lg"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="flex flex-col xl:flex-row gap-8">
    <!-- Form -->
    <div class="flex-1 glass-panel p-6 sm:p-8 rounded-3xl border border-gray-100 shadow-sm h-fit">
        <form method="POST" class="space-y-6">
            
            <h3 class="text-lg font-bold text-gray-800 mb-6 border-b border-gray-100 pb-4">Theme Configuration</h3>
            
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">Google Font Name</label>
                    <div class="relative">
                        <i class="fa-solid fa-font absolute left-4 top-3.5 text-gray-400"></i>
                        <input type="text" name="theme_font" value="<?php echo htmlspecialchars($settings_map['theme_font'] ?? 'Plus Jakarta Sans'); ?>" required class="w-full bg-gray-50 border border-gray-200 text-gray-800 py-3 pl-11 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium text-sm">
                    </div>
                    <p class="text-xs text-gray-400 mt-1 ml-1">Example: 'Inter', 'Roboto', 'Outfit'</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">Primary Color (HEX)</label>
                        <div class="relative flex items-center">
                            <input type="color" name="theme_primary" value="<?php echo htmlspecialchars($settings_map['theme_primary'] ?? '#f97316'); ?>" class="absolute left-3 w-8 h-8 rounded cursor-pointer border-0 p-0 bg-transparent">
                            <input type="text" value="<?php echo htmlspecialchars($settings_map['theme_primary'] ?? '#f97316'); ?>" disabled class="w-full bg-gray-50 border border-gray-200 text-gray-800 py-3 pl-14 pr-4 rounded-xl focus:outline-none font-medium text-sm">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">Primary Dark (Hover HEX)</label>
                        <div class="relative flex items-center">
                            <input type="color" name="theme_primary_dark" value="<?php echo htmlspecialchars($settings_map['theme_primary_dark'] ?? '#ea580c'); ?>" class="absolute left-3 w-8 h-8 rounded cursor-pointer border-0 p-0 bg-transparent">
                            <input type="text" value="<?php echo htmlspecialchars($settings_map['theme_primary_dark'] ?? '#ea580c'); ?>" disabled class="w-full bg-gray-50 border border-gray-200 text-gray-800 py-3 pl-14 pr-4 rounded-xl focus:outline-none font-medium text-sm">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">Dark Mode Background</label>
                        <div class="relative flex items-center">
                            <input type="color" name="theme_dark_bg" value="<?php echo htmlspecialchars($settings_map['theme_dark_bg'] ?? '#0f172a'); ?>" class="absolute left-3 w-8 h-8 rounded cursor-pointer border-0 p-0 bg-transparent">
                            <input type="text" value="<?php echo htmlspecialchars($settings_map['theme_dark_bg'] ?? '#0f172a'); ?>" disabled class="w-full bg-gray-50 border border-gray-200 text-gray-800 py-3 pl-14 pr-4 rounded-xl focus:outline-none font-medium text-sm">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">Dark Mode Cards</label>
                        <div class="relative flex items-center">
                            <input type="color" name="theme_card_bg" value="<?php echo htmlspecialchars($settings_map['theme_card_bg'] ?? '#1e293b'); ?>" class="absolute left-3 w-8 h-8 rounded cursor-pointer border-0 p-0 bg-transparent">
                            <input type="text" value="<?php echo htmlspecialchars($settings_map['theme_card_bg'] ?? '#1e293b'); ?>" disabled class="w-full bg-gray-50 border border-gray-200 text-gray-800 py-3 pl-14 pr-4 rounded-xl focus:outline-none font-medium text-sm">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 mt-8">
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-indigo-500/30 transform transition-all hover:-translate-y-0.5 focus:outline-none">
                    Save Appearance <i class="fa-solid fa-save ml-2"></i>
                </button>
            </div>
        </form>
    </div>
    
    <!-- Preview Sidebar -->
    <div class="w-full xl:w-80">
        <div class="glass-panel p-6 rounded-3xl border border-gray-100 shadow-sm sticky top-8">
            <h3 class="font-bold text-gray-800 mb-4 border-b border-gray-100 pb-3">Style Preview</h3>
            
            <div class="space-y-4">
                <div class="h-10 rounded-lg flex items-center justify-center text-white font-bold text-sm shadow cursor-pointer transition-colors" style="background-color: <?php echo htmlspecialchars($settings_map['theme_primary'] ?? '#f97316'); ?>" onmouseover="this.style.backgroundColor='<?php echo htmlspecialchars($settings_map['theme_primary_dark'] ?? '#ea580c'); ?>'" onmouseout="this.style.backgroundColor='<?php echo htmlspecialchars($settings_map['theme_primary'] ?? '#f97316'); ?>'">
                    Primary Button
                </div>
                
                <div class="p-4 rounded-xl shadow-inner border border-gray-800 text-white" style="background-color: <?php echo htmlspecialchars($settings_map['theme_dark_bg'] ?? '#0f172a'); ?>">
                    <p class="text-xs mb-2 opacity-70">App Background</p>
                    <div class="h-16 rounded-lg shadow-lg flex items-center px-4 border border-gray-700" style="background-color: <?php echo htmlspecialchars($settings_map['theme_card_bg'] ?? '#1e293b'); ?>">
                        <div class="h-8 w-8 rounded-full flex items-center justify-center text-white" style="background-color: <?php echo htmlspecialchars($settings_map['theme_primary'] ?? '#f97316'); ?>">
                            <i class="fa-solid fa-check text-xs"></i>
                        </div>
                        <span class="ml-3 text-sm font-semibold">Card Element</span>
                    </div>
                </div>
            </div>
            
            <div class="bg-blue-50 text-blue-700 border border-blue-100 p-3 rounded-xl text-xs font-semibold mt-6 flex gap-2">
                <i class="fa-solid fa-lightbulb mt-0.5"></i>
                Refreshing the page saves the config to Tailwind CSS globally.
            </div>
        </div>
    </div>
</div>

<script>
// Dynamic sync for color pickers
document.querySelectorAll('input[type="color"]').forEach(picker => {
    picker.addEventListener('input', (e) => {
        picker.nextElementSibling.value = e.target.value;
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
