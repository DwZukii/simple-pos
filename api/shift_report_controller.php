<?php

require_once __DIR__.'/../_init.php';

// Closing a till is a cashier action. Guard::cashierOnly() redirects to a path
// relative to this directory, so the check is done here with a working one.
$currentUser = User::getAuthenticatedUser();

if (!$currentUser || $currentUser->role !== ROLE_CASHIER) {
    redirect('../login.php');
}

if (get('action') === 'close') {
    $countedCash = post('counted_cash');
    $notes = post('notes');

    if ($countedCash === '' || !is_numeric($countedCash) || $countedCash < 0) {
        flashMessage('shift_report', 'Enter the amount of cash you counted.', FLASH_ERROR);
        redirect('../cashier_shift_report.php');
    }

    // Read from the cashier's actual sales rather than the form, so the figure
    // being reconciled against cannot be edited by the person reconciling.
    $expectedCash = Sales::getCashierTodaySales($currentUser->id);

    try {
        ShiftReport::add($currentUser->id, $expectedCash, $countedCash, $notes);
        flashMessage('shift_report', 'Shift report submitted.', FLASH_SUCCESS);
    } catch (Exception $ex) {
        flashMessage('shift_report', 'An error occured', FLASH_ERROR);
    }

    redirect('../cashier_shift_report.php');
}

redirect('../cashier_shift_report.php');
