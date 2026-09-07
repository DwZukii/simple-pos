<?php 

require_once __DIR__.'/../_init.php';

class OrderItem 
{
    public $id;
    public $order_id;
    public $product_id;
    public $quantity;
    public $price;
    public $product_name;
    public $created_at;

    public function __construct($data)
    {
        $this->id = $data['id'];
        $this->order_id = $data['order_id'];
        $this->product_id = $data['product_id'];
        $this->quantity = $data['quantity'];
        $this->price = $data['price'];
        $this->product_name = $data['product_name'];
        // Only the date-filtered query joins orders, so this is not always set.
        $this->created_at = $data['created_at'] ?? null;
    }

    public static function add($orderId, $item)
    {
        global $connection;

        $product = Product::find($item['id']);

        $stmt = $connection->prepare('INSERT INTO `order_items`(order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)');
        $stmt->bindParam("order_id", $orderId);
        $stmt->bindParam("product_id", $item['id']);
        $stmt->bindParam("quantity", $item['quantity']);
        $stmt->bindParam("price", $product->price);

        $stmt->execute();

        $product->quantity -= $item['quantity'];
        $product->update();
    }

    public static function all()
    {
        global $connection;

        $stmt = $connection->prepare('
            SELECT 
                order_items.*, 
                products.name as product_name
            FROM order_items
            INNER JOIN products
            ON order_items.product_id = products.id
        ');
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $result = $stmt->fetchAll();

        $result = array_map(fn($item) => new OrderItem($item), $result);

        return $result;

    }

    public static function allBetween($startDate, $endDate)
    {
        global $connection;

        $stmt = $connection->prepare('
            SELECT
                order_items.*,
                products.name as product_name,
                orders.created_at
            FROM order_items
            INNER JOIN products
            ON order_items.product_id = products.id
            INNER JOIN orders
            ON order_items.order_id = orders.id
            WHERE Date(orders.created_at) BETWEEN :start_date AND :end_date
            ORDER BY orders.created_at DESC
        ');
        $stmt->bindParam('start_date', $startDate);
        $stmt->bindParam('end_date', $endDate);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $result = $stmt->fetchAll();

        $result = array_map(fn($item) => new OrderItem($item), $result);

        return $result;
    }
}