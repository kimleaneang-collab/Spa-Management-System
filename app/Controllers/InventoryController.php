<?php
declare(strict_types=1);

final class InventoryController
{
    // Show the stock in and out page to signed-in users.
    public function stock(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        require __DIR__ . '/../Views/inventory/stock-in-out.php';
    }

    // Show the supplier page to signed-in users.
    public function suppliers(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('login');
        }

        require __DIR__ . '/../Views/inventory/suppliers.php';
    }
}