<?php
$reviereFile = __DIR__ . '/reviere.txt';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $gemeinde = $_POST['gemeinde'];
    $wildraum = $_POST['wildraum'];
    $karten_url = $_POST['karten_url'] ?? '';
    
    // Generiere eine neue ID
    $lastId = 0;
    
    if (file_exists($reviereFile)) {
        $content = file_get_contents($reviereFile);
        
        // Repariere das Format falls nötig (alle Einträge in separate Zeilen)
        $repairedContent = preg_replace('/(?<!^)(R-\d{3})/', "\n$1", $content);
        $repairedContent = trim($repairedContent);
        
        // Schreibe die reparierte Datei zurück
        if ($repairedContent !== $content) {
            file_put_contents($reviereFile, $repairedContent . "\n");
        }
        
        // Lade die reparierten Zeilen
        $lines = explode("\n", $repairedContent);
        $lines = array_filter($lines); // Entferne leere Zeilen
        
        foreach ($lines as $line) {
            if (preg_match('/^R-(\d+)/', trim($line), $matches)) {
                $idNum = (int) $matches[1];
                if ($idNum > $lastId) {
                    $lastId = $idNum;
                }
            }
        }
    }
    
    $newId = 'R-' . str_pad($lastId + 1, 3, '0', STR_PAD_LEFT);
    
    // Speichere das neue Revier im CSV-Format (mit Karten-URL)
    $newEntry = $newId . ',' . $name . ',' . $gemeinde . ',' . $wildraum . ',' . $karten_url;
    
    // Stelle sicher, dass die Datei mit einem Zeilenendezeichen endet
    $currentContent = '';
    if (file_exists($reviereFile)) {
        $currentContent = file_get_contents($reviereFile);
        // Füge Zeilenumbruch hinzu falls die Datei nicht leer ist und nicht mit Zeilenumbruch endet
        if (!empty($currentContent) && !preg_match('/[\r\n]$/', $currentContent)) {
            $currentContent .= "\n";
            file_put_contents($reviereFile, $currentContent);
        }
    }
    
    // Füge den neuen Eintrag hinzu
    file_put_contents($reviereFile, $newEntry . "\n", FILE_APPEND | LOCK_EX);
    
    header('Location: reviere.php');
    exit;
}
?>