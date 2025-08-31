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
    redirect('abschuesse.php');
}

// CSRF-Schutz
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    $_SESSION['error_message'] = 'Ungültiges oder fehlendes CSRF-Token.';
    redirect('abschuesse.php');
}

// Eingaben validieren
$revier = trim($_POST['revier'] ?? '');
$tierid = trim($_POST['tierid'] ?? '');
$bestehendestier = trim($_POST['bestehendestier'] ?? '');
$abschussdatum = trim($_POST['abschussdatum'] ?? '');
$abschusszeit = trim($_POST['abschusszeit'] ?? '');
$foto_url = trim($_POST['foto_url'] ?? '');
$schussdistanz = trim($_POST['schussdistanz'] ?? '');
$treffpunktlage = trim($_POST['treffpunktlage'] ?? '');
$fluchtstrecke = trim($_POST['fluchtstrecke'] ?? '');
$abschussbesonderheiten = trim($_POST['abschussbesonderheiten'] ?? '');

// Validiere Revier
if (empty($revier) || !validateId($revier, 'R-')) {
    $_SESSION['error_message'] = 'Bitte wählen Sie ein gültiges Revier aus.';
    redirect('abschuesse.php');
}

// Bestimme die Tier-ID (entweder bestehendes Tier oder neues Tier)
$finalTierID = !empty($bestehendestier) ? $bestehendestier : $tierid;

// Validiere Tier-ID
if (empty($finalTierID) || !validateId($finalTierID, 'T-')) {
    $_SESSION['error_message'] = 'Keine gültige Tier-ID vorhanden.';
    redirect('abschuesse.php');
}

// Validiere Datum und Zeit
if (empty($abschussdatum) || !validateDate($abschussdatum)) {
    $_SESSION['error_message'] = 'Bitte geben Sie ein gültiges Datum ein.';
    redirect('abschuesse.php');
}

if (empty($abschusszeit) || !validateTime($abschusszeit)) {
    $_SESSION['error_message'] = 'Bitte geben Sie eine gültige Zeit ein.';
    redirect('abschuesse.php');
}

// Validiere Foto-URL (falls vorhanden)
if (!empty($foto_url) && !validateUrl($foto_url)) {
    $_SESSION['error_message'] = 'Bitte geben Sie eine gültige Foto-URL ein.';
    redirect('abschuesse.php');
}

// Validiere Abschusszeitpunkt gegen letzte Sichtung
$letzterSichtungszeitpunkt = getLetzterSichtungszeitpunkt($finalTierID);
if ($letzterSichtungszeitpunkt !== null) {
    $abschussDateTime = new DateTime($abschussdatum . ' ' . $abschusszeit);
    $sichtungsDateTime = new DateTime($letzterSichtungszeitpunkt);
    
    if ($abschussDateTime < $sichtungsDateTime) {
        $_SESSION['error_message'] = 'Der Abschuss kann nicht vor der letzten Sichtung (' . $letzterSichtungszeitpunkt . ') stattgefunden haben!';
        redirect('abschuesse.php');
    }
}

// Validiere ob das Tier bereits abgeschossen wurde
if (istTierAbgeschossen($finalTierID)) {
    $_SESSION['error_message'] = 'Dieses Tier wurde bereits abgeschossen!';
    redirect('abschuesse.php');
}

// Bereinige Eingaben für CSV-Format
$abschussbesonderheiten = str_replace(',', ';', $abschussbesonderheiten);

// Generiere eine neue Abschuss-ID
$newId = generateId('A-', ABSCHUESSE_FILE);

// Speichere den neuen Abschuss
$newEntry = $newId . ',' . $revier . ',' . $finalTierID . ',' . $abschussdatum . ',' . $abschusszeit . ',' 
    . $foto_url . ',' . $schussdistanz . ',' . $treffpunktlage . ',' . $fluchtstrecke . ',' . $abschussbesonderheiten;

if (file_put_contents(ABSCHUESSE_FILE, $newEntry . PHP_EOL, FILE_APPEND)) {
    $_SESSION['success_message'] = 'Abschuss wurde erfolgreich gespeichert.';
} else {
    $_SESSION['error_message'] = 'Fehler beim Speichern des Abschusses.';
}

redirect('uebersicht.php');