<?php
    require_once __DIR__.'/../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../');
    $dotenv->load();
    $dotenv->required(['A', 'B', 'C']);
?>
<html>
<h1>Hello PHP!</h1>
<br/> A <?php var_dump( $_ENV['A'] ); ?>
<br/> B <?php var_dump( $_ENV['B'] ); ?>
<br/> C <?php var_dump( $_ENV['C'] ); ?>
</html>
