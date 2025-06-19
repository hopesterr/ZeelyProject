<?php
/**
 * User Model
 */

class User {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (username, email, password_hash, first_name, last_name) 
                  VALUES (:username, :email, :password_hash, :first_name, :last_name)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password_hash', password_hash($data['password'], PASSWORD_DEFAULT));
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        
        return $stmt->execute();
    }

    public function login($email, $password) {
        $query = "SELECT id, username, email, password_hash, first_name, last_name, role, is_active 
                  FROM " . $this->table_name . " 
                  WHERE email = :email AND is_active = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();
            if (password_verify($password, $user['password_hash'])) {
                return $user;
            }
        }
        return false;
    }

    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function findByUsername($username) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function findById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function updateProfile($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET first_name = :first_name, last_name = :last_name, 
                      email = :email, bio = :bio, updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':bio', $data['bio']);
        
        return $stmt->execute();
    }

    public function updatePassword($id, $new_password) {
        $query = "UPDATE " . $this->table_name . " 
                  SET password_hash = :password_hash, updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':password_hash', password_hash($new_password, PASSWORD_DEFAULT));
        
        return $stmt->execute();
    }

    public function createResetToken($email) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $query = "UPDATE " . $this->table_name . " 
                  SET reset_token = :token, reset_token_expires = :expires 
                  WHERE email = :email";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expires', $expires);
        $stmt->bindParam(':email', $email);
        
        if ($stmt->execute()) {
            return $token;
        }
        return false;
    }

    public function verifyResetToken($token) {
        $query = "SELECT id, email FROM " . $this->table_name . " 
                  WHERE reset_token = :token AND reset_token_expires > NOW()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    public function resetPassword($token, $new_password) {
        $query = "UPDATE " . $this->table_name . " 
                  SET password_hash = :password_hash, reset_token = NULL, 
                      reset_token_expires = NULL, updated_at = CURRENT_TIMESTAMP
                  WHERE reset_token = :token";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':password_hash', password_hash($new_password, PASSWORD_DEFAULT));
        $stmt->bindParam(':token', $token);
        
        return $stmt->execute();
    }

    public function getAllUsers() {
        $query = "SELECT id, username, email, first_name, last_name, role, is_active, created_at 
                  FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>