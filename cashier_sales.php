<?php
// Guard
require_once '_guards.php';
Guard::cashierOnly();

$cashierId = User::getAuthenticatedUser()->id;

// Fetch shift metrics and transactions for logged-in user
$todaySales = Sales::getCashierTodaySales($cashierId) ?? 0.00;
$shiftTransactions = Order::getByCashierToday($cashierId) ?? [];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Point of Sale System :: Cashier Sales</title>
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
                
                <!-- Daily Cash Sales Overview -->
                <div style="flex: 2; padding: 16px;">
                    <div class="subtitle">Shift Overview</div>
                    <hr/>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">My Today's Cash Sales</div>
                        </div>
                        <div class="card-content">
                            RM <?= number_format((float)$todaySales, 2) ?>
                        </div>
                    </div>
                </div>

                <!-- Shift Report Table -->
                <div style="flex: 5; padding: 16px;">
                    <div class="subtitle">My Shift Transactions</div>
                    <hr/>

                    <table id="cashierSalesTable">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Total Amount</th>
                                <th>Time</th>
                                <th>Receipt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($shiftTransactions as $order) : ?>
                                <tr>
                                    <td>#<?= $order->id ?></td>
                                    <td>RM <?= number_format((float)$order->total_amount, 2) ?></td>
                                    <td><?= date('h:i A', strtotime($order->created_at)) ?></td>
                                    <td>
                                        <a href="#" onclick="viewReceipt(<?= $order->id ?>); return false;" class="text-primary">View</a>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>

                </div>
            </div>
        </main>
    </div>

<?php require 'templates/receipt_modal.php' ?>

<script type="text/javascript">
var dataTable = new simpleDatatables.DataTable("#cashierSalesTable");
</script>

</body>
</html>