<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

final class AuditController
{
    // Show the latest audit events to administrators.
    public function index(): void
    {
        require_roles(['admin']);

        $statement = Database::connection()->query(
            'SELECT a.action, a.table_name, a.record_id, a.ip_address, a.created_at,
                    u.username
             FROM audit_logs a
             LEFT JOIN users u ON u.id = a.user_id
             ORDER BY a.id DESC
             LIMIT 200'
        );
        $auditEvents = $statement->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../Views/settings/audit-logs.php';
    }
}
