<?php
// Day / month / year / all-time switcher, shared by the admin sales report and
// the cashier's own shift page. Expects $filter from resolveReportFilter() and
// $filterAction, the page the form posts back to. $filterPrint is optional and
// adds the print button, which only the admin report wants.
?>
<div class="filter-bar">
    <form method="GET" action="<?= htmlspecialchars($filterAction) ?>" class="filter-bar" style="box-shadow: none; padding: 0; margin: 0;">
        <label for="filterType">Filter By:</label>

        <select name="filter_type" id="filterType" onchange="showFilterInput()">
            <option value="day"   <?= $filter['type'] === 'day'   ? 'selected' : '' ?>>Specific Day</option>
            <option value="month" <?= $filter['type'] === 'month' ? 'selected' : '' ?>>Specific Month</option>
            <option value="year"  <?= $filter['type'] === 'year'  ? 'selected' : '' ?>>Specific Year</option>
            <option value="all"   <?= $filter['type'] === 'all'   ? 'selected' : '' ?>>All Time</option>
        </select>

        <input type="date"   name="filter_day"   id="filterDay"   value="<?= htmlspecialchars($filter['day']) ?>">
        <input type="month"  name="filter_month" id="filterMonth" value="<?= htmlspecialchars($filter['month']) ?>">
        <input type="number" name="filter_year"  id="filterYear"  value="<?= htmlspecialchars($filter['year']) ?>" min="2000" max="2099" placeholder="YYYY" style="width: 90px;">

        <button class="btn btn-primary" type="submit">Filter</button>

        <?php if (!empty($filterPrint)) : ?>
            <button class="btn" type="button" onclick="window.print()">Print Report</button>
        <?php endif ?>
    </form>
</div>

<script type="text/javascript">
// Only the input that matches the chosen period is worth showing. The others
// still submit, which is what keeps their values when you switch back.
function showFilterInput() {
    var type = document.getElementById('filterType').value;

    document.getElementById('filterDay').style.display   = (type === 'day')   ? '' : 'none';
    document.getElementById('filterMonth').style.display = (type === 'month') ? '' : 'none';
    document.getElementById('filterYear').style.display  = (type === 'year')  ? '' : 'none';
}

showFilterInput();
</script>
