<?php
$pageTitle = 'Abschuss erfassen';
define('SECURE_ACCESS', true);
require_once 'functions.php';
include 'header.php';
?>

<h2>Abschuss erfassen</h2>

<form id="abschussform" action="saveabschuss.php" method="post">
    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
    
    <label for="revierabschuss">Revier</label>
    <select id="revierabschuss" name="revier" required aria-required="true">
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
            <label for="bestehendestier">Bereits gesichtetes Tier</label>
            <select id="bestehendestier" name="bestehendestier">
                <option value="">-- Tier auswählen --</option>
            </select>
            <p><em>oder</em></p>
        </div>
        
        <button type="button" id="neuestier-btn">Neues Tier erfassen</button>
    </div>

    <div id="tierformabschusscontainer" style="display: none;">
        <h3>Neues Tier erfassen</h3>
        
        <label for="tierartabschuss">Tierart</label>
        <select id="tierartabschuss" name="tierart" required aria-required="true">
            <option value="">-- Tierart auswählen --</option>
            <?php
            foreach (getTierarten() as $tierart) {
                echo '<option value="' . h($tierart) . '">' . h($tierart) . '</option>';
            }
            ?>
        </select>

        <label for="tiergeschlechtabschuss">Geschlecht</label>
        <select id="tiergeschlechtabschuss" name="tiergeschlecht" required aria-required="true">
            <option value="">-- Geschlecht auswählen --</option>
            <option value="männlich">männlich</option>
            <option value="weiblich">weiblich</option>
            <option value="unbekannt">unbekannt</option>
        </select>

        <label for="tieralterabschuss">Alter</label>
        <select id="tieralterabschuss" name="tieralter" required aria-required="true">
            <option value="">-- Alter auswählen --</option>
            <option value="Jungtier">Jungtier</option>
            <option value="1-2 jährig">1-2 jährig</option>
            <option value="> 2 Jahre">> 2 Jahre</option>
            <option value="unbekannt">unbekannt</option>
        </select>

        <label for="tierbesonderheitenabschuss">Besonderheiten</label>
        <textarea id="tierbesonderheitenabschuss" name="tierbesonderheiten" rows="3"></textarea>
        
        <button type="button" id="save-tier-abschuss-btn">Tier speichern und weiter</button>
    </div>

    <div id="abschussdetails" style="display: none;">
        <h3>Abschuss erfassen</h3>
        
        <input type="hidden" id="tierid" name="tierid" value="">
        
        <div id="selected-tier-info" class="info-panel" style="display: none;">
            <strong>Ausgewähltes Tier:</strong> <span id="tier-display"></span>
            <div id="letzte-sichtung-info" style="margin-top: 5px; font-size: 0.9em; color: #666;"></div>
        </div>
        
        <label for="abschussdatum">Datum</label>
        <input type="date" id="abschussdatum" name="abschussdatum" required aria-required="true" value="<?= date('Y-m-d') ?>">

        <label for="abschusszeit">Zeit</label>
        <input type="time" id="abschusszeit" name="abschusszeit" required aria-required="true" value="<?= date('H:i') ?>">

        <label for="foto_url">Foto-URL (optional)</label>
        <input type="url" id="foto_url" name="foto_url" placeholder="https://example.com/foto.jpg">
        <small>Link zu einem Foto des erlegten Tieres (z.B. Dropbox, Google Drive, etc.)</small>

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
        <textarea id="abschussbesonderheiten" name="abschussbesonderheiten" rows="3"></textarea>

        <button type="submit">Abschuss speichern</button>
    </div>
</form>

<?php include 'footer.php'; ?>