<?php
// Guard
require_once '_guards.php';
Guard::cashierOnly();

$cashierId = User::getAuthenticatedUser()->id;

// Which period to show. Defaults to today, which is what a cashier wants
// mid-shift; the switcher is there for checking an earlier day.
$filter       = resolveReportFilter();
$filterAction = 'cashier_sales.php';

// Every query below is scoped to $cashierId, so widening the dates still only
// ever shows this cashier their own takings.
// The filter defaults to today, so the headline figure is still this
// cashier's takings for the current shift unless they widen it themselves.
$periodSales       = Sales::getCashierSalesBetween($cashierId, $filter['start'], $filter['end']) ?? 0.00;
$shiftTransactions = Order::getByCashierBetween($cashierId, $filter['start'], $filter['end']) ?? [];
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

            <h1 class="page-title">Shift Sales &amp; Receipts History</h1>
            <hr class="page-title-rule"/>

            <!-- Period filter -->
            <?php require 'templates/report_filter.php' ?>

            <div class="stat-row">
                <div class="stat-card stat-money">
                    <div class="stat-label">Filtered Cash Sales</div>
                    <div class="stat-value">RM <?= number_format((float)$periodSales, 2) ?></div>
                    <div class="stat-note"><?= htmlspecialchars($filter['label']) ?></div>
                </div>
            </div>

            <div class="table-panel">
                <table id="cashierSalesTable">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date &amp; Time</th>
                            <th>Total Amount</th>
                            <th>Paid</th>
                            <th>Change</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($shiftTransactions as $order) : ?>
                            <tr>
                                <td>#<?= $order->id ?></td>
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
var dataTable = new simpleDatatables.DataTable("#cashierSalesTable");
</script>

</body>
</html>