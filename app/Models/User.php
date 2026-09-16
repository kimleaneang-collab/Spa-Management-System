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
            'SELECT u.id, u.full_name, u.username, u.email, u.phone, u.status, u.role_id, r.name AS role_name
             FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             ORDER BY u.id DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT u.id, u.full_name, u.username, u.email, u.phone, u.status, u.role_id, r.name AS role_name FROM users u LEFT JOIN roles r ON r.id = u.role_id WHERE u.id = :id');
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user !== false ? $user : null;
    }

    public function roles(): array
    {
        $stmt = $this->db->query("SELECT name FROM roles WHERE name IN ('admin', 'staff', 'receptionist') ORDER BY FIELD(name, 'admin', 'staff', 'receptionist')");

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function create(
        string $name,
        string $username,
        string $email,
        string $phone,
        string $roleName,
        string $password
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
            ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ':full_name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':status' => 'active',
        ]);
    }

    public function update(int $id, string $name, string $username, string $email, string $phone, string $roleName, string $status, string $password = ''): void
    {
        $roleId = $this->roleId($roleName);
        $fields = 'role_id = :role_id, full_name = :full_name, username = :username, email = :email, phone = :phone, status = :status';
        $parameters = [
            ':id' => $id,
            ':role_id' => $roleId,
            ':full_name' => $name,
            ':username' => $username,
            ':email' => $email,
            ':phone' => $phone,
            ':status' => $status,
        ];
        if ($password !== '') {
            $fields .= ', password_hash = :password_hash';
            $parameters[':password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }
        $stmt = $this->db->prepare('UPDATE users SET ' . $fields . ' WHERE id = :id');
        $stmt->execute($parameters);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    private function roleId(string $roleName): int
    {
        $roleName = strtolower(trim($roleName));
        $stmt = $this->db->prepare('SELECT id FROM roles WHERE name = :name LIMIT 1');
        $stmt->execute([':name' => $roleName]);
        $roleId = $stmt->fetchColumn();

        if ($roleId !== false) {
            return (int) $roleId;
        }

        $insert = $this->db->prepare('INSERT INTO roles (name, description) VALUES (:name, :description)');
        $insert->execute([':name' => $roleName, ':description' => 'Custom role']);

        return (int) $this->db->lastInsertId();
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