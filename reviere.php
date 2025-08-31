<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviere verwalten</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <img src="logo.png" alt="Logo" class="logo">
        <h1>Reviere verwalten</h1>
    </header>
    
    <nav>
        <ul>
            <li><a href="index.html">Startseite</a></li>
            <li><a href="reviere.php">Reviere verwalten</a></li>
            <li><a href="sichtungen.php">Sichtung erfassen</a></li>
            <li><a href="abschuesse.php">Abschuss erfassen</a></li>
            <li><a href="uebersicht.php">Übersicht</a></li>
        </ul>
    </nav>

    <main>
        <h2>Neues Revier hinzufügen</h2>
        
        <form action="saverevier.php" method="post">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required>

            <label for="gemeinde">Gemeinde</label>
            <input type="text" id="gemeinde" name="gemeinde" required>

            <label for="wildraum">Wildraum (1-17)</label>
            <input type="number" id="wildraum" name="wildraum" min="1" max="17" required>

            <label for="karten_url">Karten-URL (optional)</label>
            <input type="url" id="karten_url" name="karten_url" placeholder="https://maps.google.com/...">
            <small style="color: #666; display: block; margin-top: 5px;">
                Link zu einer Karte des Reviers (z.B. Google Maps, OpenStreetMap)
            </small>

            <button type="submit">Revier speichern</button>
        </form>

        <h2>Bestehende Reviere</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Gemeinde</th>
                    <th>Wildraum</th>
                    <th>Karte</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $reviereFile = __DIR__ . '/reviere.txt';
                if (file_exists($reviereFile)) {
                    $content = file_get_contents($reviereFile);
                    $repairedContent = preg_replace('/(?<!^)(R-\d{3})/', "\n$1", $content);
                    $lines = explode("\n", trim($repairedContent));
                    
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (empty($line)) continue;
                        
                        $parts = array();
                        if (strpos($line, ',') !== false) {
                            $parts = explode(',', $line);
                        } else {
                            // Legacy Format parsen
                            if (preg_match('/^(R-\d+)(.+?)(\d+)$/', $line, $matches)) {
                                $id = $matches[1];
                                $mittelteil = $matches[2];
                                $wildraum = $matches[3];
                                
                                if (preg_match('/^(.+?)([A-ZÄÖÜ][a-zäöüß]+)$/', $mittelteil, $nameMatches)) {
                                    $name = trim($nameMatches[1]);
                                    $gemeinde = trim($nameMatches[2]);
                                } else {
                                    $name = trim($mittelteil);
                                    $gemeinde = '';
                                }
                                
                                $parts = array($id, $name, $gemeinde, $wildraum, ''); // Kein Karten-URL im Legacy-Format
                            }
                        }
                        
                        if (count($parts) >= 4) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars(trim($parts[0])) . "</td>";
                            echo "<td>" . htmlspecialchars(trim($parts[1])) . "</td>";
                            echo "<td>" . htmlspecialchars(trim($parts[2])) . "</td>";
                            echo "<td>" . htmlspecialchars(trim($parts[3])) . "</td>";
                            
                            // Karten-URL (falls vorhanden)
                            $kartenUrl = isset($parts[4]) ? trim($parts[4]) : '';
                            if (!empty($kartenUrl)) {
                                echo "<td><a href='" . htmlspecialchars($kartenUrl) . "' target='_blank' rel='noopener noreferrer'>🗺️ Karte öffnen</a></td>";
                            } else {
                                echo "<td>-</td>";
                            }
                            
                            echo "</tr>";
                        }
                    }
                } else {
                    echo "<tr><td colspan='5'>Keine Reviere vorhanden</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>
</body>
</html>