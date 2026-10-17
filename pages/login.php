<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
require_once __DIR__ . '/../private_core/config.php';
require_once __DIR__ . '/../private_core/functions.php';

$error = '';
$login_method = 'id';
$id_prefix = 'P';
$userid_1 = '';
$userid_2 = '';
$userid_3 = '';
$username_input = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $login_method   = isset($_POST['login_method']) ? trim($_POST['login_method']) : 'id';
    $password       = isset($_POST['password']) ? trim($_POST['password']) : '';
    $user           = false;

    if ($login_method === 'id') {
        // ======= Method Login : ID =======
        $id_prefix = isset($_POST['id_prefix']) ? trim($_POST['id_prefix']) : 'P';
        $userid_1  = isset($_POST['userid_1']) ? trim($_POST['userid_1']) : '';
        $userid_2  = isset($_POST['userid_2']) ? trim($_POST['userid_2']) : '';
        $userid_3  = isset($_POST['userid_3']) ? trim($_POST['userid_3']) : '';

        if ($userid_1 === '' || $userid_2 === '' || $userid_3 === '' || $password === '') {
            $error = "Sila lengkapkan ID Pengguna dan Kata Laluan.";
        } else {
            $user_id = strtoupper($id_prefix . $userid_1 . $userid_2 . $userid_3);
            $user = db_getUserById($pdo, $user_id);
        }
    } else {
        // ======= Method Login : Username =======
        $username_input = isset($_POST['username_input']) ? trim($_POST['username_input']) : '';

        if ($username_input === '' || $password === '') {
            $error = "Sila lengkapkan Nama Pengguna dan Kata Laluan.";
        } else {
            $user = db_getUserByName($pdo, $username_input);
        }
    }

    if (empty($error)) {
        if ($user && db_verifyPassword($user, $password)) {
            //Get the user ID
            $user_id = $user['ID_P'] ?? $user['ID_J'] ?? '';
            $prefix = strtoupper(substr($user_id, 0, 1));

            // Session management
            $_SESSION['user_id'] = $user_id;
            $_SESSION['logged_in'] = true;
            $_SESSION['role'] = ($prefix === 'P') ? 'pelanggan' : (($prefix === 'A') ? 'admin' : 'juruteknik');
            $_SESSION['nama'] = $user['Nama_P'] ?? $user['Nama_S'] ?? $user['Nama_Penuh'] ?? $user['nama'] ?? '';
            $_SESSION['user_data'] = $user;

            // Page after login successfully
            header("Location: janjitemu.php");
            exit;
        } else {
            $error = ($login_method === 'id') 
                ? "ID Pengguna atau Kata Laluan tidak sah." 
                : "Nama Pengguna atau Kata Laluan tidak sah.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Log Masuk</title>
    <link rel="stylesheet" href="../public/global.css">
</head>

<body>
    <!--Header(Logo)-->
    <div class="logo-container">
        <div class="logo-image"></div>
        <span class="logo-text">Syarikat SolusiTemu</span>
        <a href="index.php" class="a1" style="position: absolute; right: 0%;">
            ← Balik ke halaman pertama
        </a>
    </div>
    <!--Left Decorative Cards Layer -->
    <div class="grid-overlay">
        <div class="card-layer layer-1"></div>
        <div class="card-layer layer-2"></div>
        <div class="card-layer layer-3">
            <h1 class="label" style="font-size:30px; margin-left: 10%; text-align: left;">
                ID Pengguna
            </h1>
            <h2 class="label" style="font-size: 20px; margin-left: 10%; text-align: left;margin-top: -10px;">
                P001
            </h2>
            <h1 class="label" style="font-size: 30px; margin-left: 10%; text-align: left;margin-top: -10px;">
                Nama Pengguna
            </h1>
            <h2 class="label" style="font-size: 20px; margin-left: 10%; text-align: left;margin-top: -10px;">
                Ali
            </h2>
            <h1 class="label" style="font-size: 30px; margin-left: 10%; text-align: left;margin-top: -10px;">
                Password Pengguna
            </h1>
            <h2 class="label" style="font-size: 20px; margin-left: 10%; text-align: left;margin-top: -10px;">
                ********
            </h2>
        </div>
    </div>
    <!--Footer slogan-->
    <div class="login-page-footer">
        <p>
            Janji Temu pantas. Perkhidmatan pantas.
        </p>
        <p style="color: #d9a441; margin-top: -4.167vh;">
            bila-bila masa, di mana-mana sahaja.
        </p>
    </div>
    <!--Login Form-->
    <div class="verification-container">
        <h1 class="font-oswald">Selamat kembali,</h1>
        <h2 class="font-oswald" style="margin-top: -20px;">Pengguna yang dihormati</h2>

        <?php if (!empty($error)): ?>
            <div id="error-box" style="
                background-color: rgba(220, 53, 69, 0.15); 
                border: 1px solid rgba(220, 53, 69, 0.4); 
                color: #ff7675; padding: 10px 14px; 
                border-radius: 10px; 
                margin-bottom: 15px; 
                font-size: 14px; 
                text-align: center; 
                font-family: sans-serif;">
                <!--Prevent XSS attacks-->
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input type="hidden" id="login-method" name="login_method" value="<?= htmlspecialchars($login_method) ?>">
            <!--Switch Login method button-->
            <div class="login-tabs" style="display: flex; gap: 15px; margin-bottom: 10px;">
                <!--ID method-->
                <button type="button" id="btn-id-login" class="active-tab" onclick="switchLoginMethod('id')"
                    style="background: none; border: none; color: #d4af37; cursor: pointer; border-bottom: 2px solid #d4af37; padding-bottom: 5px;">Guna
                    ID</button>
                <!--Name method-->
                <button type="button" id="btn-name-login" onclick="switchLoginMethod('name')"
                    style="background: none; border: none; color: #888; cursor: pointer; padding-bottom: 5px;">Guna
                    Nama</button>
            </div>
            <div id="name-input-group" style="display: none;">
                <label class="label">Nama Pengguna</label>
                <input type="text" id="username-input-field" name="username_input"
                    placeholder="Masukkan Nama Pengguna Anda" class="global-input"
                    value="<?= htmlspecialchars($username_input) ?>">
            </div>

            <div id="id-input-group" style="display: block;">
                <label class="label">ID Pengguna</label>
                <div class="flex-row-box">
                    <select name="id_prefix" class="login-select" required>
                        <option value="P" <?= ($id_prefix === 'P') ? 'selected' : '' ?>>P</option>
                        <option value="J" <?= ($id_prefix === 'J') ? 'selected' : '' ?>>J</option>
                        <option value="A" <?= ($id_prefix === 'A') ? 'selected' : '' ?>>A</option>
                    </select>
                    <!--User ID: Role prefix(pelanggan, juruteknik, admin)-->
                    <div class="digit-container">
                        <input type="text" name="userid_1" class="digit-input" maxlength="1" placeholder="0"
                            pattern="[0-9]" inputmode="numeric" required autocomplete="off"
                            value="<?= htmlspecialchars($userid_1) ?>">
                        <input type="text" name="userid_2" class="digit-input" maxlength="1" placeholder="0"
                            pattern="[0-9]" inputmode="numeric" required autocomplete="off"
                            value="<?= htmlspecialchars($userid_2) ?>">
                        <input type="text" name="userid_3" class="digit-input" maxlength="1" placeholder="1"
                            pattern="[0-9]" inputmode="numeric" required autocomplete="off"
                            value="<?= htmlspecialchars($userid_3) ?>">
                    </div>
                </div>
            </div>
            <!--Password container-->
            <div class="password-wrapper">
                <label class="label">Kata Laluan</label>
                <input class="global-input" type="password" id="password" name="password" placeholder="123456" required>
                <!--Password visibility toggle-->
                <svg class="icons">
                    <use href="../public/icons.svg#icon-eye-close"></use>
                </svg>
            </div>
            <a href="forgotpasswd.php" class="a1">Lupa kata laluan?</a>
            <button class="submit-btn" type="submit">Log Masuk</button>
            <button class="guest-btn" type="button" onclick="location.href='guest.php'">Tetamu →</button>
        </form>
        <h4 style=" text-align: center;">Tiada akaun?
            <a href="signup.php" class="a2">Daftar di sini</a>
        </h4>
    </div>
    <script>
        //Turn to next flex-row-box when entered a digit in the current flex-row-box
        document.querySelectorAll('.digit-input').forEach((input, index, inputs) => {
            input.addEventListener('input', (e) => {
                if (input.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && input.value.length === 0 && index > 0) {
                    inputs[index - 1].focus();
                }
            });
        });

        //Change the password input become visible(computer version)
        const passwordInput = document.getElementById('password');
        const eyeIconSvg = document.querySelector('.password-wrapper .icons');
        const eyeIconUse = eyeIconSvg.querySelector('use');

        eyeIconSvg.addEventListener('mousedown', function () {
            passwordInput.type = 'text';
            eyeIconUse.setAttribute('href', '../public/icons.svg#icon-eye-open');
        });

        eyeIconSvg.addEventListener('mouseup', function () {
            passwordInput.type = 'password';
            eyeIconUse.setAttribute('href', '../public/icons.svg#icon-eye-close');
        });

        eyeIconSvg.addEventListener('mouseleave', function () {
            passwordInput.type = 'password';
            eyeIconUse.setAttribute('href', '../public/icons.svg#icon-eye-close');
        });

        //Change the password input become visible(mobile version)
        eyeIconSvg.addEventListener('touchstart', function (e) {
            //Prevent default mobile behaviors (long-press text selection/magnifying glass)
            e.preventDefault();
            passwordInput.type = 'text';
            eyeIconUse.setAttribute('href', '../public/icons.svg#icon-eye-open');
        });

        eyeIconSvg.addEventListener('touchend', function () {
            passwordInput.type = 'password';
            eyeIconUse.setAttribute('href', '../public/icons.svg#icon-eye-close');
        });

        //Hide the error message after 3 senconds
        const errorBox = document.getElementById('error-box');
        if (errorBox) {
            setTimeout(() => {
                errorBox.style.transition = "opacity 0.5s ease";
                errorBox.style.opacity = 0;
                setTimeout(() => errorBox.style.display = 'none', 500);
            }, 3000);
        }

        //Switch login method(id or name)
        function switchLoginMethod(method) {
            const idInputGroup = document.getElementById('id-input-group');
            const nameInputGroup = document.getElementById('name-input-group');
            const btnIdLogin = document.getElementById('btn-id-login');
            const btnNameLogin = document.getElementById('btn-name-login');
            const methodInput = document.getElementById('login-method');
            const nameInputField = document.getElementById('username-input-field');
            const idInputs = document.querySelectorAll('#id-input-group input, #id-input-group select');

            if (!idInputGroup || !nameInputGroup) 
                return;
            if (methodInput) {
                methodInput.value = method;
            }
            if (method === 'id') {
                idInputGroup.style.display = 'block';
                nameInputGroup.style.display = 'none';
                if (nameInputField) nameInputField.required = false;
                idInputs.forEach(input => input.required = true);
                btnIdLogin.style.color = '#d4af37';
                btnIdLogin.style.borderBottom = '2px solid #d4af37';
                btnIdLogin.style.paddingBottom = '5px';
                btnNameLogin.style.color = '#888';
                btnNameLogin.style.borderBottom = 'none';
                btnNameLogin.style.paddingBottom = '5px';
            } else {
                idInputGroup.style.display = 'none';
                nameInputGroup.style.display = 'block';
                if (nameInputField) nameInputField.required = true;
                idInputs.forEach(input => input.required = false);
                btnNameLogin.style.color = '#d4af37';
                btnNameLogin.style.borderBottom = '2px solid #d4af37';
                btnNameLogin.style.paddingBottom = '5px';
                btnIdLogin.style.color = '#888';
                btnIdLogin.style.borderBottom = 'none';
                btnIdLogin.style.paddingBottom = '5px';
            }
        }

        // Restore active tab after form submission
        <?php if ($login_method === 'name'): ?>
        switchLoginMethod('name');
        <?php endif; ?>
    </script>
</body>

</html>