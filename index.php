<?php
// Guard
require_once '_guards.php';
Guard::cashierOnly();

$products = Product::all();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Point of Sale System :: Home</title>
    <link rel="stylesheet" type="text/css" href="./css/main.css">
    <link rel="stylesheet" type="text/css" href="./css/admin.css">
    <link rel="stylesheet" type="text/css" href="./css/cashier.css">
    <link rel="stylesheet" type="text/css" href="./css/util.css">

    <script src="./js/main.js"></script>
    <script src="./js/cashier.js"></script>
    
    <!-- Datatables Library -->
    <link rel="stylesheet" type="text/css" href="./css/datatable.css">
    <script src="./js/datatable.js"></script>

    <!-- AlpineJS Library -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

</head>
<body>

    <?php require 'templates/admin_header.php' ?>

    <div class="flex">
        <?php require 'templates/admin_navbar.php' ?>
        <main x-data='products(<?= json_encode($products) ?>)'>
            <div class="flex h-full">
                <div class="products">
                    <div class="subtitle">Products</div>
                    <hr/>

                    <?php displayFlashMessage('transaction') ?>

                    <table id="productsTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Stocks</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product) : ?>
                            <tr>
                                <td><?= htmlspecialchars($product->name) ?></td>
                                <td><?= htmlspecialchars($product->category->name ?? 'N/A') ?></td>
                                <td><?= $product->quantity ?></td>
                                <td>RM <?= number_format((float)$product->price, 2) ?></td>
                                <td>
                                    <a @click.prevent="addToCart(<?= $product->id ?>)" href="#" class="text-green-300">Add Product</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="forms">
                    <div class="flex flex-col h-full">
                        <div>
                            <div class="subtitle">Customer Orders</div>
                            <hr/>
                        </div>

                        <div id="cardItemsContainer" class="flex-grow" style="overflow-y: auto;">
                            <template x-for="cart in carts">
                                <div class="cart-item">
                                    <span class="left" x-text="cart.product.name"></span>
                                    <div class="middle">
                                        <div class="cart-item-buttons">
                                            <button type="button" @click="subtractQuantity(cart)">-</button>
                                            <span x-text="cart.quantity"></span>
                                            <button type="button" @click="addQuantity(cart)">+</button>
                                        </div>
                                    </div>
                                    <span class="right" x-text="'RM ' + (cart.quantity * cart.product.price).toFixed(2)"></span>
                                </div>                                
                            </template>
                        </div>

                        <form action="api/cashier_controller.php" method="POST" @submit="validate">

                            <input type="hidden" name="action" value="process_order">

                            <template x-for="(cart,i) in carts" :key="cart.product.id">
                                <div>
                                    <input type="hidden" :name="`cart_item[${i}][id]`" :value="cart.product.id">
                                    <input type="hidden" :name="`cart_item[${i}][quantity]`" :value="cart.quantity">
                                </div>
                            </template>

                            <div>
                                <span>Total Price: </span>
                                <span class="font-bold" x-text="'RM ' + Number(totalPrice).toFixed(2)"></span>
                            </div>
                            <div class="flex align-center gap-16 mt-16">
                                <span>Payment (RM): </span>
                                <div class="form-control flex-grow">
                                    <input 
                                        type="number" 
                                        x-model="payment" 
                                        @input="calculateChange" 
                                        step="0.01" 
                                        name="payment" 
                                        required 
                                    />
                                </div>
                            </div>
                            <div class="mt-16">
                                <span>Change: </span>
                                <span class="font-bold" x-text="(payment >= totalPrice && totalPrice > 0) ? 'RM ' + (payment - totalPrice).toFixed(2) : 'RM 0.00'"></span>
                            </div>
                            <button type="submit" class="btn btn-primary mt-16 w-full">Process Order</button>
                        </form>

                    </div>
                </div>
            </div>
        </main>
    </div>

<script type="text/javascript">
var dataTable = new simpleDatatables.DataTable("#productsTable")
</script>

<?php
// Shown once, straight after a checkout. The figures come from the session
// rather than a lookup because this is the receipt for the sale that has just
// happened, and unsetting it stops a refresh from printing it again.
if (get('show_receipt') && isset($_SESSION['receipt'])) :
    $receipt = $_SESSION['receipt'];
    unset($_SESSION['receipt']);
?>
<div id="checkoutReceipt" class="receipt-backdrop" style="display: flex;">
    <div class="receipt-paper">
        <h2 class="receipt-title">Point of Sale System</h2>
        <p class="receipt-subtitle">Payment Receipt</p>
        <hr class="receipt-rule"/>

        <div class="receipt-row">
            <span>Order</span>
            <span class="font-bold">#<?= htmlspecialchars($receipt['order_id']) ?></span>
        </div>
        <div class="receipt-row">
            <span>Date</span>
            <span><?= date('d M Y h:i A') ?></span>
        </div>

        <hr class="receipt-rule"/>

        <div class="receipt-row">
            <span>Total</span>
            <span class="font-bold">RM <?= number_format((float)$receipt['total'], 2) ?></span>
        </div>
        <div class="receipt-row">
            <span>Paid</span>
            <span>RM <?= number_format((float)$receipt['payment'], 2) ?></span>
        </div>
        <div class="receipt-row receipt-change">
            <span>Change</span>
            <span class="font-bold">RM <?= number_format((float)$receipt['change'], 2) ?></span>
        </div>

        <button type="button" class="btn btn-primary mt-16 w-full" onclick="closeCheckoutReceipt()">Done, new sale</button>
    </div>
</div>

<script type="text/javascript">
function closeCheckoutReceipt() {
    document.getElementById('checkoutReceipt').style.display = 'none';

    // Drop ?show_receipt=1 so a refresh does not look like it should reprint.
    if (window.history.replaceState) {
        window.history.replaceState(null, '', window.location.pathname);
    }
}
</script>
<?php endif ?>

</body>
</html>