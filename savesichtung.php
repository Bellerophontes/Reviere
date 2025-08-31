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
    redirect('sichtungen.php');
}

// CSRF-Schutz
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    $_SESSION['error_message'] = 'Ungültiges oder fehlendes CSRF-Token.';
    redirect('sichtungen.php');
}

// Eingaben validieren
$revier = trim($_POST['revier'] ?? '');
$tierid = trim($_POST['tierid'] ?? '');
$bestehendestier = trim($_POST['bestehendestier'] ?? '');
$sichtungsdatum = trim($_POST['sichtungsdatum'] ?? '');
$sichtungszeit = trim($_POST['sichtungszeit'] ?? '');
$foto_url = trim($_POST['foto_url'] ?? '');
$sichtungsbesonderheiten = trim($_POST['sichtungsbesonderheiten'] ?? '');

// Validiere Revier
if (empty($revier) || !validateId($revier, 'R-')) {
    $_SESSION['error_message'] = 'Bitte wählen Sie ein gültiges Revier aus.';
    redirect('sichtungen.php');
}

// Bestimme die Tier-ID (entweder bestehendes Tier oder neues Tier)
$finalTierID = !empty($bestehendestier) ? $bestehendestier : $tierid;

// Validiere Tier-ID
if (empty($finalTierID) || !validateId($finalTierID, 'T-')) {
    $_SESSION['error_message'] = 'Keine gültige Tier-ID vorhanden.';
    redirect('sichtungen.php');
}

// Validiere Datum und Zeit
if (empty($sichtungsdatum) || !validateDate($sichtungsdatum)) {
    $_SESSION['error_message'] = 'Bitte geben Sie ein gültiges Datum ein.';
    redirect('sichtungen.php');
}

if (empty($sichtungszeit) || !validateTime($sichtungszeit)) {
    $_SESSION['error_message'] = 'Bitte geben Sie eine gültige Zeit ein.';
    redirect('sichtungen.php');
}

// Validiere Foto-URL (falls vorhanden)
if (!empty($foto_url) && !validateUrl($foto_url)) {
    $_SESSION['error_message'] = 'Bitte geben Sie eine gültige Foto-URL ein.';
    redirect('sichtungen.php');
}

// Bereinige Besonderheiten für CSV-Format
$sichtungsbesonderheiten = str_replace(',', ';', $sichtungsbesonderheiten);

// Generiere eine neue Sichtungs-ID
$newSichtungId = generateId('S-', SICHTUNGEN_FILE);

// Speichere die neue Sichtung
$sichtungEntry = $newSichtungId . ',' . $revier . ',' . $finalTierID . ',' . $sichtungsdatum . ',' . $sichtungszeit . ',' . $foto_url . ',' . $sichtungsbesonderheiten;

if (file_put_contents(SICHTUNGEN_FILE, $sichtungEntry . PHP_EOL, FILE_APPEND)) {
    $_SESSION['success_message'] = 'Sichtung wurde erfolgreich gespeichert.';
} else {
    $_SESSION['error_message'] = 'Fehler beim Speichern der Sichtung.';
}

redirect('uebersicht.php');