<?php
$pageTitle = 'Backup erstellen';
define('SECURE_ACCESS', true);
require_once 'functions.php';
include 'header.php';

// Definiere die zu sichernden Dateien
$backupFiles = [
    REVIERE_FILE => 'Reviere',
    TIERE_FILE => 'Tiere',
    SICHTUNGEN_FILE => 'Sichtungen',
    ABSCHUESSE_FILE => 'Abschüsse'
];

// Verarbeite Backup-Anfrage
if (isPostRequest() && isset($_POST['create_backup']) && validateCSRFToken($_POST['csrf_token'])) {
    // Erstelle Backup-Verzeichnis falls es nicht existiert
    $backupDir = __DIR__ . '/backups';
    if (!is_dir($backupDir)) {
        if (!mkdir($backupDir, 0755, true)) {
            $_SESSION['error_message'] = 'Backup-Verzeichnis konnte nicht erstellt werden.';
            redirect('backup.php');
        }
    }
    
    // Erstelle .htaccess um den Zugriff auf das Backup-Verzeichnis zu verhindern
    $htaccessFile = $backupDir . '/.htaccess';
    if (!file_exists($htaccessFile)) {
        file_put_contents($htaccessFile, "Order deny,allow\nDeny from all");
    }
    
    // Erstelle Backup-Datei
    $timestamp = date('Y-m-d_H-i-s');
    $backupFilename = $backupDir . '/backup_' . $timestamp . '.zip';
    
    $zip = new ZipArchive();
    if ($zip->open($backupFilename, ZipArchive::CREATE) !== true) {
        $_SESSION['error_message'] = 'Backup-Datei konnte nicht erstellt werden.';
        redirect('backup.php');
    }
    
    // Füge Dateien zum Backup hinzu
    $backupSuccess = true;
    foreach ($backupFiles as $file => $description) {
        if (file_exists($file)) {
            if (!$zip->addFile($file, basename($file))) {
                $backupSuccess = false;
                break;
            }
        }
    }
    
    $zip->close();
    
    if ($backupSuccess) {
        $_SESSION['success_message'] = 'Backup wurde erfolgreich erstellt: ' . basename($backupFilename);
    } else {
        $_SESSION['error_message'] = 'Es gab Probleme beim Erstellen des Backups.';
        // Lösche fehlerhafte Backup-Datei
        if (file_exists($backupFilename)) {
            unlink($backupFilename);
        }
    }
    
    redirect('backup.php');
}

// Liste vorhandene Backups
$backups = [];
$backupDir = __DIR__ . '/backups';
if (is_dir($backupDir)) {
    $files = glob($backupDir . '/backup_*.zip');
    
    foreach ($files as $file) {
        $backups[] = [
            'filename' => basename($file),
            'size' => formatFileSize(filesize($file)),
            'date' => date('d.m.Y H:i', filemtime($file))
        ];
    }
    
    // Sortiere Backups nach Datum (neueste zuerst)
    usort($backups, function($a, $b) {
        return strcmp($b['filename'], $a['filename']);
    });
}

/**
 * Formatiert Dateigröße in lesbare Form
 * @param int $bytes Dateigröße in Bytes
 * @return string Formatierte Dateigröße
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}
?>

<h2>Backup erstellen</h2>
<p>Erstellen Sie ein Backup aller Daten, um diese zu sichern und bei Bedarf wiederherzustellen.</p>

<form action="backup.php" method="post">
    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
    <input type="hidden" name="create_backup" value="1">
    
    <div class="form-row">
        <div class="form-group">
            <p><strong>Folgende Dateien werden gesichert:</strong></p>
            <ul>
                <?php foreach ($backupFiles as $file => $description): ?>
                    <li><?= h($description) ?> (<?= h(basename($file)) ?>)</li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    
    <button type="submit">Backup jetzt erstellen</button>
</form>

<?php if (!empty($backups)): ?>
    <h3>Vorhandene Backups</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Datum</th>
                    <th>Dateiname</th>
                    <th>Größe</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($backups as $backup): ?>
                    <tr>
                        <td><?= h($backup['date']) ?></td>
                        <td><?= h($backup['filename']) ?></td>
                        <td><?= h($backup['size']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="info-message" style="margin-top: 20px;">
        <p><strong>Hinweis:</strong> Die Backups werden im Verzeichnis "backups" gespeichert und sind aus Sicherheitsgründen nicht direkt über den Browser zugänglich. Verwenden Sie FTP oder einen anderen Zugriff auf den Server, um die Backup-Dateien herunterzuladen.</p>
    </div>
<?php else: ?>
    <p>Keine Backups vorhanden.</p>
<?php endif; ?>

<?php include 'footer.php'; ?>