<?php
$dsn = 'mysql:host=127.0.0.1;dbname=facebook;charset=utf8mb4';
$username = 'root';
$pass = '';

try {
  $pdo = new PDO($dsn, $username, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  ]);
} catch (PDOException $e) {
  echo 'Connection error! ' . $e->getMessage();
}
