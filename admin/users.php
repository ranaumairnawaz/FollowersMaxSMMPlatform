<?php
require_once '../includes/header.php';

if (!$user['is_admin']) {
    header("Location: ../dashboard.php");
    exit;
}

// Handle balance update or admin toggle if needed (optional but useful)
$message = '';
$msg_type = 'success';

if (isset($_POST['action'])) {
    $target_user_id = (int) $_POST['user_id'];

    if ($_POST['action'] === 'update_balance') {
        $new_balance = (float) $_POST['balance'];
        $stmt = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
        if ($stmt->execute([$new_balance, $target_user_id])) {
            $message = "User balance updated successfully!";
        }
    } elseif ($_POST['action'] === 'toggle_admin') {
        $stmt = $pdo->prepare("UPDATE users SET is_admin = 1 - is_admin WHERE id = ? AND id != ?"); // Prevent self-demotion
        if ($stmt->execute([$target_user_id, $user['id']])) {
            $message = "User admin status updated!";
        }
    }
}

// Fetch all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$all_users = $stmt->fetchAll();
?>

<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800"><i class="fa-solid fa-users text-indigo-500 mr-2"></i> User
            Management</h2>
        <p class="text-gray-500 text-sm mt-1">View and manage all registered users.</p>
    </div>
    <div class="bg-indigo-50 px-4 py-2 rounded-xl border border-indigo-100">
        <span class="text-indigo-600 font-bold text-sm">Total Users: <?php echo count($all_users); ?></span>
    </div>
</div>

<?php if ($message): ?>
    <div
        class="p-4 mb-6 rounded-xl text-sm flex gap-3 items-center <?php echo $msg_type === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
        <i
            class="fa-solid <?php echo $msg_type === 'success' ? 'fa-check-circle' : 'fa-circle-exclamation'; ?> text-lg"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="glass-panel rounded-3xl overflow-hidden border border-gray-100 shadow-sm transition-all">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                    <th class="py-4 px-6 text-xs font-bold text-gray-400 uppercase tracking-wider">User Info</th>
                    <th class="py-4 px-6 text-xs font-bold text-gray-400 uppercase tracking-wider">Balance</th>
                    <th class="py-4 px-6 text-xs font-bold text-gray-400 uppercase tracking-wider text-center">Status
                    </th>
                    <th class="py-4 px-6 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($all_users as $u): ?>
                    <tr class="hover:bg-gray-50/50 transition-colors group">
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold overflow-hidden border border-gray-200">
                                    <?php if ($u['profile_pic']): ?>
                                        <img src="../<?php echo htmlspecialchars($u['profile_pic']); ?>"
                                            class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <?php echo strtoupper(substr($u['username'], 0, 1)); ?>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800"><?php echo htmlspecialchars($u['username']); ?>
                                        <?php if ($u['is_admin']): ?>
                                            <span
                                                class="ml-1 text-[10px] bg-indigo-100 text-indigo-600 px-1.5 py-0.5 rounded-md uppercase font-black tracking-tighter">Admin</span>
                                        <?php endif; ?>
                                    </p>
                                    <p class="text-xs text-gray-400"><?php echo htmlspecialchars($u['email']); ?></p>
                                    <p class="text-xs text-gray-400"><?php echo $u['phone']; ?></p>
                                    <p class="text-[10px] text-gray-300 mt-1 italic">Joined
                                        <?php echo date('M d, Y', strtotime($u['created_at'])); ?>
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-700">PKR
                                    <?php echo number_format($u['balance'], 3); ?></span>
                                <button
                                    onclick="openBalanceModal(<?php echo $u['id']; ?>, '<?php echo $u['username']; ?>', <?php echo $u['balance']; ?>)"
                                    class="text-[10px] text-indigo-500 hover:text-indigo-700 font-bold uppercase tracking-wider mt-1 w-fit">Edit
                                    Balance</button>
                            </div>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                Active
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <form method="POST" class="inline">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <input type="hidden" name="action" value="toggle_admin">
                                    <button type="submit"
                                        class="p-2 rounded-xl border border-gray-100 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all"
                                        title="<?php echo $u['is_admin'] ? 'Remove Admin' : 'Make Admin'; ?>">
                                        <i
                                            class="fa-solid <?php echo $u['is_admin'] ? 'fa-user-minus' : 'fa-user-shield'; ?>"></i>
                                    </button>
                                </form>
                                <?php if (!$u['is_admin']): ?>
                                <a href="impersonate.php?id=<?php echo $u['id']; ?>" class="p-2 rounded-xl border border-gray-100 text-gray-400 hover:text-orange-600 hover:bg-orange-50 transition-all" title="Login as User">
                                    <i class="fa-solid fa-mask"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Balance Modal -->
<div id="balanceModal"
    class="fixed inset-0 bg-black/50 z-[60] hidden flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-3xl p-8 max-w-sm w-full shadow-2xl transform transition-all scale-95 opacity-0 duration-300"
        id="modalContent">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-800">Edit Balance</h3>
            <button onclick="closeBalanceModal()" class="text-gray-400 hover:text-gray-600"><i
                    class="fa-solid fa-xmark text-xl"></i></button>
        </div>
        <p class="text-sm text-gray-500 mb-6">Updating balance for <span id="modalUsername"
                class="font-bold text-indigo-600"></span></p>

        <form method="POST">
            <input type="hidden" name="action" value="update_balance">
            <input type="hidden" name="user_id" id="modalUserId">
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2 ml-1">New Balance (PKR)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3.5 text-gray-300 font-bold">PKR</span>
                        <input type="number" step="0.0001" name="balance" id="modalBalance" required
                            class="w-full bg-gray-50 border border-gray-100 text-gray-800 py-3 pl-12 pr-4 rounded-2xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-bold text-lg">
                    </div>
                </div>
                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-2xl shadow-lg shadow-indigo-500/30 transition-all flex items-center justify-center gap-2">
                    Save Changes <i class="fa-solid fa-check"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openBalanceModal(id, username, balance) {
        document.getElementById('modalUserId').value = id;
        document.getElementById('modalUsername').innerText = username;
        document.getElementById('modalBalance').value = balance;

        const modal = document.getElementById('balanceModal');
        const content = document.getElementById('modalContent');

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeBalanceModal() {
        const modal = document.getElementById('balanceModal');
        const content = document.getElementById('modalContent');

        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }, 300);
    }
</script>

<?php require_once '../includes/footer.php'; ?>