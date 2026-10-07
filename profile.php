<?php
require_once 'includes/header.php';

$message = '';
$msg_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update_profile') {
            $new_username = trim($_POST['username']);
            $new_phone = trim($_POST['phone']);
            $new_email = trim($_POST['email']);

            // Check if username already exists for another user
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $stmt->execute([$new_username, $new_email, $user['id']]);
            if ($stmt->fetchColumn() > 0) {
                $message = "Username or Email already taken by another account.";
                $msg_type = 'error';
            } else {
                // Handle Profile Picture Upload
                $profile_pic_path = $user['profile_pic'];
                if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
                    $file_tmp = $_FILES['profile_pic']['tmp_name'];
                    $file_name = $_FILES['profile_pic']['name'];
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

                    if (in_array($file_ext, $allowed_exts)) {
                        $new_file_name = 'profile_' . $user['id'] . '_' . time() . '.' . $file_ext;
                        $upload_dir = 'uploads/profiles/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }

                        $dest_path = $upload_dir . $new_file_name;
                        if (move_uploaded_file($file_tmp, $dest_path)) {
                            // Delete old profile pic if exists
                            if ($user['profile_pic'] && file_exists($user['profile_pic'])) {
                                @unlink($user['profile_pic']);
                            }
                            $profile_pic_path = $dest_path;
                        }
                    } else {
                        $message = "Invalid image format. Allowed: JPG, PNG, GIF.";
                        $msg_type = 'error';
                    }
                }

                if ($msg_type !== 'error') {
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, phone = ?, profile_pic = ? WHERE id = ?");
                    if ($stmt->execute([$new_username, $new_email, $new_phone, $profile_pic_path, $user['id']])) {
                        $message = "Profile updated successfully!";
                        // Refresh user data
                        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                        $stmt->execute([$user['id']]);
                        $user = $stmt->fetch();
                    } else {
                        $message = "Failed to update profile.";
                        $msg_type = 'error';
                    }
                }
            }
        } elseif ($_POST['action'] === 'update_password') {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if (!password_verify($current_password, $user['password'])) {
                $message = "Current password is incorrect.";
                $msg_type = 'error';
            } elseif ($new_password !== $confirm_password) {
                $message = "New passwords do not match.";
                $msg_type = 'error';
            } elseif (strlen($new_password) < 6) {
                $message = "New password must be at least 6 characters.";
                $msg_type = 'error';
            } else {
                $hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($stmt->execute([$hash, $user['id']])) {
                    $message = "Password updated successfully!";
                } else {
                    $message = "Failed to update password.";
                    $msg_type = 'error';
                }
            }
        }
    }
}
?>

<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Account Settings</h1>
        <p class="text-gray-500 mt-1">Manage your profile information and security preferences.</p>
    </div>

    <?php if ($message): ?>
        <div
            class="p-4 mb-6 rounded-xl text-sm flex gap-3 items-center shadow-sm <?php echo $msg_type === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'; ?>">
            <i
                class="fa-solid <?php echo $msg_type === 'success' ? 'fa-check-circle' : 'fa-circle-exclamation'; ?> text-lg"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Sidebar Info -->
        <div class="lg:col-span-1">
            <div class="glass-panel rounded-3xl p-6 text-center">
                <div
                    class="w-24 h-24 bg-gradient-to-tr from-orange-500 to-amber-500 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg shadow-orange-500/20 overflow-hidden relative group">
                    <?php if ($user['profile_pic']): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <span
                            class="text-4xl text-white font-bold"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></span>
                    <?php endif; ?>
                </div>
                <h2 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($user['username']); ?></h2>
                <p class="text-gray-500 text-sm"><?php echo htmlspecialchars($user['email']); ?></p>

                <div class="mt-6 pt-6 border-t border-gray-100 flex flex-col gap-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">Member Since</span>
                        <span
                            class="font-semibold text-gray-700"><?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-400">Account Type</span>
                        <span
                            class="font-semibold <?php echo $user['is_admin'] ? 'text-indigo-600' : 'text-orange-600'; ?>">
                            <?php echo $user['is_admin'] ? 'Administrator' : 'Customer'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Forms -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Profile Details -->
            <div class="glass-panel rounded-3xl p-6 md:p-8 shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-user-circle text-orange-500"></i> Profile Information
                </h3>

                <form action="profile.php" method="POST" enctype="multipart/form-data" class="space-y-5">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="flex items-center gap-6 mb-8 p-4 bg-gray-50/50 rounded-2xl border border-gray-100">
                        <div class="relative group">
                            <div
                                class="w-20 h-20 rounded-2xl overflow-hidden bg-gray-200 border-2 border-white shadow-md">
                                <img id="avatar_preview"
                                    src="<?php echo $user['profile_pic'] ? htmlspecialchars($user['profile_pic']) : 'https://ui-avatars.com/api/?name=' . urlencode($user['username']) . '&background=f97316&color=fff'; ?>"
                                    class="w-full h-full object-cover">
                            </div>
                            <label for="profile_pic_input"
                                class="absolute -bottom-2 -right-2 w-8 h-8 bg-orange-500 text-white rounded-lg flex items-center justify-center cursor-pointer shadow-lg hover:bg-orange-600 transition-colors">
                                <i class="fa-solid fa-camera text-xs"></i>
                            </label>
                            <input type="file" name="profile_pic" id="profile_pic_input" class="hidden" accept="image/*"
                                onchange="previewImage(this)">
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">Profile Picture</h4>
                            <p class="text-xs text-gray-500 mt-1">PNG, JPG or GIF. Max 2MB.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700">Username</label>
                            <div class="relative">
                                <i class="fa-solid fa-at absolute left-4 top-3.5 text-gray-400 text-sm"></i>
                                <input type="text" name="username"
                                    value="<?php echo htmlspecialchars($user['username']); ?>" required
                                    class="w-full bg-gray-50/50 border border-gray-200 text-gray-800 py-3 pl-10 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all font-medium">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700">Phone Number</label>
                            <div class="relative">
                                <i class="fa-solid fa-phone absolute left-4 top-3.5 text-gray-400 text-sm"></i>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>"
                                    required
                                    class="w-full bg-gray-50/50 border border-gray-200 text-gray-800 py-3 pl-10 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all font-medium">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-gray-700">Email Address</label>
                        <div class="relative">
                            <i class="fa-solid fa-envelope absolute left-4 top-3.5 text-gray-400 text-sm"></i>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                                required
                                class="w-full bg-gray-50/50 border border-gray-200 text-gray-800 py-3 pl-10 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all font-medium">
                        </div>
                        <p class="text-[11px] text-gray-400 ml-1">Used for notifications and password recovery.</p>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                            class="bg-gradient-to-r from-orange-500 to-amber-500 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-orange-500/20 hover:shadow-orange-500/30 transform transition-all hover:-translate-y-0.5 active:scale-95">
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="glass-panel rounded-3xl p-6 md:p-8 shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-orange-500"></i> Change Password
                </h3>

                <form action="profile.php" method="POST" class="space-y-5">
                    <input type="hidden" name="action" value="update_password">

                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-gray-700">Current Password</label>
                        <div class="relative">
                            <i class="fa-solid fa-lock-open absolute left-4 top-3.5 text-gray-400 text-sm"></i>
                            <input type="password" name="current_password" required placeholder="••••••••"
                                class="w-full bg-gray-50/50 border border-gray-200 text-gray-800 py-3 pl-10 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all font-medium">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700">New Password</label>
                            <div class="relative">
                                <i class="fa-solid fa-lock absolute left-4 top-3.5 text-gray-400 text-sm"></i>
                                <input type="password" name="new_password" required minlength="6" placeholder="••••••••"
                                    class="w-full bg-gray-50/50 border border-gray-200 text-gray-800 py-3 pl-10 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all font-medium">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-gray-700">Confirm New Password</label>
                            <div class="relative">
                                <i class="fa-solid fa-shield absolute left-4 top-3.5 text-gray-400 text-sm"></i>
                                <input type="password" name="confirm_password" required minlength="6"
                                    placeholder="••••••••"
                                    class="w-full bg-gray-50/50 border border-gray-200 text-gray-800 py-3 pl-10 pr-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all font-medium">
                            </div>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                            class="bg-gray-800 text-white font-bold py-3 px-8 rounded-xl shadow-lg hover:bg-gray-900 transform transition-all hover:-translate-y-0.5 active:scale-95">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('avatar_preview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

<?php require_once 'includes/footer.php'; ?>