<?php

require_once __DIR__.'/../_init.php';

class User
{
    public $id;
    public $name;
    public $email;
    public $role;
    public $password;


    public function getHomePage() {
        if ($this->role === ROLE_ADMIN) {
            return 'admin_home.php';
        }
        return 'index.php';
    }

    private static $currentUser = null;

    public function __construct($user)
    {
        $this->id = intval($user['id']);
        $this->name = $user['name'];
        $this->email = $user['email'];
        $this->role = $user['role'];
        $this->password = $user['password'];
    }

    public static function getAuthenticatedUser()
    {
        if (!isset($_SESSION['user_id'])) return null;

        if (!static::$currentUser) {
            static::$currentUser = static::find($_SESSION['user_id']);
        }

        return static::$currentUser;
    }

    public static function find($user_id) 
    {
        global $connection;

        $stmt = $connection->prepare("SELECT * FROM `users` WHERE id=:id");
        $stmt->bindParam("id", $user_id);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        
        $result = $stmt->fetchAll();

        if (count($result) >= 1) {
            return new User($result[0]);
        }

        return null;
    }

    public static function findByEmail($email) 
    {
        global $connection;

        $stmt = $connection->prepare("SELECT * FROM `users` WHERE email=:email");
        $stmt->bindParam("email", $email);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        
        $result = $stmt->fetchAll();

        if (count($result) >= 1) {
            return new User($result[0]);
        }

        return null;
    }

    public static function all()
    {
        global $connection;

        $stmt = $connection->prepare('SELECT * FROM `users` ORDER BY id');
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $result = $stmt->fetchAll();

        $result = array_map(fn($item) => new User($item), $result);

        return $result;
    }

    public static function add($name, $email, $role, $password)
    {
        global $connection;

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql_command = 'INSERT INTO users (name, email, role, password) VALUES (:name, :email, :role, :password)';
        $stmt = $connection->prepare($sql_command);
        $stmt->bindParam('name', $name);
        $stmt->bindParam('email', $email);
        $stmt->bindParam('role', $role);
        $stmt->bindParam('password', $hashedPassword);
        $stmt->execute();
    }

    public function delete()
    {
        global $connection;

        $stmt = $connection->prepare('DELETE FROM `users` WHERE id=:id');
        $stmt->bindParam('id', $this->id);
        $stmt->execute();
    }

    public static function login($email, $password) {
        if (empty($email)) throw new Exception("The email is required");
        if (empty($password)) throw new Exception("The password is required");

        $user = static::findByEmail($email);

        if ($user && $user->verifyPassword($password)) {
            return $user;
        }

        throw new Exception('Wrong email or password.');
    }

    public function verifyPassword($password)
    {
        // Accounts that predate password hashing still store their password as
        // plain text, so fall back to a direct comparison for those rows.
        // Anything created through admin_account.php is hashed.
        if (password_get_info($this->password)['algo']) {
            return password_verify($password, $this->password);
        }

        return $this->password === $password;
    }
}