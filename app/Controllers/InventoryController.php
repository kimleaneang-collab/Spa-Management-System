<?php
declare(strict_types=1);

final class InventoryController
{
    public function stock(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        require __DIR__ . '/../Views/inventory/stock-in-out.php';
    }

    public function suppliers(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        require __DIR__ . '/../Views/inventory/suppliers.php';
    }
}