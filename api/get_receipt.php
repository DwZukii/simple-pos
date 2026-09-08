<?php

require_once __DIR__.'/../_init.php';

header('Content-type: application/json');

// This endpoint is reachable from the browser, so it repeats the checks the
// page around it makes rather than trusting them.
$currentUser = User::getAuthenticatedUser();

if (!$currentUser) {
    http_response_code(401);
    die(json_encode(['success' => false, 'error' => 'You are not signed in.']));
}

$order = Order::findForReceipt(get('id'));

if (!$order) {
    http_response_code(404);
    die(json_encode(['success' => false, 'error' => 'That order does not exist.']));
}

// An admin can reprint any receipt. A cashier can only reprint their own,
// otherwise the sales of every other cashier are one URL away.
if ($currentUser->role !== ROLE_ADMIN && $order->user_id != $currentUser->id) {
    http_response_code(403);
    die(json_encode(['success' => false, 'error' => 'That order is not yours.']));
}

$items = array_map(fn($item) => [
    'product_name' => $item->product_name,
    'quantity'     => (int) $item->quantity,
    'price'        => (float) $item->price,
    'subtotal'     => (float) $item->price * (int) $item->quantity,
], OrderItem::forOrder($order->id));

echo json_encode([
    'success' => true,
    'order'   => [
        'id'           => (int) $order->id,
        'cashier_name' => $order->cashier_name,
        'created_at'   => $order->created_at,
        'total'        => (float) $order->total_amount,
        'payment'      => $order->payment,
        'change'       => $order->getChange(),
    ],
    'items'   => $items,
]);
