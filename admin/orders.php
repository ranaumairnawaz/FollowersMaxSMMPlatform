<?php
require_once '../includes/header.php';

if (!$user['is_admin']) {
    header("Location: ../dashboard.php");
    exit;
}

// Fetch all orders
$stmt = $pdo->query("SELECT o.*, u.username, s.name as service_name FROM orders o JOIN users u ON o.user_id = u.id JOIN services s ON o.service_id = s.id ORDER BY o.created_at DESC LIMIT 500");
$orders = $stmt->fetchAll();

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
    <h2 class="text-2xl font-bold text-gray-800"><i class="fa-solid fa-list-check text-indigo-500 mr-2"></i> All Orders</h2>
    <p class="text-gray-500 text-sm mt-1">View the latest 500 orders across all users.</p>
</div>

<div class="glass-panel p-2 sm:p-6 rounded-3xl border border-gray-100 shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-gray-200 text-xs uppercase text-gray-500 tracking-wider">
                    <th class="py-4 px-4 w-12 text-center">ID</th>
                    <th class="py-4 px-4 w-32">User</th>
                    <th class="py-4 px-4 w-1/3">Service & Link</th>
                    <th class="py-4 px-4">Cost (Rs)</th>
                    <th class="py-4 px-4 text-center">API Order ID</th>
                    <th class="py-4 px-4 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $order): ?>
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="py-4 px-4 text-center font-mono text-gray-500 text-xs">#<?php echo $order['id']; ?></td>
                        <td class="py-4 px-4">
                            <span class="font-bold text-indigo-700 bg-indigo-50 px-2 py-1 rounded inline-block">
                                <i class="fa-solid fa-user text-[10px] mr-1"></i><?php echo htmlspecialchars($order['username']); ?>
                            </span>
                        </td>
                        <td class="py-4 px-4">
                            <div class="font-bold text-gray-800 truncate block max-w-xs xl:max-w-md" title="<?php echo htmlspecialchars($order['service_name']); ?>">
                                <?php echo htmlspecialchars($order['service_name']); ?>
                            </div>
                            <a href="<?php echo htmlspecialchars($order['link']); ?>" target="_blank" class="text-xs text-blue-500 hover:underline flex items-center gap-1 mt-1 truncate block max-w-xs xl:max-w-md">
                                <i class="fa-solid fa-link"></i> <?php echo htmlspecialchars($order['link']); ?>
                            </a>
                            <div class="text-[10px] font-mono text-gray-400 mt-1 uppercase">QTY: <?php echo number_format($order['quantity']); ?></div>
                        </td>
                        <td class="py-4 px-4 text-gray-800 font-bold">PKR <?php echo number_format($order['charge'], 4); ?></td>
                        <td class="py-4 px-4 text-center font-mono text-xs text-gray-500">
                            <?php echo $order['api_order_id'] ? '#' . $order['api_order_id'] : '-'; ?>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider border whitespace-nowrap <?php echo getStatusColor($order['status']); ?>">
                                <?php echo htmlspecialchars($order['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="py-12 text-center text-gray-500">No orders found in the system.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
