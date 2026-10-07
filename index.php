<?php
session_start();
require_once 'config.php';

// If already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['is_admin']) {
        header("Location: admin/index.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            
            // Redirect admin to admin dashboard, regular user to user dashboard
            if ($user['is_admin']) {
                header("Location: admin/index.php");
            } else {
                header("Location: dashboard.php");
            }
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'register') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $phone = $_POST['phone'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            // Check if username or email exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ? OR phone = ?");
            $stmt->execute([$username, $email, $phone]);
            if ($stmt->fetchColumn() > 0) {
                $error = "Username/ Email or Phone already exists.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, phone, password) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$username, $email, $phone, $hash])) {
                    $success = "Registration successful! Please login.";
                } else {
                    $error = "Registration failed. Try again.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>FollowersMax - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .glass-panel { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.5); }
        .gradient-text { background: linear-gradient(to right, #f97316, #f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
    <script>
        function toggleForm() {
            document.getElementById('login-form').classList.toggle('hidden');
            document.getElementById('register-form').classList.toggle('hidden');
            document.getElementById('form-title').innerText = document.getElementById('login-form').classList.contains('hidden') ? 'Create an Account' : 'Welcome Back';
            document.getElementById('form-subtitle').innerText = document.getElementById('login-form').classList.contains('hidden') ? 'Sign up to get started' : 'Sign in to your account';
        }
    </script>
</head>
<body class="min-h-screen flex items-center justify-center relative overflow-hidden">
    <!-- Animated background decorators -->
    <div class="absolute top-0 right-0 w-[30rem] h-[30rem] bg-orange-300/30 rounded-full blur-[80px] -z-10 mix-blend-multiply animate-pulse"></div>
    <div class="absolute bottom-0 left-0 w-[30rem] h-[30rem] bg-amber-300/30 rounded-full blur-[80px] -z-10 mix-blend-multiply animate-pulse" style="animation-delay: 2s;"></div>

    <div class="w-full max-w-md p-6">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-extrabold tracking-tight mb-2">
                <span class="gradient-text">Followers</span>Max <i class="fa-solid fa-circle-check text-blue-500 text-xl"></i>
            </h1>
            <p class="text-gray-500 font-medium font-arabic mt-2 text-xl">بِسْمِ ٱللَّٰهِ ٱلرَّحْمَٰنِ ٱلرَّحِيمِ</p>
        </div>

        <div class="glass-panel rounded-3xl p-8 shadow-xl shadow-orange-500/10 border border-white/60 relative z-10">
            <div class="text-center mb-8">
                <h2 id="form-title" class="text-2xl font-bold text-gray-800">Welcome Back</h2>
                <p id="form-subtitle" class="text-gray-500 text-sm mt-1">Sign in to your account</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 text-red-600 border border-red-200 p-3 rounded-xl mb-6 text-sm flex gap-2 items-center">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-50 text-green-600 border border-green-200 p-3 rounded-xl mb-6 text-sm flex gap-2 items-center">
                    <i class="fa-solid fa-circle-check"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form id="login-form" method="POST" class="space-y-5 <?php echo ($action === 'register' && !$success) ? 'hidden' : ''; ?>">
                <input type="hidden" name="action" value="login">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">Username</label>
                    <div class="relative">
                        <i class="fa-solid fa-user absolute left-4 top-3.5 text-gray-400 text-sm"></i>
                        <input type="text" name="username" required class="w-full bg-gray-50/50 border border-gray-200 text-gray-800 py-3 pl-10 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:bg-white transition-all text-sm font-medium">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-700">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="password" required class="w-full bg-gray-50 border border-gray-200 text-gray-800 py-3 pl-11 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all">
                    </div>
                </div>

                <div class="flex justify-between items-center text-sm px-1">
                    <label class="flex items-center space-x-2 cursor-pointer group">
                        <input type="checkbox" class="rounded border-gray-300 text-orange-500 focus:ring-orange-500 w-4 h-4 cursor-pointer transition-all checked:bg-orange-500">
                        <span class="text-gray-600 font-medium group-hover:text-gray-800 transition-colors">Remember me</span>
                    </label>
                    <a href="forgot_password.php" class="font-bold text-orange-600 hover:text-amber-500 transition-colors">Forgot Password?</a>
                </div>
                
                <button type="submit" class="w-full bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold text-lg py-3.5 rounded-xl shadow-lg shadow-orange-500/30 transform transition-all hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Sign In <i class="fa-solid fa-arrow-right ml-2 opacity-80"></i>
                </button>
                <div class="text-center mt-6">
                    <p class="text-sm text-gray-600">Don't have an account? <button type="button" onclick="toggleForm()" class="text-orange-500 font-bold hover:underline">Sign up</button></p>
                </div>
            </form>

            <!-- Register Form -->
            <form id="register-form" method="POST" class="space-y-4 <?php echo (isset($_POST['action']) && $_POST['action'] === 'register' && !$success) ? '' : 'hidden'; ?>">
                <input type="hidden" name="action" value="register">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">Choose a Username</label>
                    <div class="relative">
                        <i class="fa-solid fa-user absolute left-4 top-3.5 text-gray-400 text-sm"></i>
                        <input type="text" name="username" required minlength="3" class="w-full bg-gray-50/50 border border-gray-200 text-gray-800 py-3 pl-10 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:bg-white transition-all text-sm font-medium">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">Email Address</label>
                    <div class="relative">
                        <i class="fa-solid fa-envelope absolute left-4 top-3.5 text-gray-400 text-sm"></i>
                        <input type="email" name="email" required class="w-full bg-gray-50/50 border border-gray-200 text-gray-800 py-3 pl-10 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:bg-white transition-all text-sm font-medium">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">Phone Number</label>
                    <div class="relative">
                        <i class="fa-solid fa-phone absolute left-4 top-3.5 text-gray-400 text-sm"></i>
                        <input type="tel" name="phone" required class="w-full bg-gray-50/50 border border-gray-200 text-gray-800 py-3 pl-10 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:bg-white transition-all text-sm font-medium">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">Create Password</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-4 top-3.5 text-gray-400 text-sm"></i>
                        <input type="password" name="password" required minlength="6" class="w-full bg-gray-50/50 border border-gray-200 text-gray-800 py-3 pl-10 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:bg-white transition-all text-sm font-medium">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">Confirm Password</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-4 top-3.5 text-gray-400 text-sm"></i>
                        <input type="password" name="confirm_password" required minlength="6" class="w-full bg-gray-50/50 border border-gray-200 text-gray-800 py-3 pl-10 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:bg-white transition-all text-sm font-medium">
                    </div>
                </div>
                <button type="submit" class="w-full bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-orange-500/30 transform transition-all hover:-translate-y-0.5 focus:outline-none mt-2">
                    Create Account <i class="fa-solid fa-user-plus ml-2"></i>
                </button>
                <div class="text-center mt-6">
                    <p class="text-sm text-gray-600">Already have an account? <button type="button" onclick="toggleForm()" class="text-orange-500 font-bold hover:underline">Sign in</button></p>
                </div>
            </form>

        </div>
    </div>
</body>
</html>
