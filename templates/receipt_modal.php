<?php
// Reprint of a past order, used by the admin sales report and the cashier's
// own shift page. Both pages call viewReceipt(orderId); the contents are
// fetched from api/get_receipt.php, which decides what the viewer may see.
?>
<div id="receiptModal" class="receipt-backdrop" style="display: none;">
    <div class="receipt-paper">
        <h2 class="receipt-title">Point of Sale System</h2>
        <p class="receipt-subtitle">Digital Receipt</p>
        <hr class="receipt-rule"/>

        <div id="receiptBody"></div>

        <button type="button" class="btn btn-primary mt-16 w-full" onclick="closeReceipt()">Close</button>
    </div>
</div>

<script type="text/javascript">
function money(value) {
    return 'RM ' + Number(value || 0).toFixed(2);
}

function receiptRow(label, value, className) {
    return '<div class="receipt-row ' + (className || '') + '">'
        + '<span>' + label + '</span><span>' + value + '</span>'
        + '</div>';
}

function viewReceipt(orderId) {
    var body = document.getElementById('receiptBody');

    body.textContent = 'Loading...';
    document.getElementById('receiptModal').style.display = 'flex';

    fetch('api/get_receipt.php?id=' + encodeURIComponent(orderId))
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (!data.success) {
                body.textContent = data.error || 'That receipt could not be loaded.';
                return;
            }

            var order = data.order;
            var html = '';

            html += receiptRow('Order', '<strong>#' + order.id + '</strong>');
            html += receiptRow('Cashier', order.cashier_name || 'Not recorded');
            html += receiptRow('Date', order.created_at || 'Not recorded');
            html += '<hr class="receipt-rule"/>';

            data.items.forEach(function (item) {
                html += receiptRow(
                    escapeHtml(item.product_name) + ' &times; ' + item.quantity,
                    money(item.subtotal)
                );
            });

            html += '<hr class="receipt-rule"/>';
            html += receiptRow('Total', '<strong>' + money(order.total) + '</strong>');
            html += receiptRow('Paid', order.payment === null ? 'Not recorded' : money(order.payment));
            html += receiptRow(
                'Change',
                order.change === null ? 'Not recorded' : '<strong>' + money(order.change) + '</strong>',
                'receipt-change'
            );

            body.innerHTML = html;
        })
        .catch(function () {
            body.textContent = 'That receipt could not be loaded.';
        });
}

// Product names come from the database and are printed into the modal as
// markup, so they are escaped here the same way the PHP pages escape them.
function escapeHtml(value) {
    var span = document.createElement('span');
    span.textContent = value == null ? '' : value;
    return span.innerHTML;
}

function closeReceipt() {
    document.getElementById('receiptModal').style.display = 'none';
}
</script>
