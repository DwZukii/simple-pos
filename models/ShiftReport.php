<?php

require_once __DIR__.'/../_init.php';

class ShiftReport
{
    public $id;
    public $user_id;
    public $expected_cash;
    public $counted_cash;
    public $variance;
    public $notes;
    public $created_at;

    public function __construct($data)
    {
        $this->id = $data['id'];
        $this->user_id = $data['user_id'];
        $this->expected_cash = $data['expected_cash'];
        $this->counted_cash = $data['counted_cash'];
        $this->variance = $data['variance'];
        $this->notes = $data['notes'];
        $this->created_at = $data['created_at'];
    }

    public static function add($user_id, $expected_cash, $counted_cash, $notes)
    {
        global $connection;

        // Stored rather than derived so the handover record still shows what
        // was counted against what was expected at the time, even if the
        // underlying sales are edited later.
        $variance = $counted_cash - $expected_cash;

        $sql_command = 'INSERT INTO shift_reports (user_id, expected_cash, counted_cash, variance, notes) VALUES (:user_id, :expected_cash, :counted_cash, :variance, :notes)';
        $stmt = $connection->prepare($sql_command);
        $stmt->bindParam('user_id', $user_id);
        $stmt->bindParam('expected_cash', $expected_cash);
        $stmt->bindParam('counted_cash', $counted_cash);
        $stmt->bindParam('variance', $variance);
        $stmt->bindParam('notes', $notes);
        $stmt->execute();
    }

    public static function getByCashier($user_id)
    {
        global $connection;

        $stmt = $connection->prepare('SELECT * FROM `shift_reports` WHERE user_id = :user_id ORDER BY created_at DESC');
        $stmt->bindParam('user_id', $user_id);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $result = $stmt->fetchAll();

        $result = array_map(fn($item) => new ShiftReport($item), $result);

        return $result;
    }
}
