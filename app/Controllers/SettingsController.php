<?php
declare(strict_types=1);

final class SettingsController
{
    public function general(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        require __DIR__ . '/../Views/settings/general.php';
    }

    public function backup(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        require __DIR__ . '/../Views/settings/backup.php';
    }
}
