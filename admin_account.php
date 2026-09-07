<?php
// Guard
require_once '_guards.php';
Guard::adminOnly();

// Fetch all users using your existing User model pattern
$users = User::all();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Point of Sale System :: Manage Users</title>
    <link rel="stylesheet" type="text/css" href="./css/main.css">
    <link rel="stylesheet" type="text/css" href="./css/admin.css">
    <link rel="stylesheet" type="text/css" href="./css/util.css">

    <!-- Datatables Library -->
    <link rel="stylesheet" type="text/css" href="./css/datatable.css">
    <script src="./js/datatable.js"></script>
    <script src="./js/main.js"></script>
</head>
<body>

    <?php require 'templates/admin_header.php' ?>

    <div class="flex">
        <?php require 'templates/admin_navbar.php' ?>
        <main>
            <div class="flex">
                
                <!-- Left Column: Add New User Form -->
                <div class="category-form" style="flex: 2; padding: 16px;">
                    <span class="subtitle">New User Account</span>
                    <hr/>

                    <div class="card">
                        <div class="card-content">
                            <form method="POST" action="api/user_controller.php?action=add">

                                <?php displayFlashMessage('add_user') ?>

                                <div class="form-control">
                                    <label>Name</label>
                                    <input
                                        type="text"
                                        name="name"
                                        placeholder="Enter full name"
                                        required
                                    />
                                </div>

                                <div class="form-control mt-16">
                                    <label>Email</label>
                                    <input
                                        type="email"
                                        name="email"
                                        placeholder="Enter user email"
                                        required
                                    />
                                </div>

                                <div class="form-control mt-16">
                                    <label>Password</label>
                                    <input 
                                        type="password" 
                                        name="password" 
                                        placeholder="Enter password" 
                                        required 
                                    />
                                </div>

                                <div class="form-control mt-16">
                                    <label>Role</label>
                                    <select name="role" required>
                                        <option value="<?= ROLE_CASHIER ?>">Cashier</option>
                                        <option value="<?= ROLE_ADMIN ?>">Admin</option>
                                    </select>
                                </div>

                                <div class="mt-16">
                                    <button class="btn btn-primary w-full" type="submit">Create User</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right Column: User Accounts Table -->
                <div class="category-table" style="flex: 5; padding: 16px;">
                    <span class="subtitle">User List</span>
                    <hr/>

                    <?php displayFlashMessage('delete_user') ?>

                    <table id="usersTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $user) : ?>
                            <tr>
                                <td><?= htmlspecialchars($user->name) ?></td>
                                <td><?= htmlspecialchars($user->email) ?></td>
                                <td><?= ucfirst(strtolower(htmlspecialchars($user->role))) ?></td>
                                <td>
                                    <a class="text-red-500" href="api/user_controller.php?action=delete&id=<?= $user->id ?>">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                </div>
            </div>
        </main>
    </div>

<script type="text/javascript">
var dataTable = new simpleDatatables.DataTable("#usersTable")
</script>

</body>
</html>