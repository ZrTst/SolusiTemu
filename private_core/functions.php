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
        $idtype = 'ID_J';
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
        //Check if the name exists in PELANGGAN table
        $sql = "SELECT * FROM `PELANGGAN` WHERE `Nama_P`= ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name]);
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
    if (!$user) {
        return false;
    }
    // Check possible password column names
    $stored_password = $user['Pswd_P'] ?? $user['Pswd_S'] ?? null;
    if ($stored_password === null) {
        return false;
    }
    if (password_verify($password, $stored_password)) {
        return true;
    }
    return false;
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

?>