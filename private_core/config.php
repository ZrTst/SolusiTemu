<?php
//private_core/config.php

//$db= database
//$pass= password
//$sql= SQL query statement
//$stmt= statement
//$dsn= data source name

$host = 'localhost';
$db = 'solusitemu';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';


$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    //Halting the execution of the current code once occure error
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    //Returns only an associative array with field names as keys.
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    //Disable emulated prepared statements and foce-anable native database prepares statements.
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    //For testing uses, uncomment the next line
    //echo "Database connected successfully";
} catch (PDOException $e) {
    error_log("Database connection failed:" . $e->getMessage());
    die("Some error occured. Please try again.");
}

?>