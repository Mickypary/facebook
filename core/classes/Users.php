<?php

namespace App;

use PDO;

class Users
{
  protected $pdo;

  public function __construct(Database $pdo)
  {
    $this->pdo = $pdo;
  }

  public function checkInput($var)
  {
    // convert <b>bold</b> to &lt;b&gt; bold &lt;b&gt;
    $var = htmlspecialchars($var);
    $var = trim($var);
    $var = stripslashes($var);

    return $var;
  }

  public function checkEmail($email)
  {
    $stmt = $this->pdo->getConnection()->prepare("SELECT email FROM users WHERE email = :email");
    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
    $stmt->execute();

    $count = $stmt->rowCount();
    if ($count > 0) {
      return true;
    } else {
      return false;
    }
  }

  public function create($table, $fields = array())
  {
    $columns = implode(', ', array_keys($fields));
    $values = ':' . implode(', :', array_keys($fields));

    $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$values})";
    // var_dump($sql);
    if ($stmt = $this->pdo->getConnection()->prepare($sql)) {
      foreach ($fields as $key => $data) {
        $stmt->bindValue(":" . $key, $data, PDO::PARAM_STR);
      }
    }
    $stmt->execute();

    return $this->pdo->getConnection()->lastInsertId();
  }

  public function userIdByUsername($username)
  {
    $stmt = $this->pdo->getConnection()->prepare("SELECT user_id FROM users WHERE userLink = :username");
    $stmt->bindParam(":username", $username, PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_OBJ);

    return $user->user_id;
  }

  public function getUserData($profileId)
  {
    $stmt = $this->pdo->getConnection()->prepare("SELECT * FROM users LEFT JOIN profile ON users.user_id = profile.userId  WHERE users.user_id = :user_id");
    $stmt->bindParam(":user_id", $profileId, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_OBJ);
  }

  public function update($table, $user_id, $data = array())
  {
    $columns = '';
    $i = 1;

    foreach ($data as $name => $value) {
      $columns .= "{$name} = :{$name}";

      if ($i < count($data)) {
        $columns .= ', ';
      }
      $i++;
    }
    $sql = "UPDATE {$table} SET {$columns}  WHERE userId = :user_id";
    if ($stmt = $this->pdo->getConnection()->prepare($sql)) {
      foreach ($data as $key => $value) {
        $stmt->bindValue(":" . $key, $value);
      }
      $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    }

    $stmt->execute();
  }
} // End Class
