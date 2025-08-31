<?php
$pageTitle = 'Reviere verwalten';
define('SECURE_ACCESS', true);
require_once 'functions.php';
include 'header.php';
?>

<h2>Neues Revier hinzufügen</h2>

<form action="saverevier.php" method="post" id="revier-form">
    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
    
    <label for="name">Name</label>
    <input type="text" id="name" name="name" required aria-required="true">

    <label for="gemeinde">Gemeinde</label>
    <input type="text" id="gemeinde" name="gemeinde" required aria-required="true">

    <label for="wildraum">Wildraum (1-17)</label>
    <input type="number" id="wildraum" name="wildraum" min="1" max="17" required aria-required="true">

    <label for="karten_url">Karten-URL (optional)</label>
    <input type="url" id="karten_url" name="karten_url" placeholder="https://maps.google.com/...">
    <small>Link zu einer Karte des Reviers (z.B. Google Maps, OpenStreetMap)</small>

    <button type="submit">
	<button type="submit">Revier speichern</button>
</form>

<h2>Bestehende Reviere</h2>
<div class="table-responsive">
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
            $reviere = loadReviere();
            
            if (empty($reviere)) {
                echo '<tr><td colspan="5">Keine Reviere vorhanden</td></tr>';
            } else {
                foreach ($reviere as $revier) {
                    echo '<tr>';
                    echo '<td>' . h($revier['id']) . '</td>';
                    echo '<td>' . h($revier['name']) . '</td>';
                    echo '<td>' . h($revier['gemeinde']) . '</td>';
                    echo '<td>' . h($revier['wildraum']) . '</td>';
                    
                    // Karten-URL (falls vorhanden)
                    if (!empty($revier['karten_url'])) {
                        echo '<td><a href="' . h($revier['karten_url']) . '" target="_blank" rel="noopener noreferrer" class="external-link map-link">Karte öffnen</a></td>';
                    } else {
                        echo '<td>-</td>';
                    }
                    
                    echo '</tr>';
                }
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>