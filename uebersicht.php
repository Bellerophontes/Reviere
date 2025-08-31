<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Übersicht</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <img src="logo.png" alt="Logo" class="logo">
        <h1>Übersicht</h1>
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
        <h2>Filter</h2>
        <form id="filterform">
            <label for="filterrevier">Revier</label>
            <select id="filterrevier" name="filterrevier">
                <option value="">-- Alle Reviere --</option>
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
                                
                                $parts = array($id, $name, $gemeinde, $wildraum);
                            }
                        }
                        
                        if (count($parts) >= 2) {
                            echo '<option value="' . htmlspecialchars(trim($parts[0])) . '">' . htmlspecialchars(trim($parts[1])) . '</option>';
                        }
                    }
                }
                ?>
            </select>

            <label for="filtertier">Tierart</label>
            <select id="filtertier" name="filtertier">
                <option value="">-- Alle Tierarten --</option>
                <option value="Rehwild">Rehwild</option>
                <option value="Schwarzwild">Schwarzwild</option>
                <option value="Gämse">Gämse</option>
                <option value="Rotwild">Rotwild</option>
                <option value="Rotfuchs">Rotfuchs</option>
                <option value="Dachs">Dachs</option>
                <option value="Stockente">Stockente</option>
                <option value="Tafelente">Tafelente</option>
                <option value="Reiherente">Reiherente</option>
                <option value="Blässhuhn">Blässhuhn</option>
                <option value="Kormoran">Kormoran</option>
                <option value="Entenbastard">Entenbastard</option>
            </select>

            <button type="button" onclick="filterData()">Filtern</button>
        </form>

        <h2>Tiere</h2>
        <table id="tieretable">
            <thead>
                <tr>
                    <th>Tier-ID</th>
                    <th>Revier</th>
                    <th>Tierart</th>
                    <th>Geschlecht</th>
                    <th>Alter</th>
                    <th>Besonderheiten</th>
                    <th>Anzahl Sichtungen</th>
                    <th>Letzte Sichtung</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $tiereFile = __DIR__ . '/tiere.txt';
                $sichtungenFile = __DIR__ . '/sichtungen.txt';
                $abschuesseFile = __DIR__ . '/abschuesse.txt';
                
                // Lade alle Abschüsse um den Status zu ermitteln
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
                
                // Lade Sichtungsstatistiken
                $sichtungsStats = [];
                if (file_exists($sichtungenFile)) {
                    $sichtungen = file($sichtungenFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    foreach ($sichtungen as $sichtung) {
                        $parts = explode(',', $sichtung);
                        if (count($parts) >= 5) {
                            $tierID = trim($parts[2]);
                            $datum = trim($parts[3]);
                            $zeit = trim($parts[4]);
                            $datetime = $datum . ' ' . $zeit;
                            
                            if (!isset($sichtungsStats[$tierID])) {
                                $sichtungsStats[$tierID] = ['count' => 0, 'letzte' => ''];
                            }
                            
                            $sichtungsStats[$tierID]['count']++;
                            
                            if ($datetime > $sichtungsStats[$tierID]['letzte']) {
                                $sichtungsStats[$tierID]['letzte'] = $datetime;
                            }
                        }
                    }
                }
                
                if (file_exists($tiereFile)) {
                    $tiere = file($tiereFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    foreach ($tiere as $tier) {
                        $parts = explode(',', $tier);
                        if (count($parts) >= 6) {
                            $tierID = trim($parts[0]);
                            $status = isset($abgeschossen[$tierID]) ? "Erlegt" : "Gesichtet";
                            $statusClass = isset($abgeschossen[$tierID]) ? "style='background-color: #ffcccc;'" : "";
                            
                            $anzahlSichtungen = isset($sichtungsStats[$tierID]) ? $sichtungsStats[$tierID]['count'] : 0;
                            $letzteSichtung = isset($sichtungsStats[$tierID]) ? $sichtungsStats[$tierID]['letzte'] : 'Nie gesichtet';
                            
                            echo "<tr $statusClass>";
                            echo "<td>" . htmlspecialchars($tierID) . "</td>";
                            echo "<td>" . htmlspecialchars(trim($parts[1])) . "</td>";
                            echo "<td>" . htmlspecialchars(trim($parts[2])) . "</td>";
                            echo "<td>" . htmlspecialchars(trim($parts[3])) . "</td>";
                            echo "<td>" . htmlspecialchars(trim($parts[4])) . "</td>";
                            echo "<td>" . htmlspecialchars(trim($parts[5])) . "</td>";
                            echo "<td><strong>" . $anzahlSichtungen . "</strong></td>";
                            echo "<td>" . htmlspecialchars($letzteSichtung) . "</td>";
                            echo "<td><strong>" . $status . "</strong></td>";
                            echo "</tr>";
                        }
                    }
                } else {
                    echo "<tr><td colspan='9'>Keine Tiere vorhanden</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <h2>Sichtungen</h2>
        <table id="sichtungentable">
            <thead>
                <tr>
                    <th>Sichtungs-ID</th>
                    <th>Revier</th>
                    <th>Tier-ID</th>
                    <th>Tierart</th>
                    <th>Datum</th>
                    <th>Zeit</th>
                    <th>Foto</th>
                    <th>Besonderheiten</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (file_exists($sichtungenFile)) {
                    // Lade Tiere-Daten für die Anzeige
                    $tiereData = [];
                    if (file_exists($tiereFile)) {
                        $tiere = file($tiereFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                        foreach ($tiere as $tier) {
                            $parts = explode(',', $tier);
                            if (count($parts) >= 6) {
                                $tiereData[trim($parts[0])] = trim($parts[2]); // ID -> Tierart
                            }
                        }
                    }
                    
                    $sichtungen = file($sichtungenFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    
                    // Sortiere Sichtungen nach Datum/Zeit (neueste zuerst)
                    usort($sichtungen, function($a, $b) {
                        $partsA = explode(',', $a);
                        $partsB = explode(',', $b);
                        if (count($partsA) >= 5 && count($partsB) >= 5) {
                            $dateTimeA = trim($partsA[3]) . ' ' . trim($partsA[4]);
                            $dateTimeB = trim($partsB[3]) . ' ' . trim($partsB[4]);
                            return $dateTimeB <=> $dateTimeA;
                        }
                        return 0;
                    });
                    
                    foreach ($sichtungen as $sichtung) {
                        $parts = explode(',', $sichtung);
                        if (count($parts) >= 6) {
                            $tierID = trim($parts[2]);
                            $tierart = isset($tiereData[$tierID]) ? $tiereData[$tierID] : 'Unbekannt';
                            
                            // Foto-URL (falls vorhanden)
                            $fotoUrl = isset($parts[5]) ? trim($parts[5]) : '';
                            $besonderheiten = isset($parts[6]) ? trim($parts[6]) : (isset($parts[5]) ? trim($parts[5]) : '');
                            
                            // Prüfe ob parts[5] eine URL ist oder Besonderheiten (Legacy-Kompatibilität)
                            if (!empty($fotoUrl) && !filter_var($fotoUrl, FILTER_VALIDATE_URL)) {
                                // parts[5] ist keine URL, also sind es Besonderheiten
                                $besonderheiten = $fotoUrl;
                                $fotoUrl = '';
                            }
                            
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars(trim($parts[0])) . "</td>";
                            echo "<td>" . htmlspecialchars(trim($parts[1])) . "</td>";
                            echo "<td>" . htmlspecialchars($tierID) . "</td>";
                            echo "<td>" . htmlspecialchars($tierart) . "</td>";
                            echo "<td>" . htmlspecialchars(trim($parts[3])) . "</td>";
                            echo "<td>" . htmlspecialchars(trim($parts[4])) . "</td>";
                            
                            // Foto-Spalte
                            if (!empty($fotoUrl)) {
                                echo "<td><a href='" . htmlspecialchars($fotoUrl) . "' target='_blank' rel='noopener noreferrer'>📷 Foto öffnen</a></td>";
                            } else {
                                echo "<td>-</td>";
                            }
                            
                            echo "<td>" . htmlspecialchars($besonderheiten) . "</td>";
                            echo "</tr>";
                        }
                    }
                } else {
                    echo "<tr><td colspan='8'>Keine Sichtungen vorhanden</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <h2>Abschüsse</h2>
        <table id="abschuessetable">
            <thead>
                <tr>
                    <th>Abschuss-ID</th>
                    <th>Revier</th>
                    <th>Tier-ID</th>
                    <th>Tierart</th>
                    <th>Datum</th>
                    <th>Zeit</th>
                    <th>Foto</th>
                    <th>Schussdistanz</th>
                    <th>Treffpunktlage</th>
                    <th>Fluchtstrecke</th>
                    <th>Besonderheiten</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (file_exists($abschuesseFile)) {
                    $abschuesse = file($abschuesseFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    
                    // Sortiere Abschüsse nach Datum/Zeit (neueste zuerst)
                    usort($abschuesse, function($a, $b) {
                        $partsA = explode(',', $a);
                        $partsB = explode(',', $b);
                        if (count($partsA) >= 5 && count($partsB) >= 5) {
                            $dateTimeA = trim($partsA[3]) . ' ' . trim($partsA[4]);
                            $dateTimeB = trim($partsB[3]) . ' ' . trim($partsB[4]);
                            return $dateTimeB <=> $dateTimeA;
                        }
                        return 0;
                    });
                    
                    foreach ($abschuesse as $abschuss) {
                        $parts = explode(',', $abschuss);
                        if (count($parts) >= 9) {
                            $tierID = trim($parts[2]);
                            $tierart = isset($tiereData[$tierID]) ? $tiereData[$tierID] : 'Unbekannt';
                            
                            // Foto-URL (falls vorhanden)
                            $fotoUrl = isset($parts[5]) ? trim($parts[5]) : '';
                            $schussdistanz = isset($parts[6]) ? trim($parts[6]) : (isset($parts[5]) ? trim($parts[5]) : '');
                            $treffpunktlage = isset($parts[7]) ? trim($parts[7]) : (isset($parts[6]) ? trim($parts[6]) : '');
                            $fluchtstrecke = isset($parts[8]) ? trim($parts[8]) : (isset($parts[7]) ? trim($parts[7]) : '');
                            $besonderheiten = isset($parts[9]) ? trim($parts[9]) : (isset($parts[8]) ? trim($parts[8]) : '');
                            
                            // Prüfe ob parts[5] eine URL ist oder Schussdistanz (Legacy-Kompatibilität)
                            if (!empty($fotoUrl) && !filter_var($fotoUrl, FILTER_VALIDATE_URL)) {
                                // parts[5] ist keine URL, also verschiebe alle Werte
                                $besonderheiten = $fluchtstrecke;
                                $fluchtstrecke = $treffpunktlage;
                                $treffpunktlage = $schussdistanz;
                                $schussdistanz = $fotoUrl;
                                $fotoUrl = '';
                            }
                            
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars(trim($parts[0])) . "</td>";
                            echo "<td>" . htmlspecialchars(trim($parts[1])) . "</td>";
                            echo "<td>" . htmlspecialchars($tierID) . "</td>";
                            echo "<td>" . htmlspecialchars($tierart) . "</td>";
                            echo "<td>" . htmlspecialchars(trim($parts[3])) . "</td>";
                            echo "<td>" . htmlspecialchars(trim($parts[4])) . "</td>";
                            
                            // Foto-Spalte
                            if (!empty($fotoUrl)) {
                                echo "<td><a href='" . htmlspecialchars($fotoUrl) . "' target='_blank' rel='noopener noreferrer'>📷 Foto öffnen</a></td>";
                            } else {
                                echo "<td>-</td>";
                            }
                            
                            echo "<td>" . htmlspecialchars($schussdistanz) . "</td>";
                            echo "<td>" . htmlspecialchars($treffpunktlage) . "</td>";
                            echo "<td>" . htmlspecialchars($fluchtstrecke) . "</td>";
                            echo "<td>" . htmlspecialchars($besonderheiten) . "</td>";
                            echo "</tr>";
                        }
                    }
                } else {
                    echo "<tr><td colspan='11'>Keine Abschüsse vorhanden</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>

    <script src="script.js"></script>
</body>
</html>