<?php

namespace App;

class Post extends Users
{
  public function __construct($pdo)
  {
    $this->pdo = $pdo;
  }
}
