<?php
declare(strict_types=1);

// Generate one password hash from terminal input without saving the password in this file.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('Not found.');
}

fwrite(STDOUT, "Enter a password (your typing will be visible): ");
$plainPassword = fgets(STDIN);
if ($plainPassword === false) {
    fwrite(STDERR, "Could not read the password.\n");
    exit(1);
}
$plainPassword = rtrim($plainPassword, "\r\n");

if (
    strlen($plainPassword) < 16
    || strlen($plainPassword) > 255
    || preg_match('/[A-Za-z]/', $plainPassword) !== 1
    || preg_match('/[0-9]/', $plainPassword) !== 1
    || preg_match('/[^A-Za-z0-9\s]/', $plainPassword) !== 1
) {
    fwrite(STDERR, "Use 16-255 characters with a letter, a number, and a symbol.\n");
    exit(1);
}

$passwordHash = password_hash($plainPassword, PASSWORD_DEFAULT);
if ($passwordHash === false) {
    fwrite(STDERR, "Could not create the password hash.\n");
    exit(1);
}

fwrite(STDOUT, $passwordHash . PHP_EOL);
