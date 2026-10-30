<?php
declare(strict_types=1);

final class User
{
    private PDO $db;

    // Store the database connection used by this model.
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Return all users with their role names and account details.
    public function all(): array
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.full_name, u.username, u.email, u.phone, u.profile_image, u.status, u.role_id, u.last_login_at, r.name AS role_name
             FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             ORDER BY u.id DESC'
        );
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Find one user by ID, or return null if the user does not exist.
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT u.id, u.full_name, u.username, u.email, u.phone, u.profile_image, u.status, u.role_id, r.name AS role_name FROM users u LEFT JOIN roles r ON r.id = u.role_id WHERE u.id = :id');
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user !== false ? $user : null;
    }

    // Check the current password and save a hash of the new password when it matches.
    public function changeOwnPassword(int $id, string $currentPassword, string $newPassword): bool
    {
        $stmt = $this->db->prepare('SELECT password_hash FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $passwordHash = $stmt->fetchColumn();

        if ($passwordHash === false || !password_verify($currentPassword, (string) $passwordHash)) {
            return false;
        }

        $update = $this->db->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id');
        $update->execute([
            ':id' => $id,
            ':password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);

        return true;
    }

    // Save the user's name, email, and phone after checking that the email is unused.
    public function updateOwnProfile(int $id, string $name, string $email, string $phone): bool
    {
        if ($email !== '') {
            $emailCheck = $this->db->prepare(
                'SELECT id FROM users WHERE email = :email AND id <> :id LIMIT 1'
            );
            $emailCheck->execute([
                ':email' => $email,
                ':id' => $id,
            ]);
            if ($emailCheck->fetchColumn() !== false) {
                return false;
            }
        }

        $statement = $this->db->prepare(
            'UPDATE users
             SET full_name = :full_name, email = :email, phone = :phone
             WHERE id = :id'
        );
        $statement->execute([
            ':id' => $id,
            ':full_name' => $name,
            ':email' => $email !== '' ? $email : null,
            ':phone' => $phone !== '' ? $phone : null,
        ]);

        return $statement->rowCount() > 0 || $this->find($id) !== null;
    }

    // Add standard roles if needed and return the roles available for user accounts.
    public function roles(): array
    {
        $this->db->exec(
            "INSERT IGNORE INTO roles (name, description)
             VALUES
                ('manager', 'Spa operations management access'),
                ('receptionist', 'Customer and appointment access'),
                ('cashier', 'Point-of-sale and billing access')"
        );

        $stmt = $this->db->query(
            "SELECT name FROM roles
             WHERE name IN ('admin', 'manager', 'staff', 'receptionist', 'cashier')
             ORDER BY FIELD(name, 'admin', 'manager', 'staff', 'receptionist', 'cashier')"
        );

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Create a user account, assign its role, and store the password as a hash.
    public function create(
        string $name,
        string $username,
        string $email,
        string $phone,
        string $roleName,
        string $password,
        string $profileImage = ''
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
            'INSERT INTO users (role_id, username, password_hash, full_name, email, phone, profile_image, status, created_at)
             VALUES (:role_id, :username, :password_hash, :full_name, :email, :phone, :profile_image, :status, NOW())'
        );
        $stmt->execute([
            ':role_id' => $roleId ?? 1,
            ':username' => $username,
            ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ':full_name' => $name,
            ':email' => $email !== '' ? $email : null,
            ':phone' => $phone !== '' ? $phone : null,
            ':profile_image' => $profileImage !== '' ? $profileImage : null,
            ':status' => 'active',
        ]);
    }

    // Save a profile image and return the previous image path.
    public function updateProfileImage(int $id, string $profileImage): string
    {
        $lookup = $this->db->prepare('SELECT profile_image FROM users WHERE id = :id FOR UPDATE');
        $lookup->execute([':id' => $id]);
        $currentImage = $lookup->fetchColumn();
        if ($currentImage === false) {
            throw new RuntimeException('The selected therapist login account could not be found.');
        }

        $update = $this->db->prepare('UPDATE users SET profile_image = :profile_image WHERE id = :id');
        $update->execute([
            ':id' => $id,
            ':profile_image' => $profileImage !== '' ? $profileImage : null,
        ]);

        return (string) ($currentImage ?? '');
    }

    // Set a user's email address, using null when the value is blank.
    public function updateEmail(int $id, string $email): void
    {
        $stmt = $this->db->prepare('UPDATE users SET email = :email WHERE id = :id');
        $stmt->execute([
            ':id' => $id,
            ':email' => $email !== '' ? $email : null,
        ]);
    }

    // Update user details and save a password hash only when a new password is provided.
    public function update(int $id, string $name, string $username, string $email, string $phone, string $roleName, string $status, string $password = '', string $profileImage = ''): void
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
        if ($profileImage !== '') {
            $fields .= ', profile_image = :profile_image';
            $parameters[':profile_image'] = $profileImage;
        }
        if ($password !== '') {
            $fields .= ', password_hash = :password_hash';
            $parameters[':password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }
        $stmt = $this->db->prepare('UPDATE users SET ' . $fields . ' WHERE id = :id');
        $stmt->execute($parameters);
    }

    // Remove a user by ID.
    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    // Check whether another active administrator account exists.
    public function hasOtherActiveAdmin(int $userId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*)
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE r.name = 'admin'
               AND u.status = 'active'
               AND u.id <> :id"
        );
        $stmt->execute([':id' => $userId]);

        return (int) $stmt->fetchColumn() > 0;
    }

    // Find a role by name or create it if it is not already in the database.
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
                u.profile_image,
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