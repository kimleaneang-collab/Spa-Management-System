<?php

require_once __DIR__ . '/config/database.php';

try {

    $db = Database::connection();

    echo '<h1>Database Connected Successfully</h1>';

    $stmt = $db->query(
        "SELECT DATABASE() AS db_name"
    );

    $result = $stmt->fetch();

    echo '<p>Database: ' .
        htmlspecialchars($result['db_name']) .
        '</p>';

} catch (Throwable $e) {

    echo '<h1>Database Error</h1>';

    echo '<pre>';
    echo htmlspecialchars($e->getMessage());
    echo '</pre>';
}