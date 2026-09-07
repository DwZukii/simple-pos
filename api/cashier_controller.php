<?php

require_once __DIR__.'/../_init.php';

if (post('action') === 'process_order') {
    $currentUser = User::getAuthenticatedUser();

    // getAuthenticatedUser() returns null when the session has expired or the
    // user no longer exists, so bail out before reading ->id off it.
    if (!$currentUser) {
        redirect('../login.php');
    }

    $order = Order::create($currentUser->id);

    foreach ($_POST['cart_item'] as $item) {
        OrderItem::add($order->id, $item);
    }

    flashMessage('transaction', 'Successfull transaction.', FLASH_SUCCESS);
    redirect('../index.php');
}
