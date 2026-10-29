<?php
/**
 * Run this once from your browser or CLI to get a bcrypt hash
 * for the password you want to use, e.g.:
 *   php utils/generate_hash.php
 * or visit: http://localhost/spa-management-system/utils/generate_hash.php
 *
 * Copy the output into the `password` column for your admin
 * row in the users table, then DELETE this file.
 */
$plainPassword = "admin@00991111#$"; // change this to whatever password you want
echo password_hash($plainPassword, PASSWORD_BCRYPT);

$plainPassword = "kimlean@110105#$"; // change this to whatever password you want
echo password_hash($plainPassword, PASSWORD_BCRYPT);

$plainPassword = "receptionist@123"; // change this to whatever password you want
echo password_hash($plainPassword, PASSWORD_BCRYPT);

$plainPassword = "therapist@12345#"; // change this to whatever password you want
echo password_hash($plainPassword, PASSWORD_BCRYPT);

$plainPassword = "cashier@432152#$"; // change this to whatever password you want
echo password_hash($plainPassword, PASSWORD_BCRYPT);
