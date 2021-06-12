<?php
require_once __DIR__.'/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();
?>

Hello PHP!

<?=$_ENV['DB_USER'] ?>