<?php
define("SERVER", "localhost");
define("DBASE", "stockup");
define("USER", "root");
define("PASSWORD", "");
define("SECRET_KEY", "yoursecret");

class Connection {
    protected $conn;
    protected $options = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false
    ];

    public function __construct() {
        $dsn = "mysql:host=" . SERVER . ";dbname=" . DBASE . ";charset=utf8mb4";
        try {
            $this->conn = new \PDO($dsn, USER, PASSWORD, $this->options);
        } catch (\PDOException $e) {
            // Log the error instead of echoing it in production
            error_log('Database Connection Error: ' . $e->getMessage());
            die('Database connection failed. Please try again later.');
        }
    }

    public function connect() {
        return $this->conn;
    }
}