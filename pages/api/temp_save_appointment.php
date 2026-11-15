<?php
// api/temp_save_appointment.php
// Insert data from user into the database

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (
    !isset($_SESSION['logged_in']) ||
    $_SESSION['logged_in'] !== true ||
    !isset($_POST['status']) ||
    $_POST['status'] !== 'making-appointment'
) {
    //For someone who haven't login and unauthorized 
    http_response_code(401);
    //Pull the page 401.html without changing URL
    include($_SERVER['DOCUMENT_ROOT'] . '/SolusiTemu/pages/errors/401.html');
    exit;
}
require_once __DIR__ . '/../../private_core/config.php';
require_once __DIR__ . '/../../private_core/functions.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $timeDate = $date . ' ' . $time . ':00';
    $_SESSION['temp_data'] = [
        'idService' => $_POST['idService'] ?? '',
        'brandName' => $_POST['brandName'] ?? '',
        'modalName' => $_POST['modalName'] ?? '',
        'date' => $_POST['date'] ?? '',
        'time' => $_POST['time'] ?? '',
    ];
    header("Location: ../dashboard.php?page=pilih_juruteknik");
}
?>