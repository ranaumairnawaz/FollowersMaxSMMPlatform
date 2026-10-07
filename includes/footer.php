        </main>
    </div>
</div>

<!-- Mobile Bottom Navigation (App-like) -->
<div class="fixed bottom-0 left-0 right-0 bg-white/80 backdrop-blur-xl border-t border-gray-200 lg:hidden grid grid-cols-5 items-end px-2 pt-2 pb-safe z-50">
    <!-- Home -->
    <a href="<?php echo $base_path; ?>dashboard.php" class="flex flex-col items-center gap-1 text-gray-400 hover:text-orange-500 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? '!text-orange-600' : ''; ?> pb-2">
        <i class="fa-solid fa-house text-xl <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'drop-shadow-md' : ''; ?>"></i>
        <span class="text-[10px] font-bold">Home</span>
    </a>
    
    <!-- Orders -->
    <a href="<?php echo $base_path; ?>orders.php" class="flex flex-col items-center gap-1 text-gray-400 hover:text-orange-500 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? '!text-orange-600' : ''; ?> pb-2">
        <i class="fa-solid fa-list-check text-xl <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'drop-shadow-md' : ''; ?>"></i>
        <span class="text-[10px] font-bold">Orders</span>
    </a>

    <!-- Add Funds (Center Floating) -->
    <a href="<?php echo $base_path; ?>add_funds.php" class="flex flex-col items-center justify-between pb-2 relative -mt-6 z-10 w-full">
        <div class="w-14 h-14 bg-gradient-to-r from-orange-500 to-amber-500 rounded-full flex items-center justify-center text-white shadow-lg shadow-orange-500/40 border-4 border-white <?php echo basename($_SERVER['PHP_SELF']) == 'add_funds.php' ? 'ring-4 ring-orange-100' : ''; ?> transform transition-transform active:scale-95 mb-1 mx-auto">
            <i class="fa-solid fa-wallet text-2xl drop-shadow"></i>
        </div>
        <span class="text-[10px] font-bold <?php echo basename($_SERVER['PHP_SELF']) == 'add_funds.php' ? 'text-orange-600' : 'text-gray-400 hover:text-orange-500'; ?> text-center w-full block">Deposit</span>
    </a>

    <!-- Admin / Spacer -->
    <?php if ($user['is_admin']): ?>
    <a href="<?php echo $base_path; ?>admin/index.php" class="flex flex-col items-center gap-1 text-gray-400 hover:text-orange-500 transition-colors <?php echo strpos($_SERVER['PHP_SELF'], 'admin/') !== false ? '!text-orange-600' : ''; ?> pb-2">
        <i class="fa-solid fa-chart-pie text-xl <?php echo strpos($_SERVER['PHP_SELF'], 'admin/') !== false ? 'drop-shadow-md' : ''; ?>"></i>
        <span class="text-[10px] font-bold">Admin</span>
    </a>
    <?php else: ?>
    <div></div> <!-- Spacer so structure remains balanced -->
    <?php endif; ?>

    <!-- Logout (For Everyone) -->
    <a href="<?php echo $base_path; ?>logout.php" class="flex flex-col items-center gap-1 text-gray-400 hover:text-red-500 transition-colors pb-2">
        <i class="fa-solid fa-right-from-bracket text-xl"></i>
        <span class="text-[10px] font-bold">Log out</span>
    </a>
</div>

<?php 
$whatsapp = get_setting($pdo, 'whatsapp_number', '');
if ($whatsapp): 
    $wa_url = preg_replace('/[^0-9]/', '', $whatsapp);
?>
<!-- Floating WhatsApp Button -->
<a href="https://wa.me/<?php echo $wa_url; ?>" target="_blank" class="fixed bottom-24 md:bottom-8 right-4 md:right-8 bg-green-500 hover:bg-green-600 text-white w-14 h-14 rounded-full flex items-center justify-center shadow-lg shadow-green-500/30 transition-transform hover:scale-110 z-50">
    <i class="fa-brands fa-whatsapp text-3xl"></i>
</a>
<?php endif; ?>

<!-- Status bar padding for modern mobile browsers (iOS mainly) -->
<style>
    .pb-safe { padding-bottom: env(safe-area-inset-bottom, 12px); }
</style>

</body>
</html>
