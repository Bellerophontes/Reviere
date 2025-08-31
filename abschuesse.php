<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abschuss erfassen</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <img src="logo.png" alt="Logo" class="logo">
        <h1>Abschuss erfassen</h1>
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
        <h2>Abschuss erfassen</h2>
        
        <form id="abschussform" action="saveabschuss.php" method="post">
            <label for="revierabschuss">Revier</label>
            <select id="revierabschuss" name="revier" required>
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
                    <label for="bestehendestier">Bereits gesichtetes Tier</label>
                    <select id="bestehendestier" name="bestehendestier">
                        <option value="">-- Tier auswählen --</option>
                    </select>
                    <p><em>oder</em></p>
                </div>
                
                <button type="button" id="neuestier-btn" onclick="showNeuesTierForm()">Neues Tier erfassen</button>
            </div>

            <div id="tierformabschusscontainer" style="display: none;">
                <h3>Neues Tier erfassen</h3>
                
                <label for="tierartabschuss">Tierart</label>
                <select id="tierartabschuss" name="tierart">
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

                <label for="tiergeschlechtabschuss">Geschlecht</label>
                <select id="tiergeschlechtabschuss" name="tiergeschlecht">
                    <option value="">-- Geschlecht auswählen --</option>
                    <option value="männlich">männlich</option>
                    <option value="weiblich">weiblich</option>
                    <option value="unbekannt">unbekannt</option>
                </select>

                <label for="tieralterabschuss">Alter</label>
                <select id="tieralterabschuss" name="tieralter">
                    <option value="">-- Alter auswählen --</option>
                    <option value="Jungtier">Jungtier</option>
                    <option value="1-2 jährig">1-2 jährig</option>
                    <option value="> 2 Jahre">> 2 Jahre</option>
                    <option value="unbekannt">unbekannt</option>
                </select>

                <label for="tierbesonderheitenabschuss">Besonderheiten</label>
                <textarea id="tierbesonderheitenabschuss" name="tierbesonderheiten"></textarea>
                
                <button type="button" onclick="saveTierAndContinue()">Tier speichern und weiter</button>
            </div>

            <div id="abschussdetails" style="display: none;">
                <h3>Abschuss erfassen</h3>
                
                <input type="hidden" id="tierid" name="tierid" value="">
                
                <div id="selected-tier-info" style="background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; display: none;">
                    <strong>Ausgewähltes Tier:</strong> <span id="tier-display"></span>
                    <div id="letzte-sichtung-info" style="margin-top: 5px; font-size: 0.9em; color: #666;"></div>
                </div>
                
                <label for="abschussdatum">Datum</label>
                <input type="date" id="abschussdatum" name="abschussdatum" required>

                <label for="abschusszeit">Zeit</label>
                <input type="time" id="abschusszeit" name="abschusszeit" required>

                <label for="foto_url">Foto-URL (optional)</label>
                <input type="url" id="foto_url" name="foto_url" placeholder="https://example.com/foto.jpg">
                <small style="color: #666; display: block; margin-top: 5px;">
                    Link zu einem Foto des erlegten Tieres (z.B. Dropbox, Google Drive, etc.)
                </small>

                <label for="schussdistanz">Schussdistanz (m)</label>
                <input type="number" id="schussdistanz" name="schussdistanz" min="0" step="5">

                <label for="treffpunktlage">Treffpunktlage</label>
                <select id="treffpunktlage" name="treffpunktlage">
                    <option value="">-- Auswählen --</option>
                    <option value="Herz/Lunge">Herz/Lunge</option>
                    <option value="Kopf">Kopf</option>
                    <option value="Hals">Hals</option>
                    <option value="Blatt">Blatt</option>
                    <option value="Keule">Keule</option>
                    <option value="Sonstiges">Sonstiges</option>
                </select>

                <label for="fluchtstrecke">Fluchtstrecke (m)</label>
                <input type="number" id="fluchtstrecke" name="fluchtstrecke" min="0" step="5">

                <label for="abschussbesonderheiten">Besonderheiten</label>
                <textarea id="abschussbesonderheiten" name="abschussbesonderheiten"></textarea>

                <button type="submit">Abschuss speichern</button>
            </div>
        </form>
    </main>

    <script src="script.js"></script>
    <script>
        let currentTierData = null;
        
        // Erweiterte Funktionalität für Abschüsse
        document.addEventListener('DOMContentLoaded', function() {
            const revierSelect = document.getElementById('revierabschuss');
            const bestehendestierSelect = document.getElementById('bestehendestier');
            const abschussdatumInput = document.getElementById('abschussdatum');
            const abschusszeitInput = document.getElementById('abschusszeit');
            
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
                        const selectedTier = currentTierData.find(t => t.id === tierID);
                        if (selectedTier) {
                            document.getElementById('tierid').value = tierID;
                            const tierText = this.options[this.selectedIndex].text;
                            document.getElementById('tier-display').textContent = tierText;
                            
                            // Zeige letzte Sichtung an
                            const letzteInfo = document.getElementById('letzte-sichtung-info');
                            if (selectedTier.letzte_sichtung) {
                                letzteInfo.textContent = 'Letzte Sichtung: ' + selectedTier.letzte_sichtung;
                                letzteInfo.style.display = 'block';
                                
                                // Setze Mindestdatum und -zeit für Abschuss
                                const [datum, zeit] = selectedTier.letzte_sichtung.split(' ');
                                abschussdatumInput.min = datum;
                                
                                // Validierung beim Datum-/Zeit-Change
                                validateAbschussDateTime(selectedTier.letzte_sichtung);
                            } else {
                                letzteInfo.style.display = 'none';
                                abschussdatumInput.min = '';
                            }
                            
                            document.getElementById('selected-tier-info').style.display = 'block';
                            document.getElementById('abschussdetails').style.display = 'block';
                            document.getElementById('tierformabschusscontainer').style.display = 'none';
                        }
                    }
                });
            }
            
            // Datum/Zeit Validierung
            [abschussdatumInput, abschusszeitInput].forEach(input => {
                if (input) {
                    input.addEventListener('change', function() {
                        if (currentTierData && document.getElementById('tierid').value) {
                            const selectedTier = currentTierData.find(t => t.id === document.getElementById('tierid').value);
                            if (selectedTier && selectedTier.letzte_sichtung) {
                                validateAbschussDateTime(selectedTier.letzte_sichtung);
                            }
                        }
                    });
                }
            });
        });
        
        function validateAbschussDateTime(letzterSichtung) {
            const abschussdatum = document.getElementById('abschussdatum').value;
            const abschusszeit = document.getElementById('abschusszeit').value;
            
            if (abschussdatum && abschusszeit) {
                const abschussDateTime = new Date(abschussdatum + 'T' + abschusszeit);
                const sichtungsDateTime = new Date(letzterSichtung.replace(' ', 'T'));
                
                if (abschussDateTime < sichtungsDateTime) {
                    alert('Der Abschuss kann nicht vor der letzten Sichtung (' + letzterSichtung + ') stattgefunden haben!');
                    document.getElementById('abschussdatum').value = '';
                    document.getElementById('abschusszeit').value = '';
                    return false;
                }
            }
            return true;
        }
        
        function loadTiereForRevier(revierId) {
            fetch('managetiere.php?revier=' + encodeURIComponent(revierId) + '&type=abschuss')
                .then(response => response.json())
                .then(tiere => {
                    currentTierData = tiere;
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
            document.getElementById('tierformabschusscontainer').style.display = 'block';
            document.getElementById('abschussdetails').style.display = 'none';
        }
        
        function hideAllTierForms() {
            document.getElementById('tierformabschusscontainer').style.display = 'none';
            document.getElementById('abschussdetails').style.display = 'none';
            document.getElementById('selected-tier-info').style.display = 'none';
        }
        
        function saveTierAndContinue() {
            const formData = new FormData();
            formData.append('revier', document.getElementById('revierabschuss').value);
            formData.append('tierart', document.getElementById('tierartabschuss').value);
            formData.append('tiergeschlecht', document.getElementById('tiergeschlechtabschuss').value);
            formData.append('tieralter', document.getElementById('tieralterabschuss').value);
            formData.append('tierbesonderheiten', document.getElementById('tierbesonderheitenabschuss').value);
            
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
                    document.getElementById('letzte-sichtung-info').style.display = 'none'; // Kein vorheriger Sichtungstermin
                    document.getElementById('tierformabschusscontainer').style.display = 'none';
                    document.getElementById('abschussdetails').style.display = 'block';
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