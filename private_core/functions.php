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
        $tablename = 'pelanggan';
        $idtype = 'ID_P';
        $numColumn = 'No_Tel_P';
    } elseif ($prefix === 'J' || $prefix === 'A') {
        $tablename = 'staf';
        $idtype = 'ID_S';
        $numColumn = 'No_Tel_S';
    } else {
        return false; // prevent invalid input
    }

    //Get info from table 
    try {
        $sql = "SELECT * FROM `{$tablename}` WHERE `{$idtype}` = ? AND IFNULL(`{$numColumn}`, '') NOT LIKE '[X]%'";
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
    if ($name === null || trim($name) === '') {
        return false;
    }

    try {
        //Check if the name exists in PELANGGAN and STAF table
        $sql = "SELECT 
                    `ID_P`, `Nama_P`, `Pswd_P`, `No_Tel_P`, `Alamat`, 
                    NULL AS `ID_S`, NULL AS `Nama_S`, NULL AS `Pswd_S`, NULL AS `No_Tel_S`, NULL AS `Peranan`, NULL AS `Gbr_S`,
                    'pelanggan' AS `peranan`
                FROM `pelanggan` 
                WHERE `Nama_P` = ? AND IFNULL(`No_Tel_P`, '') NOT LIKE '[X]%'
                UNION ALL
                SELECT 
                    NULL AS `ID_P`, NULL AS `Nama_P`, NULL AS `Pswd_P`, NULL AS `No_Tel_P`, NULL AS `Alamat`, 
                    `ID_S`, `Nama_S`, `Pswd_S`, `No_Tel_S`, `Peranan`, `Gbr_S`,
                    `Peranan` AS `peranan`
                FROM `staf` 
                WHERE `Nama_S` = ? AND IFNULL(`No_Tel_S`, '') NOT LIKE '[X]%'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $name]);
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
    if ($is_match) {
        unset($user['Pswd_P']); // Delete password in $user array
        unset($user['Pswd_S']);
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

// Function to check for duplicate names
function db_checkName($pdo, $name)
{
    //Check if the name is empty or not
    if (empty(trim($name))) {
        return 'invalid';
    }
    try {
        //Check if the name exists in PELANGGAN table
        $sql = "SELECT 1 FROM `pelanggan` WHERE `Nama_P` = ?
                UNION ALL
                SELECT 1 FROM `staf` WHERE `Nama_S` = ?
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

//Function to insert new user into database
function db_userRegister($pdo, $name, $password)
{
    //Check if the name of password is empty or not
    if (trim($name) === '' || trim($password) === '') {
        return 'invalid';
    }

    // Check the strength of the password
    // Pattern analyze
    //(?=.*[a-z]) = Must contain a-z
    //(?=.*[A-Z]) = Must contain A-Z
    //(?=.*[0-9]) = Must contain 0-9
    //{8, } = Must be at least 8 characters long
    $passwordPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}$/';
    if (!preg_match($passwordPattern, $password)) {
        return 'weakpass';
    }
    try {
        $pdo->beginTransaction(); //To prevent Id duplicate when more then one people register together

        // ===== Step 1 : Auto-incrementing User ID =====
        $stmtCheckId = $pdo->query("SELECT ID_P FROM `pelanggan` ORDER BY ID_P DESC LIMIT 1 FOR UPDATE");
        $lastRow = $stmtCheckId->fetch();

        if ($lastRow) {
            $lastId = $lastRow['ID_P'];
            $numberId = substr($lastId, 1);
            $nextNumber = (int) $numberId + 1;
            $newId = 'P' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        } else {
            $newId = 'P001';
        }

        // == Step 2 : Hash the password
        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);

        // ===== Step 3 : Insert data into the database =====
        $sql = "INSERT INTO `pelanggan` (`ID_P`, `Nama_P`, `Pswd_P`) VALUES (?,?,?)";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([$newId, $name, $hashed_pass]);
        if ($success) {
            $pdo->commit();
        }
        return $success;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack(); // Undo operation
        }
        error_log("Fail insert data into database at db_insertData: " . $e->getMessage());
        return false;
    }
}

function db_updateData($pdo, $userId, $dataType, $dataChange)
{
    $trimUserId = trim($userId);
    $trimDataChange = trim($dataChange);
    $trimDbColumn = trim($dataType);

    if ($trimUserId === '' || $trimDbColumn === '' || $trimDataChange === '') {
        return 'invalid';
    }

    if (!preg_match('/^[PJA]\d{3}$/i', $trimUserId)) {
        return 'invalid';
    }

    $prefix = strtoupper(substr($trimUserId, 0, 1));
    //Match the prefix with corresponding table
    if ($prefix === 'P') {
        $tablename = 'PELANGGAN';
        $idtype = 'ID_P';
        $allowedColumns = ['Nama_P', 'Pswd_P', 'No_Tel_P', 'Alamat'];
    } elseif ($prefix === 'J' || $prefix === 'A') {
        $tablename = 'STAF';
        $idtype = 'ID_S';
        $allowedColumns = ['Nama_S', 'Pswd_S', 'No_Tel_S', 'Gbr_S'];
    } else {
        return false; //Prevent invalid input
    }

    // Prevent changing something not allowed e.g User ID, Role 
    if (!in_array($trimDbColumn, $allowedColumns, true))
        return 'invalid';

    // If the user data wanted to change is password
    if ($trimDbColumn === 'Pswd_P' || $trimDbColumn === 'Pswd_S') {
        $trimDataChange = password_hash($trimDataChange, PASSWORD_DEFAULT);
    }

    try {
        $sql = "UPDATE `$tablename` SET `$trimDbColumn` = ? WHERE `$idtype` = ?";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([$trimDataChange, $trimUserId]);
        return $success;

    } catch (PDOException $e) {
        error_log("Database query failed in db_updateData: " . $e->getMessage());
        return 'error';
    }
}

function db_getAllServices($pdo)
{
    try {
        // Get all services in database
        $sql = "SELECT * FROM `perkhidmatan` ORDER BY `ID_PK` ASC";
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll();

        // Define how should the services list
        $tree = [
            'hardware' => [],
            'software' => []
        ];

        // Save the services in array using foreach
        foreach ($rows as $row) {
            $idService = $row['ID_PK'];
            $servicesFullName = $row['Nama_PK'];

            // Seperate services to major category, subcategory, activity/status
            $part = explode('.', $servicesFullName);

            // To prevent unexpected situation and skip that row
            if (count($part) < 3) {
                continue;
            }

            // Define each part by each category 
            $mainCat = trim($part[0]);
            $subCat = trim($part[1]);
            $activity = trim($part[2]);

            // Put data in to hardware[]
            if ($mainCat === 'HW') {
                if (!isset($tree['hardware'][$subCat])) {
                    $tree['hardware'][$subCat] = [];
                }
                $tree['hardware'][$subCat][] = [
                    'idService' => $idService,
                    'serviceActivity' => $activity
                ];
                // Put data in to software[]
            } elseif ($mainCat === 'SW') {
                if (!isset($tree['software'][$subCat])) {
                    $tree['software'][$subCat] = [];
                }
                $tree['software'][$subCat][] = [
                    'idService' => $idService,
                    'serviceActivity' => $activity
                ];
            }
            // Final will become (use SW.SYSTEM.BOOTLOOP and SW.BROWSER.CRASH as e.g.)
            // $tree = [
            //     'hardware' => [...],
            //     'software' => [
            //         'SYSTEM' => [['idService' => 'PK031', 'serviceActivity' => 'BOOTLOOP'],
            //                      ['idService' => 'PK032', 'serviceActivity' => 'UPGRADE'],
            //                      ...
            //                     ],
            //         'BROWSER' => [['idService' => 'PK038', 'serviceActivity' => 'CRASH'], 
            //                      [...]
            //                     ],
            //         ...
            //     ]
            // ];
        }
        return $tree;
    } catch (PDOException $e) {
        error_log("Database query failed in db_getAllServices: " . $e->getMessage());
        return ['hardware' => [], 'software' => []];
    }
}

// Function to insert/add appointment in to database
function db_addAppointment($pdo, $userId, $staffId, $idService, $brandName, $modalName, $timeDate)
{
    try {
        $pdo->beginTransaction();
        $stmtCheckId = $pdo->query("SELECT ID_JT FROM `janjitemu` ORDER BY ID_JT DESC LIMIT 1 FOR UPDATE");
        $lastRow = $stmtCheckId->fetch();

        if ($lastRow) {
            $lastId = $lastRow['ID_JT'];
            $numberId = substr($lastId, 2);
            $nextNumber = (int) $numberId + 1;
            $newId = 'JT' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        } else {
            $newId = 'JT001';
        }

        $sql = 'INSERT INTO `janjitemu` (`ID_JT`, `Tarikh_Masa`, `Jenama`, `Model`, `Status`, `ID_P`, `ID_S`, `ID_PK`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([$newId, $timeDate, $brandName, $modalName, 'SCHEDULED.WAITING', $userId, $staffId, $idService]);
        if ($success) {
            $pdo->commit();
        }
        return $success;

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack(); // Undo operation
        }
        error_log("Database query failed in db_addAppointment: " . $e->getMessage());
        return 'error';
    }
}

// Function to get available technicans
function db_getAvailableTechnicians($pdo, $timeDate)
{
    if (empty($timeDate)) {
        return [];
    }
    try {
        $sql = "SELECT s.ID_S, s.Nama_S, s.No_Tel_S, s.Gbr_S 
                FROM staf s 
                WHERE s.Peranan = 'juruteknik' 
                AND IFNULL(s.No_Tel_S, '') NOT LIKE '[X]%'
                AND NOT EXISTS (
                    SELECT 1 FROM janjitemu jt 
                    WHERE jt.ID_S = s.ID_S AND jt.Tarikh_Masa = ?
                )";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$timeDate]);
        $technicans = $stmt->fetchAll();
        return $technicans;
    } catch (PDOException $e) {
        error_log("Database query failed in db_getAvailableTechnicans: " . $e->getMessage());
        return [];
    }
}

// Function to get customers' history appointments
function db_getHistoryAppointment($pdo, $userId)
{
    if ($userId === '' || empty($userId)) {
        return [];
    }
    $trimUserId = trim($userId);
    try {
        $sql = 'SELECT jt.*, s.Nama_S, pk.Nama_PK 
            FROM janjitemu jt
            INNER JOIN staf s ON jt.ID_S = s.ID_S 
            INNER JOIN perkhidmatan pk ON jt.ID_PK = pk.ID_PK
            WHERE ID_P = ?
            ORDER BY Tarikh_Masa ASC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$trimUserId]);
        $history = $stmt->fetchAll();
        if ($history !== '') {
            return $history;
        }
        return [];
    } catch (PDOException $e) {
        error_log("Database query failed in db_getHistoryAppointment: " . $e->getMessage());
        return [];
    }
}

// Function to get all appointments
function db_getAllAppointment($pdo)
{
    try {
        $sql = 'SELECT jt.*, s.Nama_S, pk.Nama_PK , p.Nama_P
            FROM janjitemu jt
            INNER JOIN pelanggan p ON jt.ID_P = p.ID_P
            INNER JOIN staf s ON jt.ID_S = s.ID_S 
            INNER JOIN perkhidmatan pk ON jt.ID_PK = pk.ID_PK
            ORDER BY Tarikh_Masa ASC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        if ($rows !== '') {
            return $rows;
        }
        return [];
    } catch (PDOException $e) {
        error_log("Database query failed in db_getHistoryAppointment: " . $e->getMessage());
        return [];
    }
}

// Function to get assigned appointments
function db_getSelfAppointment($pdo, $userId)
{
    if ($userId === '' || empty($userId)) {
        return [];
    }
    $trimUserId = trim($userId);
    try {
        $sql = 'SELECT jt.*, p.Nama_P, pk.Nama_PK 
            FROM janjitemu jt
            INNER JOIN pelanggan p ON jt.ID_P = p.ID_P
            INNER JOIN perkhidmatan pk ON jt.ID_PK = pk.ID_PK
            WHERE ID_S = ?
            ORDER BY Tarikh_Masa ASC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$trimUserId]);
        $history = $stmt->fetchAll();
        if ($history !== '') {
            return $history;
        }
        return [];
    } catch (PDOException $e) {
        error_log("Database query failed in db_getSelfAppointment: " . $e->getMessage());
        return [];
    }
}

// Function to update service
function db_updateService($pdo, $data)
{
    $trimData = trim($data);

    try {
        $pdo->beginTransaction();
        $stmtCheckId = $pdo->query("SELECT ID_PK FROM `perkhidmatan` ORDER BY ID_PK DESC LIMIT 1 FOR UPDATE");
        $lastRow = $stmtCheckId->fetch();

        if ($lastRow) {
            $lastId = $lastRow['ID_PK'];
            $numberId = substr($lastId, 2);
            $nextNumber = (int) $numberId + 1;
            $newId = 'PK' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        } else {
            $newId = 'PK001';
        }

        $sql = 'INSERT INTO `perkhidmatan` (`ID_PK` , `Nama_PK`) VALUES (?, ?)';
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([$newId, $trimData]);
        if ($success) {
            $pdo->commit();
        }
        return $success;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack(); // Undo operation
        }
        error_log("Database query failed in db_updateService: " . $e->getMessage());
        return 'error';
    }
}

// Function to get all user
function db_getAllUser($pdo)
{
    try {
        // Customers'
        $sqlCustomer = "SELECT `ID_P`, `Nama_P`, `No_Tel_P`, `Alamat` FROM `pelanggan` 
                        ORDER BY 
                            CASE
                                WHEN `No_Tel_P` LIKE '[X]%' THEN 1
                                ELSE 0
                                END 
                                ASC,
                                `ID_P` ASC";
        $stmtCustomer = $pdo->query($sqlCustomer);
        $customers = $stmtCustomer->fetchAll();

        // Staffs'
        $sqlStaff = "SELECT `ID_S`, `Nama_S`, `No_Tel_S`, `Peranan`, `Gbr_S` FROM `staf`  
                    ORDER BY  
                        CASE
                            WHEN `No_Tel_S` LIKE '[X]%' THEN 1
                            ELSE 0
                            END 
                            ASC,
                            `ID_S` ASC";
        $stmtStaff = $pdo->query($sqlStaff);
        $staffs = $stmtStaff->fetchAll();


        $users = [
            'customer' => $customers,
            'staff' => $staffs
        ];

        return $users;
    } catch (PDOException $e) {
        error_log("Database query failed in db_getAllUser: " . $e->getMessage());
        return 'error';
    }
}

// Function admin to add new user
function db_addUser($pdo, $uName, $uPass, $uNum, $info, $uType)
{
    //Check if the name of password is empty or not
    if (trim($uName) === '' || trim($uPass) === '') {
        return 'invalid';
    }

    if (db_checkName($pdo, $uName) !== false) {
        return 'name_used';
    }
    // Check the strength of the password
    $passwordPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{8,}$/';
    if (!preg_match($passwordPattern, $uPass)) {
        return 'weak_pass';
    }
    if ($uType === 'customer') {
        $tablename = 'pelanggan';
        $idtype = 'ID_P';
        $prefix = 'P';
    } elseif ($uType === 'staff') {
        $tablename = 'staf';
        $idtype = 'ID_S';
        $prefix = (strtolower(trim($info)) === 'admin') ? 'A' : 'J';
    } else {
        return false; //Prevent invalid input
    }

    try {
        $pdo->beginTransaction(); //To prevent Id duplicate when more then one people register together

        // ===== Step 1 : Auto-incrementing User ID =====
        $stmtCheckId = $pdo->prepare("SELECT `{$idtype}` FROM `{$tablename}` WHERE `{$idtype}` LIKE ? ORDER BY `{$idtype}` DESC LIMIT 1 FOR UPDATE");
        $stmtCheckId->execute([$prefix . '%']);
        $lastRow = $stmtCheckId->fetch();

        if ($lastRow) {
            $lastId = $lastRow[$idtype];
            $numberId = substr($lastId, 1);
            $nextNumber = (int) $numberId + 1;
            $newId = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        } else {
            $newId = $prefix . '001';
        }

        // == Step 2 : Hash the password
        $hashed_pass = password_hash($uPass, PASSWORD_DEFAULT);

        // ===== Step 3 : Insert data into the database =====
        if ($tablename === 'staf') {
            $sql = "INSERT INTO `staf` (`ID_S`, `Nama_S`, `Pswd_S`, `No_Tel_S`, `Peranan`) VALUES (?,?,?,?,?)";
        } else {
            $sql = "INSERT INTO `pelanggan` (`ID_P`, `Nama_P`, `Pswd_P`, `No_Tel_P`, `Alamat`) VALUES (?,?,?,?,?)";
        }
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([$newId, $uName, $hashed_pass, $uNum, $info]);
        if ($success) {
            $pdo->commit();
        }
        return $success;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack(); // Undo operation
        }
        error_log("Fail insert data into database at db_addUser: " . $e->getMessage());
        return false;
    }
}

// Function to change user status
function db_changeUserStatus($pdo, $userId, $action)
{
    $userId = isset($userId) ? trim($userId) : '';
    $action = isset($action) ? trim($action) : '';
    $prefix = strtoupper(substr($userId, 0, 1));

    if ($prefix === 'P') {
        $tablename = 'pelanggan';
        $idtype = 'ID_P';
        $numColumn = 'No_Tel_P';
    } elseif ($prefix === 'J' || $prefix === 'A') {
        $tablename = 'staf';
        $idtype = 'ID_S';
        $numColumn = 'No_Tel_S';
    } else {
        return false; //Prevent invalid input
    }
    if (!in_array($action, ['restore', 'delete'], true)) {
        return false;
    }
    try {
        if ($action === 'delete') {
            $sql = "UPDATE `{$tablename}` SET `{$numColumn}` = CONCAT('[X]', IFNULL(`{$numColumn}`, '')) WHERE `{$idtype}` = ? AND IFNULL(`{$numColumn}`, '') NOT LIKE '[X]%'";
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute([$userId]);
        } elseif ($action === 'restore') {
            $sql = "UPDATE `{$tablename}` SET `{$numColumn}` = REPLACE(`{$numColumn}`,'[X]', '') WHERE `{$idtype}` = ? AND 
            `{$numColumn}` LIKE '[X]%'";
            $stmt = $pdo->prepare($sql);
            $success = $stmt->execute([$userId]);
        } else {
            return false;
        }
        return $success;
    } catch (PDOException $e) {
        error_log("Fail insert data into database at db_changeUserStatus " . $e->getMessage());
        return false;
    }
}

?>