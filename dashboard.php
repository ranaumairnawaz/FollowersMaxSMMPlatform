<?php
require_once 'includes/header.php';

// Fetch all active services and group by category
$stmt = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY category, rate ASC");
$all_services = $stmt->fetchAll();

$grouped_services = [];
foreach ($all_services as $srv) {
    $cat = $srv['category'];
    if (!isset($grouped_services[$cat])) {
        $grouped_services[$cat] = [];
    }
    $grouped_services[$cat][] = $srv;
}

// Convert to JSON for JS
$json_services = json_encode($grouped_services);

// Fetch User Real Stats
$stmt = $pdo->prepare("SELECT SUM(charge) FROM orders WHERE user_id = ? AND status NOT IN ('Canceled', 'Cancelled', 'Error')");
$stmt->execute([$user['id']]);
$total_spent = (float)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$stmt->execute([$user['id']]);
$total_orders = (int)$stmt->fetchColumn();

// Handle new order submission
$message = '';
$msg_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['service_id'], $_POST['link'], $_POST['quantity'])) {
    $service_id = (int)$_POST['service_id'];
    $link = trim($_POST['link']);
    $quantity = (int)$_POST['quantity'];
    
    // Find service in DB
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$service_id]);
    $srv = $stmt->fetch();
    
    if ($srv) {
        if ($quantity >= $srv['min'] && $quantity <= $srv['max']) {
            $charge = ($srv['rate'] / 1000) * $quantity;
            
            // Re-fetch user balance inside transaction
            try {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
                $stmt->execute([$user['id']]);
                $current_balance = $stmt->fetchColumn();
                
                if ($current_balance >= $charge) {
                    // Deduct balance
                    $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?")->execute([$charge, $user['id']]);
                    
                    // Insert Order
                    $stmt = $pdo->prepare("INSERT INTO orders (user_id, service_id, link, quantity, charge, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
                    $stmt->execute([$user['id'], $service_id, $link, $quantity, $charge]);
                    $order_id = $pdo->lastInsertId();
                    
                    $api_res = api_request($pdo, [
                        'action' => 'add',
                        'service' => $srv['api_service_id'],
                        'link' => $link,
                        'quantity' => $quantity
                    ]);
                    
                    if (is_array($api_res) && isset($api_res['order'])) {
                        // Update with API Order ID
                        $pdo->prepare("UPDATE orders SET api_order_id = ? WHERE id = ?")->execute([$api_res['order'], $order_id]);
                    } else {
                        // API Error but we still charged the user. Status goes to Error.
                        // Optionally refund the user here if you prefer, but standard is admin manual review.
                        $pdo->prepare("UPDATE orders SET status = 'Error' WHERE id = ?")->execute([$order_id]);
                    }
                    
                    $pdo->commit();
                    $message = "Order Placed Successfully! Charge: PKR " . number_format($charge, 4);
                    
                    // Update user var for balance display
                    $user['balance'] -= $charge;
                    
                    // Send Email Receipt
                    require_once 'includes/mailer.php';
                    $html = get_sleek_email_html($pdo, 
                        "Order #{$order_id} Received!", 
                        "<p>Thanks! We've received your request for <b>{$srv['name']}</b>.</p>
                         <p>Link: <a href='{$link}'>{$link}</a></p>
                         <p>Quantity: {$qty}</p>
                         <p>Charge: <b>PKR " . number_format($charge, 4) . "</b></p>", 
                        "View My Orders", 
                        "http://" . $_SERVER['HTTP_HOST'] . str_replace('dashboard.php', 'orders.php', $_SERVER['PHP_SELF'])
                    );
                    send_smtp_email($pdo, $user['email'], "Order #{$order_id} Confirmation - FollowersMax", $html);
                    
                } else {
                    $pdo->rollBack();
                    $message = "Not enough funds. Please Add Funds first.";
                    $msg_type = 'error';
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = "Error processing order. Try again.";
                $msg_type = 'error';
            }
        } else {
            $message = "Quantity must be between {$srv['min']} and {$srv['max']}.";
            $msg_type = 'error';
        }
    } else {
        $message = "Invalid Service Selected.";
        $msg_type = 'error';
    }
}
?>

<!-- Announcement / Top Banners section -->
<div class="mb-8 flex flex-col items-center gap-4 text-center">
    <div class="text-3xl md:text-4xl font-arabic font-bold text-gray-800 mb-2">
        بِسْمِ ٱللَّٰهِ ٱلرَّحْمَٰنِ ٱلرَّحِيمِ
    </div>
    <p class="text-sm font-semibold text-gray-600 mt-2 uppercase tracking-wide">
        Please don't contact on any of the number provided in add funds section <br>
        If you call of contact the numbers you account can be blocked <i class="fa-solid fa-ban text-orange-500"></i>, <br> 
        <?php 
        $whatsapp = get_setting($pdo, 'whatsapp_number', '');
        if ($whatsapp): 
            $wa_url = preg_replace('/[^0-9]/', '', $whatsapp);
        ?>
        Only use given WhatsApp for all Queries </br></br></br>
        <a href="https://wa.me/<?php echo $wa_url; ?>" target="_blank" style="background-color: #67C15E;" class="text-white font-bold my-2 py-2 px-8 rounded-xl shadow-md hover:-translate-y-0.5 transition-all text-sm">
            Whatsapp <i class="fa-brands fa-whatsapp"></i>
        </a>
        
        <?php endif; ?>
        
    </p>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <!-- Stat 1 -->
    <div class="stat-card glass-panel rounded-2xl p-5 border-l-4 border-l-orange-500 relative overflow-hidden group">
        <div class="absolute -right-6 -top-6 text-orange-500/10 group-hover:text-orange-500/20 transition-colors">
            <i class="fa-solid fa-money-bill-wave text-8xl"></i>
        </div>
        <p class="text-sm text-gray-500 font-semibold mb-1">Total Spent</p>
        <h3 class="text-2xl font-bold text-gray-800">PKR <?php echo number_format($total_spent, 2); ?></h3>
    </div>
    <!-- Stat 2 -->
    <div class="stat-card glass-panel rounded-2xl p-5 border-l-4 border-l-amber-500 relative overflow-hidden group">
        <div class="absolute -right-6 -top-6 text-amber-500/10 group-hover:text-amber-500/20 transition-colors">
            <i class="fa-solid fa-chart-line text-8xl"></i>
        </div>
        <p class="text-sm text-gray-500 font-semibold mb-1">Total Orders</p>
        <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($total_orders); ?></h3>
    </div>
    <!-- Stat 3 -->
    <div class="stat-card glass-panel rounded-2xl p-5 border-l-4 border-l-orange-500 relative overflow-hidden group">
        <div class="absolute -right-6 -top-6 text-orange-500/10 group-hover:text-orange-500/20 transition-colors">
            <i class="fa-solid fa-wallet text-8xl"></i>
        </div>
        <p class="text-sm text-gray-500 font-semibold mb-1">Total Balance</p>
        <h3 class="text-2xl font-bold text-gray-800">PKR <?php echo number_format($user['balance'], 3); ?></h3>
    </div>
    <!-- Stat 4 -->
    <div class="stat-card glass-panel bg-gradient-to-br from-orange-500 to-amber-500 text-white rounded-2xl p-5 shadow-lg shadow-orange-500/20 relative overflow-hidden">
        <div class="absolute -right-2 -bottom-2 text-white/20">
            <i class="fa-solid fa-crown text-7xl"></i>
        </div>
        <p class="text-sm text-white/80 font-semibold mb-1">Account Status</p>
        <h3 class="text-2xl font-bold">NEW</h3>
    </div>
</div>

<!-- Main Area Tabs -->
<div class="glass-panel rounded-2xl p-2 mb-6 flex space-x-2">
    <button class="flex-1 md:flex-none py-2 px-6 rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 text-white font-semibold text-sm shadow-md">
        New Order
    </button>
    <button class="flex-1 md:flex-none py-2 px-6 rounded-xl text-gray-600 font-semibold text-sm hover:bg-gray-100 transition-colors">
        Check Updates
    </button>
    <button class="flex-1 md:flex-none py-2 px-6 rounded-xl text-gray-600 font-semibold text-sm hover:bg-gray-100 transition-colors">
        Others
    </button>
</div>

<!-- Alerts -->
<div class="bg-amber-50 border-l-4 border-amber-400 text-amber-700 p-4 rounded-r-xl mb-6 flex gap-3 text-sm font-medium">
    <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
    <p>Welcome to our new system interface! You can view your account status and recent updates right here.</p>
</div>

<?php if ($message): ?>
    <div class="p-4 mb-6 rounded-xl text-sm flex gap-3 items-center shadow-sm <?php echo $msg_type === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
        <i class="fa-solid <?php echo $msg_type === 'success' ? 'fa-check-circle' : 'fa-circle-exclamation'; ?> text-lg"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="flex flex-col lg:flex-row gap-8">
    <!-- Order Form Column -->
    <div class="flex-1">
        <div class="glass-panel rounded-3xl p-6 md:p-8 shadow-sm border border-gray-100">
            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                <i class="fa-solid fa-cart-plus text-primary"></i> Create New Order
            </h2>
            
            <?php if (empty($grouped_services)): ?>
                <div class="bg-amber-50 text-amber-700 p-6 rounded-2xl text-center border border-amber-200 shadow-inner">
                    <i class="fa-solid fa-server block text-3xl mb-2 text-amber-400"></i>
                    <p class="font-bold mb-1">No services available</p>
                    <p class="text-sm">Please ask the administrator to sync the services from the API.</p>
                </div>
            <?php else: ?>
            
            <form action="dashboard.php" method="POST" class="space-y-6">
                <!-- Category Select -->
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-700">Category</label>
                    <div class="relative">
                        <select id="category_select" class="w-full appearance-none bg-gray-50 border border-gray-200 text-gray-700 py-3.5 px-4 pr-10 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all font-medium text-sm">
                            <!-- Populated by JS -->
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                            <i class="fa-solid fa-chevron-down text-sm"></i>
                        </div>
                    </div>
                </div>

                <!-- Service Select -->
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-700">Service</label>
                    <div class="relative">
                        <select name="service_id" id="service_select" class="w-full appearance-none bg-gray-50 border border-gray-200 text-gray-700 py-3.5 px-4 pr-10 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all font-medium text-sm truncate">
                            <!-- Populated by JS -->
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                            <i class="fa-solid fa-chevron-down text-sm"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Description Box -->
                <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4 text-sm text-blue-800">
                    <div class="font-bold flex items-center gap-2 mb-1"><i class="fa-solid fa-circle-info"></i> Service Info</div>
                    <ul id="service_desc" class="list-disc list-inside space-y-1 mt-2 text-gray-600 pl-1">
                        <li>Start Time: Details inside service name</li>
                        <li id="min_max_text">Min: - Max: -</li>
                    </ul>
                </div>

                <!-- Link Input -->
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-700">Link</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-link text-gray-400"></i>
                        </div>
                        <input type="text" name="link" required placeholder="https://..." class="w-full bg-gray-50 border border-gray-200 text-gray-800 py-3.5 pl-11 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <!-- Average Time & Quantity Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2 relative">
                        <label class="block text-sm font-bold text-gray-700">
                            Average Time <i class="fa-solid fa-circle-question text-gray-400 cursor-pointer" title="Based on last 100 orders"></i>
                        </label>
                        <input type="text" value="Not enough data" disabled class="w-full bg-gray-100 border border-gray-200 text-gray-500 py-3.5 px-4 rounded-xl cursor-not-allowed">
                    </div>
                    
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-gray-700">Quantity</label>
                        <input type="number" id="quantity_input" name="quantity" required placeholder="0" class="w-full bg-gray-50 border border-gray-200 text-gray-800 py-3.5 px-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all">
                        <p class="text-[11px] text-gray-500 mt-1 font-medium bg-gray-100 px-2 py-1 rounded inline-block" id="qty_bounds_hint">Min: 0 - Max: 0</p>
                    </div>
                </div>

                <!-- Charge Display -->
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-700">Charge</label>
                    <div id="charge_display" class="w-full bg-orange-50 border border-orange-200 text-orange-600 font-bold text-xl py-4 px-4 rounded-xl text-center shadow-inner">
                        PKR 0.0000
                    </div>
                </div>

                <!-- Submit Button -->
                <button id="submit_btn" type="submit" class="w-full bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold text-lg py-4 rounded-xl shadow-lg shadow-orange-500/30 transform transition-all hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 mt-2">
                    Submit Order <i class="fa-solid fa-paper-plane ml-2"></i>
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Right Sidebar Information / Search (Present in original, keeping layout balanced) -->
    <div class="hidden lg:block w-80 space-y-6">
        <!-- Search -->
        <div class="glass-panel p-5 rounded-2xl border border-gray-100">
            <h3 class="font-bold text-gray-800 mb-3 text-sm uppercase tracking-wider">Quick Search</h3>
            <div class="relative">
                <input type="text" placeholder="Search for a service..." class="w-full bg-gray-50 border border-gray-200 text-gray-600 py-2.5 pl-10 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-gray-400"></i>
            </div>
        </div>
        
        <!-- Live Updates / News mock -->
        <div class="glass-panel p-5 rounded-2xl border border-gray-100">
            <h3 class="font-bold text-gray-800 mb-4 text-sm uppercase tracking-wider flex items-center justify-between">
                System Updates <span class="bg-green-100 text-green-600 text-[10px] px-2 py-0.5 rounded-full">Live</span>
            </h3>
            <div class="space-y-4">
                <div class="flex gap-3 items-start">
                    <div class="bg-orange-100 text-orange-500 p-2 rounded-lg mt-0.5"><i class="fa-solid fa-bolt"></i></div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800">TikTok Services Speed Up</p>
                        <p class="text-xs text-gray-500 mt-0.5">We've upgraded our USA nodes.</p>
                        <p class="text-[10px] text-gray-400 mt-1">2 hours ago</p>
                    </div>
                </div>
                <div class="flex gap-3 items-start">
                    <div class="bg-blue-100 text-blue-500 p-2 rounded-lg mt-0.5"><i class="fa-solid fa-arrow-trend-down"></i></div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Instagram Price Drop</p>
                        <p class="text-xs text-gray-500 mt-0.5">Real followers now 15% cheaper.</p>
                        <p class="text-[10px] text-gray-400 mt-1">Yesterday</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($grouped_services)): ?>
<script>
const servicesData = <?php echo $json_services; ?>;
const catSelect = document.getElementById('category_select');
const srvSelect = document.getElementById('service_select');
const qtyInput = document.getElementById('quantity_input');
const chargeDisplay = document.getElementById('charge_display');
const minMaxText = document.getElementById('min_max_text');
const qtyBoundsHint = document.getElementById('qty_bounds_hint');

let currentService = null;

// Populate categories
for (const cat in servicesData) {
    let opt = document.createElement('option');
    opt.value = cat;
    opt.textContent = cat;
    catSelect.appendChild(opt);
}

function updateServices() {
    srvSelect.innerHTML = '';
    const cat = catSelect.value;
    if (servicesData[cat]) {
        servicesData[cat].forEach(srv => {
            let opt = document.createElement('option');
            opt.value = srv.id;
            opt.textContent = srv.api_service_id + ' - ' + srv.name + ' - PKR ' + parseFloat(srv.rate).toFixed(4);
            srvSelect.appendChild(opt);
        });
    }
    updateCurrentService();
}

function updateCurrentService() {
    const cat = catSelect.value;
    const srvId = srvSelect.value;
    if (servicesData[cat]) {
        currentService = servicesData[cat].find(s => s.id == srvId);
    }
    
    if (currentService) {
        minMaxText.textContent = `Min: ${currentService.min} - Max: ${currentService.max}`;
        qtyBoundsHint.textContent = `Min: ${currentService.min} - Max: ${currentService.max}`;
        qtyInput.min = currentService.min;
        qtyInput.max = currentService.max;
        calculateCharge();
    }
}

function calculateCharge() {
    const qty = parseInt(qtyInput.value) || 0;
    if (currentService && qty > 0) {
        let charge = (currentService.rate / 1000) * qty;
        chargeDisplay.textContent = 'PKR ' + charge.toFixed(4);
    } else {
        chargeDisplay.textContent = 'PKR 0.0000';
    }
}

catSelect.addEventListener('change', updateServices);
srvSelect.addEventListener('change', updateCurrentService);
qtyInput.addEventListener('input', calculateCharge);

// Init
updateServices();
</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
