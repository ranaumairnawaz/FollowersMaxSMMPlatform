<?php
require_once 'includes/header.php';

// Fetch user orders
$stmt = $pdo->prepare("SELECT o.*, s.name as service_name FROM orders o JOIN services s ON o.service_id = s.id WHERE o.user_id = ? ORDER BY o.created_at DESC");
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();

// Status color helper
function getStatusColor($status) {
    switch (strtolower($status)) {
        case 'completed': return 'bg-green-100 text-green-700 border-green-200';
        case 'processing':
        case 'in progress': return 'bg-blue-100 text-blue-700 border-blue-200';
        case 'pending': return 'bg-amber-100 text-amber-700 border-amber-200';
        case 'partial': return 'bg-purple-100 text-purple-700 border-purple-200';
        case 'canceled':
        case 'cancelled':
        case 'error': return 'bg-red-100 text-red-700 border-red-200';
        default: return 'bg-gray-100 text-gray-700 border-gray-200';
    }
}
?>

<div class="mb-8">
    <h2 class="text-2xl font-bold text-gray-800"><i class="fa-solid fa-receipt text-orange-500 mr-2"></i> Order History</h2>
    <p class="text-gray-500 text-sm mt-1">Track the progress of your social media campaigns.</p>
</div>

<div class="glass-panel p-2 sm:p-6 rounded-3xl border border-gray-100 shadow-sm">
    
    <!-- Mobile optimized list view instead of table for small screens, table for lg -->
    <div class="hidden lg:block overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-gray-200 text-xs uppercase text-gray-500 tracking-wider">
                    <th class="py-4 px-4">ID</th>
                    <th class="py-4 px-4 w-1/3">Service & Link</th>
                    <th class="py-4 px-4">Charge</th>
                    <th class="py-4 px-4">Quantity</th>
                    <th class="py-4 px-4 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $order): ?>
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="py-4 px-4 text-gray-500 font-mono">#<?php echo $order['id']; ?></td>
                        <td class="py-4 px-4">
                            <div class="font-bold text-gray-800 truncate block w-64 xl:w-96" title="<?php echo htmlspecialchars($order['service_name']); ?>">
                                <?php echo htmlspecialchars($order['service_name']); ?>
                            </div>
                            <a href="<?php echo htmlspecialchars($order['link']); ?>" target="_blank" class="text-xs text-blue-500 hover:underline flex items-center gap-1 mt-1 truncate block w-64 xl:w-96">
                                <i class="fa-solid fa-link"></i> <?php echo htmlspecialchars($order['link']); ?>
                            </a>
                        </td>
                        <td class="py-4 px-4 text-gray-800 font-bold">PKR <?php echo number_format($order['charge'], 4); ?></td>
                        <td class="py-4 px-4 text-gray-600 font-medium"><?php echo number_format($order['quantity']); ?></td>
                        <td class="py-4 px-4 text-center">
                            <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider border <?php echo getStatusColor($order['status']); ?>">
                                <?php echo htmlspecialchars($order['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="py-12 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 text-gray-400 mb-3">
                                <i class="fa-solid fa-box-open text-2xl"></i>
                            </div>
                            <p class="text-gray-500 font-medium block">You haven't placed any orders yet.</p>
                            <a href="dashboard.php" class="text-orange-500 font-bold hover:underline mt-2 inline-block">Place new order</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile Native-like List View -->
    <div class="lg:hidden space-y-4">
        <?php if (count($orders) > 0): ?>
            <?php foreach ($orders as $order): ?>
            <div class="bg-white border border-gray-100 p-4 rounded-xl shadow-sm">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-xs text-gray-400 font-mono uppercase">Order #<?php echo $order['id']; ?></span>
                    <span class="px-2 py-0.5 rounded flex items-center gap-1 text-[10px] font-bold uppercase border <?php echo getStatusColor($order['status']); ?>">
                        <?php echo htmlspecialchars($order['status']); ?>
                    </span>
                </div>
                <h4 class="font-bold text-gray-800 text-sm mb-1 leading-snug line-clamp-2"><?php echo htmlspecialchars($order['service_name']); ?></h4>
                <a href="<?php echo htmlspecialchars($order['link']); ?>" target="_blank" class="text-xs text-blue-500 hover:underline flex items-center gap-1 mb-3 truncate">
                    <i class="fa-solid fa-link"></i> <?php echo htmlspecialchars($order['link']); ?>
                </a>
                <div class="flex justify-between items-center text-sm border-t border-gray-50 pt-2">
                    <div class="text-gray-500">
                        Qty: <span class="font-bold text-gray-800"><?php echo number_format($order['quantity']); ?></span>
                    </div>
                    <div class="font-bold text-orange-600">
                        PKR <?php echo number_format($order['charge'], 4); ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="py-8 text-center">
                 <p class="text-gray-500 font-medium">No orders yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
