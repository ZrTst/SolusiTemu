<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    //For someone who haven't login and unauthorized 
    http_response_code(401);
    //Pull the page 401.html without changing URL
    include($_SERVER['DOCUMENT_ROOT'] . '/SolusiTemu/pages/errors/401.html');
    exit;
}
require_once __DIR__ . '/../private_core/config.php';
require_once __DIR__ . '/../private_core/functions.php';

$role = $_SESSION['role'] ?? 'pelanggan';
$page = $_GET['page'] ?? 'home';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../public/global.css">
    <link rel="stylesheet" href="../public/css/dashboard.css">
</head>

<body>
    <nav class="sidebar-container">
        <div class="logo-container">
            <div class="logo-image"></div>
            <div class="logo-text-wrapper">
                <span class="logo-text">Syarikat</span>
                <span class="logo-text">SolusiTemu</span>
            </div>
        </div>

        <h2 class="font-oswald">Pengguna yang dihormati,</h2>
        <h2 class="font-oswald">
            <?= htmlspecialchars($_SESSION['name']) ?>
        </h2>
        <div class="sidebar-vertical-btn-group">
            <?php if ($role === 'admin'): ?>
                <a href="dashboard.php?page=janjitemu_admin" class="sidebar-vertical-btn" style="text-decoration: none;">
                    <span>Janjitemu</span></a>
                <a href="dashboard.php?page=perkhidmatan_admin" class="sidebar-vertical-btn" style="text-decoration: none;">
                    <span>Perkhidmatan</span></a>
                <a href="dashboard.php?page=pengguna_admin" class="sidebar-vertical-btn" style="text-decoration: none;">
                    <span>Pengguna</span></a>
                <a href="dashboard.php?page=laporan_admin" class="sidebar-vertical-btn" style="text-decoration: none;">
                    <span>Laporan</span></a>
                <a href="dashboard.php?page=import_admin" class="sidebar-vertical-btn" style="text-decoration: none;">
                    <span>Import</span></a>
            <?php elseif ($role === 'juruteknik'): ?>
                <a href="dashboard.php?page=janjitemu_juruteknik" class="sidebar-vertical-btn"
                    style="text-decoration: none;">
                    <span>Janjitemu</span></a>
                <a href="dashboard.php?page=perkhidmatan_pengguna" class="sidebar-vertical-btn"
                    style="text-decoration: none;">
                    <span>Perkhidmatan</span></a>
                <a href="dashboard.php?page=profil" class="sidebar-vertical-btn" style="text-decoration: none;">
                    <span>Profil</span></a>
            <?php else: ?>
                <a href="dashboard.php?page=janjitemu_pelanggan" class="sidebar-vertical-btn"
                    style="text-decoration: none;">
                    <span>Janjitemu</span></a>
                <a href="dashboard.php?page=perkhidmatan_pengguna" class="sidebar-vertical-btn"
                    style="text-decoration: none;">
                    <span>Perkhidmatan</span></a>
                <a href="dashboard.php?page=juruteknik" class="sidebar-vertical-btn" style="text-decoration: none;">
                    <span>Juruteknik</span></a>
                <a href="dashboard.php?page=profil" class="sidebar-vertical-btn" style="text-decoration: none;">
                    <span>Profil</span></a>
            <?php endif; ?>
            <a href="logout.php" style="text-decoration: none">
                <button id="logout-btn" class="logout-btn">Log Keluar
                    <svg class="icons">
                        <use href="../public/icons.svg#logout-icon"></use>
                    </svg>
                </button>
            </a>
        </div>
    </nav>
    <main class="main-content">
        <?php
        if ($role === 'admin'):
            switch ($page) {
                case 'janjitemu_admin':
                    require_once __DIR__ . '/sub_pages/janjitemu_admin.php';
                    break;
                case 'perkhidmatan_admin':
                    require_once __DIR__ . '/sub_pages/perkhidmatan_admin.php';
                    break;
                case 'pengguna_admin':
                    require_once __DIR__ . '/sub_pages/pengguna_admin.php';
                    break;
                case 'laporan_admin':
                    require_once __DIR__ . '/sub_pages/laporan_admin.php';
                    break;
                case 'import_admin':
                    require_once __DIR__ . '/sub_pages/import_admin.php';
                    break;
                default:
                    require_once __DIR__ . '/sub_pages/home.php';
                    break;
            }
        elseif ($role === 'juruteknik'):
            switch ($page) {
                case 'janjitemu_juruteknik':
                    require_once __DIR__ . '/sub_pages/janjitemu_juruteknik.php';
                    break;
                case 'perkhidmatan_pengguna':
                    require_once __DIR__ . '/sub_pages/perkhidmatan_pengguna.php';
                    break;
                case 'profil':
                    require_once __DIR__ . '/sub_pages/profil.php';
                    break;
                default:
                    require_once __DIR__ . '/sub_pages/home.php';
                    break;
            }
        else:
            switch ($page) {
                case 'janjitemu_pelanggan':
                    require_once __DIR__ . '/sub_pages/janjitemu_pelanggan.php';
                    break;
                case 'perkhidmatan_pengguna':
                    require_once __DIR__ . '/sub_pages/perkhidmatan_pengguna.php';
                    break;
                case 'juruteknik':
                    require_once __DIR__ . '/sub_pages/juruteknik.php';
                    break;
                case 'profil':
                    require_once __DIR__ . '/sub_pages/profil.php';
                    break;
                default:
                    require_once __DIR__ . '/sub_pages/home.php';
                    break;
            }
        endif;
        ?>
    </main>
    <script>
    </script>
</body>