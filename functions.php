<?php
/**
 * Gemeinsame Funktionen für das Wildtiermanagement-System
 */

// Verhindere direkten Zugriff auf die Datei
if (!defined('SECURE_ACCESS')) {
    header('HTTP/1.1 403 Forbidden');
    exit('Direkter Zugriff verboten');
}

/**
 * Konfiguration
 */
define('DATA_DIR', __DIR__);
define('REVIERE_FILE', DATA_DIR . '/reviere.txt');
define('TIERE_FILE', DATA_DIR . '/tiere.txt');
define('SICHTUNGEN_FILE', DATA_DIR . '/sichtungen.txt');
define('ABSCHUESSE_FILE', DATA_DIR . '/abschuesse.txt');

/**
 * Sicherheitsfunktionen
 */

/**
 * Ausgabe sicher filtern
 * 
 * @param string $data Zu filternde Daten
 * @return string Gefilterter String
 */
function h($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * CSRF-Token generieren und in Session speichern
 * 
 * @return string CSRF-Token
 */
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * CSRF-Token validieren
 * 
 * @param string $token Vom Benutzer gesendetes Token
 * @return bool True wenn Token gültig ist
 */
function validateCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    
    return false;
}

/**
 * Datenvalidierung
 */

/**
 * Validiere eine ID mit spezifischem Präfix
 * 
 * @param string $id Die zu validierende ID
 * @param string $prefix Erwartetes Präfix (R-, T-, S-, A-)
 * @return bool True wenn die ID gültig ist
 */
function validateId($id, $prefix) {
    return preg_match('/^' . preg_quote($prefix, '/') . '-\d{3}$/', $id);
}

/**
 * Validiere Datum im Format YYYY-MM-DD
 * 
 * @param string $date Das zu validierende Datum
 * @return bool True wenn das Datum gültig ist
 */
function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/**
 * Validiere Zeit im Format HH:MM
 * 
 * @param string $time Die zu validierende Zeit
 * @return bool True wenn die Zeit gültig ist
 */
function validateTime($time) {
    $t = DateTime::createFromFormat('H:i', $time);
    return $t && $t->format('H:i') === $time;
}

/**
 * Validiere URL
 * 
 * @param string $url Die zu validierende URL
 * @return bool True wenn die URL gültig ist
 */
function validateUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Datei-Operationen
 */

/**
 * Lade und parse Reviere aus der Datei
 * 
 * @return array Array mit Revierinformationen
 */
function loadReviere() {
    $reviere = [];
    
    if (file_exists(REVIERE_FILE)) {
        $content = file_get_contents(REVIERE_FILE);
        $repairedContent = preg_replace('/(?<!^)(R-\d{3})/', "\n$1", $content);
        $lines = explode("\n", trim($repairedContent));
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $parts = [];
            
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
                    
                    $parts = [$id, $name, $gemeinde, $wildraum, ''];
                }
            }
            
            if (count($parts) >= 4) {
                $reviere[] = [
                    'id' => trim($parts[0]),
                    'name' => trim($parts[1]),
                    'gemeinde' => trim($parts[2]),
                    'wildraum' => trim($parts[3]),
                    'karten_url' => isset($parts[4]) ? trim($parts[4]) : ''
                ];
            }
        }
    }
    
    return $reviere;
}

/**
 * Lade und parse Tiere aus der Datei
 * 
 * @param string $revierId Optional: Filtern nach Revier-ID
 * @return array Array mit Tierinformationen
 */
function loadTiere($revierId = null) {
    $tiere = [];
    
    if (file_exists(TIERE_FILE)) {
        $lines = file(TIERE_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            $parts = explode(',', $line);
            
            if (count($parts) >= 6) {
                $tierId = trim($parts[0]);
                $tierRevierId = trim($parts[1]);
                
                // Filter nach Revier-ID wenn angegeben
                if ($revierId !== null && $tierRevierId !== $revierId) {
                    continue;
                }
                
                $tiere[$tierId] = [
                    'id' => $tierId,
                    'revier' => $tierRevierId,
                    'art' => trim($parts[2]),
                    'geschlecht' => trim($parts[3]),
                    'alter' => trim($parts[4]),
                    'besonderheiten' => trim($parts[5]),
                    'display' => trim($parts[2]) . ' (' . trim($parts[3]) . ', ' . trim($parts[4]) . ')'
                ];
            }
        }
    }
    
    return $tiere;
}

/**
 * Lade und parse Sichtungen aus der Datei
 * 
 * @param string $revierId Optional: Filtern nach Revier-ID
 * @param string $tierId Optional: Filtern nach Tier-ID
 * @return array Array mit Sichtungsinformationen
 */
function loadSichtungen($revierId = null, $tierId = null) {
    $sichtungen = [];
    
    if (file_exists(SICHTUNGEN_FILE)) {
        $lines = file(SICHTUNGEN_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            $parts = explode(',', $line);
            
            if (count($parts) >= 5) {
                $sichtungsId = trim($parts[0]);
                $sichtungsRevierId = trim($parts[1]);
                $sichtungsTierId = trim($parts[2]);
                
                // Filter nach Revier-ID wenn angegeben
                if ($revierId !== null && $sichtungsRevierId !== $revierId) {
                    continue;
                }
                
                // Filter nach Tier-ID wenn angegeben
                if ($tierId !== null && $sichtungsTierId !== $tierId) {
                    continue;
                }
                
                $fotoUrl = isset($parts[5]) ? trim($parts[5]) : '';
                $besonderheiten = isset($parts[6]) ? trim($parts[6]) : '';
                
                // Prüfe ob parts[5] eine URL ist oder Besonderheiten (Legacy-Kompatibilität)
                if (!empty($fotoUrl) && !filter_var($fotoUrl, FILTER_VALIDATE_URL)) {
                    $besonderheiten = $fotoUrl;
                    $fotoUrl = '';
                }
                
                $sichtungen[$sichtungsId] = [
                    'id' => $sichtungsId,
                    'revier' => $sichtungsRevierId,
                    'tier' => $sichtungsTierId,
                    'datum' => trim($parts[3]),
                    'zeit' => trim($parts[4]),
                    'foto_url' => $fotoUrl,
                    'besonderheiten' => $besonderheiten
                ];
            }
        }
    }
    
    return $sichtungen;
}

/**
 * Lade und parse Abschüsse aus der Datei
 * 
 * @param string $revierId Optional: Filtern nach Revier-ID
 * @param string $tierId Optional: Filtern nach Tier-ID
 * @return array Array mit Abschussinformationen
 */
function loadAbschuesse($revierId = null, $tierId = null) {
    $abschuesse = [];
    
    if (file_exists(ABSCHUESSE_FILE)) {
        $lines = file(ABSCHUESSE_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            $parts = explode(',', $line);
            
            if (count($parts) >= 5) {
                $abschussId = trim($parts[0]);
                $abschussRevierId = trim($parts[1]);
                $abschussTierId = trim($parts[2]);
                
                // Filter nach Revier-ID wenn angegeben
                if ($revierId !== null && $abschussRevierId !== $revierId) {
                    continue;
                }
                
                // Filter nach Tier-ID wenn angegeben
                if ($tierId !== null && $abschussTierId !== $tierId) {
                    continue;
                }
                
                $fotoUrl = isset($parts[5]) ? trim($parts[5]) : '';
                $schussdistanz = isset($parts[6]) ? trim($parts[6]) : '';
                $treffpunktlage = isset($parts[7]) ? trim($parts[7]) : '';
                $fluchtstrecke = isset($parts[8]) ? trim($parts[8]) : '';
                $besonderheiten = isset($parts[9]) ? trim($parts[9]) : '';
                
                // Prüfe ob parts[5] eine URL ist oder Schussdistanz (Legacy-Kompatibilität)
                if (!empty($fotoUrl) && !filter_var($fotoUrl, FILTER_VALIDATE_URL)) {
                    $besonderheiten = isset($parts[8]) ? trim($parts[8]) : '';
                    $fluchtstrecke = isset($parts[7]) ? trim($parts[7]) : '';
                    $treffpunktlage = isset($parts[6]) ? trim($parts[6]) : '';
                    $schussdistanz = $fotoUrl;
                    $fotoUrl = '';
                }
                
                $abschuesse[$abschussId] = [
                    'id' => $abschussId,
                    'revier' => $abschussRevierId,
                    'tier' => $abschussTierId,
                    'datum' => trim($parts[3]),
                    'zeit' => trim($parts[4]),
                    'foto_url' => $fotoUrl,
                    'schussdistanz' => $schussdistanz,
                    'treffpunktlage' => $treffpunktlage,
                    'fluchtstrecke' => $fluchtstrecke,
                    'besonderheiten' => $besonderheiten
                ];
            }
        }
    }
    
    return $abschuesse;
}

/**
 * Generiere eine neue ID mit automatischer Nummerierung
 * 
 * @param string $prefix Präfix für die ID (R-, T-, S-, A-)
 * @param string $file Dateipfad zur Datei mit bestehenden IDs
 * @return string Neue ID
 */
function generateId($prefix, $file) {
    $lastId = 0;
    
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            $parts = explode(',', $line);
            
            if (count($parts) >= 1 && strpos($parts[0], $prefix) === 0) {
                $idNum = (int) substr($parts[0], 2);
                if ($idNum > $lastId) {
                    $lastId = $idNum;
                }
            }
        }
    }
    
    return $prefix . str_pad($lastId + 1, 3, '0', STR_PAD_LEFT);
}

/**
 * Prüfe ob ein Tier abgeschossen wurde
 * 
 * @param string $tierId Die zu prüfende Tier-ID
 * @return bool True wenn das Tier abgeschossen wurde
 */
function istTierAbgeschossen($tierId) {
    $abschuesse = loadAbschuesse(null, $tierId);
    return !empty($abschuesse);
}

/**
 * Erhalte den letzten Sichtungszeitpunkt eines Tieres
 * 
 * @param string $tierId Die Tier-ID
 * @return string|null Letzter Sichtungszeitpunkt im Format "YYYY-MM-DD HH:MM" oder null
 */
function getLetzterSichtungszeitpunkt($tierId) {
    $sichtungen = loadSichtungen(null, $tierId);
    
    if (empty($sichtungen)) {
        return null;
    }
    
    $letzterZeitpunkt = null;
    
    foreach ($sichtungen as $sichtung) {
        $zeitpunkt = $sichtung['datum'] . ' ' . $sichtung['zeit'];
        
        if ($letzterZeitpunkt === null || $zeitpunkt > $letzterZeitpunkt) {
            $letzterZeitpunkt = $zeitpunkt;
        }
    }
    
    return $letzterZeitpunkt;
}

/**
 * Zähle die Anzahl der Sichtungen für ein Tier
 * 
 * @param string $tierId Die Tier-ID
 * @return int Anzahl der Sichtungen
 */
function countSichtungen($tierId) {
    $sichtungen = loadSichtungen(null, $tierId);
    return count($sichtungen);
}

/**
 * Erhalte eine Liste von möglichen Tierarten
 * 
 * @return array Liste der Tierarten
 */
function getTierarten() {
    return [
        'Rehwild',
        'Schwarzwild',
        'Gämse',
        'Rotwild',
        'Rotfuchs',
        'Dachs',
        'Stockente',
        'Tafelente',
        'Reiherente',
        'Blässhuhn',
        'Kormoran',
        'Entenbastard'
    ];
}

/**
 * Hilfsfunktion zur Anzeige einer Fehlermeldung
 * 
 * @param string $message Die Fehlermeldung
 */
function showError($message) {
    echo '<div class="error-message">' . h($message) . '</div>';
}

/**
 * Hilfsfunktion zur Anzeige einer Erfolgsmeldung
 * 
 * @param string $message Die Erfolgsmeldung
 */
function showSuccess($message) {
    echo '<div class="success-message">' . h($message) . '</div>';
}

/**
 * Prüfe ob die aktuelle Anfrage ein POST-Request ist
 * 
 * @return bool True wenn die Anfrage ein POST-Request ist
 */
function isPostRequest() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Sichere Weiterleitung
 * 
 * @param string $url URL für die Weiterleitung
 */
function redirect($url) {
    header('Location: ' . $url);
    exit();
}

/**
 * Erstellt ein Backup der Datendateien
 * 
 * @param string $backupDir Verzeichnis für das Backup
 * @return array Status des Backups und Nachricht
 */
function createBackup($backupDir) {
    // Dateien, die gesichert werden sollen
    $files = [
        REVIERE_FILE,
        TIERE_FILE,
        SICHTUNGEN_FILE,
        ABSCHUESSE_FILE
    ];
    
    // Erstelle Backup-Verzeichnis falls es nicht existiert
    if (!is_dir($backupDir)) {
        if (!mkdir($backupDir, 0755, true)) {
            return [
                'success' => false,
                'message' => 'Backup-Verzeichnis konnte nicht erstellt werden.'
            ];
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
        return [
            'success' => false,
            'message' => 'Backup-Datei konnte nicht erstellt werden.'
        ];
    }
    
    // Füge Dateien zum Backup hinzu
    $backupSuccess = true;
    foreach ($files as $file) {
        if (file_exists($file)) {
            if (!$zip->addFile($file, basename($file))) {
                $backupSuccess = false;
                break;
            }
        }
    }
    
    $zip->close();
    
    if ($backupSuccess) {
        return [
            'success' => true,
            'message' => 'Backup wurde erfolgreich erstellt: ' . basename($backupFilename),
            'filename' => basename($backupFilename)
        ];
    } else {
        // Lösche fehlerhafte Backup-Datei
        if (file_exists($backupFilename)) {
            unlink($backupFilename);
        }
        
        return [
            'success' => false,
            'message' => 'Es gab Probleme beim Erstellen des Backups.'
        ];
    }
}

/**
 * Exportiert Daten im angegebenen Format
 * 
 * @param array $data Die zu exportierenden Daten
 * @param string $format Das Format (csv oder json)
 * @param string $filename Der Dateiname
 */
function exportData($data, $format, $filename) {
    if ($format === 'csv') {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        
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
    } elseif ($format === 'json') {
        header('Content-Type: application/json; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.json"');
        
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/**
 * Formatiert Dateigröße in lesbare Form
 * 
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

/**
 * Protokolliert eine Aktion im System
 * 
 * @param string $action Die durchgeführte Aktion
 * @param string $details Details zur Aktion
 * @param string $status Status der Aktion (success, error, warning, info)
 */
function logAction($action, $details = '', $status = 'info') {
    $logFile = __DIR__ . '/logs/system.log';
    $logDir = dirname($logFile);
    
    // Erstelle Log-Verzeichnis falls es nicht existiert
    if (!is_dir($logDir)) {
        if (!mkdir($logDir, 0755, true)) {
            return false;
        }
        
        // Erstelle .htaccess um den Zugriff auf das Log-Verzeichnis zu verhindern
        $htaccessFile = $logDir . '/.htaccess';
        file_put_contents($htaccessFile, "Order deny,allow\nDeny from all");
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $logEntry = sprintf(
        "[%s] [%s] [%s] %s | %s | %s\n",
        $timestamp,
        $status,
        $ip,
        $action,
        $details,
        $userAgent
    );
    
    return file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX) !== false;
}