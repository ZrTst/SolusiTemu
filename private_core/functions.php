<?php 
//private_core/functions.php
//$sql= SQL query statement
//$stmt= statement



//function get user info by id
function db_getUserById($pdo, $id){
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
    } elseif ($prefix === 'J' || $prefix === 'A' ) {
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
        return false;     }
}

// Function to verify password
function db_verifyPassword($user, $password) {
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
}
?>