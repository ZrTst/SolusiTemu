<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (
    !isset($_SESSION['logged_in']) ||
    $_SESSION['logged_in'] !== true ||
    !isset($_SESSION['role']) ||
    ($_SESSION['role'] !== 'juruteknik')
) {
    http_response_code(401);
    include($_SERVER['DOCUMENT_ROOT'] . '/SolusiTemu/pages/errors/401.html');
    exit;
}

$userId = isset($_SESSION['user_id']) ? trim($_SESSION['user_id']) : '';
$SelfAppointment = db_getSelfAppointment($pdo, $userId);
?>
<link rel="stylesheet" href="../public/css/appointment.css">
<div class="appointment-container">
    <!-- Header area -->
    <div class="banner-container">
        <h1 class="font-oswald" style="margin: 0px;">Temu Janji yang ditetapkan</h1>
        <svg class="icons">
            <use href="../public/icons.svg#history-icon"></use>
        </svg>
    </div>
    <div class="appointment-list-container">
        <div class="label">
            <span>No.</span>
            <span>Pelanggan</span>
            <span>Perkhidmatan</span>
            <span>Model</span>
            <span>Tarikh & Masa</span>
            <span>Status</span>
            <span>Tindakan</span>
        </div>
        <?php foreach ($SelfAppointment as $appointment): ?>
            <div class="data-label">
                <span>
                    <?php echo htmlspecialchars($appointment['ID_JT']); ?>
                </span>
                <span>
                    <?php echo htmlspecialchars($appointment['Nama_P']); ?>
                </span>
                <span>
                    <?php echo htmlspecialchars($appointment['Nama_PK']); ?>
                </span>
                <span>
                    <strong
                        style="color: #D4AF37;"><?php echo htmlspecialchars($appointment['Jenama']); ?></strong><br><small><?php echo htmlspecialchars($appointment['Model']); ?></small>
                </span>
                <span>
                    <?php echo htmlspecialchars($appointment['Tarikh_Masa']); ?>
                </span>
                <span>
                    <?php echo htmlspecialchars($appointment['Status']); ?>
                </span>
                <span>
                    <button>Accepted</button>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
</div>