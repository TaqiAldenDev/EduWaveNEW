<?php
// TODO: Move these to environment variables for production
$host='localhost'; $db='eduwave'; $user='root'; $pass='';
$dsn="mysql:host=$host;dbname=$db;charset=utf8mb4";
try { $pdo=new PDO($dsn,$user,$pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);}
catch(PDOException $e){exit("DB error: ".$e->getMessage());}
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>