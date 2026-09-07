<?php

require_once __DIR__.'/../_init.php';

if (post('action') === 'process_order') {
    $currentUser = User::getAuthenticatedUser();

    // getAuthenticatedUser() returns null when the session has expired or the
    // user no longer exists, so bail out before reading ->id off it.
    if (!$currentUser) {
        redirect('../login.php');
    }

    $cartItems = $_POST['cart_item'] ?? [];

    if (!is_array($cartItems) || count($cartItems) === 0) {
        flashMessage('transaction', 'Add at least one product before processing the order.', FLASH_ERROR);
        redirect('../index.php');
    }

    // Total the quantities per product first. The same product arriving on two
    // separate lines would otherwise be checked against stock twice in
    // isolation, and both checks could pass while the sum does not fit.
    $requested = [];

    foreach ($cartItems as $item) {
        $productId = $item['id'] ?? null;
        $quantity = $item['quantity'] ?? 0;

        if (!$productId || !is_numeric($quantity) || $quantity < 1) {
            flashMessage('transaction', 'That order contained an invalid quantity.', FLASH_ERROR);
            redirect('../index.php');
        }

        $requested[$productId] = ($requested[$productId] ?? 0) + $quantity;
    }

    // Check every line before writing anything, so an order can never be
    // partially recorded and stock can never be driven negative.
    foreach ($requested as $productId => $quantity) {
        $product = Product::find($productId);

        if (!$product) {
            flashMessage('transaction', 'One of those products no longer exists.', FLASH_ERROR);
            redirect('../index.php');
        }

        if ($product->quantity < $quantity) {
            flashMessage(
                'transaction',
                "Not enough stock for {$product->name}. Only {$product->quantity} left.",
                FLASH_ERROR
            );
            redirect('../index.php');
        }
    }

    try {
        $connection->beginTransaction();

        $order = Order::create($currentUser->id);

        foreach ($cartItems as $item) {
            OrderItem::add($order->id, $item);
        }

        $connection->commit();
    } catch (Exception $ex) {
        $connection->rollBack();

        flashMessage('transaction', 'The order could not be completed.', FLASH_ERROR);
        redirect('../index.php');
    }

    flashMessage('transaction', 'Successfull transaction.', FLASH_SUCCESS);
    redirect('../index.php');
}
