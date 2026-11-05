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

$roleMenus = [
    'admin' => [
        'title' => 'Pentadbir tertinggi,',
        'pages' => [
            'janjitemu_admin' => ['label' => 'Janjitemu', 'file' => 'janjitemu_admin.php'],
            'perkhidmatan_admin' => ['label' => 'Perkhidmatan', 'file' => 'perkhidmatan_admin.php'],
            'pengguna_admin' => ['label' => 'Pengguna', 'file' => 'pengguna_admin.php'],
            'laporan_admin' => ['label' => 'Laporan', 'file' => 'laporan_admin.php'],
            'import_admin' => ['label' => 'Import', 'file' => 'import_admin.php'],
        ]
    ],
    'juruteknik' => [
        'title' => 'Pakar teknikal teras,',
        'pages' => [
            'janjitemu_juruteknik' => ['label' => 'Janjitemu', 'file' => 'janjitemu_juruteknik.php'],
            'perkhidmatan_pengguna' => ['label' => 'Perkhidmatan', 'file' => 'perkhidmatan_pengguna.php'],
            'profil' => ['label' => 'Profil', 'file' => 'profil.php'],
        ]
    ],
    'pelanggan' => [
        'title' => 'Pengguna yang dihormati,',
        'pages' => [
            'janjitemu_pelanggan' => ['label' => 'Janjitemu', 'file' => 'janjitemu_pelanggan.php'],
            'perkhidmatan_pengguna' => ['label' => 'Perkhidmatan', 'file' => 'perkhidmatan_pengguna.php'],
            'juruteknik' => ['label' => 'Juruteknik', 'file' => 'juruteknik.php'],
            'profil' => ['label' => 'Profil', 'file' => 'profil.php'],
        ]
    ]
];

$currentRole = $roleMenus[$role] ?? $roleMenus['pelanggan'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../public/global.css">
    <link rel="stylesheet" href="../public/css/dashboard.css">
    <link rel="stylesheet" href="../public/css/home.css">
    <link rel="stylesheet" href="../public/css/profile.css">
</head>

<body>
    <nav class="sidebar-container">
        <div class="logo-container">
            <div class="logo-image"></div>
            <div class="logo-text-wrapper">
                <span class="logo-text">Sistem JanjiTemu</span>
                <span class="logo-text">Syarikat SolusiTemu</span>
            </div>
        </div>

        <h2 class="font-oswald">
            <?= htmlspecialchars($currentRole['title']) ?>
        </h2>
        <h2 class="font-oswald">
            <?= htmlspecialchars($_SESSION['name']) ?>
        </h2>
        <div class="sidebar-vertical-btn-group">
            <?php foreach ($currentRole['pages'] as $pageKey => $menuItem): ?>
                <a href="dashboard.php?page=<?= $pageKey ?>" class="sidebar-vertical-btn" style="text-decoration: none;">
                    <span><?= htmlspecialchars($menuItem['label']) ?></span>
                </a>
            <?php endforeach; ?>

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
        if (array_key_exists($page, $currentRole['pages'])) {
            $fileToInclude = $currentRole['pages'][$page]['file'];
            require_once __DIR__ . "/sub_pages/{$fileToInclude}";
        } else {
            require_once __DIR__ . '/sub_pages/home.php';
        }
        ?>
    </main>
    <script>
    </script>
</body>