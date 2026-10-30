<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (($_SESSION['logged_in'] ?? false) == true) {
    header("Location: dashboard.php");
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
$signup_username = isset($_POST['name-signup']) ? trim($_POST['name-signup']) : '';
$signup_status = $_SESSION['status'] ?? null;
// Flash session (Undifine $_SESSION['status'] to prevent status duplicate)
unset($_SESSION['status']);
// Only error occurs will display sign-up container
$show_signup = in_array($signup_status, ['NameUsed', 'InvalidUsername', 'DatabaseError', 'InvalidInput', 'WeakPassword'], true);

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $signup_action = isset($_POST['signup']) ? trim($_POST['signup']) : 'inactive';
    if ($signup_action !== 'active') {
        //Get the login method from POST (name = "login_method")
        $login_method = isset($_POST['login_method']) ? trim($_POST['login_method']) : 'id';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $user = false;

        if ($login_method === 'id') {
            // ======= Method Login : ID =======
            $id_prefix = isset($_POST['id_prefix']) ? trim($_POST['id_prefix']) : 'P';
            $userid_1 = isset($_POST['userid_1']) ? trim($_POST['userid_1']) : '';
            $userid_2 = isset($_POST['userid_2']) ? trim($_POST['userid_2']) : '';
            $userid_3 = isset($_POST['userid_3']) ? trim($_POST['userid_3']) : '';

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
                $user_id = $user['ID_P'] ?? $user['ID_S'] ?? '';
                $prefix = strtoupper($user_id[0]);

                // Session management
                $_SESSION['user_id'] = $user_id;
                $_SESSION['logged_in'] = true;
                $_SESSION['role'] = ($prefix === 'P') ? 'pelanggan' : (($prefix === 'A') ? 'admin' : 'juruteknik');
                $_SESSION['name'] = $user['Nama_P'] ?? $user['Nama_S'] ?? '';
                $_SESSION['user_data'] = $user;

                // Page after login successfully
                header("Location: dashboard.php");
                exit;
            } else {
                $error = ($login_method === 'id') ? "ID Pengguna atau Kata Laluan tidak sah." : "Nama Pengguna atau Kata Laluan tidak sah.";
            }
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
    <link rel="stylesheet" href="../public/css/login.css">
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
            <div class="error-box">
                <!--Prevent XSS attacks-->
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- SERVER(back to login.php) will get login_method, username_input, id_prefix, userid_1, userid_2, userid_3, password, signup -->
        <form method="POST" action="login.php">
            <input type="hidden" id="login-method" name="login_method" value="<?= htmlspecialchars($login_method) ?>">
            <!--Switch Login method button-->
            <div class="login-tabs" style="display: flex; gap: 15px; margin-bottom: 10px;">
                <!--ID method-->
                <button type="button" id="btn-id-login" onclick="switchLoginMethod('id')"
                    style="background: none; border: none; color: #d4af37; cursor: pointer; border-bottom: 2px solid #d4af37; padding-bottom: 5px;">Guna
                    ID</button>
                <!--Name method-->
                <button type="button" id="btn-name-login" onclick="switchLoginMethod('name')"
                    style="background: none; border: none; color: #888; cursor: pointer; padding-bottom: 5px;">Guna
                    Nama</button>
            </div>
            <!--For Name method login use-->
            <div id="name-input-group" style="display: none;">
                <label class="label">Nama Pengguna</label>
                <input type="text" id="username-input-field" name="username_input"
                    placeholder="Masukkan Nama Pengguna Anda" class="global-input" autocomplete="off"
                    value="<?= htmlspecialchars($username_input) ?>">
            </div>
            <!--For ID method login use-->
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
            <h4 style=" text-align: center;">Tiada akaun?
                <button class="signup-btn" id="signup" name="signup" value="inactive" type="button">
                    Daftar di sini</button>
            </h4>
        </form>
    </div>

    <div class="signup-overlay" id="signup-form" style="display: <?= $show_signup ? 'flex' : 'none' ?>;">
        <div class="signup-container">
            <div class="form-header font-oswald" style="font-size: 40px;">
                <span>Daftar Akaun Baharu</span>
                <svg class="icons" id="minimizeicon">
                    <use href="../public/icons.svg#minimize-icon"></use>
                </svg>
            </div>
            <!-- SERVER(go to api/signup.php) will get status, signup, name-signup, password-signup -->
            <form method="POST" action="api/signup.php">
                <input type="hidden" name="status" value="registering">
                <input type="hidden" name="signup" value="active">
                <!-- Name container -->
                <label class="label">Nama</label>
                <input class="global-input" type="text" id="name-signup" name="name-signup"
                    placeholder="Masukkan nama penuh anda" autocomplete="off" required>
                <?php if ($signup_status === 'NameUsed'): ?>
                    <div class="error-box">Nama telah digunakan.</div>
                <?php elseif ($signup_status === 'InvalidUsername' || $signup_status === 'InvalidInput'): ?>
                    <div class="error-box">Sila masukkan nama dan kata laluan yang sah.</div>
                <?php elseif ($signup_status === 'DatabaseError'): ?>
                    <div class="error-box">Ralat pangkalan data. Sila cuba sebentar lagi.</div>
                <?php elseif ($signup_status === 'RegisterSuccess'): ?>

                <?php endif; ?>
                <!--Password container-->
                <label class="label">Kata Laluan</label>
                <input class="global-input" type="password" id="password-signup" name="password-signup"
                    placeholder="Minima 8 aksara" required>
                <div id="weak-pass-error" class="error-box" style="display: none;">Kata laluan mesti mengandungi huruf
                    besar, huruf kecil,
                    nombor dan minima 8 aksara.
                </div>
                <label class="label">Sahkan Kata Laluan</label>
                <input class="global-input" id="confirm-pass" type="password" placeholder="Masukkan semula kata laluan"
                    required>
                <div class="error-box" id="pass-error" style="display: none;">
                    Kata laluan tidak sepadan
                </div>
                <button class="submit-btn" type="submit">Daftar</button>
            </form>
        </div>
    </div>

    <!-- JavaScripts -->
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
        const errorBoxes = document.querySelectorAll('.error-box');
        if (errorBoxes) {
            setTimeout(() => {
                errorBoxes.forEach(box => {
                    box.style.transition = "opacity 0.5 ease";
                    box.style.opacity = 0;
                    setTimeout(() =>
                        box.style.display = 'none', 500);
                });
            }, 3000);
        }

        //Switch login method(id or name)
        const idInputGroup = document.getElementById('id-input-group');
        const nameInputGroup = document.getElementById('name-input-group');
        const btnIdLogin = document.getElementById('btn-id-login');
        const btnNameLogin = document.getElementById('btn-name-login');
        const methodInput = document.getElementById('login-method');
        const nameInputField = document.getElementById('username-input-field');
        const idInputs = document.querySelectorAll('#id-input-group input, #id-input-group select');

        function switchLoginMethod(method) {
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

        // Display & Hide the signup container
        const SignupForm = document.getElementById('signup-form');
        const MinimizeIcon = document.getElementById('minimizeicon');
        const SignupBtn = document.getElementById('signup');

        if (SignupBtn && SignupForm) {
            SignupBtn.addEventListener("click", function () {
                SignupForm.style.display = 'flex';
            });
        }
        if (MinimizeIcon && SignupForm) {
            MinimizeIcon.addEventListener('click', function () {
                SignupForm.style.display = 'none';
            });
        }

        //Let all the required form = false
        SignupBtn.addEventListener("click", function () {
            nameInputField.required = false;
            idInputs.forEach(input => input.required = false);
            passwordInput.required = false;
            SignupBtn.value = 'active';
            if (SignupForm) {
                SignupForm.style.display = 'flex';
            }
        });

        //Function to define password strength
        function isPasswordStrong(password) {
            const hasUpperCase = /[A-Z]/.test(password); // Check uppercase letters existance
            const hasLowerCase = /[a-z]/.test(password); // Check lowercase letters existance
            const hasNumber = /[0-9]/.test(password);    // Check number existence
            const isLongEnough = password.length >= 8;   // Check password length

            return hasUpperCase && hasLowerCase && hasNumber && isLongEnough;
        }

        //Confirm password re-type correctly
        const registerPass = document.getElementById('password-signup');
        const confirmPass = document.getElementById('confirm-pass');
        const passErrorMsg = document.getElementById('pass-error');
        const weakPassErrorMsg = document.getElementById('weak-pass-error');
        const registerForm = document.querySelector('#signup-form form');
        const nameSignup = document.getElementById('name-signup');

        registerForm.addEventListener('submit', (e) => {
            const pswd = registerPass.value;
            const confirmPswd = confirmPass.value;

            if (!isPasswordStrong(pswd)) {
                e.preventDefault(); // Stop form submission
                weakPassErrorMsg.style.display = 'block';
                weakPassErrorMsg.style.opacity = 1;
                weakPassErrorMsg.style.transition = 'opacity 0.3s ease';
                setTimeout(() => {
                    weakPassErrorMsg.style.transition = "opacity 0.5s ease";
                    weakPassErrorMsg.style.opacity = 0;
                    setTimeout(() => {
                        weakPassErrorMsg.style.display = 'none';
                    }, 500);
                }, 3000);
                return;
            }
            if (pswd !== confirmPswd) {
                e.preventDefault(); // Stop form submission
                passErrorMsg.style.display = 'block';
                passErrorMsg.style.opacity = 1;
                passErrorMsg.style.transition = 'opacity 0.3s ease';
                setTimeout(() => {
                    passErrorMsg.style.transition = "opacity 0.5s ease";
                    passErrorMsg.style.opacity = 0;
                    setTimeout(() => {
                        passErrorMsg.style.display = 'none';
                    }, 500);
                }, 3000);
                return;
            }
        });

        // Restore active tab after form submission
        <?php if ($login_method === 'name'): ?>
            switchLoginMethod('name');
        <?php endif; ?>
    </script>
</body>

</html>