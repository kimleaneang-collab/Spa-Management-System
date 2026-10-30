<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/DatabaseBackup.php';
require_once __DIR__ . '/../../config/database.php';

final class SettingsController
{
    /** Display general settings to administrators and managers. */
    public function general(): void
    {
        require_roles(['admin', 'manager']);

        require __DIR__ . '/../Views/settings/general.php';
    }

    /**
     * Create, download, or list database backups for administrators.
     *
     * POST creates a backup. A GET request with a download parameter streams a
     * backup file. A regular GET request displays the available backups.
     */
    public function backup(): void
    {
        require_roles(['admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (($_POST['action'] ?? '') !== 'create') {
                http_response_code(400);
                exit('Unsupported backup action.');
            }

            $db = Database::connection();
            $filename = (new DatabaseBackup($db))->create();
            write_audit_log($db, 'database.backup_created', 'database_backups', null, null, [
                'filename' => $filename,
            ]);
            $_SESSION['backup_message'] = 'Database backup created successfully.';
            redirect('backup');
        }

        if (isset($_GET['download'])) {
            $backupModel = new DatabaseBackup(Database::connection());
            $filename = (string) $_GET['download'];
            $path = $backupModel->pathFor($filename);
            if ($path === null) {
                http_response_code(404);
                exit('Backup not found.');
            }

            write_audit_log(Database::connection(), 'database.backup_downloaded', 'database_backups', null, null, [
                'filename' => $filename,
            ]);
            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . (string) filesize($path));
            header('X-Content-Type-Options: nosniff');
            readfile($path);
            exit;
        }

        // The backup list is only available through a regular GET request.
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            header('Allow: GET, POST');
            exit('Method not allowed.');
        }

        $backupModel = new DatabaseBackup(Database::connection());
        $backups = $backupModel->available();
        $backupMessage = (string) ($_SESSION['backup_message'] ?? '');
        unset($_SESSION['backup_message']);

        require __DIR__ . '/../Views/settings/backup.php';
    }
}
