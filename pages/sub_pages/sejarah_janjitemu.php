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

$userId = isset($_SESSION['user_id']) ? trim($_SESSION['user_id']) : '';
$history_appointment = db_getHistoryAppointment($pdo, $userId);
?>
<link rel="stylesheet" href="../public/css/appointment.css">
<div class="appointment-container">
    <!-- Header area -->
    <div class="banner-container">
        <h1 class="font-barlowcondensed" style="margin: 0px;">Sejarah JanjiTemu</h1>
        <svg class="icons">
            <use href="../public/icons.svg#history-icon"></use>
        </svg>
    </div>
    <!-- Switch page icon -->
    <div class="switch-page">
        <svg class="icons">
            <use href="../public/icons.svg#arrow-exchange-left-icon"></use>
        </svg>
        <a href="dashboard.php?page=janjitemu_pelanggan" class="font-barlowcondensed" style="margin: 0px;">Borang
            JanjiTemu</a>
    </div>
    <div class="history-container">
        <div class="label">
            <span>No.</span>
            <span>Perkhidmatan</span>
            <span>Juruteknik</span>
            <span>Model</span>
            <span>Tarikh & Masa</span>
            <span>Status</span>
            <span>Tindakan</span>
        </div>
        <?php foreach ($history_appointment as $appointment): ?>
            <div class="data-label">
                <span>
                    <?php echo htmlspecialChars($appointment['ID_JT']); ?>
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
    </div>
</div>