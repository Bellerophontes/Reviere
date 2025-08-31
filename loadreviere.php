<?php
$reviereFile = __DIR__ . '/reviere.txt';

if (file_exists($reviereFile)) {
    $reviere = file($reviereFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($reviere as $revier) {
        // Debug: Zeige die Rohdaten
        // echo "<!-- Debug: '" . $revier . "' -->";
        
        // Versuche verschiedene Trennzeichen
        $parts = array();
        
        // Erst versuchen mit Komma zu trennen
        if (strpos($revier, ',') !== false) {
            $parts = explode(',', $revier);
        }
        // Falls kein Komma, versuche Semikolon
        else if (strpos($revier, ';') !== false) {
            $parts = explode(';', $revier);
        }
        // Falls weder Komma noch Semikolon, versuche nach dem Muster zu parsen
        else {
            // Pattern: R-001Revier OberbalmOberbalm7
            // Extrahiere ID (R-xxx), dann den Rest
            if (preg_match('/^(R-\d+)(.+?)(\d+)$/', $revier, $matches)) {
                $id = $matches[1];
                $mittelteil = $matches[2];
                $wildraum = $matches[3];
                
                // Versuche Name und Gemeinde zu trennen (heuristisch)
                // Suche nach Großbuchstaben als Trennzeichen
                if (preg_match('/^(.+?)([A-ZÄÖÜ][a-zäöüß]+)$/', $mittelteil, $nameMatches)) {
                    $name = trim($nameMatches[1]);
                    $gemeinde = trim($nameMatches[2]);
                } else {
                    $name = trim($mittelteil);
                    $gemeinde = '';
                }
                
                $parts = array($id, $name, $gemeinde, $wildraum);
            }
        }
        
        if (count($parts) >= 4) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars(trim($parts[0])) . "</td>";
            echo "<td>" . htmlspecialchars(trim($parts[1])) . "</td>";
            echo "<td>" . htmlspecialchars(trim($parts[2])) . "</td>";
            echo "<td>" . htmlspecialchars(trim($parts[3])) . "</td>";
            echo "</tr>";
        } else {
            // Fallback: Zeige Rohdaten in einer Zelle
            echo "<tr><td colspan='4'>Unbekanntes Format: " . htmlspecialchars($revier) . "</td></tr>";
        }
    }
} else {
    echo "<tr><td colspan='4'>Keine Reviere vorhanden (Datei nicht gefunden)</td></tr>";
}
?>