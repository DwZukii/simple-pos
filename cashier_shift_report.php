<?php
// Guard
require_once '_guards.php';
Guard::cashierOnly();

$cashierId = User::getAuthenticatedUser()->id;

// What the till should hold, based on this cashier's sales today.
$expectedCash = Sales::getCashierTodaySales($cashierId);
$pastReports  = ShiftReport::getByCashier($cashierId);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Point of Sale System :: Shift Report</title>
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

                <!-- Cash handover form -->
                <div style="flex: 2; padding: 16px;">
                    <div class="subtitle">Close Shift</div>
                    <hr/>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Expected in Till</div>
                        </div>
                        <div class="card-content">
                            RM <?= number_format((float)$expectedCash, 2) ?>
                        </div>
                    </div>

                    <div class="card mt-16">
                        <div class="card-content">
                            <form method="POST" action="api/shift_report_controller.php?action=close">

                                <?php displayFlashMessage('shift_report') ?>

                                <div class="form-control">
                                    <label>Cash Counted (RM)</label>
                                    <input
                                        type="number"
                                        name="counted_cash"
                                        step="0.01"
                                        min="0"
                                        placeholder="e.g. 999.00"
                                        required
                                    />
                                </div>

                                <div class="form-control mt-16">
                                    <label>Notes (optional)</label>
                                    <input
                                        type="text"
                                        name="notes"
                                        placeholder="Reason for any difference"
                                    />
                                </div>

                                <div class="mt-16">
                                    <button class="btn btn-primary w-full" type="submit">Submit Handover</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Previous handovers -->
                <div style="flex: 5; padding: 16px;">
                    <div class="subtitle">My Previous Handovers</div>
                    <hr/>

                    <table id="shiftReportsTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Expected</th>
                                <th>Counted</th>
                                <th>Difference</th>
                                <th>Result</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($pastReports as $report) : ?>
                                <?php $variance = (float)$report->variance; ?>
                                <tr>
                                    <td><?= date('d M Y, h:i A', strtotime($report->created_at)) ?></td>
                                    <td>RM <?= number_format((float)$report->expected_cash, 2) ?></td>
                                    <td>RM <?= number_format((float)$report->counted_cash, 2) ?></td>
                                    <td>RM <?= number_format($variance, 2) ?></td>
                                    <td>
                                        <?php if ($variance == 0) : ?>
                                            <span class="text-green-300">Balanced</span>
                                        <?php elseif ($variance > 0) : ?>
                                            <span>Over</span>
                                        <?php else : ?>
                                            <span class="text-red-500">Short</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($report->notes ?? '') ?></td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>

                </div>
            </div>
        </main>
    </div>

<script type="text/javascript">
var dataTable = new simpleDatatables.DataTable("#shiftReportsTable");
</script>

</body>
</html>
