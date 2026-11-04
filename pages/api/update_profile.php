<?php
// api/update_profile.php
// Update user data 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_POST['status']) || $_POST['status'] !== "editing-profile") {
    //For someone who not supposed to at here
    http_response_code(401);
    //Pull the page 401.html without changing URL
    include($_SERVER['DOCUMENT_ROOT'] . '/SolusiTemu/pages/errors/401.html');
    exit;
}
require_once __DIR__ . '/../../private_core/config.php';
require_once __DIR__ . '/../../private_core/functions.php';

// Unified function
function redirectWithStatus($status)
{
    header("Location: ../dashboard.php?page=profil&status=" . urlencode($status));
    exit();
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    // 4 types data name, password, number, address
    $data_type = isset($_POST['data_type']) ? strtolower(trim($_POST['data_type'])) : '';
    $userId = isset($_SESSION['user_id']) ? trim($_SESSION['user_id']) : '';
    $dataChange = isset($_POST['data_change']) ? trim($_POST['data_change']) : '';
    $role = isset($_SESSION['role']) ? trim($_SESSION['role']) : '';
    $passwordPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}$/';
    // Standard way to write regular expression
    // $phoneNumberPattern = '/^01[0-9]{1}-?[0-9]{7,8}$/';
    // ===== New way to write regular expression =====
    $phoneNumberPattern = '/^01\d-?\d{3,4} ?\d{4}$/';

    // Check validation of inputs
    if (empty($data_type) || empty($userId) || empty($dataChange)) {
        redirectWithStatus('invalid');
    }

    // Check validation of data types 
    $allowedDataTypes = ['name', 'password', 'number', 'address'];
    if (!in_array($data_type, $allowedDataTypes, true)) {
        redirectWithStatus('invalid');
    }

    // Check strength of password (if)
    if ($data_type === 'password') {
        if (!preg_match($passwordPattern, $dataChange)) {
            redirectWithStatus('weak_password');
        }
    }

    //Check validation of phone number (if)
    if ($data_type === 'number') {
        if (!preg_match($phoneNumberPattern, $dataChange)) {
            redirectWithStatus('invalid_phone_number');
        }
        // Deleted dash(-) then update in database
        // ========== str_replace('target', 'replace to', 'String')
        $dataChange = preg_replace('/[-\s]+/', '', $dataChange);
    }

    //Check validation of name used (if)
    if ($data_type === 'name') {
        if (db_checkName($pdo, $dataChange) === true) {
            redirectWithStatus('name_used');
        }
    }

    $db_column = '';

    if ($role === 'juruteknik') {
        switch ($data_type) {
            case 'name':
                $db_column = 'Nama_S';
                break;
            case 'password':
                $db_column = 'Pswd_S';
                break;
            case 'number':
                $db_column = 'No_Tel_S';
                break;
            case 'address':
                redirectWithStatus('invalid');
        }
    } else {
        switch ($data_type) {
            case 'name':
                $db_column = 'Nama_P';
                break;
            case 'password':
                $db_column = 'Pswd_P';
                break;
            case 'number':
                $db_column = 'No_Tel_P';
                break;
            case 'address':
                $db_column = 'Alamat';
                break;
        }
    }

    if (empty($db_column)) {
        redirectWithStatus('invalid');
    }

    $response = db_updateData($pdo, $userId, $db_column, $dataChange);

    if ($response === true) {
        $user = db_getUserById($pdo, $userId);
        if ($db_column === 'Nama_P' || $db_column === 'Nama_S') {
            $_SESSION['name'] = $user['Nama_P'] ?? $user['Nama_S'] ?? '';
        }
        $_SESSION['user_data'] = $user;
        redirectWithStatus('success');
    }
    redirectWithStatus($response);
}
?>