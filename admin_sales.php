<?php
//Guard
require_once '_guards.php';
Guard::adminOnly();

// Which period the report is showing. resolveReportFilter() turns the day /
// month / year / all-time choice into a plain start and end date.
$filter       = resolveReportFilter();
$filterAction = 'admin_sales.php';

$startDate = $filter['start'];
$endDate   = $filter['end'];

// Fetch Sales Summary and Transactions
$todaySales   = Sales::getTodaySales();
$totalSales   = Sales::getTotalSales();
$rangeSales   = Sales::getSalesBetween($startDate, $endDate);
$orders       = Order::allBetween($startDate, $endDate);
$topProduct   = Sales::getTopProductBetween($startDate, $endDate);
$topCategory  = Sales::getTopCategoryBetween($startDate, $endDate);

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

            <!-- Period Filter & Report Action Bar -->
            <?php require 'templates/report_filter.php' ?>

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
                            <?= htmlspecialchars($filter['label']) ?>
                        </div>
                    </div>

                    <div class="card mt-16">
                        <div class="card-header">
                            <div class="card-title">Best Selling Product</div>
                        </div>
                        <?php if ($topProduct) : ?>
                            <div class="card-content font-bold">
                                <?= htmlspecialchars($topProduct['name']) ?>
                            </div>
                            <div class="card-content">
                                <?= $topProduct['quantity'] ?> sold in this period
                            </div>
                        <?php else : ?>
                            <div class="card-content">Nothing sold in this period</div>
                        <?php endif ?>
                    </div>

                    <div class="card mt-16">
                        <div class="card-header">
                            <div class="card-title">Best Selling Category</div>
                        </div>
                        <?php if ($topCategory) : ?>
                            <div class="card-content font-bold">
                                <?= htmlspecialchars($topCategory['name']) ?>
                            </div>
                            <div class="card-content">
                                <?= $topCategory['quantity'] ?> items sold in this period
                            </div>
                        <?php else : ?>
                            <div class="card-content">Nothing sold in this period</div>
                        <?php endif ?>
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