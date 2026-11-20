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
?>
<link rel="stylesheet" href="../public/css/import.css">
<form class="import-container" action="api/import_data.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="status" value="importing">
    <!-- Header area -->
    <div class="banner-container">
        <h1 class="font-barlowcondensed" style="margin: 0px;">Import Data</h1>
        <svg class="icons">
            <use href="../public/icons.svg#import-icon"></use>
        </svg>
    </div>
    <!-- Switch customers or staffs -->
    <div class="radio-group font-barlowcondensed">
        <label class="radio-pill">
            <input type="radio" name="user" value="customer" checked>
            <span>Pelanggan</span>
        </label>
        <label class="radio-pill">
            <input type="radio" name="user" value="staff">
            <span>Staf</span>
        </label>
    </div>
    <!-- Import area -->
    <input type="file" name="file" id="file-upload" class="hidden-input" accept=".csv, .txt">
    <label class="drop-zone" for="file-upload">
        <svg class="icons">
            <use href="../public/icons.svg#file-icon"></use>
        </svg>
        <span class="label">Letak file anda di sini untuk memuatnaik data</span>
        <span>atau</span>
        <span class="label input">pilih fail anda</span>
        <span class="label">Menyokong fail <span class="file">.csv</span> dan <span class="file">.txt</span></span>
    </label>
    <!-- File Name -->
    <div class="notice">
        <svg class="icons" style="display: none; cursor: pointer;" onclick="clearFile()">
            <use href="../public/icons.svg#trash-icon"></use>
        </svg>
        <h3>Belum ada fail dipilih</h3>
        <h2 style="display: none;white-space: pre-line;" id="file-name"></h2>
    </div>
    <button type="submit" class="submit-btn" id="submitBtn" disabled>Import</button>
</form>

<!-- JavaScript -->
<script>
    const dropZone = document.querySelector('.drop-zone');
    const fileInput = document.getElementById('file-upload');
    const noFileNotice = document.querySelector('.notice h3');
    const trashBin = document.querySelector('.notice svg');
    const fileNameDisplay = document.getElementById('file-name');
    const submitBtn = document.getElementById('submitBtn');

    function updateFileDisplay(files) {
        if (files && files.length > 0) {
            const file = files[0];
            let formattedSize = '';
            if (file.size < 1024 * 1024) {
                formattedSize = (file.size / 1024).toFixed(2) + ' KB';
            } else {
                formattedSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
            }
            noFileNotice.style.display = 'none';
            trashBin.style.display = 'flex';
            fileNameDisplay.style.display = 'flex';
            fileNameDisplay.textContent = `Sudah memilih: ${file.name}\nSaiz Fail: ${formattedSize}`;
            submitBtn.disabled = false;
        }
    }

    fileInput.addEventListener('change', function () {
        if (this.files.length > 0) {
            updateFileDisplay(this.files);
        } else {
            clearFile();
        }
    });

    function clearFile() {
        fileInput.value = '';
        trashBin.style.display = 'none';
        fileNameDisplay.style.display = 'none';
        fileNameDisplay.textContent = '';
        noFileNotice.style.display = 'block';
        submitBtn.disabled = true;
    }

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        document.body.addEventListener(eventName, e => e.preventDefault());
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.add('is-dragover');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.remove('is-dragover');
        });
    });

    dropZone.addEventListener('drop', function (e) {
        const droppedFiles = e.dataTransfer.files;
        if (droppedFiles && droppedFiles.length > 0) {
            fileInput.files = droppedFiles;
            updateFileDisplay(droppedFiles);
        }
    });

    const importForm = document.querySelector('.import-container');

    importForm.addEventListener('submit', function () {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Sedang memproses...';
    });
</script>