<?php
$pageTitle = 'Daten exportieren';
define('SECURE_ACCESS', true);
require_once 'functions.php';
include 'header.php';

// Definiere Exporttypen
$exportTypes = [
    'csv' => 'CSV-Format',
    'json' => 'JSON-Format'
];

// Verarbeite Export-Anfrage
if (isPostRequest() && isset($_POST['export_type']) && isset($_POST['data_type'])) {
    $exportType = $_POST['export_type'];
    $dataType = $_POST['data_type'];
    
    // Validiere Export-Typ
    if (!array_key_exists($exportType, $exportTypes)) {
        $_SESSION['error_message'] = 'Ungültiger Export-Typ';
        redirect('export.php');
    }
    
    // Exportiere Daten basierend auf Typ
    switch ($dataType) {
        case 'reviere':
            $rawData = loadReviere();
            // Nur die wichtigen Felder auswählen
            $data = [];
            foreach ($rawData as $item) {
                $data[] = [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'gemeinde' => $item['gemeinde'],
                    'wildraum' => $item['wildraum'],
                    'karten_url' => $item['karten_url']
                ];
            }
            $filename = 'reviere_export_' . date('Y-m-d');
            break;
        case 'tiere':
            $rawData = loadTiere();
            // Nur die wichtigen Felder auswählen
            $data = [];
            foreach ($rawData as $item) {
                $data[] = [
                    'id' => $item['id'],
                    'revier' => $item['revier'],
                    'art' => $item['art'],
                    'geschlecht' => $item['geschlecht'],
                    'alter' => $item['alter'],
                    'besonderheiten' => $item['besonderheiten']
                ];
            }
            $filename = 'tiere_export_' . date('Y-m-d');
            break;
        case 'sichtungen':
            $rawData = loadSichtungen();
            // Nur die wichtigen Felder auswählen
            $data = [];
            foreach ($rawData as $item) {
                $data[] = [
                    'id' => $item['id'],
                    'revier' => $item['revier'],
                    'tier' => $item['tier'],
                    'datum' => $item['datum'],
                    'zeit' => $item['zeit'],
                    'foto_url' => $item['foto_url'],
                    'besonderheiten' => $item['besonderheiten']
                ];
            }
            $filename = 'sichtungen_export_' . date('Y-m-d');
            break;
        case 'abschuesse':
            $rawData = loadAbschuesse();
            // Nur die wichtigen Felder auswählen
            $data = [];
            foreach ($rawData as $item) {
                $data[] = [
                    'id' => $item['id'],
                    'revier' => $item['revier'],
                    'tier' => $item['tier'],
                    'datum' => $item['datum'],
                    'zeit' => $item['zeit'],
                    'foto_url' => $item['foto_url'],
                    'schussdistanz' => $item['schussdistanz'],
                    'treffpunktlage' => $item['treffpunktlage'],
                    'fluchtstrecke' => $item['fluchtstrecke'],
                    'besonderheiten' => $item['besonderheiten']
                ];
            }
            $filename = 'abschuesse_export_' . date('Y-m-d');
            break;
        default:
            $_SESSION['error_message'] = 'Ungültiger Datentyp';
            redirect('export.php');
    }
    
    // Setze entsprechende Header für Download
    header('Content-Disposition: attachment; filename="' . $filename . '.' . $exportType . '"');
    
    if ($exportType === 'csv') {
        header('Content-Type: text/csv; charset=UTF-8');
        
        // Ausgabe direkt in den Output-Buffer
        $output = fopen('php://output', 'w');
        
        // BOM für Excel-Kompatibilität
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Schreibe CSV-Header
        if (!empty($data)) {
            $firstItem = reset($data);
            fputcsv($output, array_keys($firstItem));
            
            // Schreibe Daten
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
    } elseif ($exportType === 'json') {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?>

<h2>Daten exportieren</h2>
<p>Hier können Sie die erfassten Daten in verschiedenen Formaten exportieren.</p>

<form action="export.php" method="post">
    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
    
    <label for="data_type">Datentyp</label>
    <select id="data_type" name="data_type" required>
        <option value="">-- Bitte wählen --</option>
        <option value="reviere">Reviere</option>
        <option value="tiere">Tiere</option>
        <option value="sichtungen">Sichtungen</option>
        <option value="abschuesse">Abschüsse</option>
    </select>
    
    <label for="export_type">Exportformat</label>
    <select id="export_type" name="export_type" required>
        <option value="">-- Bitte wählen --</option>
        <?php foreach ($exportTypes as $value => $label): ?>
            <option value="<?= h($value) ?>"><?= h($label) ?></option>
        <?php endforeach; ?>
    </select>
    
    <button type="submit">Exportieren</button>
</form>

<div class="info-message" style="margin-top: 20px;">
    <p><strong>Hinweis:</strong> Exportierte Daten können in Tabellenkalkulationsprogrammen wie Excel oder in Datenbanken importiert werden.</p>
</div>

<?php include 'footer.php'; ?>