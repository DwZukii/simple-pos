<?php

require_once __DIR__.'/../_init.php';

// The grouped queries below list their columns out rather than using
// `orders.*`. MySQL works out that the other columns depend on the primary key
// and lets `orders.*` through, but MariaDB (which XAMPP ships) does not, and
// rejects the query under ONLY_FULL_GROUP_BY.
class Order
{
    public $id;
    public $user_id;
    public $created_at;
    public $total_amount;
    // What the customer handed over. Nullable because orders recorded before
    // the receipt feature existed have no figure to show.
    public $payment;
    // Only the reporting queries join users, so this is not set on every Order.
    public $cashier_name;

    public function __construct($order)
    {
        $this->id = $order['id'];
        $this->user_id = $order['user_id'];
        $this->created_at = $order['created_at'];
        // Only the reporting queries total up the order's items, so this is
        // not set on every Order.
        $this->total_amount = $order['total_amount'] ?? null;
        $this->payment = isset($order['payment']) ? (float) $order['payment'] : null;
        $this->cashier_name = $order['cashier_name'] ?? null;
    }

    // The change is never stored. It is always payment minus the total, so
    // keeping a third column would only create a way for the three to disagree.
    public function getChange()
    {
        if ($this->payment === null || $this->total_amount === null) {
            return null;
        }

        return max(0, $this->payment - (float) $this->total_amount);
    }

    public static function create($user_id, $payment = null)
    {
        global $connection;

        $sql_command = 'INSERT INTO orders (user_id, payment) VALUES (:user_id, :payment)';
        $stmt = $connection->prepare($sql_command);
        $stmt->bindParam('user_id', $user_id);
        $stmt->bindParam('payment', $payment);
        $stmt->execute();

        // Look the order up by its own insert id. Selecting the newest row
        // instead would return somebody else's order if a second checkout
        // landed in between.
        return static::find($connection->lastInsertId());
    }


    public static function getByCashierToday($user_id)
    {
        global $connection;

        $sql_command = ("
            SELECT
                orders.id,
                orders.user_id,
                orders.payment,
                orders.created_at,
                SUM(order_items.quantity*order_items.price) as total_amount
            FROM
                `orders`
            INNER JOIN
                order_items on order_items.order_id = orders.id
            WHERE orders.user_id = :user_id
                AND Date(orders.created_at) = Curdate()
            GROUP BY orders.id, orders.user_id, orders.payment, orders.created_at
            ORDER BY orders.created_at DESC;
        ");

        $stmt = $connection->prepare($sql_command);
        $stmt->bindParam('user_id', $user_id);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $result = $stmt->fetchAll();

        $result = array_map(fn($item) => new Order($item), $result);

        return $result;
    }

    // One row per order rather than per line, so the sales report can show a
    // receipt button against each sale.
    public static function allBetween($startDate, $endDate)
    {
        global $connection;

        $sql_command = ("
            SELECT
                orders.id,
                orders.user_id,
                orders.payment,
                orders.created_at,
                users.name as cashier_name,
                SUM(order_items.quantity*order_items.price) as total_amount
            FROM
                `orders`
            INNER JOIN
                order_items on order_items.order_id = orders.id
            LEFT JOIN
                users on users.id = orders.user_id
            WHERE Date(orders.created_at) BETWEEN :start_date AND :end_date
            GROUP BY orders.id, orders.user_id, orders.payment, orders.created_at, users.name
            ORDER BY orders.created_at DESC;
        ");

        $stmt = $connection->prepare($sql_command);
        $stmt->bindParam('start_date', $startDate);
        $stmt->bindParam('end_date', $endDate);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $result = $stmt->fetchAll();

        $result = array_map(fn($item) => new Order($item), $result);

        return $result;
    }

    // find() on its own has no total and no cashier name, which is everything a
    // receipt is made of, so the receipt endpoint uses this instead.
    public static function findForReceipt($id)
    {
        global $connection;

        $sql_command = ("
            SELECT
                orders.id,
                orders.user_id,
                orders.payment,
                orders.created_at,
                users.name as cashier_name,
                SUM(order_items.quantity*order_items.price) as total_amount
            FROM
                `orders`
            LEFT JOIN
                order_items on order_items.order_id = orders.id
            LEFT JOIN
                users on users.id = orders.user_id
            WHERE orders.id = :id
            GROUP BY orders.id, orders.user_id, orders.payment, orders.created_at, users.name;
        ");

        $stmt = $connection->prepare($sql_command);
        $stmt->bindParam('id', $id);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $result = $stmt->fetchAll();

        if (count($result) >= 1) {
            return new Order($result[0]);
        }

        return null;
    }

    public static function find($id)
    {
        global $connection;

        $stmt = $connection->prepare('SELECT * FROM `orders` WHERE id=:id');
        $stmt->bindParam('id', $id);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $result = $stmt->fetchAll();

        if (count($result) >= 1) {
            return new Order($result[0]);
        }

        return null;
    }
}
