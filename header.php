<?php
define('SECURE_ACCESS', true);
require_once 'functions.php';

// Starte die Session für CSRF-Schutz
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sicherheits-Header setzen
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: same-origin');
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Wildtiermanagement-System zur professionellen Verwaltung von Jagdrevieren und strukturierten Erfassung von Wildtiersichtungen und Abschüssen">
    <title><?= h($pageTitle ?? 'Wildtiermanagement') ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <img src="logo.png" alt="Wildtiermanagement Logo" class="logo">
        <h1><?= h($pageTitle ?? 'Wildtiermanagement') ?></h1>
    </header>
    
    <nav>
        <button class="nav-toggle" aria-expanded="false" aria-label="Menü öffnen">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <ul>
            <li><a href="index.html" <?= (basename($_SERVER['PHP_SELF']) == 'index.html') ? 'aria-current="page"' : '' ?>>Startseite</a></li>
            <li><a href="reviere.php" <?= (basename($_SERVER['PHP_SELF']) == 'reviere.php') ? 'aria-current="page"' : '' ?>>Reviere verwalten</a></li>
            <li><a href="sichtungen.php" <?= (basename($_SERVER['PHP_SELF']) == 'sichtungen.php') ? 'aria-current="page"' : '' ?>>Sichtung erfassen</a></li>
            <li><a href="abschuesse.php" <?= (basename($_SERVER['PHP_SELF']) == 'abschuesse.php') ? 'aria-current="page"' : '' ?>>Abschuss erfassen</a></li>
            <li><a href="uebersicht.php" <?= (basename($_SERVER['PHP_SELF']) == 'uebersicht.php') ? 'aria-current="page"' : '' ?>>Übersicht</a></li>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" aria-haspopup="true" aria-expanded="false">Werkzeuge</a>
                <ul class="dropdown-menu" aria-label="Untermenü">
                    <li><a href="export.php" <?= (basename($_SERVER['PHP_SELF']) == 'export.php') ? 'aria-current="page"' : '' ?>>Daten exportieren</a></li>
                    <li><a href="backup.php" <?= (basename($_SERVER['PHP_SELF']) == 'backup.php') ? 'aria-current="page"' : '' ?>>Backup erstellen</a></li>
                </ul>
            </li>
        </ul>
    </nav>

    <main>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-message message-dismissible">
                <?= h($_SESSION['success_message']) ?>
                <button type="button" class="close-btn" onclick="this.parentElement.style.display='none'" aria-label="Schließen">&times;</button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="error-message message-dismissible">
                <?= h($_SESSION['error_message']) ?>
                <button type="button" class="close-btn" onclick="this.parentElement.style.display='none'" aria-label="Schließen">&times;</button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['warning_message'])): ?>
            <div class="warning-message message-dismissible">
                <?= h($_SESSION['warning_message']) ?>
                <button type="button" class="close-btn" onclick="this.parentElement.style.display='none'" aria-label="Schließen">&times;</button>
            </div>
            <?php unset($_SESSION['warning_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['info_message'])): ?>
            <div class="info-message message-dismissible">
                <?= h($_SESSION['info_message']) ?>
                <button type="button" class="close-btn" onclick="this.parentElement.style.display='none'" aria-label="Schließen">&times;</button>
            </div>
            <?php unset($_SESSION['info_message']); ?>
        <?php endif; ?>