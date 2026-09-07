<?php
require_once '../_guards.php';
Guard::adminOnly();

$action = $_GET['action'] ?? '';

// 1. ADD NEW USER
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'cashier';

    if (empty($email) || empty($password)) {
        setFlashMessage('add_user', 'Email and password are required.', 'danger');
        header('Location: ../admin_account.php');
        exit();
    }

    // Check if user already exists
    $existingUser = User::where('email', $email)->first() ?? User::where('username', $email)->first();
    if ($existingUser) {
        setFlashMessage('add_user', 'User with this email already exists.', 'danger');
        header('Location: ../admin_account.php');
        exit();
    }

    // Hash password and save
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Create new user (adjust properties based on your User model schema)
    $user = new User();
    if (property_exists($user, 'email')) {
        $user->email = $email;
    }
    $user->username = $email;
    $user->password = $hashedPassword;
    $user->role     = $role;
    $user->save();

    setFlashMessage('add_user', 'User created successfully!', 'success');
    header('Location: ../admin_account.php');
    exit();
}

// 2. DELETE USER
if ($action === 'delete' && isset($_GET['id'])) {
    $userId = (int)$_GET['id'];
    
    // Prevent deleting the currently logged in admin
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
        setFlashMessage('delete_user', 'You cannot delete your own active account.', 'danger');
        header('Location: ../admin_account.php');
        exit();
    }

    $user = User::find($userId);
    if ($user) {
        $user->delete();
        setFlashMessage('delete_user', 'User deleted successfully.', 'success');
    } else {
        setFlashMessage('delete_user', 'User not found.', 'danger');
    }

    header('Location: ../admin_account.php');
    exit();
}

// Default redirect if no matching action
header('Location: ../admin_account.php');
exit();