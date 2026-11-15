<?php
// api/update_profile.php
// Update user data 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (
    !isset($_SESSION['logged_in']) ||
    $_SESSION['logged_in'] !== true ||
    !isset($_POST['status']) ||
    ($_POST['status'] !== "editing-profile" && $_POST['status'] !== "adding-user")
) {
    http_response_code(401);
    //Pull the page 401.html without changing URL
    include($_SERVER['DOCUMENT_ROOT'] . '/SolusiTemu/pages/errors/401.html');
    exit;
}
require_once __DIR__ . '/../../private_core/config.php';
require_once __DIR__ . '/../../private_core/functions.php';

// Unified function
$redirectWithStatus = function ($status) {
    header("Location: ../dashboard.php?page=profil&status=" . urlencode($status));
    exit();
};

$redirectWithStatusAdmin = function ($status) {
    header("Location: ../dashboard.php?page=pengguna_admin&status=" . urlencode($status));
    exit();
};

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';
    $data_type = isset($_POST['data_type']) ? strtolower(trim($_POST['data_type'])) : '';
    $userId = isset($_SESSION['user_id']) ? trim($_SESSION['user_id']) : '';
    $dataChange = isset($_POST['data_change']) ? trim($_POST['data_change']) : '';
    $role = isset($_SESSION['role']) ? trim($_SESSION['role']) : '';

    $prefix = strtoupper(substr($userId, 0, 1));

    $passwordPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}$/';
    // Standard way to write regular expression
    // $phoneNumberPattern = '/^01[0-9]{1}-?[0-9]{7,8}$/';
    // ===== New way to write regular expression =====
    $phoneNumberPattern = '/^01\d-?\d{3,4} ?\d{4}$/';

    if ($status === 'editing-profile') {
        $userIdEdit = isset($_POST['id']) ? trim($_POST['id']) : '';

        // Check validation of inputs
        if (empty($data_type) || empty($userId) || empty($dataChange) || ($prefix === 'A' && empty($userIdEdit))) {
            if ($prefix === 'A') {
                $redirectWithStatusAdmin('invalid');
            }
            $redirectWithStatus('invalid');
        }

        // Check validation of data types 
        $allowedDataTypes = ['name', 'password', 'number', 'address'];
        if (!in_array($data_type, $allowedDataTypes, true)) {
            if ($prefix === 'A') {
                $redirectWithStatusAdmin('invalid');
            }
            $redirectWithStatus('invalid');
        }

        // Check strength of password (if)
        if ($data_type === 'password') {
            if (!preg_match($passwordPattern, $dataChange)) {
                if ($prefix === 'A') {
                    $redirectWithStatusAdmin('weak_pass');
                }
                $redirectWithStatus('weak_pass');
            }
        }

        //Check validation of phone number (if)
        if ($data_type === 'number') {
            if (!preg_match($phoneNumberPattern, $dataChange)) {
                if ($prefix === 'A') {
                    $redirectWithStatusAdmin('invalid_phone_number');
                }
                $redirectWithStatus('invalid_phone_number');
            }
            // Deleted dash(-) then update in database
            // ========== str_replace('target', 'replace to', 'String')
            $dataChange = preg_replace('/[-\s]+/', '', $dataChange);
        }

        //Check validation of name used (if)
        if ($data_type === 'name') {
            if (db_checkName($pdo, $dataChange) === true) {
                if ($prefix === 'A') {
                    $redirectWithStatusAdmin('name_used');
                }
                $redirectWithStatus('name_used');
            }
        }

        $db_column = '';
        $prefixEdit = strtoupper(substr($userIdEdit, 0, 1));

        if ($role === 'juruteknik' || ($prefix === 'A' && ($prefixEdit === 'J' || $prefixEdit === 'A'))) {
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
                case 'pfp':
                    $db_column = 'Gbr_S';
                    break;
                case 'address':
                    if ($prefix === 'A') {
                        $redirectWithStatusAdmin('invalid');
                    }
                    $redirectWithStatus('invalid');
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
            if ($prefix === 'A') {
                $redirectWithStatusAdmin('invalid');
            }
            $redirectWithStatus('invalid');
        }

        if ($prefix === 'A') {
            $response = db_updateData($pdo, $userIdEdit, $db_column, $dataChange);
        } else {
            $response = db_updateData($pdo, $userId, $db_column, $dataChange);
        }

        if ($response === true) {
            if ($prefix !== 'A' || $userIdEdit === $userId) {
                $user = db_getUserById($pdo, $userId);
                if ($db_column === 'Nama_P' || $db_column === 'Nama_S') {
                    $_SESSION['name'] = $user['Nama_P'] ?? $user['Nama_S'] ?? '';
                }
                $_SESSION['user_data'] = $user;
            }
            if ($prefix === 'A') {
                $redirectWithStatusAdmin('success');
            }
            $redirectWithStatus('success');
        }

        if ($prefix === 'A') {
            $redirectWithStatusAdmin($response);
        }
        $redirectWithStatus($response);

    } else if ($status === 'adding-user' && $prefix === 'A') {
        $uName = isset($_POST['uname']) ? trim($_POST['uname']) : '';
        $uPass = isset($_POST['upass']) ? trim($_POST['upass']) : '';
        $uNum = isset($_POST['unum']) ? trim($_POST['unum']) : '';
        $uRole = isset($_POST['role']) ? trim($_POST['role']) : '';
        $uAddress = isset($_POST['uaddress']) ? trim($_POST['uaddress']) : '-';

        $is_staff = !empty($uRole);
        $uType = $is_staff ? 'staff' : 'customer';
        $info = $is_staff ? $uRole : $uAddress;

        if (empty($uName) || empty($uPass) || empty($uNum) || ($is_staff && empty($info))) {
            $redirectWithStatusAdmin('invalid');
        }

        if (db_checkName($pdo, $uName) === true) {
            $redirectWithStatusAdmin('name_used');
        }

        if (!preg_match($passwordPattern, $uPass)) {
            $redirectWithStatusAdmin('weak_pass');
        }

        $uNum = preg_replace('/[-\s]+/', '', $uNum);

        $response = db_addUser($pdo, $uName, $uPass, $uNum, $info, $uType);

        if ($response === true) {
            $redirectWithStatusAdmin('success');
        }
        $redirectWithStatusAdmin($response);
    } else {
        if ($prefix === 'A') {
            $redirectWithStatusAdmin('invalid');
        }
        $redirectWithStatus('invalid');
    }
}
?>