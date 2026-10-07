<?php
// Script to run via cronjob to sync pending orders statuses
require_once __DIR__ . '/config.php';

// Get Active Orders
$stmt = $pdo->query("SELECT id, api_order_id FROM orders WHERE status NOT IN ('Completed', 'Canceled', 'Cancelled', 'Error', 'Partial') AND api_order_id IS NOT NULL");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$orders) {
    die("No pending orders to sync.\n");
}

// Bulk sync endpoint for speed
// action=status&orders=1,2,3
$order_ids = array_column($orders, 'api_order_id');
$id_map = []; // Map api_order_id back to local id
foreach ($orders as $o) {
    $id_map[$o['api_order_id']] = $o['id'];
}

$api_res = api_request($pdo, [
    'action' => 'status',
    'orders' => implode(',', $order_ids)
]);

if (is_array($api_res) && !isset($api_res['error'])) {
    $pdo->beginTransaction();
    $update_stmt = $pdo->prepare("UPDATE orders SET status = ?, charge = COALESCE(?, charge) WHERE id = ?");
    
    foreach ($api_res as $api_order_id => $details) {
        if (isset($id_map[$api_order_id])) {
            $local_id = $id_map[$api_order_id];
            
            // Format check
            if (isset($details['status'])) {
                $status = $details['status'];
                $charge = isset($details['charge']) ? (float)$details['charge'] : null;
                $update_stmt->execute([$status, $charge, $local_id]);
            }
        }
    }
    $pdo->commit();
    echo "Successfully synced " . count($api_res) . " order statuses.\n";
} else {
    echo "Error calling API.\n";
}
?>
