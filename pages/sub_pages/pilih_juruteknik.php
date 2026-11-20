<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (
    !isset($_SESSION['temp_data']) ||
    !isset($_SESSION['role']) ||
    ($_SESSION['role'] !== 'pelanggan')
) {
    http_response_code(401);
    include($_SERVER['DOCUMENT_ROOT'] . '/SolusiTemu/pages/error/401.html');
    exit;
}

$tempData = $_SESSION['temp_data'];
$idService = $tempData['idService'];
$brandName = $tempData['brandName'];
$modalName = $tempData['modalName'];
$time = $tempData['time'];
$date = $tempData['date'];
// Combine date and time
$combinedDateTime = $date . ' ' . $time;
$availableTechnicians = db_getAvailableTechnicians($pdo, $combinedDateTime);
if (!$availableTechnicians || !is_array($availableTechnicians)) {
    $availableTechnicians = [];
}

?>
<link rel="stylesheet" href="../public/css/appointment.css">
<form class="appointment-container" id="final-appointment-form" method="POST" action="api/make_appointment.php">
    <input type="hidden" name="status" value="making-appointment">
    <input type="hidden" name="idService" value="<?= htmlspecialchars($idService) ?>">
    <input type="hidden" name="brandName" value="<?= htmlspecialchars($brandName) ?>">
    <input type="hidden" name="modalName" value="<?= htmlspecialchars($modalName) ?>">
    <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">
    <input type="hidden" name="time" value="<?= htmlspecialchars($time) ?>">
    <!-- New value (chosen juruteknik) -->
    <input type="hidden" name="idJuruteknik" value="">
    <!-- Header area -->
    <div class="banner-container">
        <h1 class="font-barlowcondensed" style="margin: 0px;">Langkah Terakhir: Pilih Juruteknik</h1>
        <svg class="icons">
            <use href="../public/icons.svg#tool-icon"></use>
        </svg>
    </div>
    <!-- Display technicans -->
    <div class="technicans-container">
        <?php if (!empty($availableTechnicians) && is_array($availableTechnicians)): ?>
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
                <div class="technican" data-id-technican="<?= htmlspecialchars($technician['ID_S'] ?? '') ?>">
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
                    <button class="choose label" type="button" onclick="selectTechnican(this)">Pilih</button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="notice">
                <svg class="icons-notice" style="width: 64px; height: 64px; fill: var(--text);">
                    <use href="../public/icons.svg#alert-icon"></use>
                </svg>
                <h2>Maaf, Tiada Juruteknik Tersedia</h2>
                <p>Semua juruteknik telah ditempah pada tarikh dan masa yang dipilih
                    (<?= htmlspecialchars($date . ' ' . $time) ?>).</p>
                <p>Sila kembali ke langkah sebelumnya untuk memilih masa yang lain.</p>
                <button type="button" class="back-btn" onclick="window.history.back();">Kembali Pilih Masa</button>
            </div>
        <?php endif; ?>
    </div>
</form>
<!-- JavaScripts -->
<script>
    function selectTechnican(element) {
        const technicanContainer = element.closest('.technican');
        const technicanId = technicanContainer.dataset.idTechnican;
        const selectedTechnican = document.querySelector('input[name="idJuruteknik"]');
        const form = document.querySelector('form');

        element.classList.toggle('active');
        selectedTechnican.value = technicanId;
        sessionStorage.clear();
        form.submit();
    }
</script>