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
$plainPassword = "admin123"; // change this to whatever password you want
echo password_hash($plainPassword, PASSWORD_BCRYPT);
