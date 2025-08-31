<?php
$abschuesseFile = 'abschuesse.txt';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $revier = $_POST['revier'];
    $tierid = $_POST['tierid'];
    $bestehendestier = $_POST['bestehendestier'];
    $abschussdatum = $_POST['abschussdatum'];
    $abschusszeit = $_POST['abschusszeit'];
    $foto_url = $_POST['foto_url'] ?? '';
    $schussdistanz = $_POST['schussdistanz'];
    $treffpunktlage = $_POST['treffpunktlage'];
    $fluchtstrecke = $_POST['fluchtstrecke'];
    $abschussbesonderheiten = $_POST['abschussbesonderheiten'];
    
    // Bestimme die Tier-ID (entweder bestehendes Tier oder neues Tier)
    $finalTierID = !empty($bestehendestier) ? $bestehendestier : $tierid;
    
    if (empty($finalTierID)) {
        // Fehler: Keine Tier-ID vorhanden
        header('Location: abschuesse.php?error=no_tier');
        exit;
    }
    
    // Generiere eine neue Abschuss-ID
    $lastId = 0;
    if (file_exists($abschuesseFile)) {
        $lines = file($abschuesseFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $parts = explode(',', $line);
            if (count($parts) >= 1 && strpos($parts[0], 'A-') === 0) {
                $idNum = (int) substr($parts[0], 2);
                if ($idNum > $lastId) {
                    $lastId = $idNum;
                }
            }
        }
    }
    
    $newId = 'A-' . str_pad($lastId + 1, 3, '0', STR_PAD_LEFT);
    
    // Speichere den neuen Abschuss (referenziert auf Tier-ID, mit Foto-URL)
    $newEntry = $newId . ',' . $revier . ',' . $finalTierID . ',' . $abschussdatum . ',' . $abschusszeit . ',' . $foto_url . ',' . $schussdistanz . ',' . $treffpunktlage . ',' . $fluchtstrecke . ',' . $abschussbesonderheiten;
    
    file_put_contents($abschuesseFile, $newEntry . PHP_EOL, FILE_APPEND);
    
    header('Location: uebersicht.php');
    exit;
}
?>