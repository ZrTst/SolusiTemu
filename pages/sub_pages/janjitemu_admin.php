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

$allAppointment = db_getAllAppointment($pdo);
?>
<link rel="stylesheet" href="../public/css/appointment.css">
<div class="appointment-container">
    <!-- Header area -->
    <div class="banner-container">
        <h1 class="font-barlowcondensed" style="margin: 0px;">Senarai JanjiTemu</h1>
        <svg class="icons">
            <use href="../public/icons.svg#history-icon"></use>
        </svg>
    </div>
    <div class="appointment-list-container">
        <div class="label">
            <span>No.</span>
            <span>Pelanggan</span>
            <span>Perkhidmatan</span>
            <span>Juruteknik</span>
            <span>Model</span>
            <span>Tarikh & Masa</span>
            <span>Status</span>
            <span>Tindakan</span>
        </div>
        <?php foreach ($allAppointment as $appointment): ?>
            <div class="data-label">
                <span>
                    <?php echo htmlspecialChars($appointment['ID_JT']); ?>
                </span>
                <span>
                    <?php echo htmlspecialChars($appointment['Nama_P']); ?>
                </span>
                <span>
                    <?php echo htmlspecialChars($appointment['Nama_PK']); ?>
                </span>
                <span>
                    <?php echo htmlspecialChars($appointment['Nama_S']); ?>
                </span>
                <span>
                    <strong
                        style="color: #D4AF37;"><?php echo htmlspecialChars($appointment['Jenama']); ?></strong><br><small><?php echo htmlspecialChars($appointment['Model']); ?></small>
                </span>
                <span>
                    <?php echo htmlspecialChars($appointment['Tarikh_Masa']); ?>
                </span>
                <span>
                    <?php echo htmlspecialChars($appointment['Status']); ?>
                </span>
                <span>
                    <button>Ubah Suai</button>
                </span>
            </div>
        <?php endforeach; ?>
        <!-- Add new user container -->
        <div class="data-label" onclick="addUser(this)" style="display: flex;" id="add-technician">
            <span>Tambah staff ( juruteknik / admin ) baharu</span>
            <svg class="icons" style="width: 16px; height: 16px;">
                <use href="../public/icons.svg#plus-icon"></use>
            </svg>
        </div>
    </div>
</div>