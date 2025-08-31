<?php
$pageTitle = 'Übersicht';
define('SECURE_ACCESS', true);
require_once 'functions.php';
include 'header.php';
?>

<h2>Filter</h2>
<form id="filterform">
    <div class="form-row">
        <div class="form-group">
            <label for="filterrevier">Revier</label>
            <select id="filterrevier" name="filterrevier">
                <option value="">-- Alle Reviere --</option>
                <?php
                $reviere = loadReviere();
                
                foreach ($reviere as $revier) {
                    echo '<option value="' . h($revier['id']) . '">' . h($revier['name']) . '</option>';
                }
                ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="filtertier">Tierart</label>
            <select id="filtertier" name="filtertier">
                <option value="">-- Alle Tierarten --</option>
                <?php
                foreach (getTierarten() as $tierart) {
                    echo '<option value="' . h($tierart) . '">' . h($tierart) . '</option>';
                }
                ?>
            </select>
        </div>
        
        <div class="form-group">
            <button type="button" id="filter-button">Filtern</button>
            <button type="button" id="reset-filter-button" onclick="resetFilter()">Filter zurücksetzen</button>
            <button type="button" id="print-button" onclick="window.print()">Drucken</button>
        </div>
    </div>
</form>

<h2>Tiere</h2>
<div class="table-responsive">
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
            // Lade alle benötigten Daten
            $tiere = loadTiere();
            $abschuesse = loadAbschuesse();
            $sichtungen = loadSichtungen();
            
            // Erstelle Hilfsdatenstrukturen
            $abgeschosseneTiere = [];
            foreach ($abschuesse as $abschuss) {
                $abgeschosseneTiere[$abschuss['tier']] = true;
            }
            
            $sichtungsStats = [];
            foreach ($sichtungen as $sichtung) {
                $tierID = $sichtung['tier'];
                $datetime = $sichtung['datum'] . ' ' . $sichtung['zeit'];
                
                if (!isset($sichtungsStats[$tierID])) {
                    $sichtungsStats[$tierID] = ['count' => 0, 'letzte' => ''];
                }
                
                $sichtungsStats[$tierID]['count']++;
                
                if ($datetime > $sichtungsStats[$tierID]['letzte']) {
                    $sichtungsStats[$tierID]['letzte'] = $datetime;
                }
            }
            
            if (empty($tiere)) {
                echo '<tr><td colspan="9">Keine Tiere vorhanden</td></tr>';
            } else {
                foreach ($tiere as $tierID => $tier) {
                    $istAbgeschossen = isset($abgeschosseneTiere[$tierID]);
                    $statusClass = $istAbgeschossen ? ' class="tier-abgeschossen"' : '';
                    
                    $anzahlSichtungen = isset($sichtungsStats[$tierID]) ? $sichtungsStats[$tierID]['count'] : 0;
                    $letzteSichtung = isset($sichtungsStats[$tierID]) ? $sichtungsStats[$tierID]['letzte'] : 'Nie gesichtet';
                    
                    echo "<tr$statusClass>";
                    echo '<td>' . h($tierID) . '</td>';
                    echo '<td>' . h($tier['revier']) . '</td>';
                    echo '<td>' . h($tier['art']) . '</td>';
                    echo '<td>' . h($tier['geschlecht']) . '</td>';
                    echo '<td>' . h($tier['alter']) . '</td>';
                    echo '<td>' . h($tier['besonderheiten']) . '</td>';
                    echo '<td><strong>' . $anzahlSichtungen . '</strong></td>';
                    echo '<td>' . h($letzteSichtung) . '</td>';
                    echo '<td><strong>' . ($istAbgeschossen ? 'Erlegt' : 'Gesichtet') . '</strong></td>';
                    echo '</tr>';
                }
            }
            ?>
        </tbody>
    </table>
</div>

<h2>Sichtungen</h2>
<div class="table-responsive">
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
			// Sortiere Sichtungen nach Datum/Zeit (neueste zuerst)
            $sortedSichtungen = $sichtungen;
            usort($sortedSichtungen, function($a, $b) {
                $dateTimeA = $a['datum'] . ' ' . $a['zeit'];
                $dateTimeB = $b['datum'] . ' ' . $b['zeit'];
                return strcmp($dateTimeB, $dateTimeA); // Absteigend sortieren
            });
            
            if (empty($sortedSichtungen)) {
                echo '<tr><td colspan="8">Keine Sichtungen vorhanden</td></tr>';
            } else {
                foreach ($sortedSichtungen as $sichtung) {
                    $tierID = $sichtung['tier'];
                    $tierart = isset($tiere[$tierID]) ? $tiere[$tierID]['art'] : 'Unbekannt';
                    
                    echo '<tr>';
                    echo '<td>' . h($sichtung['id']) . '</td>';
                    echo '<td>' . h($sichtung['revier']) . '</td>';
                    echo '<td>' . h($tierID) . '</td>';
                    echo '<td>' . h($tierart) . '</td>';
                    echo '<td>' . h($sichtung['datum']) . '</td>';
                    echo '<td>' . h($sichtung['zeit']) . '</td>';
                    
                    // Foto-Spalte
                    if (!empty($sichtung['foto_url'])) {
                        echo '<td><a href="' . h($sichtung['foto_url']) . '" target="_blank" rel="noopener noreferrer" class="external-link photo-link">Foto öffnen</a></td>';
                    } else {
                        echo '<td>-</td>';
                    }
                    
                    echo '<td>' . h($sichtung['besonderheiten']) . '</td>';
                    echo '</tr>';
                }
            }
            ?>
        </tbody>
    </table>
</div>

<h2>Abschüsse</h2>
<div class="table-responsive">
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
            // Sortiere Abschüsse nach Datum/Zeit (neueste zuerst)
            $sortedAbschuesse = $abschuesse;
            usort($sortedAbschuesse, function($a, $b) {
                $dateTimeA = $a['datum'] . ' ' . $a['zeit'];
                $dateTimeB = $b['datum'] . ' ' . $b['zeit'];
                return strcmp($dateTimeB, $dateTimeA); // Absteigend sortieren
            });
            
            if (empty($sortedAbschuesse)) {
                echo '<tr><td colspan="11">Keine Abschüsse vorhanden</td></tr>';
            } else {
                foreach ($sortedAbschuesse as $abschuss) {
                    $tierID = $abschuss['tier'];
                    $tierart = isset($tiere[$tierID]) ? $tiere[$tierID]['art'] : 'Unbekannt';
                    
                    echo '<tr>';
                    echo '<td>' . h($abschuss['id']) . '</td>';
                    echo '<td>' . h($abschuss['revier']) . '</td>';
                    echo '<td>' . h($tierID) . '</td>';
                    echo '<td>' . h($tierart) . '</td>';
                    echo '<td>' . h($abschuss['datum']) . '</td>';
                    echo '<td>' . h($abschuss['zeit']) . '</td>';
                    
                    // Foto-Spalte
                    if (!empty($abschuss['foto_url'])) {
                        echo '<td><a href="' . h($abschuss['foto_url']) . '" target="_blank" rel="noopener noreferrer" class="external-link photo-link">Foto öffnen</a></td>';
                    } else {
                        echo '<td>-</td>';
                    }
                    
                    echo '<td>' . h($abschuss['schussdistanz']) . '</td>';
                    echo '<td>' . h($abschuss['treffpunktlage']) . '</td>';
                    echo '<td>' . h($abschuss['fluchtstrecke']) . '</td>';
                    echo '<td>' . h($abschuss['besonderheiten']) . '</td>';
                    echo '</tr>';
                }
            }
            ?>
        </tbody>
    </table>
</div>

<script>
// Zusätzliche JavaScript-Funktionen für die Übersichtsseite
function resetFilter() {
    document.getElementById('filterrevier').value = '';
    document.getElementById('filtertier').value = '';
    filterData();
}
</script>

<?php include 'footer.php'; ?>