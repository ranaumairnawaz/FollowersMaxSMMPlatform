<?php
require_once 'config.php';
require_once 'includes/mailer.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?")->execute([$token, $expires, $user['id']]);
        
        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . str_replace('forgot_password.php', '', $_SERVER['PHP_SELF']) . "reset_password.php?token=" . $token;
        
        $title = "Password Reset Request";
        $message = "<p>You requested a password reset. Click the button below to set a new password. This link will expire in 1 hour.</p>";
        $html = get_sleek_email_html($pdo, $title, $message, "Reset My Password", $reset_link);
        
        if (send_smtp_email($pdo, $email, "Reset your FollowersMax Password", $html)) {
            $success = "If an account exists, a reset link was sent to your email address.";
        } else {
            // Note: In local environment without SMTP configured, this hits.
            $error = "Failed to send the email. Please contact the administrator.";
        }
    } else {
        // Prevent email enumeration
        $success = "If an account exists, a reset link was sent to your email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>FollowersMax - Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .glass-panel { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.5); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center relative overflow-hidden bg-gray-50">
    <div class="w-full max-w-md p-6">
        <div class="glass-panel rounded-3xl p-8 shadow-xl shadow-orange-500/10 border border-white/60">
            <div class="text-center mb-6">
                <i class="fa-solid fa-key text-4xl text-orange-500 mb-4 inline-block"></i>
                <h2 class="text-2xl font-bold text-gray-800">Forgot Password?</h2>
                <p class="text-gray-500 text-sm mt-2">Enter your email and we'll send you a recovery link.</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 text-red-600 border border-red-200 p-3 rounded-xl mb-6 text-sm flex gap-2 items-center">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-50 text-green-600 border border-green-200 p-3 rounded-xl mb-6 text-sm flex gap-2 items-center text-center">
                    <i class="fa-solid fa-check-circle text-lg"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
                <div class="text-center">
                    <a href="index.php" class="text-orange-500 font-bold hover:underline">Return to Login</a>
                </div>
            <?php else: ?>
                <form method="POST" class="space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1.5 ml-1">Email Address</label>
                        <div class="relative">
                            <i class="fa-solid fa-envelope absolute left-4 top-3.5 text-gray-400 text-sm"></i>
                            <input type="email" name="email" required class="w-full bg-gray-50 border border-gray-200 text-gray-800 py-3 pl-10 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all text-sm font-medium">
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-orange-500/30 transition-all focus:outline-none">
                        Send Recovery Link
                    </button>
                    <div class="text-center mt-4">
                        <a href="index.php" class="text-sm font-bold text-gray-500 hover:text-orange-500 transition-colors"><i class="fa-solid fa-arrow-left mr-1"></i> Back to Login</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
