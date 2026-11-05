<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (
    !isset($_SESSION['logged_in']) ||
    $_SESSION['logged_in'] !== true ||
    !isset($_SESSION['role']) ||
    ($_SESSION['role'] !== 'pelanggan' && $_SESSION['role'] !== 'juruteknik' && $_SESSION['role'] !== 'admin')
) {
    http_response_code(401);
    include($_SERVER['DOCUMENT_ROOT'] . '/SolusiTemu/pages/errors/401.html');
    exit;
}

$role = isset($_SESSION['role']) ? trim($_SESSION['role']) : 'pelanggan';
$pages = [
    'admin' => 'janjitemu_admin',
    'juruteknik' => 'janjitemu_juruteknik',
    'pelanggan' => 'janjitemu_pelanggan'
];
$targetPage = $pages[$role] ?? 'janjitemu_pelanggan';
?>
<div class="msg-container">
    <div class="logo-container">
        <div class="logo-image"></div>
        <span class="logo-text">Sistem JanjiTemu Syarikat SolusiTemu</span>
    </div>
    <h1 style="font-family: 'Oswald', sans-serif;letter-spacing: 0.05em;">Selamat datang ke SolusiTemu</h1>
    <?php if ($role === 'admin'): ?>
            <h3 style="width: 100%;word-break: break-word;">
                Panel kawalan pentadbir untuk mengurus pengguna, perkhidmatan, dan memantau prestasi sistem.
            </h3>
    <?php elseif ($role === 'juruteknik'): ?>
            <h3 style="width: 100%;word-break: break-word;">Hab tugasan anda untuk mengurus jadual, menjejak tempahan, dan
                meningkatkan prestasi kerja anda dengan mudah.</h3>
            </a>
    <?php else: ?>
            <h3 style="width: 100%;word-break: break-word;">
                Urus dan semak janji temu anda dengan mudah di sini.
            </h3>
    <?php endif; ?>
    <a href="dashboard.php?page=<?= $targetPage ?>" style="text-decoration: none">
        <button class="btn">Mula Sekarang</button>
    </a>
</div>