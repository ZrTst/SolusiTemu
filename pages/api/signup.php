<?php
// api/signup.php
// Insert data from user into the database

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_POST['status']) || $_POST['status'] !== 'registering') {
    //For someone who haven't login and unauthorized 
    http_response_code(401);
    //Pull the page 401.html without changing URL
    include($_SERVER['DOCUMENT_ROOT'] . '/SolusiTemu/pages/errors/401.html');
    exit;
}
require_once __DIR__ . '/../../private_core/config.php';
require_once __DIR__ . '/../../private_core/functions.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $name = isset($_POST['name-signup']) ? trim($_POST['name-signup']) : '';
    $password = isset($_POST['password-signup']) ? trim($_POST['password-signup']) : '';

    if ($name !== '' && $password !== '') {
        $resultStatus = db_checkName($pdo, $name);
        if ($resultStatus === 'invalid') {
            $_SESSION['status'] = 'InvalidUsername';
        } elseif ($resultStatus === true) {
            $_SESSION['status'] = 'NameUsed';
        } elseif ($resultStatus === 'error') {
            $_SESSION['status'] = 'DatabaseError';
        } elseif ($resultStatus === false) {
            $statusInsert = db_insertData($pdo, $name, $password);
            if ($statusInsert === true) {
                $_SESSION['status'] = 'RegisterSuccess';
            } elseif ($statusInsert === 'weakpass') {
                $_SESSION['status'] = 'WeakPassword';
            } elseif ($statusInsert === 'invalid') {
                $_SESSION['status'] = 'InvalidUsername';
            } else {
                $_SESSION['status'] = 'DatabaseError';
            }
        } else {
            error_log("Unexpected variable occur at function db_checkName()");
            $_SESSION['status'] = 'UnknownError';
        }
    } else {
        $_SESSION['status'] = 'InvalidInput';
    }
    header("Location: ../login.php");
    exit();
}

?>