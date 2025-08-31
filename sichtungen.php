<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sichtung erfassen</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <img src="logo.png" alt="Logo" class="logo">
        <h1>Sichtung erfassen</h1>
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
        <h2>Sichtung erfassen</h2>
        
        <form id="sichtungsform" action="savesichtung.php" method="post">
            <label for="revier">Revier</label>
            <select id="revier" name="revier" required>
                <option value="">-- Revier auswählen --</option>
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

            <div id="tierauswahl" style="display: none;">
                <h3>Tier auswählen</h3>
                
                <div id="bestehende-tiere" style="display: none;">
                    <label for="bestehendestier">Bereits bekanntes Tier (erneute Sichtung)</label>
                    <select id="bestehendestier" name="bestehendestier">
                        <option value="">-- Tier auswählen --</option>
                    </select>
                    <p><em>oder</em></p>
                </div>
                
                <button type="button" id="neuestier-btn" onclick="showNeuesTierForm()">Neues Tier erfassen</button>
            </div>

            <div id="tierformcontainer" style="display: none;">
                <h3>Neues Tier erfassen</h3>
                
                <label for="tierart">Tierart</label>
                <select id="tierart" name="tierart">
                    <option value="">-- Tierart auswählen --</option>
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

                <label for="tiergeschlecht">Geschlecht</label>
                <select id="tiergeschlecht" name="tiergeschlecht">
                    <option value="">-- Geschlecht auswählen --</option>
                    <option value="männlich">männlich</option>
                    <option value="weiblich">weiblich</option>
                    <option value="unbekannt">unbekannt</option>
                </select>

                <label for="tieralter">Alter</label>
                <select id="tieralter" name="tieralter">
                    <option value="">-- Alter auswählen --</option>
                    <option value="Jungtier">Jungtier</option>
                    <option value="1-2 jährig">1-2 jährig</option>
                    <option value="> 2 Jahre">> 2 Jahre</option>
                    <option value="unbekannt">unbekannt</option>
                </select>

                <label for="tierbesonderheiten">Besonderheiten</label>
                <textarea id="tierbesonderheiten" name="tierbesonderheiten"></textarea>
                
                <button type="button" onclick="saveTierAndContinue()">Tier speichern und weiter</button>
            </div>

            <div id="sichtungsdetails" style="display: none;">
                <h3>Sichtung erfassen</h3>
                
                <input type="hidden" id="tierid" name="tierid" value="">
                
                <div id="selected-tier-info" style="background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; display: none;">
                    <strong>Ausgewähltes Tier:</strong> <span id="tier-display"></span>
                </div>
                
                <label for="sichtungsdatum">Datum</label>
                <input type="date" id="sichtungsdatum" name="sichtungsdatum" required>

                <label for="sichtungszeit">Zeit</label>
                <input type="time" id="sichtungszeit" name="sichtungszeit" required>

                <label for="foto_url">Foto-URL (optional)</label>
                <input type="url" id="foto_url" name="foto_url" placeholder="https://example.com/foto.jpg">
                <small style="color: #666; display: block; margin-top: 5px;">
                    Link zu einem Foto der Sichtung (z.B. Dropbox, Google Drive, etc.)
                </small>

                <label for="sichtungsbesonderheiten">Besonderheiten</label>
                <textarea id="sichtungsbesonderheiten" name="sichtungsbesonderheiten"></textarea>

                <button type="submit">Sichtung speichern</button>
            </div>
        </form>
    </main>

    <script src="script.js"></script>
    <script>
        // Erweiterte Funktionalität für Sichtungen
        document.addEventListener('DOMContentLoaded', function() {
            const revierSelect = document.getElementById('revier');
            const bestehendestierSelect = document.getElementById('bestehendestier');
            
            if (revierSelect) {
                revierSelect.addEventListener('change', function() {
                    const revierId = this.value;
                    if (revierId) {
                        loadTiereForRevier(revierId);
                        document.getElementById('tierauswahl').style.display = 'block';
                    } else {
                        document.getElementById('tierauswahl').style.display = 'none';
                        hideAllTierForms();
                    }
                });
            }
            
            if (bestehendestierSelect) {
                bestehendestierSelect.addEventListener('change', function() {
                    const tierID = this.value;
                    if (tierID) {
                        document.getElementById('tierid').value = tierID;
                        const tierText = this.options[this.selectedIndex].text;
                        document.getElementById('tier-display').textContent = tierText;
                        document.getElementById('selected-tier-info').style.display = 'block';
                        document.getElementById('sichtungsdetails').style.display = 'block';
                        document.getElementById('tierformcontainer').style.display = 'none';
                    }
                });
            }
        });
        
        function loadTiereForRevier(revierId) {
            fetch('managetiere.php?revier=' + encodeURIComponent(revierId) + '&type=lebend')
                .then(response => response.json())
                .then(tiere => {
                    const select = document.getElementById('bestehendestier');
                    select.innerHTML = '<option value="">-- Tier auswählen --</option>';
                    
                    if (tiere.length > 0) {
                        tiere.forEach(tier => {
                            const option = document.createElement('option');
                            option.value = tier.id;
                            option.textContent = tier.display + ' (' + tier.id + ')';
                            select.appendChild(option);
                        });
                        document.getElementById('bestehende-tiere').style.display = 'block';
                    } else {
                        document.getElementById('bestehende-tiere').style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Fehler beim Laden der Tiere:', error);
                    document.getElementById('bestehende-tiere').style.display = 'none';
                });
        }
        
        function showNeuesTierForm() {
            document.getElementById('tierformcontainer').style.display = 'block';
            document.getElementById('sichtungsdetails').style.display = 'none';
        }
        
        function hideAllTierForms() {
            document.getElementById('tierformcontainer').style.display = 'none';
            document.getElementById('sichtungsdetails').style.display = 'none';
            document.getElementById('selected-tier-info').style.display = 'none';
        }
        
        function saveTierAndContinue() {
            const formData = new FormData();
            formData.append('revier', document.getElementById('revier').value);
            formData.append('tierart', document.getElementById('tierart').value);
            formData.append('tiergeschlecht', document.getElementById('tiergeschlecht').value);
            formData.append('tieralter', document.getElementById('tieralter').value);
            formData.append('tierbesonderheiten', document.getElementById('tierbesonderheiten').value);
            
            fetch('managetiere.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    document.getElementById('tierid').value = result.tierID;
                    document.getElementById('tier-display').textContent = result.tierName + ' (' + result.tierID + ')';
                    document.getElementById('selected-tier-info').style.display = 'block';
                    document.getElementById('tierformcontainer').style.display = 'none';
                    document.getElementById('sichtungsdetails').style.display = 'block';
                } else {
                    alert('Fehler beim Speichern des Tieres');
                }
            })
            .catch(error => {
                console.error('Fehler:', error);
                alert('Fehler beim Speichern des Tieres');
            });
        }
    </script>
</body>
</html>