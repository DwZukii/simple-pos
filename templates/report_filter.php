<?php
// Day / month / year / all-time switcher, shared by the admin sales report and
// the cashier's own shift page. Expects $filter from resolveReportFilter() and
// $filterAction, the page the form posts back to.
?>
<div class="card p-16 mb-16" style="margin: 16px;">
    <form method="GET" action="<?= htmlspecialchars($filterAction) ?>" class="flex align-center gap-16">
        <label><span class="font-bold">Show:</span></label>

        <select name="filter_type" id="filterType" onchange="showFilterInput()">
            <option value="day"   <?= $filter['type'] === 'day'   ? 'selected' : '' ?>>A day</option>
            <option value="month" <?= $filter['type'] === 'month' ? 'selected' : '' ?>>A month</option>
            <option value="year"  <?= $filter['type'] === 'year'  ? 'selected' : '' ?>>A year</option>
            <option value="all"   <?= $filter['type'] === 'all'   ? 'selected' : '' ?>>All time</option>
        </select>

        <input type="date"   name="filter_day"   id="filterDay"   value="<?= htmlspecialchars($filter['day']) ?>">
        <input type="month"  name="filter_month" id="filterMonth" value="<?= htmlspecialchars($filter['month']) ?>">
        <input type="number" name="filter_year"  id="filterYear"  value="<?= htmlspecialchars($filter['year']) ?>" min="2000" max="2099" placeholder="YYYY" style="width: 90px;">

        <button class="btn btn-primary" type="submit">Apply</button>
        <button class="btn" type="button" onclick="window.print()">Print Report</button>
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
