<?php
// Guard
require_once '_guards.php';
Guard::adminOnly();

$products = Product::all();
$selectedProduct = null;

if (get('id')) {
    $selectedProduct = Product::find(get('id'));
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Point of Sale System :: Add Stock</title>
    <link rel="stylesheet" type="text/css" href="./css/main.css">
    <link rel="stylesheet" type="text/css" href="./css/admin.css">
    <link rel="stylesheet" type="text/css" href="./css/util.css">
</head>
<body>

    <?php require 'templates/admin_header.php' ?>

    <div class="flex">
        <?php require 'templates/admin_navbar.php' ?>
        <main>
            <div class="wrapper">
                <div class="w-40p">
                    <div class="subtitle">Add Inventory Stock</div>
                    <hr/>

                    <div class="card">
                        <div class="card-content">
                            <form method="POST" action="api/product_controller.php?action=add_stock">

                                <?php displayFlashMessage('add_stock') ?>

                                <div class="form-control">
                                    <label>Select Product</label>
                                    <select name="id" required="">
                                        <option value=""> -- Select Product -- </option>
                                        <?php foreach ($products as $product) : ?>
                                            <option 
                                                value="<?= $product->id ?>"
                                                <?= ($selectedProduct && $selectedProduct->id === $product->id) ? 'selected' : '' ?>
                                            >
                                                <?= htmlspecialchars($product->name) ?> (Current Stock: <?= $product->quantity ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-control mt-16">
                                    <label>Additional Stock Quantity</label>
                                    <input 
                                        type="number" 
                                        name="quantity" 
                                        min="1" 
                                        step="1" 
                                        placeholder="e.g. 10" 
                                        required="" 
                                    />
                                </div>

                                <div class="mt-16">
                                    <button class="btn btn-primary w-full" type="submit">Add Stock</button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>

</body>
</html>