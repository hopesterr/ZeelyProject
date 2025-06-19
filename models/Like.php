<?php
/**
 * Like Model
 */

class Like {
    private $conn;
    private $table_name = "project_likes";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function add($user_id, $project_id) {
        $query = "INSERT INTO " . $this->table_name . " (user_id, project_id) 
                  VALUES (:user_id, :project_id)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':project_id', $project_id);
        
        return $stmt->execute();
    }

    public function remove($user_id, $project_id) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE user_id = :user_id AND project_id = :project_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':project_id', $project_id);
        
        return $stmt->execute();
    }

    public function countByProject($project_id) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                  WHERE project_id = :project_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':project_id', $project_id);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['total'];
    }

    public function userHasLiked($user_id, $project_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE user_id = :user_id AND project_id = :project_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':project_id', $project_id);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    public function getTopWeek($limit = 3) {
        $query = "SELECT p.*, u.username, u.first_name, u.last_name, 
                         COUNT(pl.id) as like_count
                  FROM projects p
                  JOIN users u ON p.user_id = u.id
                  LEFT JOIN " . $this->table_name . " pl ON p.id = pl.project_id 
                  WHERE p.status = 'published' 
                    AND pl.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                  GROUP BY p.id
                  ORDER BY like_count DESC, p.created_at DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function getProjectsWithLikes($limit = null, $offset = 0) {
        $query = "SELECT p.*, u.username, u.first_name, u.last_name, 
                         COUNT(pl.id) as like_count
                  FROM projects p
                  JOIN users u ON p.user_id = u.id
                  LEFT JOIN " . $this->table_name . " pl ON p.id = pl.project_id
                  WHERE p.status = 'published'
                  GROUP BY p.id
                  ORDER BY p.featured DESC, p.created_at DESC";
        
        if ($limit) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function toggle($user_id, $project_id) {
        if ($this->userHasLiked($user_id, $project_id)) {
            return $this->remove($user_id, $project_id);
        } else {
            return $this->add($user_id, $project_id);
        }
    }
}
?>