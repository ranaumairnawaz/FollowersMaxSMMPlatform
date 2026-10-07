<?php
require_once 'config.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if (!$token) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    $error = "Invalid or expired password reset link. Please request a new one.";
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'];
        $confirm = $_POST['confirm_password'];
        
        if ($password !== $confirm) {
            $error = "Passwords do not match.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?")->execute([$hash, $user['id']]);
            $success = "Your password has been successfully updated!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>FollowersMax - Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .glass-panel { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.5); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center relative bg-gray-50">
    <div class="w-full max-w-md p-6">
        <div class="glass-panel rounded-3xl p-8 shadow-xl shadow-orange-500/10 border border-white/60">
            <div class="text-center mb-6">
                <i class="fa-solid fa-lock-open text-4xl text-orange-500 mb-4 inline-block"></i>
                <h2 class="text-2xl font-bold text-gray-800">Set New Password</h2>
                <p class="text-gray-500 text-sm mt-2">Enter your new secure password below.</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 text-red-600 border border-red-200 p-3 rounded-xl mb-6 text-sm flex gap-2 items-center">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-50 text-green-600 border border-green-200 p-3 rounded-xl mb-6 text-sm flex gap-2 items-center">
                    <i class="fa-solid fa-check-circle text-lg"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
                <div class="text-center mt-6">
                    <a href="index.php" class="bg-orange-500 text-white font-bold py-2 px-6 rounded-lg hover:bg-orange-600 inline-block">Proceed to Login</a>
                </div>
            <?php elseif ($user): ?>
                <form method="POST" class="space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">New Password</label>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute left-4 top-3.5 text-gray-400 text-sm"></i>
                            <input type="password" name="password" required minlength="6" class="w-full bg-gray-50 border border-gray-200 text-gray-800 py-3 pl-10 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all text-sm font-medium">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">Confirm New Password</label>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute left-4 top-3.5 text-gray-400 text-sm"></i>
                            <input type="password" name="confirm_password" required minlength="6" class="w-full bg-gray-50 border border-gray-200 text-gray-800 py-3 pl-10 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all text-sm font-medium">
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-orange-500/30 transition-all focus:outline-none">
                        Save Password <i class="fa-solid fa-check ml-1"></i>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
