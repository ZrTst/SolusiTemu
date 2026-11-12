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

$allServices = db_getAllServices($pdo);
// Sub-category status e.g. HW.XXX.BROKEN
$hardwareList = $allServices['hardware'] ?? [];
$softwareList = $allServices['software'] ?? [];

?>
<link rel="stylesheet" href="../public/css/service_lists.css">
<div class="services-container">
    <!-- Header area -->
    <div class="banner-container">
        <h1 class="font-oswald" style="margin: 0px;">Skop Perkhidmatan </h1>
        <svg class="icons">
            <use href="../public/icons.svg#setting-icon"></use>
        </svg>
    </div>
    <!-- Hardware Services -->
    <div class="hardware-services">
        <label class="label">Hardware</label>
        <?php foreach ($hardwareList as $subCat => $servicesList): ?>
            <div class="subService-container">
                <!-- Label of Sub-Categories -->
                <div class="subCat">
                    <label class="label" onclick="expandServices(this)">
                        <span>
                            <?= htmlspecialchars($subCat) ?>
                        </span>
                    </label>
                </div>
                <!-- Choices -->
                <div class="activities-container">
                    <?php foreach ($servicesList as $item): ?>
                        <button type="button" class="activities-btn font-oswald"
                            data-id="<?= htmlspecialchars($item['idService']) ?>" onclick="selectService(this)">
                            <?= strtolower(htmlspecialchars($item['serviceActivity'])) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <!-- Software Services -->
    <div class="software-services">
        <label class="label">Software</label>
        <?php foreach ($softwareList as $subCat => $servicesList): ?>
            <div class="subService-container">
                <!-- Label of Sub-Categories -->
                <div class="subCat">
                    <label class="label" onclick="expandServices(this)">
                        <span>
                            <?= htmlspecialchars($subCat) ?>
                        </span>
                    </label>
                </div>
                <!-- Choices -->
                <div class="activities-container">
                    <?php foreach ($servicesList as $item): ?>
                        <button type="button" class="activities-btn font-oswald"
                            data-id="<?= htmlspecialchars($item['idService']) ?>" onclick="selectService(this)">
                            <?= strtolower(htmlspecialchars($item['serviceActivity'])) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<script>
</script>