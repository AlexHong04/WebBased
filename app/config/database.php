<?php

require_once 'config.php';

class Database
{
    private $host = DB_HOST;
    private $user = DB_USER;
    private $password = DB_PASS;
    private $dbName = DB_NAME;
    private $dbPort = DB_PORT;

    private $dbh;
    private $stmt;
    private $error;

    // Creates the PDO connection using the provided database settings.
    public function __construct()
    {
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbName . ';port=' . $this->dbPort;

        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        ];

        try {
            $this->dbh = new PDO($dsn, $this->user, $this->password, $options);
            echo "<script>console.log('Connect database successful')</script>";
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    // Prepares an SQL statement.
    public function query($sql)
    {
        $this->stmt = $this->dbh->prepare($sql);
    }

    // Executes the prepared SQL statement.
    public function execute()
    {
        return $this->stmt->execute();
    }

    // Fetches and returns all rows from the executed query.
    public function resultAll()
    {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Executes the query and returns one row
    public function result()
    {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Binds a value to a named SQL parameter (prevents SQL injection).
    public function bind($param, $value)
    {
        $this->stmt->bindValue($param, $value);
    }

    // // Is unique?
    function is_unique($value, $table, $field)
    {
        global $_db;
        $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
        $stm->execute([$value]);
        return $stm->fetchColumn() == 0;
    }

    // // Is exists?
    function is_exists($value, $table, $field)
    {
        global $_db;
        $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
        $stm->execute([$value]);
        return $stm->fetchColumn() > 0;
    }
}
