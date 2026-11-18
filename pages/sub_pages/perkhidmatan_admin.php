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
                            data-id="<?= htmlspecialchars($item['idService']) ?>"
                            title="<?= strtolower(htmlspecialchars($item['serviceActivity'])) ?>">
                            <?= strtolower(htmlspecialchars($item['serviceActivity'])) ?>
                        </button>
                    <?php endforeach; ?>
                    <!-- Inline input box container -->
                    <div class="inline-add-wrapper" data-main-cat="Hardware" data-sub-cat="
                        <?= htmlspecialchars($subCat) ?>">

                        <button type="button" class="activities-btn font-barlowcondensed add-trigger-btn"
                            onclick="openInlineAdd(this)">
                            <svg class="icons" style="width: 16px; height: 16px;">
                                <use href="../public/icons.svg#plus-icon"></use>
                            </svg>
                        </button>
                        <!-- Input box and form -->
                        <form class="inline-add-form" method="POST" action="api/update_service.php" style="display: none;">
                            <input type="hidden" name="type" value="add">
                            <input type="hidden" name="status" value="updating-service">
                            <input type="hidden" name="main-cat" value="Hardware">
                            <input type="hidden" name="sub-cat" value="<?= htmlspecialchars($subCat) ?>">
                            <input type="text" name="data-change" class="inline-input font-barlowcondensed"
                                placeholder="Nama aktiviti..." autocomplete="off" required>
                            <!-- Save button -->
                            <button type="submit" class="inline-action-btn confirm" title="Simpan">✓</button>
                            <!-- Cancel button -->
                            <button type="button" class="inline-action-btn cancel" onclick="closeInlineAdd(this)"
                                title="Batal">✕</button>
                        </form>
                    </div>
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
                            data-id="<?= htmlspecialchars($item['idService']) ?>"
                            title="<?= strtolower(htmlspecialchars($item['serviceActivity'])) ?>">
                            <?= strtolower(htmlspecialchars($item['serviceActivity'])) ?>
                        </button>
                    <?php endforeach; ?>
                    <!-- Inline input box container -->
                    <div class="inline-add-wrapper" data-main-cat="Software" data-sub-cat="
                        <?= htmlspecialchars($subCat) ?>">

                        <button type="button" class="activities-btn font-barlowcondensed" onclick="openInlineAdd(this)">
                            <svg class="icons" style="width: 16px; height: 16px;">
                                <use href="../public/icons.svg#plus-icon"></use>
                            </svg>
                        </button>
                        <!-- Input box and form -->
                        <form class="inline-add-form" method="POST" action="api/update_service.php" style="display: none;">
                            <input type="hidden" name="type" value="add">
                            <input type="hidden" name="status" value="updating-service">
                            <input type="hidden" name="main-cat" value="Software">
                            <input type="hidden" name="sub-cat" value="<?= htmlspecialchars($subCat) ?>">
                            <input type="text" name="data-change" class="inline-input font-barlowcondensed"
                                placeholder="Nama aktiviti..." autocomplete="off" required>
                            <!-- Save button -->
                            <button type="submit" class="inline-action-btn confirm" title="Simpan">✓</button>
                            <!-- Cancel button -->
                            <button type="button" class="inline-action-btn cancel" onclick="closeInlineAdd(this)"
                                title="Batal">✕</button>
                        </form>
                    </div>
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
    <!-- New service fill form -->
    <div class="new-subCat-service-container">
        <label class="label font-barlowcondensed" style="font-size: 30px;">Tambah perkhidmatan Baharu</label>
        <form class="subService-container" method="POST" action="api/update_service.php">
            <input type="hidden" name="status" value="updating-service">
            <div class="radio-group font-barlowcondensed">
                <label class="radio-pill">
                    <input type="radio" name="main-cat" value="Hardware" checked>
                    <span>Hardware</span>
                </label>
                <label class="radio-pill">
                    <input type="radio" name="main-cat" value="Software">
                    <span>Software</span>
                </label>
            </div>
            <div class="subCat" style="justify-content: space-between; width: 80%; gap: 10px;">
                <input type="text" name="sub-cat" class="inline-input font-barlowcondensed"
                    placeholder="Nama Sub-Kategori (cth: GPU)..." autocomplete="off" required
                    style="font-size: 22px; border-bottom: 1px solid var(--gold-bright); flex: 1;">
            </div>
            <div class="activities-container">
                <input type="text" name="data-change" class="inline-input font-barlowcondensed"
                    placeholder="Aktiviti pertama..." autocomplete="off"
                    style="border-bottom: 1px solid var(--gold-bright);" required>
            </div>
            <button class="submit-btn" style="margin: 0px;">Tambah</button>
        </form>
    </div>
</div>
<script>
    // Open input box
    function openInlineAdd(btn) {
        document.querySelectorAll('.inline-add-form').forEach(form => {
            form.style.display = 'none';
            form.previousElementSibling.style.display = 'inline-flex';
        });
        const wrapper = btn.closest('.inline-add-wrapper');
        const form = wrapper.querySelector('.inline-add-form');
        const input = form.querySelector('.inline-input');

        btn.style.display = 'none';
        form.style.display = 'inline-flex';
        input.value = '';
        input.focus();
    }

    // Close input box 
    function closeInlineAdd(btn) {
        const wrapper = btn.closest('.inline-add-wrapper');
        const form = wrapper.querySelector('.inline-add-form');
        const triggerBtn = wrapper.querySelector('.add-trigger-btn');

        form.style.display = 'none';
        triggerBtn.style.display = 'inline-flex';
    }

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