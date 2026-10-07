<?php
require_once '../includes/header.php';

if (!$user['is_admin']) {
    header("Location: ../dashboard.php");
    exit;
}

$message = '';
$msg_type = 'success';

// Handle Approvals/Cancellations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['txn_id'])) {
    $action = $_POST['action'];
    $txn_id = (int)$_POST['txn_id'];
    
    // Fetch txn
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND status = 'Pending'");
    $stmt->execute([$txn_id]);
    $txn = $stmt->fetch();
    
    if ($txn) {
        if ($action === 'approve') {
            try {
                $pdo->beginTransaction();
                // Update transaction status
                $pdo->prepare("UPDATE transactions SET status = 'Approved' WHERE id = ?")->execute([$txn_id]);
                // Add balance to user
                $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")->execute([$txn['amount'], $txn['user_id']]);
                $pdo->commit();
                $message = "Transaction Approved. User balance updated.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = "Error approving transaction.";
                $msg_type = "error";
            }
        } elseif ($action === 'cancel') {
            $pdo->prepare("UPDATE transactions SET status = 'Cancelled' WHERE id = ?")->execute([$txn_id]);
            $message = "Transaction Cancelled.";
        }
    }
}

// Fetch all transactions
$stmt = $pdo->query("SELECT t.*, u.username FROM transactions t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC");
$transactions = $stmt->fetchAll();
?>

<div class="mb-8">
    <h2 class="text-2xl font-bold text-gray-800"><i class="fa-solid fa-money-bill-transfer text-indigo-500 mr-2"></i> Fund Approvals</h2>
    <p class="text-gray-500 text-sm mt-1">Review manual bank transfer proofs and credit user balances.</p>
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
                <tr class="border-b border-gray-200 text-xs uppercase text-gray-500 tracking-wider">
                    <th class="py-4 px-4">Date & User</th>
                    <th class="py-4 px-4">Amount</th>
                    <th class="py-4 px-4 text-center">Proof Screenshot</th>
                    <th class="py-4 px-4 text-center">Status</th>
                    <th class="py-4 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php if (count($transactions) > 0): ?>
                    <?php foreach ($transactions as $txn): ?>
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="py-4 px-4">
                            <span class="block font-bold text-gray-800"><?php echo htmlspecialchars($txn['username']); ?></span>
                            <span class="block text-xs text-gray-400 mt-0.5"><?php echo date('M d, Y', strtotime($txn['created_at'])); ?></span>
                        </td>
                        <td class="py-4 px-4">
                            <span class="font-bold text-indigo-700 bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100 block w-max">
                                PKR <?php echo number_format($txn['amount'], 2); ?>
                            </span>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <a href="../uploads/<?php echo htmlspecialchars($txn['screenshot_path']); ?>" target="_blank" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl transition-colors font-semibold text-xs border border-gray-200">
                                <i class="fa-solid fa-image text-blue-500"></i> View Target
                            </a>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <?php if ($txn['status'] === 'Pending'): ?>
                                <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-sm border border-amber-200">Pending</span>
                            <?php elseif ($txn['status'] === 'Approved'): ?>
                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-sm border border-green-200">Approved</span>
                            <?php else: ?>
                                <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-sm border border-red-200">Cancelled</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 px-4 text-right">
                            <?php if ($txn['status'] === 'Pending'): ?>
                                <div class="flex justify-end gap-2">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="txn_id" value="<?php echo $txn['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" onclick="return confirm('Approve this transaction?')" class="bg-green-500 hover:bg-green-600 text-white w-9 h-9 rounded-xl shadow shadow-green-500/30 transition-all flex items-center justify-center" title="Approve">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    </form>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="txn_id" value="<?php echo $txn['id']; ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <button type="submit" onclick="return confirm('Cancel this transaction?')" class="bg-red-500 hover:bg-red-600 text-white w-9 h-9 rounded-xl shadow shadow-red-500/30 transition-all flex items-center justify-center" title="Cancel">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <span class="text-xs text-gray-400 font-medium">No actions available</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="py-12 text-center text-gray-500">No transactions found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
