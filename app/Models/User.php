<?php
declare(strict_types=1);

final class User
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all(int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            'SELECT u.full_name, u.username, u.email, u.status, r.name AS role_name
             FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             ORDER BY u.id DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function roles(): array
    {
        $stmt = $this->db->query('SELECT name FROM roles ORDER BY id ASC');

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function create(
        string $name,
        string $username,
        string $email,
        string $phone,
        string $roleName
    ): void {
        $roleId = null;
        if ($roleName !== '') {
            $roleStmt = $this->db->prepare(
                'SELECT id FROM roles WHERE name = :name LIMIT 1'
            );
            $roleStmt->execute([':name' => strtolower($roleName)]);
            $roleId = $roleStmt->fetchColumn();

            if (!$roleId) {
                $insertRole = $this->db->prepare(
                    'INSERT INTO roles (name, description) VALUES (:name, :description)'
                );
                $insertRole->execute([
                    ':name' => strtolower($roleName),
                    ':description' => 'Custom role',
                ]);
                $roleId = (int) $this->db->lastInsertId();
            }
        }

        $stmt = $this->db->prepare(
            'INSERT INTO users (role_id, username, password_hash, full_name, email, phone, status, created_at)
             VALUES (:role_id, :username, :password_hash, :full_name, :email, :phone, :status, NOW())'
        );
        $stmt->execute([
            ':role_id' => $roleId ?? 1,
            ':username' => $username,
            ':password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            ':full_name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':status' => 'active',
        ]);
    }

    /**
     * Find user by username.
     */
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
            FROM users AS u
            INNER JOIN roles AS r
                ON r.id = u.role_id
            WHERE u.username = :username
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':username' => $username
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user !== false ? $user : null;
    }

    /**
     * Update last login time.
     */
    public function updateLastLogin(int $userId): void
    {
        $stmt = $this->db->prepare(
            "
            UPDATE users
            SET last_login_at = NOW()
            WHERE id = :id
            "
        );

        $stmt->execute([
            ':id' => $userId
        ]);
    }

    /**
     * Save login attempt.
     */
    public function logAttempt(
        string $username,
        ?int $userId,
        string $ip,
        bool $success
    ): void {
        $stmt = $this->db->prepare(
            "
            INSERT INTO login_attempts
            (
                username,
                user_id,
                ip_address,
                was_successful
            )
            VALUES
            (
                :username,
                :user_id,
                :ip_address,
                :was_successful
            )
            "
        );

        $stmt->execute([
            ':username' => $username,
            ':user_id' => $userId,
            ':ip_address' => $ip,
            ':was_successful' => $success ? 1 : 0,
        ]);
    }

    /**
     * Count failed login attempts.
     */
    public function failedAttemptsSince(
        string $username,
        int $minutes = 15
    ): int {
        /*
         * The value is strictly converted to an integer
         * before being inserted into the SQL.
         */
        $minutes = max(
            1,
            min(60, (int) $minutes)
        );

        $sql = "
            SELECT COUNT(*)
            FROM login_attempts
            WHERE username = :username
              AND was_successful = 0
              AND attempted_at >=
                  (NOW() - INTERVAL {$minutes} MINUTE)
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':username' => $username
        ]);

        return (int) $stmt->fetchColumn();
    }
}