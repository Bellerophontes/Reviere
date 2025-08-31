<?php
$tiereFile = 'tiere.txt';
$sichtungenFile = 'sichtungen.txt';
$abschuesseFile = 'abschuesse.txt';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $revier = $_POST['revier'];
    $tierart = $_POST['tierart'];
    $tiergeschlecht = $_POST['tiergeschlecht'];
    $tieralter = $_POST['tieralter'];
    $tierbesonderheiten = $_POST['tierbesonderheiten'];
    
    // Generiere eine neue Tier-ID
    $lastId = 0;
    if (file_exists($tiereFile)) {
        $lines = file($tiereFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $parts = explode(',', $line);
            if (count($parts) >= 1 && strpos($parts[0], 'T-') === 0) {
                $idNum = (int) substr($parts[0], 2);
                if ($idNum > $lastId) {
                    $lastId = $idNum;
                }
            }
        }
    }
    
    $newTierId = 'T-' . str_pad($lastId + 1, 3, '0', STR_PAD_LEFT);
    
    // Speichere das neue Tier
    $tierEntry = $newTierId . ',' . $revier . ',' . $tierart . ',' . $tiergeschlecht . ',' . $tieralter . ',' . $tierbesonderheiten;
    file_put_contents($tiereFile, $tierEntry . PHP_EOL, FILE_APPEND);
    
    // Gebe die neue Tier-ID als JSON zurück
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'tierID' => $newTierId, 'tierName' => $tierart . ' (' . $tiergeschlecht . ', ' . $tieralter . ')']);
    exit;
} else {
    // Lade bestehende Tiere für ein Revier
    $revier = $_GET['revier'] ?? '';
    $type = $_GET['type'] ?? 'all'; // 'all', 'lebend', 'abschuss'
    $tiere = [];
    
    if (file_exists($tiereFile) && !empty($revier)) {
        // Lade alle abgeschossenen Tiere
        $abgeschossen = [];
        if (file_exists($abschuesseFile)) {
            $abschuesse = file($abschuesseFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($abschuesse as $abschuss) {
                $parts = explode(',', $abschuss);
                if (count($parts) >= 3) {
                    $abgeschossen[trim($parts[2])] = true; // Tier-ID ist in Spalte 2
                }
            }
        }
        
        // Lade Sichtungen für Datum-Validierung
        $letzterSichtungen = [];
        if (file_exists($sichtungenFile)) {
            $sichtungen = file($sichtungenFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($sichtungen as $sichtung) {
                $parts = explode(',', $sichtung);
                if (count($parts) >= 5) {
                    $tierID = trim($parts[2]);
                    $datum = trim($parts[3]);
                    $zeit = trim($parts[4]);
                    $datetime = $datum . ' ' . $zeit;
                    
                    if (!isset($letzterSichtungen[$tierID]) || $datetime > $letzterSichtungen[$tierID]) {
                        $letzterSichtungen[$tierID] = $datetime;
                    }
                }
            }
        }
        
        $lines = file($tiereFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $parts = explode(',', $line);
            if (count($parts) >= 6 && trim($parts[1]) === $revier) {
                $tierID = trim($parts[0]);
                $istAbgeschossen = isset($abgeschossen[$tierID]);
                
                // Filter basierend auf type
                if ($type === 'lebend' && $istAbgeschossen) {
                    continue; // Überspringe abgeschossene Tiere
                } else if ($type === 'abschuss' && $istAbgeschossen) {
                    continue; // Überspringe bereits abgeschossene Tiere
                }
                
                $tier = [
                    'id' => $tierID,
                    'revier' => trim($parts[1]),
                    'art' => trim($parts[2]),
                    'geschlecht' => trim($parts[3]),
                    'alter' => trim($parts[4]),
                    'besonderheiten' => trim($parts[5]),
                    'display' => trim($parts[2]) . ' (' . trim($parts[3]) . ', ' . trim($parts[4]) . ')',
                    'abgeschossen' => $istAbgeschossen,
                    'letzte_sichtung' => isset($letzterSichtungen[$tierID]) ? $letzterSichtungen[$tierID] : null
                ];
                
                $tiere[] = $tier;
            }
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($tiere);
    exit;
}
?>