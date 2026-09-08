<?php

require_once __DIR__.'/../_init.php';

class Sales
{
    public static function getTodaySales()
    {
        global $connection;

        $sql_command = ("
            SELECT 
                SUM(order_items.quantity*order_items.price) as today,
                DATE_FORMAT(orders.created_at, '%Y-%m-%d') as _date
            FROM 
                `order_items` 
            INNER JOIN 
                orders on order_items.order_id = orders.id 
            WHERE Date(created_at)=Curdate()
            GROUP BY 
                _date;
        ");

        $stmt = $connection->prepare($sql_command);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $result = $stmt->fetchAll();

        if (count($result) >= 1) {
            return $result[0]['today'];
        }

        return 0;
    }

    
    public static function getCashierTodaySales($user_id)
    {
        global $connection;

        $sql_command = ("
            SELECT
                SUM(order_items.quantity*order_items.price) as today
            FROM
                `order_items`
            INNER JOIN
                orders on order_items.order_id = orders.id
            WHERE orders.user_id = :user_id
                AND Date(orders.created_at) = Curdate();
        ");

        $stmt = $connection->prepare($sql_command);
        $stmt->bindParam('user_id', $user_id);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $result = $stmt->fetchAll();

        if (count($result) >= 1) {
            return $result[0]['today'] ?? 0;
        }

        return 0;
    }

    public static function getCashierSalesBetween($user_id, $startDate, $endDate)
    {
        global $connection;

        $sql_command = ("
            SELECT
                SUM(order_items.quantity*order_items.price) as total
            FROM
                `order_items`
            INNER JOIN
                orders on order_items.order_id = orders.id
            WHERE orders.user_id = :user_id
                AND Date(orders.created_at) BETWEEN :start_date AND :end_date;
        ");

        $stmt = $connection->prepare($sql_command);
        $stmt->bindParam('user_id', $user_id);
        $stmt->bindParam('start_date', $startDate);
        $stmt->bindParam('end_date', $endDate);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $result = $stmt->fetchAll();

        if (count($result) >= 1) {
            return $result[0]['total'] ?? 0;
        }

        return 0;
    }

    public static function getSalesBetween($startDate, $endDate)
    {
        global $connection;

        // order_items has no timestamp of its own, so the date comes from the
        // order it belongs to.
        $sql_command = ("
            SELECT
                SUM(order_items.quantity*order_items.price) as total
            FROM
                `order_items`
            INNER JOIN
                orders on order_items.order_id = orders.id
            WHERE Date(orders.created_at) BETWEEN :start_date AND :end_date;
        ");

        $stmt = $connection->prepare($sql_command);
        $stmt->bindParam('start_date', $startDate);
        $stmt->bindParam('end_date', $endDate);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $result = $stmt->fetchAll();

        if (count($result) >= 1) {
            return $result[0]['total'] ?? 0;
        }

        return 0;
    }

    // The single best selling product over a period, by units moved rather than
    // by money, which is what "most sold" normally means on a shop floor.
    // Returns null when nothing was sold in that window.
    public static function getTopProductBetween($startDate, $endDate)
    {
        global $connection;

        $sql_command = ("
            SELECT
                products.name as name,
                SUM(order_items.quantity) as quantity
            FROM
                `order_items`
            INNER JOIN
                orders on order_items.order_id = orders.id
            INNER JOIN
                products on order_items.product_id = products.id
            WHERE Date(orders.created_at) BETWEEN :start_date AND :end_date
            GROUP BY products.id, products.name
            ORDER BY quantity DESC
            LIMIT 1;
        ");

        return static::topRow($sql_command, $startDate, $endDate);
    }

    public static function getTopCategoryBetween($startDate, $endDate)
    {
        global $connection;

        $sql_command = ("
            SELECT
                categories.name as name,
                SUM(order_items.quantity) as quantity
            FROM
                `order_items`
            INNER JOIN
                orders on order_items.order_id = orders.id
            INNER JOIN
                products on order_items.product_id = products.id
            INNER JOIN
                categories on products.category_id = categories.id
            WHERE Date(orders.created_at) BETWEEN :start_date AND :end_date
            GROUP BY categories.id, categories.name
            ORDER BY quantity DESC
            LIMIT 1;
        ");

        return static::topRow($sql_command, $startDate, $endDate);
    }

    // Both queries above differ only in what they group by, so they share the
    // running of it.
    private static function topRow($sql_command, $startDate, $endDate)
    {
        global $connection;

        $stmt = $connection->prepare($sql_command);
        $stmt->bindParam('start_date', $startDate);
        $stmt->bindParam('end_date', $endDate);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $result = $stmt->fetchAll();

        if (count($result) >= 1) {
            return [
                'name'     => $result[0]['name'],
                'quantity' => (int) $result[0]['quantity'],
            ];
        }

        return null;
    }

    public static function getTotalSales()
    {
        global $connection;

        $sql_command = "SELECT SUM(quantity*price) as total FROM order_items";

        $stmt = $connection->prepare($sql_command);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $result = $stmt->fetchAll();

        if (count($result) >= 1) {
            return $result[0]['total'];
        }

        return 0;
    }

}