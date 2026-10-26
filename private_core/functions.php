<?php
//private_core/functions.php

//function get user info by id
function db_getUserById($pdo, $id)
{
    //Match ID format (P: Pelanggan, J: Juruteknik, A: Admin)
    if (!preg_match('/^[PJA]\d{3}$/i', $id)) {
        return false;
    }

    //Get the prefix of the id
    $prefix = strtoupper(substr($id, 0, 1));

    //Match the prefix with corresponding table
    if ($prefix === 'P') {
        $tablename = 'PELANGGAN';
        $idtype = 'ID_P';
    } elseif ($prefix === 'J' || $prefix === 'A') {
        $tablename = 'STAF';
        $idtype = 'ID_S';
    } else {
        return false; //Prevent invalid input
    }

    //Get info from table 
    try {

        $sql = "SELECT * FROM `{$tablename}` WHERE `{$idtype}`= ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();

    } catch (PDOException $e) {
        error_log("Database query failed: " . $e->getMessage());
        return false;
    }
}

//function get user info by name
function db_getUserByName($pdo, $name)
{

    //Check if the name is empty or not
    if (empty(trim($name))) {
        return false;
    }
    try {
        //Check if the name exists in PELANGGAN and STAF table
        $sql = "SELECT * FROM `PELANGGAN` WHERE `Nama_P`= ?
                UNION ALL
                SELECT * FROM `STAF` WHERE `Nama_S` = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $name]);
        $user = $stmt->fetch();
        if ($user) {
            return $user;
        }

        //Check if the name exists in STAF table
        $sql = "SELECT * FROM `STAF` WHERE `Nama_S`= ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name]);
        return $stmt->fetch();

    } catch (PDOException $e) {
        error_log("Database query failed in db_getUserByName: " . $e->getMessage());
        return false;
    }
}

// Function to verify password
function db_verifyPassword($user, $password)
{
    $dummy_hash = '$2y$10$abcdefghijklmnopqrstuvwxyzaaaaaaaaaaaaaaaaaaaaa'; //Random hashed string
    $stored_password = $user['Pswd_P'] ?? $user['Pswd_S'] ?? $dummy_hash;
    $is_match = password_verify($password, $stored_password);
    if (empty($user) || (!isset($user['Pswd_P']) && !isset($user['Pswd_S']))) {
        return false;
    }
    return $is_match;
}

//Function to destroy session and logout
function db_destroySession()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    //Clear all the data inside the session array
    $_SESSION = array();

    //Delete session in server
    session_destroy();

    //Delete cookie that include session(PHPSESSID) in user's browser
    $sessionName = session_name();
    if (isset($_COOKIE[$sessionName])) {
        setcookie(
            //Session name
            $sessionName,
            //Change $sessionName become empty string
            '',
            //Let the cookie expire
            time() - 42000,
            //At directory '/' (Whole website)
            '/'
        );
    }
}

function db_checkName($pdo, $name)
{
    //Check if the name is empty or not
    if (empty(trim($name))) {
        return 'invalid';
    }
    try {
        //Check if the name exists in PELANGGAN table
        $sql = "SELECT 1 FROM `PELANGGAN` WHERE `Nama_P` = ?
                UNION ALL
                SELECT 1 FROM `STAF` WHERE `Nama_S` = ?
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $name]);
        $user = $stmt->fetch();
        if ($user) {
            return true;
        }
        return false;

    } catch (PDOException $e) {
        error_log("Database query failed in db_checkName: " . $e->getMessage());
        return 'error';
    }
}

function db_insertData($pdo, $name, $password)
{
    //Check if the name of password is empty or not
    if (trim($name) === '' || trim($password) === '') {
        return 'invalid';
    }
    try {
        // ===== Step 1 : Auto-incrementing User ID =====
        $stmtCheckId = $pdo->query("SELECT ID_P FROM PELANGGAN ORDER BY ID_P DESC LIMIT 1");
        $lastRow = $stmtCheckId->fetch();

        if ($lastRow) {
            $lastId = $lastRow['ID_P'];
            $numberId = substr($lastId, 1);
            $nextNumber = (int) $numberId + 1;
            $newId = 'P' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        } else {
            $newId = 'P001';
        }
        // ===== Step 2 : Hash Encryption =====
        // == Step 2(a) : Check the strength of the password ==
        // Pattern analyze
        //(?=.*[a-z]) = Must contain a-z
        //(?=.*[A-Z]) = Must contain A-Z
        //(?=.*[0-9]) = Must contain 0-9
        //{8, } = Must be at least 8 characters long
        $passwordPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}$/';
        if (!preg_match($passwordPattern, $password)) {
            return 'weakpass';
        }
        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
        // ===== Step 3 : Insert data into the database =====
        $sql = "INSERT INTO PELANGGAN (ID_P, Nama_P, Pswd_P) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([$newId, $name, $hashed_pass]);
        return $success;
    } catch (PDOException $e) {
        error_log("Fail insert date into database at db_insertData: " . $e->getMessage());
        return false;
    }
}
?>