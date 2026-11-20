<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (
    !isset($_SESSION['role']) ||
    ($_SESSION['role'] !== 'pelanggan') &&
    ($_SESSION['role'] !== 'juruteknik')
) {
    http_response_code(401);
    include($_SERVER['DOCUMENT_ROOT'] . '/SolusiTemu/pages/error/401.html');
    exit;
}
$availableTechnicians = db_getAvailableTechnicians($pdo, '1970-9-20 15:30');
if (!$availableTechnicians || !is_array($availableTechnicians)) {
    $availableTechnicians = [];
}

?>
<link rel="stylesheet" href="../public/css/appointment.css">
<div class="appointment-container">
    <!-- Header area -->
    <div class="banner-container">
        <h1 class="font-barlowcondensed" style="margin: 0px;">Juruteknik kami</h1>
        <svg class="icons">
            <use href="../public/icons.svg#tool-icon"></use>
        </svg>
    </div>
    <!-- Display technicans -->
    <div class="technicans-container">
        <?php foreach ($availableTechnicians as $technician): ?>
            <?php $phone = $technician['No_Tel_S'] ?? '';
            // Displays phone numbers with the national numbering plan
            if (trim($phone) !== '') {
                if (substr($phone, 0, 3) === '011' || substr($phone, 0, 3) === '015') {
                    $displayUsePhoneNum = substr($phone, 0, 3) . '-' . substr($phone, 3, 4) . ' ' . substr($phone, 7);
                } else {
                    $displayUsePhoneNum = substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . ' ' . substr($phone, 6);
                }
            } else {
                $displayUsePhoneNum = '';
            } ?>
            <div class="technican">
                <div class="photo-container">
                    <?php $pfp_src = !empty($technician['Gbr_S']) ? '../images/uploads/' . htmlspecialchars($technician['Gbr_S']) : '../images/user_pfp.jpeg';
                    ?>
                    <img class="photo-container" src="<?= htmlspecialchars($pfp_src) ?>"
                        alt="<?= htmlspecialchars($technician['Nama_S']) ?>">
                </div>
                <div class="info-container">
                    <div class="info label">Nama juruteknik: </div>
                    <div class="data label">
                        <?= htmlspecialchars($technician['Nama_S'] ?? '') ?>
                    </div>
                    <div class="info label">Nombor Telefon juruteknik: </div>
                    <div class="data label"><?= htmlspecialchars($displayUsePhoneNum ?? '-') ?></div>
                </div>

            </div>
        <?php endforeach; ?>
    </div>
</div>
<!-- JavaScripts -->
<script>
</script>