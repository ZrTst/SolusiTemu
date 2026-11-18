<?php
// api/import_data.php
// Admin import data

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (
    !isset($_POST['status']) ||
    $_POST['status'] !== "importing" ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    http_response_code(401);
    include($_SERVER['DOCUMENT_ROOT'] . '/SolusiTemu/pages/errors/401.html');
    exit;
}

// Increse time limit for this script
set_time_limit(300);

require_once __DIR__ . '/../../private_core/config.php';
require_once __DIR__ . '/../../private_core/functions.php';

// Unified function
$redirectWithStatus = function ($status) {
    header("Location: ../dashboard.php?page=import_admin&status=" . urlencode($status));
    exit();
};

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $redirectWithStatus('upload_error');
    }
    $fileSize = (int) ($_FILES['file']['size'] ?? 0);
    $maxSize = 100 * 1024; // 100KB

    if ($fileSize <= 0 || $fileSize > $maxSize) {
        $redirectWithStatus('file_too_large');
    }

    // Allowed file type
    $allowedExts = ['csv', 'txt'];
    $allowedMimes = [
        'text/plain',
        'text/csv',
        'text/x-csv',
        'application/csv',
        'application/x-csv',
        'application/vnd.ms-excel'
    ];

    // File extension .csv .txt
    $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($_FILES['file']['tmp_name']);

    if (!in_array($ext, $allowedExts, true) || !in_array($mime, $allowedMimes, true)) {
        $redirectWithStatus('invalid');
    }

    $filePath = $_FILES['file']['tmp_name'];
    $handle = fopen($filePath, 'r');

    if ($handle === false) {
        $redirectWithStatus('upload_error');
    }

    // Byte Order Mark
    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF") {
        rewind($handle);
    }

    // Front-end variable
    $role = isset($_POST['user']) ? trim($_POST['user']) : '';
    if (!in_array($role, ['customer', 'staff'], true)) {
        fclose($handle);
        $redirectWithStatus('invalid_role');
    }

    $rowNumber = 0;
    $successRow = 0;
    $failedRow = 0;

    while (($row = fgetcsv($handle, 0, ',')) !== false) {
        $rowNumber++;

        if (
            $row === [NULL] || empty(array_filter(array_map('trim', $row), 'strlen'))
        ) {
            continue;
        }

        // Skip table header
        if ($rowNumber === 1) {
            continue;
        }

        $uname = trim($row[0] ?? '');
        $upass = trim($row[1] ?? '');
        $rawNum = trim($row[2] ?? '');
        $rawInfo = trim($row[3] ?? '');

        $phoneNumberPattern = '/^01\d-?\d{3,4} ?\d{4}$/';
        if (preg_match('/^\+60/', $rawNum)) {
            $unum = substr($rawNum, 2);
        } else {
            $unum = $rawNum;
        }
        if (!preg_match($phoneNumberPattern, $unum)) {
            $unum = '';
        }
        $unum = preg_replace('/[-\s]+/', '', $unum);

        if ($role === 'staff') {
            $uinfo = strtolower($rawInfo);
            if (!in_array($uinfo, ['admin', 'juruteknik'], true)) {
                $failedRow++;
                continue;
            }
        } else {
            if (in_array(strtolower($rawInfo), ['admin', 'juruteknik', 'staff'], true)) {
                $failedRow++;
                continue;
            }
            $uinfo = $rawInfo;
        }

        if (empty($uname) || empty($upass)) {
            $failedRow++;
            continue;
        }
        if (preg_match('/^[=+\-@]/', $uname) || preg_match('/^[=+\-@]/', $uinfo)) {
            $failedRow++;
            continue;
        }

        $response = db_addUser($pdo, $uname, $upass, $unum, $uinfo, $role);
        if ($response === true) {
            $successRow++;
        } else {
            $failedRow++;
        }
    }

    fclose($handle);
    header("Location: ../dashboard.php?page=import_admin&added={$successRow}&failed={$failedRow}");
    exit();
}
?>