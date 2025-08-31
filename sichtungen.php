<?php
$pageTitle = 'Sichtung erfassen';
define('SECURE_ACCESS', true);
require_once 'functions.php';
include 'header.php';
?>

<h2>Sichtung erfassen</h2>

<form id="sichtungsform" action="savesichtung.php" method="post">
    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
    
    <label for="revier">Revier</label>
    <select id="revier" name="revier" required aria-required="true">
        <option value="">-- Revier auswählen --</option>
        <?php
        $reviere = loadReviere();
        
        foreach ($reviere as $revier) {
            echo '<option value="' . h($revier['id']) . '">' . h($revier['name']) . '</option>';
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
        
        <button type="button" id="neuestier-btn">Neues Tier erfassen</button>
    </div>

    <div id="tierformcontainer" style="display: none;">
        <h3>Neues Tier erfassen</h3>
        
        <label for="tierart">Tierart</label>
        <select id="tierart" name="tierart" required aria-required="true">
            <option value="">-- Tierart auswählen --</option>
            <?php
            foreach (getTierarten() as $tierart) {
                echo '<option value="' . h($tierart) . '">' . h($tierart) . '</option>';
            }
            ?>
        </select>

        <label for="tiergeschlecht">Geschlecht</label>
        <select id="tiergeschlecht" name="tiergeschlecht" required aria-required="true">
            <option value="">-- Geschlecht auswählen --</option>
            <option value="männlich">männlich</option>
            <option value="weiblich">weiblich</option>
            <option value="unbekannt">unbekannt</option>
        </select>

        <label for="tieralter">Alter</label>
        <select id="tieralter" name="tieralter" required aria-required="true">
            <option value="">-- Alter auswählen --</option>
            <option value="Jungtier">Jungtier</option>
            <option value="1-2 jährig">1-2 jährig</option>
            <option value="> 2 Jahre">> 2 Jahre</option>
            <option value="unbekannt">unbekannt</option>
        </select>

        <label for="tierbesonderheiten">Besonderheiten</label>
        <textarea id="tierbesonderheiten" name="tierbesonderheiten" rows="3"></textarea>
        
        <button type="button" id="save-tier-btn">Tier speichern und weiter</button>
    </div>

    <div id="sichtungsdetails" style="display: none;">
        <h3>Sichtung erfassen</h3>
        
        <input type="hidden" id="tierid" name="tierid" value="">
        
        <div id="selected-tier-info" class="info-panel" style="display: none;">
            <strong>Ausgewähltes Tier:</strong> <span id="tier-display"></span>
        </div>
        
        <label for="sichtungsdatum">Datum</label>
        <input type="date" id="sichtungsdatum" name="sichtungsdatum" required aria-required="true" value="<?= date('Y-m-d') ?>">

        <label for="sichtungszeit">Zeit</label>
        <input type="time" id="sichtungszeit" name="sichtungszeit" required aria-required="true" value="<?= date('H:i') ?>">

        <label for="foto_url">Foto-URL (optional)</label>
        <input type="url" id="foto_url" name="foto_url" placeholder="https://example.com/foto.jpg">
        <small>Link zu einem Foto der Sichtung (z.B. Dropbox, Google Drive, etc.)</small>

        <label for="sichtungsbesonderheiten">Besonderheiten</label>
        <textarea id="sichtungsbesonderheiten" name="sichtungsbesonderheiten" rows="3"></textarea>

        <button type="submit">Sichtung speichern</button>
    </div>
</form>

<?php include 'footer.php'; ?>