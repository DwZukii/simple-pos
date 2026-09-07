<?php

require_once __DIR__.'/../_init.php';

// Creating and deleting accounts is admin-only. Guard::adminOnly() redirects to
// 'login.php' relative to this directory, which does not exist, so check here
// and send them somewhere real.
if (!Guard::isAdmin()) {
    redirect('../login.php');
}

if (get('action') === 'add') {
    $name = post('name');
    $email = post('email');
    $password = post('password');
    $role = post('role') === ROLE_ADMIN ? ROLE_ADMIN : ROLE_CASHIER;

    if (empty($name) || empty($email) || empty($password)) {
        flashMessage('add_user', 'Name, email and password are all required.', FLASH_ERROR);
        redirect('../admin_account.php');
    }

    if (User::findByEmail($email)) {
        flashMessage('add_user', 'A user with that email already exists.', FLASH_ERROR);
        redirect('../admin_account.php');
    }

    try {
        User::add($name, $email, $role, $password);
        flashMessage('add_user', 'User created successfully.', FLASH_SUCCESS);
    } catch (Exception $ex) {
        flashMessage('add_user', 'An error occured', FLASH_ERROR);
    }

    redirect('../admin_account.php');
}

if (get('action') === 'delete') {
    $id = get('id');

    // Deleting the account you are currently signed in as would lock you out
    // half way through the request.
    if (User::getAuthenticatedUser()->id == $id) {
        flashMessage('delete_user', 'You cannot delete the account you are signed in as.', FLASH_ERROR);
        redirect('../admin_account.php');
    }

    User::find($id)?->delete();

    flashMessage('delete_user', 'User deleted successfully.', FLASH_SUCCESS);
    redirect('../admin_account.php');
}

redirect('../admin_account.php');
