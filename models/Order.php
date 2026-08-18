<?php

require_once __DIR__.'/../_init.php';

class Order
{
    public $id;
    public $user_id;
    public $created_at;

    public function __construct($order)
    {
        $this->id = $order['id'];
        $this->user_id = $order['user_id'];
        $this->created_at = $order['created_at'];
    }

    public static function create($user_id)
    {
        global $connection;

        $sql_command = 'INSERT INTO orders (user_id) VALUES (:user_id)';
        $stmt = $connection->prepare($sql_command);
        $stmt->bindParam('user_id', $user_id);
        $stmt->execute();

        // Look the order up by its own insert id. Selecting the newest row
        // instead would return somebody else's order if a second checkout
        // landed in between.
        return static::find($connection->lastInsertId());
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