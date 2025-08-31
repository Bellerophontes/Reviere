<?php
define('SECURE_ACCESS', true);
require_once 'functions.php';

header('Content-Type: application/json');

if (isPostRequest()) {
    // Validiere Eingaben
    $revier = $_POST['revier'] ?? '';
    $tierart = $_POST['tierart'] ?? '';
    $tiergeschlecht = $_POST['tiergeschlecht'] ?? '';
    $tieralter = $_POST['tieralter'] ?? '';
    $tierbesonderheiten = $_POST['tierbesonderheiten'] ?? '';
    
    // Einfache Validierung
    if (empty($revier) || !validateId($revier, 'R-')) {
        echo json_encode(['success' => false, 'message' => 'Ungültige Revier-ID']);
        exit;
    }
    
    if (empty($tierart) || !in_array($tierart, getTierarten())) {
        echo json_encode(['success' => false, 'message' => 'Ungültige Tierart']);
        exit;
    }
    
    // Speichere das neue Tier
    $newTierId = generateId('T-', TIERE_FILE);
    
    // Sanitize und bereinige Eingaben
    $tierbesonderheiten = str_replace(',', ';', $tierbesonderheiten); // Kommas ersetzen um CSV-Format zu erhalten
    
    // Speichere das neue Tier
    $tierEntry = $newTierId . ',' . $revier . ',' . $tierart . ',' . $tiergeschlecht . ',' . $tieralter . ',' . $tierbesonderheiten;
    file_put_contents(TIERE_FILE, $tierEntry . PHP_EOL, FILE_APPEND);
    
    // Gebe die neue Tier-ID als JSON zurück
    echo json_encode([
        'success' => true, 
        'tierID' => $newTierId, 
        'tierName' => $tierart . ' (' . $tiergeschlecht . ', ' . $tieralter . ')'
    ]);
    exit;
} else {
    // Lade bestehende Tiere für ein Revier
    $revier = $_GET['revier'] ?? '';
    $type = $_GET['type'] ?? 'all'; // 'all', 'lebend', 'abschuss'
    
    // Validiere Revier-ID
    if (empty($revier) || !validateId($revier, 'R-')) {
        echo json_encode([]);
        exit;
    }
    
    // Lade alle Tiere für das Revier
    $tiere = loadTiere($revier);
    
    // Lade alle abgeschossenen Tiere
    $abgeschossen = [];
    $abschuesse = loadAbschuesse();
    
    foreach ($abschuesse as $abschuss) {
        $abgeschossen[$abschuss['tier']] = true;
    }
    
    // Lade letzte Sichtungen für jedes Tier
    $letzterSichtungen = [];
    $sichtungen = loadSichtungen();
    
    foreach ($sichtungen as $sichtung) {
        $tierID = $sichtung['tier'];
        $zeitpunkt = $sichtung['datum'] . ' ' . $sichtung['zeit'];
        
        if (!isset($letzterSichtungen[$tierID]) || $zeitpunkt > $letzterSichtungen[$tierID]) {
            $letzterSichtungen[$tierID] = $zeitpunkt;
        }
    }
    
    // Ergebnis-Array vorbereiten
    $result = [];
    
    foreach ($tiere as $tierID => $tier) {
        $istAbgeschossen = isset($abgeschossen[$tierID]);
        
        // Filter basierend auf type
        if ($type === 'lebend' && $istAbgeschossen) {
            continue; // Überspringe abgeschossene Tiere
        } else if ($type === 'abschuss' && $istAbgeschossen) {
            continue; // Überspringe bereits abgeschossene Tiere
        }
        
        // Füge Tier zum Ergebnis hinzu
        $result[] = [
            'id' => $tierID,
            'revier' => $tier['revier'],
            'art' => $tier['art'],
            'geschlecht' => $tier['geschlecht'],
            'alter' => $tier['alter'],
            'besonderheiten' => $tier['besonderheiten'],
            'display' => $tier['display'],
            'abgeschossen' => $istAbgeschossen,
            'letzte_sichtung' => isset($letzterSichtungen[$tierID]) ? $letzterSichtungen[$tierID] : null
        ];
    }
    
    echo json_encode($result);
    exit;
}