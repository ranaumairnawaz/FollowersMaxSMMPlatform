<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . (strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../index.php' : 'index.php'));
    exit;
}

// Fetch real user data from DB
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: " . (strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '../index.php' : 'index.php'));
    exit;
}

// Handle Phone Update from Popup
if (isset($_POST['update_phone_action'])) {
    if (!empty($_POST['new_phone'])) {
        $new_phone = trim($_POST['new_phone']);
        $stmt = $pdo->prepare("UPDATE users SET phone = ? WHERE id = ?");
        if ($stmt->execute([$new_phone, $user_id])) {
            $user['phone'] = $new_phone; // Update local variable
            $phone_updated = true;
        }
    } elseif (isset($_POST['dismiss_phone_modal'])) {
        $_SESSION['phone_modal_dismissed'] = true;
    }
}

$is_admin_area = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
$base_path = $is_admin_area ? '../' : './';
// Get appearance settings
$theme_font = get_setting($pdo, 'theme_font', 'Plus Jakarta Sans');
$theme_primary = get_setting($pdo, 'theme_primary', '#f97316');
$theme_primary_dark = get_setting($pdo, 'theme_primary_dark', '#ea580c');
$theme_dark_bg = get_setting($pdo, 'theme_dark_bg', '#0f172a');
$theme_card_bg = get_setting($pdo, 'theme_card_bg', '#1e293b');

$font_url_param = str_replace(' ', '+', $theme_font);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>FollowersMax - Modern SMM Module</title>
    <!-- Google Fonts dynamically loaded -->
    <link
        href="https://fonts.googleapis.com/css2?family=<?php echo htmlspecialchars($font_url_param); ?>:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"<?php echo htmlspecialchars($theme_font); ?>"', 'sans-serif'],
                    },
                    colors: {
                        primary: '<?php echo htmlspecialchars($theme_primary); ?>', // Orange-500 equivalent
                        primaryDark: '<?php echo htmlspecialchars($theme_primary_dark); ?>', // Orange-600 equivalent
                        darkBg: '<?php echo htmlspecialchars($theme_dark_bg); ?>',
                        cardBg: '<?php echo htmlspecialchars($theme_card_bg); ?>',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: '<?php echo htmlspecialchars($theme_font); ?>', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .dark .glass-panel {
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.05);
            color: white;
        }

        .gradient-text {
            background: linear-gradient(to right, #f97316, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px -5px rgba(249, 115, 22, 0.3);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        /* Mobile Bottom Nav App-like */
        @media (max-width: 768px) {
            .mobile-pb {
                padding-bottom: 80px;
            }

            .sidebar-closed {
                transform: translateX(-100%);
            }

            .sidebar-open {
                transform: translateX(0);
            }
        }
    </style>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('main-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }
    </script>
</head>

<body class="bg-gray-50 text-gray-800 antialiased mobile-pb overflow-x-hidden">

    <?php if (isset($_SESSION['admin_user_id'])): ?>
        <!-- Impersonation Bar -->
        <div class="bg-indigo-600 text-white py-2 px-4 flex items-center justify-between sticky top-0 z-[60] shadow-lg">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-mask text-indigo-200"></i>
                <span class="text-sm font-medium">You are currently viewing as
                    <b><?php echo htmlspecialchars($user['username']); ?></b></span>
            </div>
            <a href="<?php echo $base_path; ?>admin/impersonate.php?action=stop"
                class="bg-white/20 hover:bg-white/30 text-white text-xs font-bold py-1.5 px-4 rounded-lg transition-all flex items-center gap-2">
                <i class="fa-solid fa-right-from-bracket"></i> Back to Admin
            </a>
        </div>
    <?php endif; ?>

    <div class="flex h-screen overflow-hidden relative">
        <!-- Sidebar Overlay (Mobile) -->
        <div id="sidebar-overlay" onclick="toggleSidebar()"
            class="fixed inset-0 bg-black/50 z-30 hidden backdrop-blur-sm transition-opacity"></div>

        <!-- Sidebar -->
        <aside id="main-sidebar"
            class="fixed inset-y-0 left-0 w-64 glass-panel border-r border-gray-200 flex flex-col z-40 transform -translate-x-full md:translate-x-0 md:static transition-transform duration-300 ease-in-out">
            <div class="h-20 flex items-center justify-center border-b border-gray-100">
                <h1 class="text-2xl font-extrabold tracking-tight">
                    <span class="gradient-text">Followers</span>Max <i
                        class="fa-solid fa-circle-check text-blue-500 text-sm"></i>
                </h1>
            </div>

            <!-- Sidebar User Profile -->
            <div class="px-6 py-6 border-b border-gray-50 bg-gray-50/30">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-2xl overflow-hidden bg-gradient-to-tr from-orange-500 to-amber-500 shadow-md flex-shrink-0">
                        <?php if ($user['profile_pic']): ?>
                            <img src="<?php echo htmlspecialchars($base_path . $user['profile_pic']); ?>"
                                class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-white font-bold text-lg">
                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-800 truncate">
                            <?php echo htmlspecialchars($user['username']); ?>
                        </p>
                        <p class="text-[10px] font-bold text-orange-600 uppercase tracking-wider">PKR
                            <?php echo number_format($user['balance'], 2); ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto py-6 px-4 space-y-2">
                <!-- Regular Links -->
                <a href="<?php echo $base_path; ?>dashboard.php"
                    class="flex items-center space-x-3 px-4 py-3 rounded-xl <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-orange-50 text-orange-600 font-bold' : 'text-gray-600 hover:bg-gray-50 transition-colors'; ?>">
                    <i class="fa-solid fa-house w-5 text-center"></i>
                    <span>New Order</span>
                </a>
                <a href="<?php echo $base_path; ?>orders.php"
                    class="flex items-center space-x-3 px-4 py-3 rounded-xl <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'bg-orange-50 text-orange-600 font-bold' : 'text-gray-600 hover:bg-gray-50 transition-colors'; ?>">
                    <i class="fa-solid fa-list-check w-5 text-center"></i>
                    <span>Orders List</span>
                </a>
                <a href="<?php echo $base_path; ?>add_funds.php"
                    class="flex items-center space-x-3 px-4 py-3 rounded-xl <?php echo basename($_SERVER['PHP_SELF']) == 'add_funds.php' ? 'bg-orange-50 text-orange-600 font-bold' : 'text-gray-600 hover:bg-gray-50 transition-colors'; ?>">
                    <i class="fa-solid fa-wallet w-5 text-center"></i>
                    <span>Add Funds</span>
                </a>
                <a href="<?php echo $base_path; ?>profile.php"
                    class="flex items-center space-x-3 px-4 py-3 rounded-xl <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'bg-orange-50 text-orange-600 font-bold' : 'text-gray-600 hover:bg-gray-50 transition-colors'; ?>">
                    <i class="fa-solid fa-user-gear w-5 text-center"></i>
                    <span>Profile Settings</span>
                </a>
                <?php if ($user['is_admin']): ?>
                    <div class="pt-4 pb-2">
                        <p class="px-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Admin Panel</p>
                    </div>
                    <a href="<?php echo $base_path; ?>admin/index.php"
                        class="flex items-center space-x-3 px-4 py-3 rounded-xl <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'admin/') !== false ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-gray-600 hover:bg-gray-50 transition-colors'; ?>">
                        <i class="fa-solid fa-chart-pie w-5 text-center"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="<?php echo $base_path; ?>admin/orders.php"
                        class="flex items-center space-x-3 px-4 py-3 rounded-xl <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' && strpos($_SERVER['PHP_SELF'], 'admin/') !== false ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-gray-600 hover:bg-gray-50 transition-colors'; ?>">
                        <i class="fa-solid fa-list-check w-5 text-center"></i>
                        <span>All Orders</span>
                    </a>
                    <a href="<?php echo $base_path; ?>admin/funds.php"
                        class="flex items-center space-x-3 px-4 py-3 rounded-xl <?php echo basename($_SERVER['PHP_SELF']) == 'funds.php' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-gray-600 hover:bg-gray-50 transition-colors'; ?>">
                        <i class="fa-solid fa-money-bill-transfer w-5 text-center"></i>
                        <span>Fund Approvals</span>
                    </a>
                    <a href="<?php echo $base_path; ?>admin/users.php"
                        class="flex items-center space-x-3 px-4 py-3 rounded-xl <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-gray-600 hover:bg-gray-50 transition-colors'; ?>">
                        <i class="fa-solid fa-users w-5 text-center"></i>
                        <span>Manage Users</span>
                    </a>
                    <a href="<?php echo $base_path; ?>admin/services.php"
                        class="flex items-center space-x-3 px-4 py-3 rounded-xl <?php echo basename($_SERVER['PHP_SELF']) == 'services.php' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-gray-600 hover:bg-gray-50 transition-colors'; ?>">
                        <i class="fa-solid fa-server w-5 text-center"></i>
                        <span>Services</span>
                    </a>
                    <a href="<?php echo $base_path; ?>admin/settings.php"
                        class="flex items-center space-x-3 px-4 py-3 rounded-xl <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-gray-600 hover:bg-gray-50 transition-colors'; ?>">
                        <i class="fa-solid fa-gear w-5 text-center"></i>
                        <span>Settings</span>
                    </a>
                    <a href="<?php echo $base_path; ?>admin/appearance.php"
                        class="flex items-center space-x-3 px-4 py-3 rounded-xl <?php echo basename($_SERVER['PHP_SELF']) == 'appearance.php' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-gray-600 hover:bg-gray-50 transition-colors'; ?>">
                        <i class="fa-solid fa-palette w-5 text-center"></i>
                        <span>Appearance</span>
                    </a>
                    <a href="<?php echo $base_path; ?>admin/smtp.php"
                        class="flex items-center space-x-3 px-4 py-3 rounded-xl <?php echo basename($_SERVER['PHP_SELF']) == 'smtp.php' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-gray-600 hover:bg-gray-50 transition-colors'; ?>">
                        <i class="fa-solid fa-envelope w-5 text-center"></i>
                        <span>SMTP Mailer</span>
                    </a>
                <?php endif; ?>

                <!-- Auth / Profile -->
                <a href="<?php echo $base_path; ?>logout.php"
                    class="flex items-center space-x-3 px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 transition-colors mt-auto">
                    <i class="fa-solid fa-right-from-bracket w-5 text-center"></i>
                    <span>Log Out</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col relative overflow-hidden">
            <!-- Topbar -->
            <header
                class="h-20 glass-panel border-b border-gray-200 flex items-center justify-between px-6 z-10 sticky top-0">
                <div class="flex items-center md:hidden">
                    <h1 class="text-xl font-extrabold tracking-tight">
                        <span class="gradient-text">Followers</span>Max
                    </h1>
                </div>

                <div class="hidden md:flex items-center gap-4 text-gray-500">
                    <button class="hover:text-primary transition-colors"><i
                            class="fa-solid fa-bars text-xl"></i></button>
                </div>

                <div class="flex items-center gap-4 ml-auto">
                    <div
                        class="bg-gradient-to-r from-orange-500 to-amber-500 p-[2px] rounded-full drop-shadow-sm cursor-pointer hover:drop-shadow-md transition- hidden sm:inline">
                        <div class="bg-white rounded-full px-4 py-1.5 flex items-center gap-2 ">
                            <span class="text-sm font-bold text-gray-800 hidden sm:inline">PKR
                                <?php echo number_format($user['balance'], 3); ?></span>
                            <div class="w-1 h-4 bg-gray-200 mx-1 hidden sm:inline"></div>
                            <?php if ($user['profile_pic']): ?>
                                <img src="<?php echo htmlspecialchars($base_path . $user['profile_pic']); ?>"
                                    class="w-6 h-6 rounded-full object-cover border border-gray-100">
                            <?php else: ?>
                                <i class="fa-solid fa-user text-gray-400 text-sm"></i>
                            <?php endif; ?>
                            <span
                                class="text-sm font-semibold text-gray-600 hidden sm:inline"><?php echo htmlspecialchars($user['username']); ?></span>
                        </div>
                    </div>

                    <!-- Sidebar Trigger (Mobile) -->
                    <button onclick="toggleSidebar()"
                        class="md:hidden w-10 h-10 flex items-center justify-center bg-gray-100 rounded-xl text-gray-600 hover:bg-orange-50 hover:text-orange-600 transition-all">
                        <i class="fa-solid fa-bars-staggered"></i>
                    </button>

                    <a href="<?php echo $base_path; ?>logout.php"
                        class="text-gray-400 hover:text-red-500 transition-colors ml-2" title="Logout">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </a>
                </div>
            </header>

            <!-- Main Scrollable Area -->
            <main class="flex-1 overflow-y-auto overflow-x-hidden p-4 md:p-8 relative">
                <!-- Background Decoration -->
                <div
                    class="absolute top-0 right-0 w-96 h-96 bg-orange-300/20 rounded-full blur-3xl -z-10 transform translate-x-1/2 -translate-y-1/2">
                </div>
                <div
                    class="absolute bottom-0 left-0 w-96 h-96 bg-amber-200/20 rounded-full blur-3xl -z-10 transform -translate-x-1/2 translate-y-1/2">
                </div>

                <?php
                $current_page = basename($_SERVER['PHP_SELF']);
                if (empty($user['phone']) && !isset($phone_updated) && !isset($_SESSION['phone_modal_dismissed']) && !in_array($current_page, ['profile.php', 'logout.php'])):
                    ?>
                    <!-- Phone Number Update Popup -->
                    <div id="phone-modal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
                        <div
                            class="glass-panel w-full max-w-md rounded-3xl p-8 shadow-2xl relative z-10 animate-in fade-in zoom-in duration-300">

                            <!-- Close Button -->
                            <form method="POST" class="absolute top-4 right-4">
                                <input type="hidden" name="update_phone_action" value="1">
                                <input type="hidden" name="dismiss_phone_modal" value="1">
                                <button type="submit"
                                    class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </form>

                            <div class="text-center mb-6">
                                <div
                                    class="w-20 h-20 bg-orange-100 text-orange-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fa-solid fa-phone text-3xl"></i>
                                </div>
                                <h2 class="text-2xl font-bold text-gray-800">Update Phone Number</h2>
                                <p class="text-gray-500 text-sm mt-2">We need your phone number to provide better support
                                    and security for your account.</p>
                            </div>

                            <form method="POST" class="space-y-4">
                                <input type="hidden" name="update_phone_action" value="1">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2 ml-1">Phone Number</label>
                                    <div class="relative">
                                        <i class="fa-solid fa-phone absolute left-4 top-3.5 text-gray-400 text-sm"></i>
                                        <input type="tel" name="new_phone" required placeholder="e.g. +923001234567"
                                            class="w-full bg-gray-50/50 border border-gray-200 text-gray-800 py-3.5 pl-10 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:bg-white transition-all text-sm font-medium">
                                    </div>
                                </div>
                                <button type="submit"
                                    class="w-full bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-orange-500/30 transform transition-all hover:-translate-y-0.5">
                                    Save Phone Number <i class="fa-solid fa-check ml-2"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>