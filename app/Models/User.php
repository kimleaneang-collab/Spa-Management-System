<?php
class User {
    private $conn;
    private $table = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($email) {
        $query = "SELECT u.user_id, u.full_name, u.email, u.password, r.role_name 
                  FROM " . $this->table . " u
                  JOIN roles r ON u.role_id = r.role_id
                  WHERE u.email = :email LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>