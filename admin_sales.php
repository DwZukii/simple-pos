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

// Fetch Sales Summary and Transactions.
// Today's and all-time takings no longer have their own cards: the filter
// covers both, as "Specific Day" on today and as "All Time".
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

            <h1 class="page-title">System Sales History &amp; Analytics</h1>
            <hr class="page-title-rule"/>

            <!-- Period filter. $filterPrint adds the print button, which only
                 this page wants. -->
            <?php $filterPrint = true; require 'templates/report_filter.php' ?>

            <div class="stat-row">
                <div class="stat-card stat-money">
                    <div class="stat-label">Filtered Cash Sales</div>
                    <div class="stat-value">RM <?= number_format((float)$rangeSales, 2) ?></div>
                    <div class="stat-note"><?= htmlspecialchars($filter['label']) ?></div>
                </div>

                <div class="stat-card stat-product">
                    <div class="stat-label">Most Sold Item</div>
                    <?php if ($topProduct) : ?>
                        <div class="stat-value"><?= htmlspecialchars($topProduct['name']) ?></div>
                        <div class="stat-note"><?= $topProduct['quantity'] ?> units sold</div>
                    <?php else : ?>
                        <div class="stat-value">&mdash;</div>
                        <div class="stat-note">Nothing sold in this period</div>
                    <?php endif ?>
                </div>

                <div class="stat-card stat-category">
                    <div class="stat-label">Top Category</div>
                    <?php if ($topCategory) : ?>
                        <div class="stat-value"><?= htmlspecialchars($topCategory['name']) ?></div>
                        <div class="stat-note"><?= $topCategory['quantity'] ?> total items sold</div>
                    <?php else : ?>
                        <div class="stat-value">&mdash;</div>
                        <div class="stat-note">Nothing sold in this period</div>
                    <?php endif ?>
                </div>
            </div>

            <div class="table-panel">
                <table id="transactionsTable">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Cashier / Staff</th>
                            <th>Date &amp; Time</th>
                            <th>Total Amount</th>
                            <th>Paid</th>
                            <th>Change</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($orders as $order) : ?>
                            <tr>
                                <td>#<?= $order->id ?></td>
                                <td class="font-bold"><?= htmlspecialchars($order->cashier_name ?? 'System / Admin') ?></td>
                                <td><?= date('d M Y h:i A', strtotime($order->created_at)) ?></td>
                                <td>RM <?= number_format((float)$order->total_amount, 2) ?></td>
                                <td><?= $order->payment === null ? '&mdash;' : 'RM '.number_format($order->payment, 2) ?></td>
                                <td><?= $order->getChange() === null ? '&mdash;' : 'RM '.number_format($order->getChange(), 2) ?></td>
                                <td>
                                    <button type="button" class="btn-receipt" onclick="viewReceipt(<?= $order->id ?>)">View Receipt</button>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>

<?php require 'templates/receipt_modal.php' ?>

<script type="text/javascript">
var dataTable = new simpleDatatables.DataTable("#transactionsTable")
</script>

</body>
</html>