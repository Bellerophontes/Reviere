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
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle ?? 'Wildtiermanagement') ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <img src="logo.png" alt="Wildtiermanagement Logo" class="logo">
        <h1><?= h($pageTitle ?? 'Wildtiermanagement') ?></h1>
    </header>
    
    <nav>
        <ul>
            <li><a href="index.html" <?= (basename($_SERVER['PHP_SELF']) == 'index.html') ? 'aria-current="page"' : '' ?>>Startseite</a></li>
            <li><a href="reviere.php" <?= (basename($_SERVER['PHP_SELF']) == 'reviere.php') ? 'aria-current="page"' : '' ?>>Reviere verwalten</a></li>
            <li><a href="sichtungen.php" <?= (basename($_SERVER['PHP_SELF']) == 'sichtungen.php') ? 'aria-current="page"' : '' ?>>Sichtung erfassen</a></li>
            <li><a href="abschuesse.php" <?= (basename($_SERVER['PHP_SELF']) == 'abschuesse.php') ? 'aria-current="page"' : '' ?>>Abschuss erfassen</a></li>
            <li><a href="uebersicht.php" <?= (basename($_SERVER['PHP_SELF']) == 'uebersicht.php') ? 'aria-current="page"' : '' ?>>Übersicht</a></li>
        </ul>
    </nav>

    <main>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-message message-dismissible">
                <?= h($_SESSION['success_message']) ?>
                <button type="button" class="close-btn" onclick="this.parentElement.style.display='none'">&times;</button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="error-message message-dismissible">
                <?= h($_SESSION['error_message']) ?>
                <button type="button" class="close-btn" onclick="this.parentElement.style.display='none'">&times;</button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['warning_message'])): ?>
            <div class="warning-message message-dismissible">
                <?= h($_SESSION['warning_message']) ?>
                <button type="button" class="close-btn" onclick="this.parentElement.style.display='none'">&times;</button>
            </div>
            <?php unset($_SESSION['warning_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['info_message'])): ?>
            <div class="info-message message-dismissible">
                <?= h($_SESSION['info_message']) ?>
                <button type="button" class="close-btn" onclick="this.parentElement.style.display='none'">&times;</button>
            </div>
            <?php unset($_SESSION['info_message']); ?>
        <?php endif; ?>