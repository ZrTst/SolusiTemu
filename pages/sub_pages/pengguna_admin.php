<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (
    !isset($_SESSION['logged_in']) ||
    $_SESSION['logged_in'] !== true ||
    !isset($_SESSION['role']) ||
    ($_SESSION['role'] !== 'admin')
) {
    http_response_code(401);
    include($_SERVER['DOCUMENT_ROOT'] . '/SolusiTemu/pages/error/401.html');
    exit;
}

$adminId = isset($_SESSION['user_id']) ? trim($_SESSION['user_id']) : '';
$user_list = db_getAllUser($pdo);
$customer_list = $user_list['customer'] ?? [];
$staff_list = $user_list['staff'] ?? [];

// Function to generate random strong password
function generatePass(int $length): string
{
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#$%^&*';
    $maxIndex = strlen($chars) - 1;
    $password = '';
    do {
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $maxIndex)];
        }
    } while (
        $length >= 4 && (
            !preg_match('/[A-Z]/', $password) ||
            !preg_match('/[a-z]/', $password) ||
            !preg_match('/[0-9]/', $password) ||
            !preg_match('/[!@#$%^&*]/', $password)
        )
    );
    return $password;
}
$randomPass = generatePass(8);
$all_user_list = array_merge($customer_list, $staff_list);
?>

<link rel="stylesheet" href="../public/css/user_management.css">
<div class="users-container">
    <!-- Header area -->
    <div class="banner-container">
        <h1 class="font-barlowcondensed" style="margin: 0px;">Senarai pengguna dalam Sistem SolusiTemu</h1>
        <svg class="icons">
            <use href="../public/icons.svg#setting-icon"></use>
        </svg>
    </div>
    <!-- Search area -->
    <div class="search-container">
        <input class="search-bar" type="search" placeholder="Sedang mencari sesiapa...?">
        <svg class="icons">
            <use href="../public/icons.svg#search-icon"></use>
        </svg>
    </div>
    <!-- User container -->
    <div class="user-list-container">
        <!-- Name List -->
        <div class="list-container">
            <!-- Switch customers or staffs -->
            <div class="radio-group font-barlowcondensed">
                <label class="radio-pill">
                    <input type="radio" name="user" value="all" checked>
                    <span>Semua</span>
                </label>
                <label class="radio-pill">
                    <input type="radio" name="user" value="customer">
                    <span>Pelanggan</span>
                </label>
                <label class="radio-pill">
                    <input type="radio" name="user" value="staff">
                    <span>Staf</span>
                </label>
            </div>
            <!-- Customer list -->
            <div class="customer-container">
                <?php foreach ($customer_list as $user):
                    $phone = $user['No_Tel_P'];
                    $bannedClass = (substr($phone, 0, 3) === '[X]') ? 'inactive' : ''; ?>
                    <div class="data-label <?= $bannedClass ?>" data-target="<?= htmlspecialchars($user['ID_P']) ?>"
                        onclick="selectUser(this)">
                        <span>
                            <?php echo htmlspecialchars($user['ID_P']); ?>
                        </span>
                        <span>
                            <?php echo htmlspecialchars($user['Nama_P']); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
                <!-- Add new user container -->
                <div class="data-label" onclick="addUser(this)" style="display: none;" id="add-customer">
                    <span>Tambah pelanggan baharu</span>
                    <svg class="icons" style="width: 16px; height: 16px;">
                        <use href="../public/icons.svg#plus-icon"></use>
                    </svg>
                </div>
            </div>
            <!-- Staff list -->
            <div class="staff-container">
                <?php foreach ($staff_list as $user):
                    $phone = $user['No_Tel_S'];
                    $bannedClass = (substr($phone, 0, 3) === '[X]') ? 'inactive' : ''; ?>
                    <div class="data-label <?= $bannedClass ?>" data-target="<?= htmlspecialchars($user['ID_S']) ?>"
                        onclick="selectUser(this)">
                        <span>
                            <?php echo htmlspecialchars($user['ID_S']); ?>
                        </span>
                        <span>
                            <?php echo htmlspecialchars($user['Nama_S']); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
                <!-- Add new user container -->
                <div class=" data-label" onclick="addUser(this)" style="display: none;" id="add-technician">
                    <span>Tambah staff ( juruteknik / admin ) baharu</span>
                    <svg class="icons" style="width: 16px; height: 16px;">
                        <use href="../public/icons.svg#plus-icon"></use>
                    </svg>
                </div>
            </div>
            <!-- No matched result container -->
            <div class="notice" style="display: none;">
                <svg class="icons-notice" style="width: 64px; height: 64px; fill: var(--text);">
                    <use href="../public/icons.svg#alert-icon"></use>
                </svg>
                <h2>Tiada pengguna yang dicari</h2>
                <p>Sila cuba kata kunci lain :)</p>
            </div>
        </div>
        <!-- Right side profil details -->
        <form class="user-profile" method="POST" action="api/update_profile.php" enctype="multipart/form-data">
            <input type="hidden" name="status" value="editing-profile">
            <input type="hidden" name="id" value="">
            <input type="hidden" name="data_type" value="">
            <?php
            // $u as $user
            foreach ($all_user_list as $index => $u):
                $is_staff = isset($u['ID_S']);
                $uid = $is_staff ? $u['ID_S'] : $u['ID_P'];
                $uname = $is_staff ? $u['Nama_S'] : $u['Nama_P'];
                $phone = $is_staff ? $u['No_Tel_S'] : $u['No_Tel_P'];
                $isBanned = (substr($phone, 0, 3) === '[X]') ? true : false;
                if ($isBanned === true) {
                    $phone = preg_replace('/\[X\]/', '', $phone);
                }
                $uprefix = strtoupper(substr($uid, 0, 1));
                // Displays phone numbers with the national numbering plan
                if (trim($phone) !== '') {
                    if (substr($phone, 0, 3) === '011' || substr($phone, 0, 3) === '015') {
                        $displayUsePhoneNum = substr($phone, 0, 3) . '-' . substr($phone, 3, 4) . ' ' . substr($phone, 7);
                    } else {
                        $displayUsePhoneNum = substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . ' ' . substr($phone, 6);
                    }
                } else {
                    $displayUsePhoneNum = '';
                }
                $info = $is_staff ? $u['Peranan'] : $u['Alamat'];
                $pfpEditable = (!$isBanned && ($uid === $adminId));
                $nameEditable = (!$isBanned && ($uprefix !== 'A' || $adminId === 'A001'));
                $passEditable = (!$isBanned && ($uprefix !== 'A' || $uid === $adminId || $adminId === 'A001'));
                $numberEditable = (!$isBanned && ($uprefix !== 'A' || $uid === $adminId || $adminId === 'A001'));
                $statusEditable = ($uid !== 'A001' && $uid !== $adminId && ($uprefix !== 'A' || $adminId === 'A001'));
                ?>
                <div class="profile-container" style="display:<?= $index === 0 ? 'grid' : 'none'; ?>;"
                    data-id-user="<?= htmlspecialchars($uid) ?>">
                    <?php if ($is_staff === true):
                        $pfp_src = ($is_staff && !empty($u['Gbr_S'])) ? '../images/uploads/' . htmlspecialchars($u['Gbr_S']) : '../images/user_pfp.jpeg';
                        ?>
                        <div class="profile-picture-container">
                            <div class="avatar-container">
                                <img src='<?= htmlspecialchars($pfp_src) ?>' class="profile-picture">
                                <?php if ($pfpEditable): ?>
                                    <label title="Tukar gambar profil baharu">
                                        <input type="file" style="display: none;" name="file" onchange="uploadPfp(this)"
                                            accept="image/jpeg, image/png, image.jpg"><!-- function at here -->
                                        <svg class="icons">
                                            <use href="../public/icons.svg#camera-icon"></use>
                                        </svg>
                                    </label>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php elseif ($is_staff === false): ?>
                        <div class="profile-picture-container">
                            <img src='../images/user_pfp.jpeg' class="profile-picture">
                        </div>
                    <?php endif; ?>
                    <span class="error-box" style="display: none;">
                        File terlalu besar
                    </span>
                    <!-- User ID -->
                    <div class="info-group">ID pengguna</div>
                    <div class="info-group">
                        <?= htmlspecialchars($uid) ?>
                        <div class="status">
                            <?php if ($isBanned === true): ?>
                                <div class="badget inactive"></div>
                                Nyahaktif
                            <?php else: ?>
                                <div class="badget active"></div>
                                Aktif
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- Username -->
                    <div class="info-group">Nama Pengguna</div>
                    <div class="info-group"><?= htmlspecialchars($uname) ?>
                        <?php if ($nameEditable): ?>
                            <svg class="icons" onclick="changeInfo(this)"> <!-- function at here -->
                                <use href="../public/icons.svg#edit-icon"></use>
                            </svg>
                        <?php endif; ?>
                    </div>
                    <!-- User password -->
                    <div class="info-group">Kata Laluan</div>
                    <div class="info-group">***
                        <?php if ($passEditable): ?>
                            <svg class="icons" onclick="changeInfo(this)"> <!-- function at here -->
                                <use href="../public/icons.svg#reset-icon"></use>
                            </svg>
                        <?php endif; ?>
                    </div>
                    <!-- User Number -->
                    <div class="info-group">Nombor Telefon</div>
                    <div class="info-group">
                        <?php
                        if (trim($displayUsePhoneNum) === '') {
                            echo '<span style="color: var(--gold); font-size: 18px;">Belum dipasang nombor telefon</span>';
                        } else {
                            echo htmlspecialchars($displayUsePhoneNum);
                        }
                        ?>
                        <?php if ($numberEditable): ?>
                            <svg class="icons" onclick="changeInfo(this)"> <!-- function at here -->
                                <use href="../public/icons.svg#edit-icon"></use>
                            </svg>
                        <?php endif; ?>
                    </div>
                    <!-- User information based on their role -->
                    <?php if ($is_staff === true): ?>
                        <!-- If user is juruteknik/admin -->
                        <div class="info-group">Peranan</div>
                        <div class="info-group"><?= htmlspecialchars($info) ?></div>
                    <?php elseif ($is_staff === false): ?>
                        <!-- If user is customer -->
                        <div class="info-group">Alamat</div>
                        <div class="info-group">
                            <?php
                            if ($info === '') {
                                echo '<span style="color: var(--gold); font-size: 18px;">Belum dipasang alamat</span>';
                            } else {
                                echo htmlspecialchars($info);
                            } ?>
                            <?php if (!$isBanned): ?>
                                <svg class="icons" onclick="changeInfo(this)"> <!-- function at here -->
                                    <use href="../public/icons.svg#edit-icon"></use>
                                </svg>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <!-- Action -->
                    <!--    Account is not A001, Account not self account, Account is not A except current acc is A001-->
                    <?php if ($statusEditable): ?>
                        <div class="info-group">Tindakan</div>
                        <div class="radio-group font-barlowcondensed">
                            <label class="radio-pill">
                                <input type="radio" name="action" value="none">
                                <span>None</span>
                            </label>
                            <?php if ($isBanned === true): ?>
                                <label class="radio-pill">
                                    <input type="radio" name="action" value="restore">
                                    <span>Aktifkan Semula</span>
                                </label>
                            <?php elseif (!$isBanned): ?>
                                <label class="radio-pill">
                                    <input type="radio" name="action" value="delete">
                                    <span>Padam Akaun</span>
                                </label>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="submit-btn action-submit-btn" style="margin-top: 10px;" disabled>Buat
                            Tindakan</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <div class="profile-container" style="display: none;" id="add-user">
                <div class="profile-picture-container">
                    <img src='../images/user_pfp.jpeg' class="profile-picture">
                </div>
                <!-- User ID -->
                <div class="info-group">ID pengguna</div>
                <div class="info-group">Akan diberi secara automatik</div>
                <!-- Username -->
                <div class="info-group">Nama Pengguna</div>
                <div class="info-group">
                    <div class="inline-add-form">
                        <input type="text" name="uname" class="info-group" placeholder="Taip di sini..."
                            autocomplete="off" required>
                    </div>
                </div>
                <!-- User password -->
                <div class="info-group">Kata Laluan</div>
                <div class="info-group">
                    <div class="inline-add-form">
                        <input type="text" name="upass" class="info-group" value="<?= htmlspecialchars($randomPass) ?>"
                            autocomplete="off" readonly>
                    </div>
                    <svg class="icons" onclick="copyPass(this)">
                        <use href="../public/icons.svg#copy-icon"></use>
                    </svg>
                </div>
                <!-- User Number -->
                <div class="info-group">Nombor Telefon</div>
                <div class="info-group">
                    <div class="inline-add-form">
                        <input type="text" name="unum" class="info-group" placeholder="Taip di sini..."
                            autocomplete="off" required>
                    </div>
                </div>
                <!-- User information based on their role -->
                <!-- If user is juruteknik/admin -->
                <div class="info-group" id="role">Peranan</div>
                <div class="info-group" id="role-option">
                    <select class="inline-add-form role-select" name="role">
                        <option value="juruteknik" selected>Juruteknik</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <!-- If user is customer -->
                <div class="info-group" id="address">Alamat</div>
                <div class="info-group" id="address-input">
                    <div class="inline-add-form">
                        <input type="text" name="uaddress" class="info-group" placeholder="Taip di sini..."
                            autocomplete="off">
                    </div>
                </div>
                <button class="submit-btn" style="margin-top: 10px;">Tambah</button>
            </div>
        </form>
    </div>
</div>
<script>
    // Switch button
    const listBtn = document.querySelectorAll("input[name = 'user']");
    const customerList = document.querySelector('.customer-container');
    const staffList = document.querySelector('.staff-container');
    const addCustomerBtn = document.getElementById('add-customer');
    const addTechnicianBtn = document.getElementById('add-technician');

    listBtn.forEach(radio => {
        radio.addEventListener('change', function () {
            const selectedList = this.value;
            customerList.style.display = (selectedList === 'all' || selectedList === 'customer') ? '' : 'none';
            addCustomerBtn.style.display = (selectedList === 'customer') ? '' : 'none';
            staffList.style.display = (selectedList === 'all' || selectedList === 'staff') ? '' : 'none';
            addTechnicianBtn.style.display = (selectedList === 'staff') ? '' : 'none';
        });
    });

    // Edit button 
    function changeInfo(element) {
        document.querySelectorAll('input[name="data_change"]').forEach(input => {
            closeInlineAdd(input);
        });
        document.querySelectorAll('input[name="action"]').forEach(radio => radio.checked = false);
        document.querySelectorAll('.action-submit-btn').forEach(btn => btn.disabled = true);

        const iconContainer = element.closest('.info-group');
        if (!iconContainer) return;

        if (!iconContainer.dataset.oldHtml) {
            iconContainer.dataset.oldHtml = iconContainer.innerHTML;
        }
        const infoLabelContainer = iconContainer.previousElementSibling;
        const labelText = infoLabelContainer ? infoLabelContainer.textContent.trim() : '';

        const dataTypeInput = document.querySelector('input[name="data_type"]');
        const idInput = document.querySelector('input[name="id"]');
        const statusInput = document.querySelector('input[name="status"]');

        statusInput.value = 'editing-profile';
        dataTypeInput.disabled = false;
        idInput.disabled = false;

        switch (labelText) {
            case 'Nama Pengguna':
                dataTypeInput.value = 'name';
                break;
            case 'Kata Laluan':
                dataTypeInput.value = 'password';
                break;
            case 'Nombor Telefon':
                dataTypeInput.value = 'number';
                break;
            case 'Alamat':
                dataTypeInput.value = 'address';
                break;
        }
        idInput.value = element.closest('.profile-container').dataset.idUser;
        let inlineBox = '';

        if (dataTypeInput.value === 'password') {
            inlineBox = `<div class="inline-add-form">
                                    <input type="text" name="data_change" class="info-group" value="<?= htmlspecialchars($randomPass) ?>" autocomplete="off" required readonly>
                                    <button type="submit" class="inline-action-btn confirm" title="Simpan">✓</button>
                                    <button type="button" class="inline-action-btn cancel" onclick="closeInlineAdd(this)" title="Batal">✕</button>
                                    <svg class="icons" onclick="copyPass(this)">
                                        <use href="../public/icons.svg#copy-icon"></use>
                                    </svg>
                                </div>`;
        } else {
            inlineBox = `<div class="inline-add-form">
                                    <input type="text" name="data_change" class="info-group" placeholder="Taip di sini..." autocomplete="off" required>
                                    <button type="submit" class="inline-action-btn confirm" title="Simpan">✓</button>
                                    <button type="button" class="inline-action-btn cancel" onclick="closeInlineAdd(this)" title="Batal">✕</button>
                                </div>`;
        }

        iconContainer.innerHTML = inlineBox;
        iconContainer.querySelector('input[name="data_change"]').focus();
    }


    // Close input box 
    function closeInlineAdd(element) {
        const formContainer = element.closest('.inline-add-form');
        const fatherContainer = formContainer.parentElement;
        if (fatherContainer && fatherContainer.dataset.oldHtml) {
            fatherContainer.innerHTML = fatherContainer.dataset.oldHtml;
            delete fatherContainer.dataset.oldHtml;
        }
    }

    // Show selected user profile 
    function selectUser(element) {
        const selectedUser = element.dataset.target;
        const userProfile = document.querySelectorAll('.profile-container');

        document.querySelectorAll('input[class="info-group"]').forEach(form => {
            closeInlineAdd(form);
        });
        userProfile.forEach(profile => {
            if (profile.dataset.idUser === selectedUser) {
                profile.style.display = 'grid';
            } else {
                profile.style.display = 'none';
            }
        });
        document.querySelectorAll('.data-label').forEach(el => el.classList.remove('active'));
        element.classList.add('active');
        disableRequired()
        document.querySelectorAll('input[name="action"]').forEach(radio => radio.checked = false);
        document.querySelectorAll('.action-submit-btn').forEach(btn => btn.disabled = true);
    }

    document.querySelector('.data-label')?.classList.add('active');

    // Function show signup page 
    function addUser(element) {
        document.querySelectorAll('.data-label').forEach(el => el.classList.remove('active'));
        document.querySelector('input[name="status"]').value = 'adding-user';

        document.querySelectorAll('.inline-add-form').forEach(form => {
            if (form.closest('.profile-container')?.id !== 'add-user') {
                closeInlineAdd(form);
            }
        });
        document.querySelectorAll('.profile-container').forEach(profile => {
            profile.style.display = 'none';
        });

        const addForm = document.getElementById('add-user');
        if (addForm) {
            addForm.style.display = 'grid';
        }
        const isCustomer = (element.id === 'add-customer');
        enableRequired(isCustomer);
        if (element.id === 'add-customer') {
            document.getElementById('address').style.display = 'flex';
            document.getElementById('address-input').style.display = 'flex';
            document.getElementById('role').style.display = 'none';
            document.getElementById('role-option').style.display = 'none';
        } else {
            document.getElementById('role').style.display = 'flex';
            document.getElementById('role-option').style.display = 'flex';
            document.getElementById('address').style.display = 'none';
            document.getElementById('address-input').style.display = 'none';
        }
    }

    const status = document.querySelector('input[name="status"]');
    const dataType = document.querySelector('input[name="data_type"]');
    const idUser = document.querySelector('input[name="id"]');
    const uName = document.querySelector('input[name="uname"]');
    const uPass = document.querySelector('input[name="upass"]');
    const uNumber = document.querySelector('input[name="unum"]');
    const uAddress = document.querySelector('input[name="uaddress"]');
    const role = document.querySelector('select[name="role"]');
    // Function disable required when editing profile
    function disableRequired() {
        status.value = 'editing-profile';
        dataType.disabled = false;
        idUser.disabled = false;

        uName.required = false;
        uPass.required = false;
        uNumber.required = false;
        uAddress.required = false;

        uName.disabled = true;
        uPass.disabled = true;
        uNumber.disabled = true;
        uAddress.disabled = true;
        role.disabled = true;

        uName.value = '';
        uNumber.value = '';
        uAddress.value = '';
    }

    // Function enable required when adding user
    function enableRequired(isCustomer) {
        status.value = 'adding-user';
        dataType.value = '';
        idUser.value = '';
        dataType.disabled = true;
        idUser.disabled = true;

        uName.disabled = false;
        uPass.disabled = false;
        uNumber.disabled = false;

        uName.required = true;
        uPass.required = true;
        uNumber.required = true;

        if (isCustomer) {
            uAddress.disabled = false;
            uAddress.required = false;
            role.disabled = true;
        } else {
            role.disabled = false;
            uAddress.disabled = true;
            uAddress.required = false;
            uAddress.value = '';
        }
    }

    // Admin action (Delete user account)
    const actionInputs = document.querySelectorAll('input[name="action"]');

    actionInputs.forEach(action => {
        action.addEventListener('change', function () {
            const realAction = this.value;
            const currentProfile = this.closest('.profile-container');
            const currentActionBtn = currentProfile.querySelector('.action-submit-btn');
            document.querySelectorAll('input[name="data_change"]').forEach(input => {
                closeInlineAdd(input);
            });
            if (realAction === 'delete' || realAction === 'restore') {
                disableRequired();
                idUser.disabled = false;
                idUser.value = currentProfile.dataset.idUser;
                dataType.value = '';
                dataType.disabled = true;

                if (currentActionBtn) currentActionBtn.disabled = false;
            } else if (realAction === 'none') {
                if (currentActionBtn) currentActionBtn.disabled = true;
                dataType.disabled = true;
                idUser.value = currentProfile.dataset.idUser;
            } else {
                if (currentActionBtn) currentActionBtn.disabled = true;
            }
        });
    });

    // Copy temporary password
    function copyPass(element) {
        const input = element.closest('.info-group')?.querySelector('input');
        const passText = input ? input.value.trim() : '';
        if (!passText) return;

        navigator.clipboard.writeText(passText).then(() => {
            const useTag = element.querySelector('use');
            if (useTag) {
                useTag.setAttribute('href', '../public/icons.svg#copy-check-icon');
                setTimeout(() => {
                    useTag.setAttribute('href', '../public/icons.svg#copy-icon');
                }, 1500);
            }
        });
    }
    disableRequired();

    // Search function
    const searchBar = document.querySelector('.search-bar');
    const noticeContainer = document.querySelector('.notice');

    if (searchBar) {
        searchBar.addEventListener('input', function () {
            const keyword = this.value.trim().toLowerCase();
            const listFilter = document.querySelector('input[name="user"]:checked')?.value || 'all';
            let showName = false;

            // Staffs and Customers container
            document.querySelectorAll('.staff-container, .customer-container').forEach(section => {
                const isCustomerSection = section.classList.contains('customer-container');
                const isAllowedByFilter = (listFilter === 'all') ||
                    (listFilter === 'customer' && isCustomerSection) ||
                    (listFilter === 'staff' && !isCustomerSection);
                const userList = section.querySelectorAll(':scope > .data-label:not(:last-child)');
                let showSection = false;

                userList.forEach(user => {
                    const userName = user.textContent.trim().toLowerCase();
                    const isUserNameMatch = keyword === '' || userName.includes(keyword);

                    if (isUserNameMatch) {
                        user.style.display = '';
                        showSection = true;
                        if (isAllowedByFilter) {
                            showName = true;
                        }
                    } else {
                        user.style.display = 'none';
                    }
                });

                section.style.display = (isAllowedByFilter && (keyword === '' || showSection)) ? '' : 'none';
            });

            if (noticeContainer) {
                noticeContainer.style.display = (keyword !== '' && !showName) ? 'flex' : 'none';
            }
        });
    }

    const form = document.querySelector('form.user-profile');
    // Upload profile picture
    function uploadPfp(input) {
        const currentProfile = input.closest('.profile-container');
        const errorBox = currentProfile ? currentProfile.querySelector('.error-box') : null;

        if (input.files && input.files.length > 0) {
            const file = input.files[0];

            if (file.size > 2 * 1024 * 1024) {
                if (errorBox) errorBox.style.display = 'inline-flex';
                input.value = '';
            } else {
                if (errorBox) errorBox.style.display = 'none';
                disableRequired();
                idUser.disabled = false;
                idUser.value = currentProfile.dataset.idUser;
                dataType.value = 'file';
                dataType.disabled = false;
                form.submit();
            }
        }
    }
</script>