<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (
    !isset($_SESSION['logged_in']) ||
    $_SESSION['logged_in'] !== true ||
    !isset($_SESSION['role']) ||
    ($_SESSION['role'] !== 'pelanggan' && $_SESSION['role'] !== 'juruteknik')
) {
    http_response_code(401);
    include($_SERVER['DOCUMENT_ROOT'] . '/SolusiTemu/pages/error/401.html');
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
        <h1 class="font-barlowcondensed" style="margin: 0px;">Skop Perkhidmatan </h1>
        <svg class="icons">
            <use href="../public/icons.svg#setting-icon"></use>
        </svg>
    </div>
    <div class="search-container">
        <input class="search-bar" type="search" placeholder="Sedang mencari sesuatu perkhidmatan...?">
        <svg class="icons">
            <use href="../public/icons.svg#search-icon"></use>
        </svg>
    </div>
    <!-- Hardware Services -->
    <div class="hardware-services">
        <label class="label">Hardware</label>
        <?php foreach ($hardwareList as $subCat => $servicesList): ?>
            <div class="subService-container">
                <!-- Label of Sub-Categories -->
                <div class="subCat">
                    <label class="label">
                        <span>
                            <?= htmlspecialchars($subCat) ?>
                        </span>
                    </label>
                </div>
                <!-- Choices -->
                <div class="activities-container">
                    <?php foreach ($servicesList as $item): ?>
                        <button type="button" class="activities-btn font-barlowcondensed"
                            data-id="<?= htmlspecialchars($item['idService']) ?>">
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
                    <label class="label">
                        <span>
                            <?= htmlspecialchars($subCat) ?>
                        </span>
                    </label>
                </div>
                <!-- Choices -->
                <div class="activities-container">
                    <?php foreach ($servicesList as $item): ?>
                        <button type="button" class="activities-btn font-barlowcondensed"
                            data-id="<?= htmlspecialchars($item['idService']) ?>">
                            <?= strtolower(htmlspecialchars($item['serviceActivity'])) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <!-- No matched result container -->
    <div class="notice" style="display: none;">
        <svg class="icons-notice" style="width: 64px; height: 64px; fill: var(--text);">
            <use href="../public/icons.svg#alert-icon"></use>
        </svg>
        <h2>Tiada perkhidmatan yang dicari</h2>
        <p>Sila cuba kata kunci lain :)</p>
    </div>
</div>
<script>
    // Search function
    const searchBar = document.querySelector('.search-bar');
    const noticeContainer = document.querySelector('.notice');

    if (searchBar) {
        searchBar.addEventListener('input', function () {
            const keyword = this.value.trim().toLowerCase();

            // Hardware and Software 
            document.querySelectorAll('.hardware-services, .software-services').forEach(section => {
                const mainCatName = section.querySelector(':scope > .label').textContent.trim().toLowerCase();
                const isMainMatch = keyword !== '' && mainCatName.includes(keyword);
                let hasVisibleCard = false;

                // Sub-services
                section.querySelectorAll('.subService-container').forEach(card => {
                    const subCatName = card.querySelector('.subCat .label').textContent.trim().toLowerCase();
                    const isSubCatMatch = keyword !== '' && subCatName.includes(keyword);
                    let hasVisibleBtn = false;

                    // Activities
                    card.querySelectorAll('.activities-btn:not(.add-trigger-btn)').forEach(btn => {
                        const btnText = btn.textContent.trim().toLowerCase();

                        if (keyword === '' || isMainMatch || isSubCatMatch || btnText.includes(keyword)) {
                            btn.style.display = '';
                            hasVisibleBtn = true;
                        } else {
                            btn.style.display = 'none';
                        }
                    });

                    if (keyword === '' || hasVisibleBtn) {
                        card.style.display = '';
                        hasVisibleCard = true;
                    } else {
                        card.style.display = 'none';
                    }
                });

                section.style.display = (keyword === '' || hasVisibleCard) ? '' : 'none';
                if (noticeContainer) {
                    noticeContainer.style.display = (keyword !== '' && !hasVisibleCard) ? 'flex' : 'none';
                }
            });

        });
    }
</script>