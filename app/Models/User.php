<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

final class User
{
    public function __construct(private PDO $db)
    {
    }

    public function findByUsername(string $username): ?array
    {
        $sql = "
            SELECT
                u.id,
                u.username,
                u.password_hash,
                u.full_name,
                u.email,
                u.phone,
                u.role_id,
                u.status,
                r.name AS role_name
            FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            WHERE u.username = :username
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);

        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function updateLastLogin(int $userId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET last_login_at = NOW() WHERE id = :id'
        );
        $stmt->execute(['id' => $userId]);
    }

    public function logAttempt(
        string $username,
        ?int $userId,
        string $ip,
        bool $success
    ): void {
        $stmt = $this->db->prepare(
            'INSERT INTO login_attempts
                (username, user_id, ip_address, was_successful)
             VALUES
                (:username, :user_id, :ip_address, :was_successful)'
        );

        $stmt->execute([
            'username' => $username,
            'user_id' => $userId,
            'ip_address' => $ip,
            'was_successful' => $success ? 1 : 0,
        ]);
    }

    public function failedAttemptsSince(string $username, int $minutes = 15): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
             FROM login_attempts
             WHERE username = :username
               AND was_successful = 0
               AND attempted_at >= (NOW() - INTERVAL :minutes MINUTE)'
        );

        // MySQL does not safely bind an INTERVAL value in every configuration,
        // so validate the integer before interpolating it.
        $minutes = max(1, min(60, $minutes));
        $sql = 'SELECT COUNT(*)
                FROM login_attempts
                WHERE username = :username
                  AND was_successful = 0
                  AND attempted_at >= (NOW() - INTERVAL ' . $minutes . ' MINUTE)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);

        return (int) $stmt->fetchColumn();
    }
}
