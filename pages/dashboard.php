<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
require_once __DIR__ . '/../private_core/config.php';
require_once __DIR__ . '/../private_core/functions.php';


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../public/global.css">
</head>

<body>
    <nav class="sidebar-container">
        <div class="logo-container">
            <div class="logo-image"></div>
            <div class="logo-text-wrapper">
                <span class="logo-text">Syarikat</span>
                <span class="logo-text">SolusiTemu</span>
            </div>
            <svg class="icons" id="icon-menu">
                <use href="../public/icons.svg#icon-menu-active"></use>
            </svg>
        </div>
        <h2 class="font-oswald">Pengguna yang dihormati,</h2>
        <h2 class="font-oswald">
            <?= htmlspecialchars($_SESSION['nama']) ?>
        </h2>
        <div class="sidebar-vertical-btn-group">
            <button class="sidebar-vertical-btn"><span class="btn-text">Janjitemu</span></button>
            <button class="sidebar-vertical-btn"><span class="btn-text">Perkhidmatan</span></button>
            <button class="sidebar-vertical-btn"><span class="btn-text">Juruteknik</span></button>
            <button class="sidebar-vertical-btn"><span class="btn-text">Profil</span></button>
            <a href="logout.php" style="text-decoration: none">
                <button id="logout-btn" class="logout-btn">Log Keluar
                    <svg class="icons">
                        <use href="../public/icons.svg#logout-icon"></use>
                    </svg>
                </button>
            </a>
        </div>
    </nav>
    <script>
        //Sidebar functions collapsed
        const menuIcon = document.getElementById('icon-menu');
        const useTag = menuIcon.querySelector('use'); 
        const sidebarContainer = document.querySelector('.sidebar-container');
        const sidebarHeader = sidebarContainer.querySelector('h2');
        const sidebarButtons = document.querySelectorAll('.sidebar-vertical-btn');

        menuIcon.addEventListener('click', function() {
            const currentHref = useTag.getAttribute('href');
            if (currentHref === '../public/icons.svg#icon-menu-active'){
                sidebarContainer.classList.toggle("collapsed");
                useTag.setAttribute('href', '../public/icons.svg#icon-menu-inactive');
            } else {
                useTag.setAttribute('href', '../public/icons.svg#icon-menu-active');
                sidebarContainer.classList.remove("collapsed");
            }
        });

    </script>
</body>