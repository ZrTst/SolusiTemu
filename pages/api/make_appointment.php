<?php
// api/update_profile.php
// Update user data 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_POST['status']) || $_POST['status'] !== "making-appointment") {
    //For someone who not supposed to at here
    http_response_code(401);
    //Pull the page 401.html without changing URL
    include($_SERVER['DOCUMENT_ROOT'] . '/SolusiTemu/pages/errors/401.html');
    exit;
}
require_once __DIR__ . '/../../private_core/config.php';
require_once __DIR__ . '/../../private_core/functions.php';

$userId = isset($_SESSION['user_id']) ? trim($_SESSION['user_id']) : '';

$redirectWithStatus = function ($status) {
    header("Location: ../dashboard.php?page=janjitemu_pelanggan&status=" . urlencode($status));
    exit();
};

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $idService = isset($_POST['idService']) ? trim($_POST['idService']) : '';
    $brandName = isset($_POST['brandName']) ? trim($_POST['brandName']) : '';
    $modalName = isset($_POST['modalName']) ? trim($_POST['modalName']) : '';
    $staffId = isset($_POST['idJuruteknik']) ? trim($_POST['idJuruteknik']) : '';
    $date = isset($_POST['date']) ? trim($_POST['date']) : '';
    $time = isset($_POST['time']) ? trim($_POST['time']) : '';

    // Define the regular expression for every input
    $idServicePattern = '/^PK\d{3}$/';
    $datePattern = '/^20\d{2}-\d{2}-\d{2}$/';
    $timePattern = '/^\d{2}:\d{2}$/';
    $availableTime = ['09:00', '10:30', '12:00', '14:00', '15:30', '17:00'];
    if (
        !preg_match($idServicePattern, $idService) ||
        !preg_match($datePattern, $date) ||
        !preg_match($timePattern, $time) ||
        !in_array($time, $availableTime)
    ) {
        $redirectWithStatus('invalid');
    }
    $timeDate = $date . ' ' . $time . ':00';

    $response = db_addAppointment($pdo, $userId, $staffId, $idService, $brandName, $modalName, $timeDate);

    if ($response === true) {
        $redirectWithStatus('success');
    }
    $redirectWithStatus($response);
}
?>