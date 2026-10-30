<?php
declare(strict_types=1);

final class DatabaseBackup
{
    private const FILE_PATTERN = '/^spa-management-\d{8}-\d{6}-[a-f0-9]{12}\.sql$/';

    // Store the database connection used to create backups.
    public function __construct(private PDO $db)
    {
    }

    // Write the database schema and rows to a private backup file.
    public function create(): string
    {
        $directory = $this->backupDirectory();
        $filename = 'spa-management-' . date('Ymd-His') . '-' . bin2hex(random_bytes(6)) . '.sql';
        $path = $directory . DIRECTORY_SEPARATOR . $filename;
        $stream = fopen($path, 'xb');
        if ($stream === false) {
            throw new RuntimeException('Unable to create the database backup file.');
        }

        $transactionStarted = false;
        try {
            if (!chmod($path, 0600)) {
                throw new RuntimeException('Unable to restrict access to the database backup file.');
            }

            $databaseName = (string) $this->db->query('SELECT DATABASE()')->fetchColumn();
            if ($databaseName === '') {
                throw new RuntimeException('Unable to determine the connected database.');
            }

            $this->db->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
            $this->db->beginTransaction();
            $transactionStarted = true;

            $this->write($stream, "-- Relax Spa database backup\n-- Created " . date(DATE_ATOM) . "\n\n");
            $this->write($stream, 'CREATE DATABASE IF NOT EXISTS ' . $this->quoteIdentifier($databaseName) . ";\n");
            $this->write($stream, 'USE ' . $this->quoteIdentifier($databaseName) . ";\n");
            $this->write($stream, "SET FOREIGN_KEY_CHECKS=0;\n\n");

            $tables = $this->db->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_NUM);
            foreach ($tables as $tableRow) {
                $tableName = (string) ($tableRow[0] ?? '');
                if ($tableName === '') {
                    throw new RuntimeException('The database returned an invalid table name.');
                }

                $quotedTable = $this->quoteIdentifier($tableName);
                $createResult = $this->db->query('SHOW CREATE TABLE ' . $quotedTable)->fetch(PDO::FETCH_NUM);
                if (!is_array($createResult) || !isset($createResult[1])) {
                    throw new RuntimeException('Unable to read the definition for database table ' . $tableName . '.');
                }

                $this->write($stream, "\n-- Table: " . $tableName . "\n");
                $this->write($stream, 'DROP TABLE IF EXISTS ' . $quotedTable . ";\n");
                $this->write($stream, (string) $createResult[1] . ";\n");

                $columns = $this->db->query('SHOW COLUMNS FROM ' . $quotedTable)->fetchAll(PDO::FETCH_COLUMN);
                if ($columns === []) {
                    continue;
                }

                $quotedColumns = implode(', ', array_map(
                    // Quote each column name before adding it to the backup SQL.
                    fn (string $column): string => $this->quoteIdentifier($column),
                    array_map('strval', $columns)
                ));
                $rows = $this->db->query('SELECT * FROM ' . $quotedTable);
                while (($row = $rows->fetch(PDO::FETCH_NUM)) !== false) {
                    $values = array_map(
                        // Convert each database value into safe SQL text.
                        fn (mixed $value): string => $this->quoteValue($value),
                        $row
                    );
                    $this->write(
                        $stream,
                        'INSERT INTO ' . $quotedTable . ' (' . $quotedColumns . ') VALUES (' . implode(', ', $values) . ");\n"
                    );
                }
            }

            $this->write($stream, "\nSET FOREIGN_KEY_CHECKS=1;\n");
            if (!fflush($stream)) {
                throw new RuntimeException('Unable to finish writing the database backup.');
            }
            $this->db->commit();
            $transactionStarted = false;
            fclose($stream);

            return $filename;
        } catch (Throwable $exception) {
            if ($transactionStarted && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            fclose($stream);
            if (is_file($path) && !unlink($path)) {
                error_log('Unable to remove incomplete database backup: ' . $path);
            }
            throw $exception;
        }
    }

    // List the newest valid backup files and their sizes and dates.
    public function available(): array
    {
        $directory = $this->backupDirectory();
        $files = glob($directory . DIRECTORY_SEPARATOR . 'spa-management-*.sql') ?: [];
        $backups = [];
        foreach ($files as $path) {
            $filename = basename($path);
            if (preg_match(self::FILE_PATTERN, $filename) === 1 && is_file($path) && !is_link($path)) {
                $backups[] = [
                    'filename' => $filename,
                    'size' => filesize($path),
                    'created_at' => filemtime($path),
                ];
            }
        }

        usort($backups,
            // Sort backup details from newest to oldest.
            static fn (array $left, array $right): int => $right['created_at'] <=> $left['created_at']
        );

        return array_slice($backups, 0, 20);
    }

    // Return the safe path to a named backup, or null if it is unavailable.
    public function pathFor(string $filename): ?string
    {
        if (preg_match(self::FILE_PATTERN, $filename) !== 1) {
            return null;
        }

        $path = $this->backupDirectory() . DIRECTORY_SEPARATOR . $filename;

        return is_file($path) && !is_link($path) ? $path : null;
    }

    // Create and verify a writable backup directory outside the public web folder.
    private function backupDirectory(): string
    {
        $configuredDirectory = trim((string) (getenv('APP_BACKUP_DIR') ?: ''));
        $directory = $configuredDirectory !== ''
            ? $configuredDirectory
            : dirname(dirname(APP_ROOT)) . DIRECTORY_SEPARATOR . 'spa-management-backups';

        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create the private database backup directory.');
        }

        $realDirectory = realpath($directory);
        $publicDirectory = realpath(APP_ROOT . DIRECTORY_SEPARATOR . 'public');
        if ($realDirectory === false || $publicDirectory === false) {
            throw new RuntimeException('Unable to verify the database backup directory.');
        }

        $publicPrefix = rtrim($publicDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (str_starts_with(strtolower($realDirectory . DIRECTORY_SEPARATOR), strtolower($publicPrefix))) {
            throw new RuntimeException('Database backups must be stored outside the public web directory.');
        }
        if (!is_writable($realDirectory) || !chmod($realDirectory, 0700)) {
            throw new RuntimeException('The database backup directory is not securely writable.');
        }

        return $realDirectory;
    }

    // Escape a database name or column name for use in SQL.
    private function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    // Convert a value into safely quoted SQL text, preserving null values.
    private function quoteValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        $quoted = $this->db->quote((string) $value, PDO::PARAM_STR);
        if ($quoted === false) {
            throw new RuntimeException('Unable to safely encode a database backup value.');
        }

        return $quoted;
    }

    // Write all supplied text to the backup stream or raise an error.
    private function write($stream, string $contents): void
    {
        $offset = 0;
        $length = strlen($contents);
        while ($offset < $length) {
            $written = fwrite($stream, substr($contents, $offset));
            if ($written === false || $written === 0) {
                throw new RuntimeException('Unable to write the database backup file.');
            }
            $offset += $written;
        }
    }
}
