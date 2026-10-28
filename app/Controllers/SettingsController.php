<?php
declare(strict_types=1);

final class SettingsController
{
    public function general(): void
    {
        require_roles(['admin', 'manager']);

        require __DIR__ . '/../Views/settings/general.php';
    }

    public function backup(): void
    {
        require_roles(['admin']);

        require __DIR__ . '/../Views/settings/backup.php';
    }
}
