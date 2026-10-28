<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['role']) || $_SESSION['role'] !== 'pelanggan') {
    http_response_code(401);
    include($_SERVER['DOCUMENT_ROOT'] . '/SolusiTemu/pages/errors/401.html');
    exit;
}
?>
<div class="msg-container">
    <div class="logo-container">
        <div class="logo-image"></div>
        <span class="logo-text">Syarikat SolusiTemu</span>
    </div>
    <h1 style="font-family: 'Oswald', sans-serif;letter-spacing: 0.05em;">Selamat datang ke SolusiTemu</h1>
    <h3 style="width: 100%;word-break: break-word;">Urus dan semak janji temu anda dengan mudah di sini.</h3>
    <a href="dashboard.php?page=janjitemu_pelanggan" style="text-decoration: none">
        <button class="btn">Mula Sekarang</button>
    </a>
</div>