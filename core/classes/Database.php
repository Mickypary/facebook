<?php

namespace App;

use PDO;
use PDOException;

class Database
{
  // the question mark means either PDO or null. i.e Nullable type
  private ?PDO $conn = null;
  public function __construct(private string $host, private string $dbname, private string $user, private string $password) {}

  public function getConnection(): PDO
  {
    if ($this->conn === null) {
      $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8";
      $this->conn =  new PDO($dsn, $this->user, $this->password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
      ]);
    }


    return $this->conn;
  }
}
