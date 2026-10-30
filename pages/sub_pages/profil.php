<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (
    !isset($_SESSION['logged_in']) ||
    $_SESSION['logged_in'] !== true ||
    !isset($_SESSION['role']) ||
    ($_SESSION['role'] !== 'pelanggan' && $_SESSION['role'] !== 'juruteknik')
) {
    http_response_code(401);
    include($_SERVER['DOCUMENT_ROOT'] . '/SolusiTemu/pages/errors/401.html');
    exit;
}

$user = $_SESSION['user_data'];
$phone = $user['No_Tel_P'] ?? $user['No_Tel_S'] ?? '';
$role = $user['peranan'] ?? '';
$dataToChange = trim($_POST['data_type'] ?? '');
?>

<!-- SERVER will get status(L23), data_type(L28), data_change(L88) by POST method -->
<form class="profile-container" id="profile-container" method="POST" action="api/update_profile.php">
    <input type="hidden" name="status" value="editing-profile">
    <div class="profile-picture-container">
        <div class="profile-picture"></div>
    </div>
    <!-- Hidden field -->
    <input type="hidden" id="data-type" name="data_type" value="">
    <!-- User ID -->
    <div class="info-group">ID pengguna</div>
    <div class="info-group"><?= htmlspecialchars($user['ID_P'] ?? $user['ID_S'] ?? '') ?></div>
    <!-- Username -->
    <div class="info-group">Nama pengguna</div>
    <div class="info-group"><?= htmlspecialchars($user['Nama_P'] ?? $user['Nama_S'] ?? '') ?>
        <svg class="icons" onclick="changeInfo('name')"> <!-- function at here -->
            <use href="../public/icons.svg#edit-icon"></use>
        </svg>
    </div>
    <!-- User password -->
    <div class="info-group">Kata Laluan</div>
    <div class="info-group">***
        <svg class="icons" onclick="changeInfo('password')"> <!-- function at here -->
            <use href="../public/icons.svg#edit-icon"></use>
        </svg>
    </div>
    <!-- User number -->
    <div class="info-group">Nombor Telefon</div>
    <div class="info-group">
        <?php
        if (trim($phone) === '') {
            echo '<span style="color: var(--gold); font-size: 18px;">Tambahkan nombor telefon untuk memudahkan urusan menghubungi!</span>';
        } else {
            echo htmlspecialchars($phone);
        }
        ?>
        <svg class="icons" onclick="changeInfo('number')"> <!-- function at here -->
            <use href="../public/icons.svg#edit-icon"></use>
        </svg>
    </div>
    <!-- User information based on their role -->
    <?php if ($role === 'juruteknik') { ?>
        <!-- If user is juruteknik -->
        <div class="info-group">Peranan</div>
        <div class="info-group"><?= htmlspecialchars($role) ?></div>
    <?php } else { ?>
        <!-- If user is customer -->
        <div class="info-group">Alamat</div>
        <div class="info-group"><?php if (trim($user['Alamat']) === '') {
            echo '<span style="color: var(--gold); font-size: 18px;">Belum dipasang alamat</span>';
        } else {
            echo htmlspecialchars($user['Alamat']);
        } ?>
            <svg class="icons" onclick="changeInfo('address')"> <!-- function at here -->
                <use href="../public/icons.svg#edit-icon"></use>
            </svg>
        </div>
    <?php } ?>
    <!-- Update information form -->
    <div class="change-data-overlay" id="change-data-overlay" style="display: none;">
        <div class="change-data-form">
            <div class="form-header font-oswald" style="font-size: 40px;">
                <span>KemasKini Maklumat</span>
                <svg class="icons" id="minimizeicon-profile">
                    <use href="../public/icons.svg#minimize-icon"></use>
                </svg>
            </div>
            <label class="label" id="label-data-type"></label>
            <input type="text" id="dataChangeInput" name="data_change" placeholder="" class="global-input"
                autocomplete="off" required>
            <div id="weak-pass-error" class="error-box" style="display: none;">Kata laluan mesti mengandungi huruf
                besar, huruf kecil,
                nombor dan minima 8 aksara.
            </div>
            <!-- Retype password -->
            <label class="label" id="retype-pass-label" style="display: none;">Sahkan Kata Laluan</label>
            <input class="global-input" id="retype-pass-input" type="password" placeholder="Masukkan semula kata laluan"
                style="display: none;" required>
            <div class="error-box" id="pass-error" style="display: none;">
                Kata laluan tidak sepadan
            </div>
            <!-- Button submit form -->
            <button class="submit-btn" type="submit" id="edit-info-btn">Selesai</button>
        </div>
    </div>
</form>

<!-- JavaScripts -->
<script>
    // Data type of user wanted to change 
    const dataTypeToUpdate = document.getElementById('data-type');
    // Visibility of the change-data-form
    const updateDataForm = document.getElementById('change-data-overlay');
    const updateDataFormText = document.getElementById('dataChangeInput');
    const retypePassLabel = document.getElementById('retype-pass-label');
    const retypePassInput = document.getElementById('retype-pass-input');
    const labelDataType = document.getElementById('label-data-type');

    function changeInfo(infoType) {
        updateDataFormText.type = "text";
        updateDataFormText.value = "";
        retypePassInput.value = "";
        retypePassInput.required = false;
        if (dataTypeToUpdate) {
            dataTypeToUpdate.value = infoType;
        }
        switch (infoType) {
            case 'name':
                updateDataFormText.placeholder = "Masukkan nama baharu anda.";
                labelDataType.textContent = "Nama baharu";
                break;
            case 'password':
                labelDataType.textContent = "Kata laluan baharu";
                updateDataFormText.placeholder = "Masukkan kata laluan baharu anda.";
                updateDataFormText.type = "password";
                retypePassLabel.style.display = "flex";
                retypePassInput.style.display = "flex";
                retypePassInput.required = true;
                break;
            case 'number':
                labelDataType.textContent = "Nombor telefon baharu";
                updateDataFormText.placeholder = "Masukkan nombor telefon baharu anda.";
                break;
            case 'address':
                labelDataType.textContent = "Alamat rumah baharu";
                updateDataFormText.placeholder = "Masukkan alamat baharu anda.";
                break;

        }
        updateDataForm.style.display = 'flex';
        updateDataFormText.focus();
    }

    //Function define password strength
    function isPasswordStrong(password) {
        const hasUpperCase = /[A-Z]/.test(password); // Check uppercase letters existance
        const hasLowerCase = /[a-z]/.test(password); // Check lowercase letters existance
        const hasNumber = /[0-9]/.test(password);    // Check number existence
        const isLongEnough = password.length >= 8;   // Check password length

        return hasUpperCase && hasLowerCase && hasNumber && isLongEnough;
    }

    // Verify password consistency
    const editInfoForm = document.querySelector('#profile-container');
    const passErrorMsg = document.getElementById('pass-error');
    const weakPassErrorMsg = document.getElementById('weak-pass-error');

    if (editInfoForm) {
        editInfoForm.addEventListener('submit', (e) => {
            const newPswd = updateDataFormText.value;
            const retypeConfirmPswd = retypePassInput.value;
            if (dataTypeToUpdate && dataTypeToUpdate.value === 'password') {
                if (!isPasswordStrong(newPswd)) {
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
                if (newPswd !== retypeConfirmPswd) {
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
            }
        });
    }

    const MinimizeIcon_profile = document.getElementById('minimizeicon-profile');
    if (MinimizeIcon_profile && updateDataForm) {
        MinimizeIcon_profile.addEventListener('click', function () {
            updateDataForm.style.display = 'none';
            retypePassLabel.style.display = "none";
            retypePassInput.style.display = "none";
        });
    }

</script>