<?php
define('SECURE_ACCESS', true);
require_once 'functions.php';

// Starte die Session für CSRF-Schutz
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Nur POST-Anfragen erlauben
if (!isPostRequest()) {
    $_SESSION['error_message'] = 'Nur POST-Anfragen sind erlaubt.';
    redirect('reviere.php');
}

// CSRF-Schutz
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    $_SESSION['error_message'] = 'Ungültiges oder fehlendes CSRF-Token.';
    redirect('reviere.php');
}

// Eingabevalidierung
$name = trim($_POST['name'] ?? '');
$gemeinde = trim($_POST['gemeinde'] ?? '');
$wildraum = trim($_POST['wildraum'] ?? '');
$karten_url = trim($_POST['karten_url'] ?? '');

// Validiere Pflichtfelder
if (empty($name) || empty($gemeinde) || empty($wildraum)) {
    $_SESSION['error_message'] = 'Bitte füllen Sie alle Pflichtfelder aus.';
    redirect('reviere.php');
}

// Validiere Wildraum
if (!is_numeric($wildraum) || $wildraum < 1 || $wildraum > 17) {
    $_SESSION['error_message'] = 'Der Wildraum muss zwischen 1 und 17 liegen.';
    redirect('reviere.php');
}

// Validiere Karten-URL (falls vorhanden)
if (!empty($karten_url) && !validateUrl($karten_url)) {
    $_SESSION['error_message'] = 'Bitte geben Sie eine gültige URL für die Karte ein.';
    redirect('reviere.php');
}

// Generiere eine neue ID
$newId = generateId('R-', REVIERE_FILE);

// Bereinige Eingaben für CSV-Format
$name = str_replace(',', ';', $name);
$gemeinde = str_replace(',', ';', $gemeinde);

// Speichere das neue Revier im CSV-Format
$newEntry = $newId . ',' . $name . ',' . $gemeinde . ',' . $wildraum . ',' . $karten_url;

// Stelle sicher, dass die Datei mit einem Zeilenendezeichen endet
if (file_exists(REVIERE_FILE)) {
    $currentContent = file_get_contents(REVIERE_FILE);
    if (!empty($currentContent) && !preg_match('/[\r\n]$/', $currentContent)) {
        file_put_contents(REVIERE_FILE, $currentContent . PHP_EOL);
    }
}

// Füge den neuen Eintrag hinzu
if (file_put_contents(REVIERE_FILE, $newEntry . PHP_EOL, FILE_APPEND | LOCK_EX)) {
    $_SESSION['success_message'] = 'Revier wurde erfolgreich gespeichert.';
} else {
    $_SESSION['error_message'] = 'Fehler beim Speichern des Reviers.';
}

redirect('reviere.php');