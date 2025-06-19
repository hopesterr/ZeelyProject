<?php
/**
 * Project Model
 */

class Project {
    private $conn;
    private $table_name = "projects";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllProjects($limit = null, $offset = 0, $user_id = null) {
        $query = "SELECT p.*, u.username, u.first_name, u.last_name 
                  FROM " . $this->table_name . " p
                  JOIN users u ON p.user_id = u.id
                  WHERE p.status = 'published'";
        
        if ($user_id) {
            $query .= " AND p.user_id = :user_id";
        }
        
        $query .= " ORDER BY p.featured DESC, p.created_at DESC";
        
        if ($limit) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($user_id) {
            $stmt->bindParam(':user_id', $user_id);
        }
        
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getProjectById($id) {
        $query = "SELECT p.*, u.username, u.first_name, u.last_name 
                  FROM " . $this->table_name . " p
                  JOIN users u ON p.user_id = u.id
                  WHERE p.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getUserProjects($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = :user_id 
                  ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function createProject($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, title, description, image_path, external_link, github_link, technologies, status) 
                  VALUES (:user_id, :title, :description, :image_path, :external_link, :github_link, :technologies, :status)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':image_path', $data['image_path']);
        $stmt->bindParam(':external_link', $data['external_link']);
        $stmt->bindParam(':github_link', $data['github_link']);
        $stmt->bindParam(':technologies', $data['technologies']);
        $stmt->bindParam(':status', $data['status']);
        
        return $stmt->execute();
    }

    public function updateProject($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET title = :title, description = :description, external_link = :external_link,
                      github_link = :github_link, technologies = :technologies, status = :status,
                      updated_at = CURRENT_TIMESTAMP";
        
        if (isset($data['image_path'])) {
            $query .= ", image_path = :image_path";
        }
        
        $query .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':external_link', $data['external_link']);
        $stmt->bindParam(':github_link', $data['github_link']);
        $stmt->bindParam(':technologies', $data['technologies']);
        $stmt->bindParam(':status', $data['status']);
        
        if (isset($data['image_path'])) {
            $stmt->bindParam(':image_path', $data['image_path']);
        }
        
        return $stmt->execute();
    }

    public function deleteProject($id) {
        // Get project info first to delete image file
        $project = $this->getProjectById($id);
        
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            // Delete image file if exists
            if ($project && $project['image_path'] && file_exists($project['image_path'])) {
                unlink($project['image_path']);
            }
            return true;
        }
        return false;
    }

    public function toggleFeatured($id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET featured = NOT featured, updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function countProjects($user_id = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE status = 'published'";
        
        if ($user_id) {
            $query .= " AND user_id = :user_id";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($user_id) {
            $stmt->bindParam(':user_id', $user_id);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'];
    }
}
?>