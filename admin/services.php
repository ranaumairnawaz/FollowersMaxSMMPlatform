<?php
require_once '../includes/header.php';

if (!$user['is_admin']) {
    header("Location: ../dashboard.php");
    exit;
}

$message = '';
$msg_type = 'success';

// Handle Sync Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'sync') {
    $api_url = get_setting($pdo, 'api_url');
    $api_key = get_setting($pdo, 'api_key');
    $margin = (float)get_setting($pdo, 'profit_margin', 150); // percentage
    
    if (empty($api_url) || empty($api_key)) {
        $message = "API URL or Key not configured. Go to Settings first.";
        $msg_type = "error";
    } else {
        $services = api_request($pdo, ['action' => 'services']);
        
        if (isset($services['error'])) {
            $message = "API Error: " . $services['error'];
            $msg_type = 'error';
        } else if (is_array($services)) {
            try {
                // TRUNCATE causes an implicit commit in MySQL, so do it BEFORE starting the transaction
                $pdo->exec("TRUNCATE TABLE services");
                
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("INSERT INTO services (api_service_id, name, category, rate, min, max) VALUES (?, ?, ?, ?, ?, ?)");
                
                $count = 0;
                $usd_rate = (float)get_setting($pdo, 'usd_rate', 280);
                
                foreach ($services as $srv) {
                    if (!isset($srv['service']) || !isset($srv['rate'])) continue;
                    
                    $base_rate_usd = (float)$srv['rate'];
                    $final_rate_pkr = $base_rate_usd * $usd_rate * ($margin / 100);
                    
                    $stmt->execute([
                        $srv['service'],
                        $srv['name'] ?? 'Unknown',
                        $srv['category'] ?? 'Uncategorized',
                        $final_rate_pkr,
                        $srv['min'] ?? 0,
                        $srv['max'] ?? 0
                    ]);
                    $count++;
                }
                
                $pdo->commit();
                $message = "Successfully synced $count services! Converted from USD to PKR using Rs $usd_rate rate, plus $margin% margin applied!";
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $message = "Database Error during sync: " . $e->getMessage();
                $msg_type = 'error';
            }
        } else {
            $message = "API returned unexpected format.";
            $msg_type = 'error';
        }
    }
}

// Fetch current
$stmt = $pdo->query("SELECT * FROM services ORDER BY category, api_service_id");
$local_services = $stmt->fetchAll();

$margin = (float)get_setting($pdo, 'profit_margin', 150);
?>

<div class="mb-8 flex justify-between items-end">
    <div>
        <h2 class="text-2xl font-bold text-gray-800"><i class="fa-solid fa-server text-indigo-500 mr-2"></i> Manage Services</h2>
        <p class="text-gray-500 text-sm mt-1">
            Exchange Rate: <b><?php echo (float)get_setting($pdo, 'usd_rate', 280); ?> PKR/USD</b> &bull; Margin: <b><?php echo $margin; ?>%</b>
        </p>
    </div>
    
    <form method="POST">
        <input type="hidden" name="action" value="sync">
        <button type="submit" onclick="return confirm('This will clear current services and fetch latest from API. Proceed?')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-indigo-500/30 transform transition-all hover:-translate-y-0.5 focus:outline-none flex items-center gap-2">
            <i class="fa-solid fa-rotate"></i> Sync Services Now
        </button>
    </form>
</div>

<?php if ($message): ?>
    <div class="p-4 mb-6 rounded-xl text-sm flex gap-3 items-center <?php echo $msg_type === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
        <i class="fa-solid <?php echo $msg_type === 'success' ? 'fa-check-circle' : 'fa-circle-exclamation'; ?> text-lg"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="glass-panel p-2 sm:p-6 rounded-3xl border border-gray-100 shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-gray-200 text-xs uppercase text-gray-500 tracking-wider bg-gray-50/50">
                    <th class="py-4 px-4 rounded-tl-xl text-center">ID</th>
                    <th class="py-4 px-4 w-1/4">Category</th>
                    <th class="py-4 px-4 w-1/3">Service Name</th>
                    <th class="py-4 px-4">Min-Max</th>
                    <th class="py-4 px-4 rounded-tr-xl">Rate (After Markup)</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php if (count($local_services) > 0): ?>
                    <?php 
                    $current_category = '';
                    foreach ($local_services as $srv): 
                    ?>
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-4 text-center font-mono text-gray-500"><?php echo htmlspecialchars($srv['api_service_id']); ?></td>
                        <td class="py-3 px-4">
                            <span class="inline-block bg-indigo-50 text-indigo-700 border border-indigo-100 px-2 py-1 rounded truncate max-w-[200px] text-xs font-bold font-mono" title="<?php echo htmlspecialchars($srv['category']); ?>">
                                <?php echo htmlspecialchars($srv['category']); ?>
                            </span>
                        </td>
                        <td class="py-3 px-4 text-gray-800 font-semibold truncate max-w-[300px]" title="<?php echo htmlspecialchars($srv['name']); ?>">
                            <?php echo htmlspecialchars($srv['name']); ?>
                        </td>
                        <td class="py-3 px-4 text-gray-600 font-mono text-xs">
                            <?php echo number_format($srv['min']) . ' - ' . number_format($srv['max']); ?>
                        </td>
                        <td class="py-3 px-4 text-orange-600 font-bold bg-orange-50/30">
                            PKR <?php echo number_format($srv['rate'], 4); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="py-12 text-center text-gray-500">
                            <i class="fa-solid fa-server text-4xl block mb-3 text-gray-300"></i>
                            No services synced yet. Click "Sync Services Now" to fetch from the provider.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
