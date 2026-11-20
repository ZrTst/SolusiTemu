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

$showLogs = false;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $status === 'searching-log') {
    // Function to get error log from error.log
    function getErrorLog($filePath, $maxLength)
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return [];
        }
        $file = fopen($filePath, 'r');
        $fileSize = filesize($filePath);

        // If file is 0B
        if ($fileSize === 0) {
            fclose($file);
            return [];
        }

        // Compare size
        $readBytes = min($fileSize, 32768);
        fseek($file, -$readBytes, SEEK_END);
        $rawContent = fread($file, $readBytes);
        fclose($file);

        // Seperate lines in to array
        $lines = explode("\n", trim($rawContent));
        if ($fileSize > $readBytes) {
            array_shift($lines);
        }
        $lines = array_reverse($lines);

        $parsedLogs = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line))
                continue;

            // Message pattern
            $pattern = '/^\[([^\]]+)\]\s+\[([^\]]+)\]\s+\[pid\s+[^\]]+\]\s+(?:\[client\s+([^\]]+)\]\s+)?(.+)$/';
            if (preg_match($pattern, $line, $matches)) {
                $level = $matches[2];

                if (strpos($level, 'mpm_winnt:') !== false || strpos($level, 'ssl:warn') !== false) {
                    continue;
                }

                $cleanTime = preg_replace('/\.\d+/', '', $matches[1]);
                $cleanMsg = preg_replace('/\s*\(index\.php[^)]+\)/', '', $matches[4]);

                $parsedLogs[] = [
                    'time' => $cleanTime,
                    'level' => $level,
                    'client' => $matches[3] ?: 'System',
                    'message' => $cleanMsg
                ];
            }
            if (count($parsedLogs) >= $maxLength) {
                break;
            }
        }

        return $parsedLogs;
    }
    $logs = getErrorLog('C:\xampp\apache\logs\error.log', 50);
    $showLogs = true;
} else {
    $allAppointment = db_getAllAppointment($pdo);
    $showLogs = false;
}

?>
<link rel="stylesheet" href="../public/css/appointment.css">
<link rel="stylesheet" href="../public/css/report.css">
<div class="report-container">
    <!-- Header area -->
    <div class="banner-container">
        <h1 class="font-barlowcondensed" style="margin: 0px;">Laporan SolusiTemu</h1>
        <svg class="icons">
            <use href="../public/icons.svg#report-icon"></use>
        </svg>
    </div>
    <!-- Switch appointment or access log -->
    <div class="radio-group font-barlowcondensed">
        <label class="radio-pill">
            <input type="radio" name="list" value="appointment" <?= $showLogs ? '' : 'checked'; ?>>
            <span>JanjiTemu</span>
        </label>
        <label class="radio-pill">
            <input type="radio" name="list" value="log" <?= $showLogs ? 'checked' : ''; ?>>
            <span>Log Pengurusan</span>
        </label>
    </div>
    <!-- Search Area -->
    <div class="date-search-container" style="<?= $showLogs ? 'display: none;' : ''; ?>">
        <div class="date-container">
            <div class="date-group">
                <span class="font-barlowcondensed label-text">Dari</span>
                <input type="date" id="startDate" aria-label="Tarikh mula">
            </div>

            <div class="date-group">
                <span class="font-barlowcondensed label-text">Hingga</span>
                <input type="date" id="endDate" aria-label="Tarikh akhir">
            </div>

            <button type="button" class="filter-btn font-barlowcondensed" onclick="filterData()">
                Penapis
            </button>
        </div>
        <!-- Search Container -->
        <div class="search-container">
            <input class="search-bar" type="search" placeholder="Sedang mencari sesuatu...?">
            <svg class="icons">
                <use href="../public/icons.svg#search-icon"></use>
            </svg>
        </div>
    </div>
    <!-- Reset Button -->
    <button type="button" class="reset-btn font-barlowcondensed" onclick="resetFilter()"
        style="<?= $showLogs ? 'display: none;' : ''; ?>">
        Set Semula
    </button>
    <!-- ========== Server Log Area ========== -->
    <form class="log-container" method="POST" action="" style="display: <?= $showLogs ? 'flex' : 'none'; ?>;">
        <input type="hidden" name="status" value="searching-log">
        <?php if ($showLogs === true) { ?>
            <?php foreach ($logs as $log): ?>
                <div class="label">
                    <?php foreach ($log as $logInfo): ?>
                        <div class="data-label">
                            <?= htmlspecialchars($logInfo); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php } ?>
    </form>
    <?php if ($showLogs === false) { ?>
        <!-- ========== Normal Reports Area ========== -->
        <!-- Reports -->
        <div class="appointment-list-container" style="display: none;">
            <div class="label">
                <span>No.</span>
                <span>Pelanggan</span>
                <span>Perkhidmatan</span>
                <span>Juruteknik</span>
                <span>Model</span>
                <span>Tarikh & Masa</span>
                <span>Status</span>
            </div>
            <?php foreach ($allAppointment as $appointment): ?>
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
                        <?php echo htmlspecialchars($appointment['Nama_S']); ?>
                    </span>
                    <span>
                        <strong style="color: #D4AF37;">
                            <?php echo htmlspecialchars($appointment['Jenama']); ?>
                        </strong><br><small>
                            <?php echo htmlspecialchars($appointment['Model']); ?>
                        </small>
                    </span>
                    <span>
                        <?php echo htmlspecialchars($appointment['Tarikh_Masa']); ?>
                    </span>
                    <span>
                        <?php echo htmlspecialchars($appointment['Status']); ?>
                    </span>
                </div>
            <?php endforeach; ?>
            <button class="submit-btn" onclick="printAppointments()">Cetak
                <svg class="icons">
                    <use href="../public/icons.svg#printer-icon"></use>
                </svg>
            </button>
        </div>
        <!-- No matched result container -->
        <div class="notice" style="display: none;">
            <svg class="icons-notice" style="width: 64px; height: 64px; fill: var(--text);">
                <use href="../public/icons.svg#alert-icon"></use>
            </svg>
            <h2>Tiada laporan janjitemu yang dicari</h2>
            <p>Sila cuba kata kunci lain :)</p>
        </div>
        <!-- Default display -->
        <span class="default-notice label">
            ----- Guna fungsi penapis -----
        </span>
    <?php } ?>
</div>
<!-- JavaScript -->
<script>
    // General variable
    const listBtn = document.querySelectorAll("input[name='list']");
    const dateSearchContainer = document.querySelector('.date-search-container');
    const resetBtn = document.querySelector('button[class="reset-btn font-barlowcondensed"]');
    const logContainer = document.querySelector('.log-container');
    const statusInput = document.querySelector('input[name="status"]');
    // Date / Keyword filter
    const startInput = document.getElementById('startDate');
    const endInput = document.getElementById('endDate');
    const searchBar = document.querySelector('.search-bar');
    const noticeContainer = document.querySelector('.notice');
    const userList = document.querySelector('.appointment-list-container');
    const defaultNotice = document.querySelector('.default-notice');

    listBtn.forEach(radio => {
        radio.addEventListener('change', function () {
            const selectedList = this.value;

            if (selectedList === 'appointment') {
                dateSearchContainer.style.display = '';
                resetBtn.style.display = '';
                logContainer.style.display = 'none';
                statusInput.value = '';
                logContainer.submit();
            } else if (selectedList === 'log') {
                dateSearchContainer.style.display = 'none';
                resetBtn.style.display = 'none';
                if (userList) userList.style.display = 'none';
                if (defaultNotice) defaultNotice.style.display = 'none';
                if (noticeContainer) noticeContainer.style.display = 'none';
                logContainer.style.display = 'flex';
                statusInput.value = 'searching-log';
                logContainer.submit();
            }
        });
    });

    if (startInput && endInput) {
        startInput.addEventListener('change', (e) => {
            e.preventDefault();
            endInput.min = startInput.value;
        });

        endInput.addEventListener('change', (e) => {
            e.preventDefault();
            startInput.max = endInput.value;
        });
    }

    function parseRowDate(rawText) {
        if (!rawText) return '';
        const datePart = rawText.split(' ')[0].trim();
        if (datePart.includes('/')) {
            const parts = datePart.split('/');
            if (parts.length === 3 && parts[2].length === 4) {
                return `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
            }
        }
        return datePart;
    }
    function filterData() {
        const start = startInput.value;
        const end = endInput.value;
        const keyword = searchBar ? searchBar.value.trim().toLowerCase() : '';
        if (!userList) return;
        if (start === '' && end === '' && keyword === '') {
            userList.style.display = 'none';
            if (noticeContainer) noticeContainer.style.display = 'none';
            if (defaultNotice) defaultNotice.style.display = 'flex';
            return;
        }
        if (defaultNotice) defaultNotice.style.display = 'none';
        const userRows = userList.querySelectorAll(':scope > .data-label');
        let matchCount = 0;
        userRows.forEach(row => {
            const spans = row.querySelectorAll('span');
            if (spans.length < 7) return;
            const appointmentId = spans[0].textContent.trim().toLowerCase();
            const customerName = spans[1].textContent.trim().toLowerCase();
            const service = spans[2].textContent.trim().toLowerCase();
            const technicianName = spans[3].textContent.trim().toLowerCase();
            const brandModelName = spans[4].textContent.trim().toLowerCase();
            const rawDateText = spans[5].textContent.trim();
            const rowDate = parseRowDate(rawDateText);
            const rowStatus = spans[6].textContent.trim().toLowerCase();
            const isKeywordMatch = keyword === '' ||
                customerName.includes(keyword) ||
                appointmentId.includes(keyword) ||
                rowStatus.includes(keyword) ||
                service.includes(keyword) ||
                technicianName.includes(keyword) ||
                brandModelName.includes(keyword);
            let isDateMatch = true;
            if (rowDate) {
                if (start && rowDate < start) isDateMatch = false;
                if (end && rowDate > end) isDateMatch = false;
            }
            if (isKeywordMatch && isDateMatch) {
                row.style.display = 'grid';
                matchCount++;
            } else {
                row.style.display = 'none';
            }
        });
        if (matchCount > 0) {
            userList.style.display = 'grid';
            if (noticeContainer) noticeContainer.style.display = 'none';
        } else {
            userList.style.display = 'none';
            if (noticeContainer) noticeContainer.style.display = 'flex';
        }
    }

    function resetFilter() {
        startInput.value = '';
        endInput.value = '';
        startInput.removeAttribute('max');
        endInput.removeAttribute('min');
        if (searchBar) {
            searchBar.value = '';
        }
        filterData();
    }
    if (searchBar) {
        searchBar.addEventListener('input', filterData);
    }

    // Print function
    function printAppointments() {
        const container = document.querySelector('.appointment-list-container');
        if (!container) return;

        const visibleRows = Array.from(container.querySelectorAll(':scope > .data-label'))
            .filter(row => row.style.display !== 'none');

        if (visibleRows.length === 0) {
            alert('Tiada data untuk dicetak.');
            return;
        }

        let tableRowsHtml = '';
        visibleRows.forEach(row => {
            const spans = row.querySelectorAll('span');
            if (spans.length < 7) return;

            tableRowsHtml += `
                <tr>
                    <td>${spans[0].innerHTML}</td>
                    <td>${spans[1].innerHTML}</td>
                    <td>${spans[2].innerHTML}</td>
                    <td>${spans[3].innerHTML}</td>
                    <td>${spans[4].innerHTML}</td>
                    <td>${spans[5].innerHTML}</td>
                    <td>${spans[6].innerHTML}</td>
                </tr>
            `;
        });

        const printDate = new Date().toLocaleString('ms-MY');

        const iframe = document.createElement('iframe');
        iframe.style.position = 'fixed';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        document.body.appendChild(iframe);

        const doc = iframe.contentWindow.document;
        doc.open();
        doc.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Laporan JanjiTemu - SolusiTemu</title>
                <style>
                    @page {
                        size: A4 landscape;
                        margin: 12mm;
                    }
                    body {
                        font-family: Arial, Helvetica, sans-serif;
                        background: #ffffff !important;
                        color: #111111 !important;
                        margin: 0;
                        padding: 0;
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                    .report-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-end;
                        border-bottom: 2px solid #111111;
                        padding-bottom: 8px;
                        margin-bottom: 14px;
                    }
                    .report-header h2 {
                        margin: 0;
                        font-size: 18pt;
                        color: #000000;
                    }
                    .report-meta {
                        font-size: 9pt;
                        color: #444444;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        table-layout: fixed;
                        font-size: 9.5pt;
                    }
                    th {
                        background-color: #222222 !important;
                        color: #ffffff !important;
                        font-weight: bold;
                        text-align: left;
                        padding: 8px 6px;
                        border: 1px solid #222222;
                    }
                    td {
                        padding: 8px 6px;
                        border: 1px solid #cccccc;
                        color: #111111 !important;
                        vertical-align: top;
                        word-wrap: break-word;
                        overflow-wrap: break-word;
                    }
                    tbody tr:nth-child(even) {
                        background-color: #f5f5f5 !important;
                    }
                    tr {
                        break-inside: avoid;
                        page-break-inside: avoid;
                    }
                    td strong {
                        color: #9a7b1c !important;
                    }
                </style>
            </head>
            <body>
                <div class="report-header">
                    <h2>Laporan JanjiTemu SolusiTemu</h2>
                    <div class="report-meta">Tarikh Cetak: ${printDate} | Jumlah Rekod: ${visibleRows.length}</div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 7%;">No.</th>
                            <th style="width: 12%;">Pelanggan</th>
                            <th style="width: 22%;">Perkhidmatan</th>
                            <th style="width: 11%;">Juruteknik</th>
                            <th style="width: 18%;">Model</th>
                            <th style="width: 14%;">Tarikh & Masa</th>
                            <th style="width: 16%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${tableRowsHtml}
                    </tbody>
                </table>
            </body>
            </html>
        `);
        doc.close();

        iframe.contentWindow.focus();
        setTimeout(() => {
            iframe.contentWindow.print();
            document.body.removeChild(iframe);
        }, 250);
    }


</script>