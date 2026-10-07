<?php
require_once 'includes/header.php';

// Handle Add Funds Form Submission
$message = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT);
    
    if ($amount && $amount > 0 && isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        $filename = $_FILES['screenshot']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid('txn_') . '.' . $ext;
            $upload_path = 'uploads/' . $new_filename;
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }
            
            if (move_uploaded_file($_FILES['screenshot']['tmp_name'], $upload_path)) {
                $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, screenshot_path) VALUES (?, ?, ?)");
                if ($stmt->execute([$user['id'], $amount, $new_filename])) {
                    $message = "Your fund request has been submitted and is pending admin approval.";
                    $msg_type = "success";
                    
                    // Send Email Notification
                    require_once 'includes/mailer.php';
                    $html = get_sleek_email_html($pdo, 
                        "Fund Request Submitted", 
                        "<p>Your deposit of <b>PKR {$amount}</b> via Manual Bank Transfer has been successfully received.</p>
                         <p>An administrator will review your payment screenshot and apply the funds to your balance shortly.</p>", 
                        "Go to Dashboard", 
                        "http://" . $_SERVER['HTTP_HOST'] . str_replace('add_funds.php', 'dashboard.php', $_SERVER['PHP_SELF'])
                    );
                    send_smtp_email($pdo, $user['email'], "Deposit Processing - FollowersMax", $html);
                } else {
                    $message = "Database error. Please try again.";
                    $msg_type = "error";
                }
            } else {
                $message = "Failed to upload screenshot.";
                $msg_type = "error";
            }
        } else {
            $message = "Invalid file type. Only JPG, PNG, and PDF are allowed.";
            $msg_type = "error";
        }
    } else {
        $message = "Please enter a valid amount and upload a screenshot.";
        $msg_type = "error";
    }
}

// Fetch pending/approved transactions for this user
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$transactions = $stmt->fetchAll();

// Fetch Bank Settings
$bank_name = get_setting($pdo, 'bank_name', 'Not Configured');
$account_title = get_setting($pdo, 'account_title', 'Not Configured');
$account_number = get_setting($pdo, 'account_number', 'Not Configured');
?>

<div class="mb-8">
    <h2 class="text-2xl font-bold text-gray-800"><i class="fa-solid fa-wallet text-orange-500 mr-2"></i> Add Funds</h2>
    <p class="text-gray-500 text-sm mt-1">Transfer manually via bank and upload payment proof to get balance added.</p>
</div>

<?php if ($message): ?>
    <div class="p-4 mb-6 rounded-xl text-sm flex gap-3 items-center <?php echo $msg_type === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
        <i class="fa-solid <?php echo $msg_type === 'success' ? 'fa-check-circle' : 'fa-circle-exclamation'; ?> text-lg"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Bank Details Card -->
    <div class="glass-panel p-6 md:p-8 rounded-3xl border border-gray-100 shadow-sm relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 text-orange-500/5">
            <i class="fa-solid fa-building-columns text-9xl"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-800 mb-6 border-b border-gray-100 pb-4">Our Bank Details</h3>
        
        <div class="space-y-5">
            <div>
                <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Bank Name</p>
                <p class="text-lg font-semibold text-gray-800 bg-gray-50 p-3 rounded-xl border border-gray-100"><?php echo htmlspecialchars($bank_name); ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Account Title</p>
                <p class="text-lg font-semibold text-gray-800 bg-gray-50 p-3 rounded-xl border border-gray-100"><?php echo htmlspecialchars($account_title); ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Account / IBAN Number</p>
                <div class="flex">
                    <p class="text-lg font-semibold text-orange-600 bg-orange-50 p-3 rounded-l-xl border border-orange-100 flex-1 font-mono tracking-wider"><?php echo htmlspecialchars($account_number); ?></p>
                    <button onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($account_number); ?>')" class="bg-orange-100 hover:bg-orange-200 text-orange-600 px-4 rounded-r-xl border-y border-r border-orange-100 transition-colors" title="Copy to clipboard">
                        <i class="fa-regular fa-copy"></i>
                    </button>
                </div>
            </div>
        </div>
        <p class="text-sm text-amber-600 mt-6 bg-amber-50 p-3 rounded-xl border border-amber-100">
            <i class="fa-solid fa-circle-info mr-1"></i> Please transfer the exact amount and take a <b>clear screenshot</b> of the successful transaction.
        </p>
    </div>

    <!-- Upload Proof Card -->
    <div class="glass-panel p-6 md:p-8 rounded-3xl border border-gray-100 shadow-sm">
        <h3 class="text-lg font-bold text-gray-800 mb-6 border-b border-gray-100 pb-4">Submit Payment Proof</h3>
        
        <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Amount Sent (PKR)</label>
                <div class="relative">
                    <span class="absolute left-4 top-3.5 text-gray-400 font-bold tracking-widest text-[10px] mt-0.5">PKR</span>
                    <input type="number" step="0.01" name="amount" required placeholder="e.g. 500" class="w-full bg-gray-50 border border-gray-200 text-gray-800 py-3 pl-[3.5rem] pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 font-medium font-mono text-lg">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Upload Screenshot</label>
                <div class="border-2 border-dashed border-gray-300 rounded-2xl p-6 text-center hover:bg-gray-50 hover:border-orange-400 transition-all cursor-pointer relative">
                    <input type="file" name="screenshot" accept="image/jpeg,image/png,application/pdf" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                    <i class="fa-solid fa-cloud-arrow-up text-4xl text-gray-400 mb-3 block"></i>
                    <p class="text-sm font-semibold text-gray-600">Click or drag your receipt here</p>
                    <p class="text-xs text-gray-400 mt-1">JPEG, PNG, or PDF up to 5MB</p>
                </div>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-orange-500/30 transform transition-all hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                Submit for Approval <i class="fa-solid fa-paper-plane ml-2"></i>
            </button>
        </form>
    </div>
</div>

<!-- Transaction History -->
<div class="glass-panel p-6 rounded-3xl border border-gray-100 shadow-sm">
    <h3 class="text-lg font-bold text-gray-800 mb-4">Your Fund Requests</h3>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-gray-200 text-xs uppercase text-gray-500 tracking-wider">
                    <th class="py-3 px-4">Date</th>
                    <th class="py-3 px-4">Amount</th>
                    <th class="py-3 px-4">Screenshot</th>
                    <th class="py-3 px-4 text-right">Status</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php if (count($transactions) > 0): ?>
                    <?php foreach ($transactions as $txn): ?>
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-4 text-gray-600 font-medium whitespace-nowrap"><?php echo date('M d, Y h:i A', strtotime($txn['created_at'])); ?></td>
                        <td class="py-3 px-4 text-gray-800 font-bold">PKR <?php echo number_format($txn['amount'], 2); ?></td>
                        <td class="py-3 px-4">
                            <a href="uploads/<?php echo htmlspecialchars($txn['screenshot_path']); ?>" target="_blank" class="text-blue-500 hover:underline inline-flex items-center gap-1">
                                <i class="fa-solid fa-image"></i> View
                            </a>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <?php if ($txn['status'] === 'Pending'): ?>
                                <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">Pending</span>
                            <?php elseif ($txn['status'] === 'Approved'): ?>
                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">Approved</span>
                            <?php else: ?>
                                <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">Cancelled</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="py-8 text-center text-gray-500">No fund requests found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
