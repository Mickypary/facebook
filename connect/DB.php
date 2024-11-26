<?php

class DB
{
  private static function connect()
  {
    $dsn = "mysql:host=127.0.0.1;dbname=facebook; charset=utf8mb4";
    $pdo = new PDO($dsn, 'root', '', [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    // $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
  }

  public static function query($query, $params = array())
  {
    $stmt = self::connect()->prepare($query);
    $stmt->execute($params);

    if (explode(' ', $query)[0] == 'SELECT') {
      $data = $stmt->fetchAll();
      return $data;
    }
  }
}
