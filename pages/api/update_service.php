<?php
// api/update_service.php
// Update service list 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (
    !isset($_POST['status']) ||
    $_POST['status'] !== "updating-service" ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    http_response_code(401);
    //Pull the page 401.html without changing URL
    include($_SERVER['DOCUMENT_ROOT'] . '/SolusiTemu/pages/error/401.html');
    exit;
}
require_once __DIR__ . '/../../private_core/config.php';
require_once __DIR__ . '/../../private_core/functions.php';

// Unified function
$redirectWithStatus = function ($status) {
    header("Location: ../dashboard.php?page=perkhidmatan_admin&status=" . urlencode($status));
    exit();
};

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $mainCat = isset($_POST['main-cat']) ? trim($_POST['main-cat']) : '';
    $subCat = isset($_POST['sub-cat']) ? trim($_POST['sub-cat']) : '';
    $dataChanged = isset($_POST['data-change']) ? trim($_POST['data-change']) : '';

    if ($subCat !== '') {
        // Main category
        if ($mainCat === 'Hardware' || $mainCat === 'Software') {
            if ($mainCat === 'Hardware') {
                $mainCat = 'HW';
            } else {
                $mainCat = 'SW';
            }
        } else {
            $redirectWithStatus('invalid');
        }

        // Sub-category
        $subCat = strtoupper($subCat);
        $dataChanged = strtoupper($dataChanged);

        // Activity
        $pattern = '/^[a-zA-Z]+$/';
        if (!preg_match($pattern, $dataChanged)) {
            $redirectWithStatus('invalid');
        }
        $finalService = $mainCat . '.' . $subCat . '.' . $dataChanged;
        $response = db_updateService($pdo, $finalService);
        if ($response === true) {
            $redirectWithStatus('success');
        } else {
            $redirectWithStatus('failed');
        }
    }
}