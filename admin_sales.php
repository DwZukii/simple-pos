<?php
//Guard
require_once '_guards.php';
Guard::adminOnly();

// Date range filter, defaulting to the current month so far. Anything that is
// not a real YYYY-MM-DD date falls back to the default rather than being handed
// to the query.
function validDate($value, $fallback)
{
    $date = DateTime::createFromFormat('Y-m-d', $value);

    if ($date && $date->format('Y-m-d') === $value) {
        return $value;
    }

    return $fallback;
}

$startDate = validDate(get('start_date'), date('Y-m-01'));
$endDate   = validDate(get('end_date'), date('Y-m-d'));

// Fetch Sales Summary and Transactions
$todaySales   = Sales::getTodaySales();
$totalSales   = Sales::getTotalSales();
$rangeSales   = Sales::getSalesBetween($startDate, $endDate);
$orders       = Order::allBetween($startDate, $endDate);

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
                        <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
                    </div>
                    <div>
                        <label>To: </label>
                        <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
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

                    <div class="card mt-16">
                        <div class="card-header">
                            <div class="card-title">Selected Period</div>
                        </div>
                        <div class="card-content">
                            RM <?= number_format((float)$rangeSales, 2) ?>
                        </div>
                        <div class="card-content">
                            <?= htmlspecialchars($startDate) ?> to <?= htmlspecialchars($endDate) ?>
                        </div>
                    </div>

                </div>
                <div style="flex: 5; padding: 16px">
                    <div class="subtitle">Orders</div>
                    <hr/>

                    <table id="transactionsTable">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Cashier</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Paid</th>
                                <th>Change</th>
                                <th>Receipt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $order) : ?>
                                <tr>
                                    <td>#<?= $order->id ?></td>
                                    <td><?= htmlspecialchars($order->cashier_name ?? 'Not recorded') ?></td>
                                    <td><?= date('d M Y h:i A', strtotime($order->created_at)) ?></td>
                                    <td>RM <?= number_format((float)$order->total_amount, 2) ?></td>
                                    <td><?= $order->payment === null ? '&mdash;' : 'RM '.number_format($order->payment, 2) ?></td>
                                    <td><?= $order->getChange() === null ? '&mdash;' : 'RM '.number_format($order->getChange(), 2) ?></td>
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
var dataTable = new simpleDatatables.DataTable("#transactionsTable")
</script>

</body>
</html>