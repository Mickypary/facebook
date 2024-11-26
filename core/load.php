<?php

// include 'database/connection.php';
// include 'classes/Users.php';
// include 'classes/Post.php';

// global $pdo;

// $loadFromUser = new Users($pdo);
// $loadFromPost = new Post($pdo);

// define("BASE_URL", "http://localhost/facebook");

// Either use the above without namespace or use below with namespace

require dirname(__DIR__) . "/vendor/autoload.php";

$dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

use App\{Database, Users, Post};

$db = new Database($_ENV['DB_HOST'], $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASS']);
// print_r($db);
// exit;
$loadFromUser = new Users($db);
$loadFromPost = new Post($db);



define("BASE_URL", "http://localhost/facebook");
