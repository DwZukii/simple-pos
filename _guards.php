<?php

require_once '_init.php';

class Guard {

    public static function adminOnly()
    {
        $currentUser = User::getAuthenticatedUser();

        if (!$currentUser || $currentUser->role !== ROLE_ADMIN) {
            redirect('login.php');
        }
    }

    public static function cashierOnly()
    {
        $currentUser = User::getAuthenticatedUser();

        if (!$currentUser || $currentUser->role !== ROLE_CASHIER) {
            redirect('login.php');
        }
    }

    public static function isAdmin()
    {
        $currentUser = User::getAuthenticatedUser();

        return $currentUser && $currentUser->role === ROLE_ADMIN;
    }

    // For the controllers in api/. adminOnly() sends people to 'login.php'
    // relative to the running script, which from that directory points at a
    // file that does not exist.
    public static function adminOnlyFromApi()
    {
        if (!static::isAdmin()) {
            redirect('../login.php');
        }
    }

    public static function hasModel($modelClass)
    {
        $model = $modelClass::find(get('id'));

        if ($model == null) {
            header('Content-type: text/plain');
            die('Page not found');
        }

        return $model;
    }

    public static function guestOnly() 
    {
        $currentUser = User::getAuthenticatedUser();

        if (!$currentUser) return;

        redirect($currentUser->getHomePage());
    }
}