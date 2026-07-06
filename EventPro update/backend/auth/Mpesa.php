<?php
// backend/auth/webhook.php — Mongike Webhook Handler for EventPro
include '../config/database.php';

// Verify the request is from Mongike
$apiKey = MONGIKE_API_KEY;
$receivedKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

if ($receivedKey !== $apiKey) {
    http_response_code(401);
    exit('Unauthorized');
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true) ?? [];

// Log raw webhook for debugging
$log_dir = __DIR__ . '/../logs';
if (!is_dir($log_dir)) {
    mkdir($log_dir, 0777, true);
}
file_put_contents($log_dir . '/webhook.log', date('[Y-m-d H:i:s] ') . $raw . PHP_EOL, FILE_APPEND | LOCK_EX);

$paymentStatus = strtoupper((string)($payload['payment_status'] ?? $payload['status'] ?? ''));
if ($paymentStatus === 'COMPLETED' || $paymentStatus === 'SUCCESS' || $paymentStatus === 'SUCCESSFUL') {
    $order_id = $payload['order_id'] ?? '';
    $mpesa_code = $payload['reference'] ?? '';

    if (!$order_id) {
        http_response_code(200);
        echo 'OK - No Order ID';
        exit;
    }

    // Find the pending payment
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE checkout_id = :checkout_id AND status = 'pending'");
    $stmt->execute(['checkout_id' => $order_id]);
    $payment = $stmt->fetch();

    if ($payment) {
        // Update payment status to completed
        $upd_pay = $pdo->prepare("UPDATE payments SET status = 'completed', mpesa_code = :mpesa_code, paid_at = CURRENT_TIMESTAMP WHERE id = :id");
        $upd_pay->execute(['mpesa_code' => $mpesa_code, 'id' => $payment['id']]);

        // Update booking status to confirmed
        $upd_book = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = :id");
        $upd_book->execute(['id' => $payment['booking_id']]);
        
        http_response_code(200);
        echo 'OK - Payment Confirmed';
        exit;
    }
}

http_response_code(200);
echo 'OK';
