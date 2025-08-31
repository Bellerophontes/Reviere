<?php
$sichtungenFile = 'sichtungen.txt';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $revier = $_POST['revier'];
    $tierid = $_POST['tierid'];
    $bestehendestier = $_POST['bestehendestier'];
    $sichtungsdatum = $_POST['sichtungsdatum'];
    $sichtungszeit = $_POST['sichtungszeit'];
    $foto_url = $_POST['foto_url'] ?? '';
    $sichtungsbesonderheiten = $_POST['sichtungsbesonderheiten'];
    
    // Bestimme die Tier-ID (entweder bestehendes Tier oder neues Tier)
    $finalTierID = !empty($bestehendestier) ? $bestehendestier : $tierid;
    
    if (empty($finalTierID)) {
        // Fehler: Keine Tier-ID vorhanden
        header('Location: sichtungen.php?error=no_tier');
        exit;
    }
    
    // Generiere eine neue Sichtungs-ID
    $lastSichtungId = 0;
    if (file_exists($sichtungenFile)) {
        $lines = file($sichtungenFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $parts = explode(',', $line);
            if (count($parts) >= 1 && strpos($parts[0], 'S-') === 0) {
                $idNum = (int) substr($parts[0], 2);
                if ($idNum > $lastSichtungId) {
                    $lastSichtungId = $idNum;
                }
            }
        }
    }
    
    $newSichtungId = 'S-' . str_pad($lastSichtungId + 1, 3, '0', STR_PAD_LEFT);
    
    // Speichere die neue Sichtung (referenziert auf Tier-ID, mit Foto-URL)
    $sichtungEntry = $newSichtungId . ',' . $revier . ',' . $finalTierID . ',' . $sichtungsdatum . ',' . $sichtungszeit . ',' . $foto_url . ',' . $sichtungsbesonderheiten;
    
    file_put_contents($sichtungenFile, $sichtungEntry . PHP_EOL, FILE_APPEND);
    
    header('Location: uebersicht.php');
    exit;
}
?>