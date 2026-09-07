<?php
//Guard
require_once '_guards.php';
Guard::adminOnly();

// Handle Date Range Filtering (Defaults to current month if not set)
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate   = $_GET['end_date']   ?? date('Y-m-d');

// Fetch Sales Summary and Transactions
$todaySales   = Sales::getTodaySales();
$totalSales   = Sales::getTotalSales();
$transactions = OrderItem::all(); 

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Point of Sale System :: Sales</title>
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

            <!-- Date Filter & Report Action Bar -->
            <div class="card p-16 mb-16" style="margin: 16px;">
                <form method="GET" action="admin_sales.php" style="display: flex; gap: 16px; align-items: center;">
                    <div>
                        <label>From: </label>
                        <input type="date" name="start_date" value="<?= $startDate ?>">
                    </div>
                    <div>
                        <label>To: </label>
                        <input type="date" name="end_date" value="<?= $endDate ?>">
                    </div>
                    <button class="btn btn-primary" type="submit">Filter Sales</button>
                    <button class="btn" type="button" onclick="window.print()">Print Report</button>
                </form>
            </div>

            <div class="flex">
                <div style="flex: 2; padding: 16px;">
                    <div class="subtitle">Sales Informations</div>
                    <hr/>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Today's Sales</div>
                        </div>
                        <div class="card-content">
                            RM <?= number_format((float)$todaySales, 2) ?>
                        </div>
                    </div>

                    <div class="card mt-16">
                        <div class="card-header">
                            <div class="card-title">Total Sales</div>
                        </div>
                        <div class="card-content">
                            RM <?= number_format((float)$totalSales, 2) ?>
                        </div>
                    </div>

                </div>
                <div style="flex: 5; padding: 16px">
                    <div class="subtitle">Transactions</div>
                    <hr/>

                    <table id="transactionsTable">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($transactions as $transaction) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($transaction->product_name) ?></td>
                                    <td><?= $transaction->quantity ?></td>
                                    <td>RM <?= number_format((float)$transaction->price, 2) ?></td>
                                    <td>RM <?= number_format((float)($transaction->quantity * $transaction->price), 2) ?></td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>

                </div>
            </div>

        </main>
    </div>

<script type="text/javascript">
var dataTable = new simpleDatatables.DataTable("#transactionsTable")
</script>

</body>
</html>