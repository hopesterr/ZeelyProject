<?php
/**
 * Skill Model
 */

class Skill {
    private $conn;
    private $table_name = "skills";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllSkills() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY category, name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getSkillById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function createSkill($data) {
        $query = "INSERT INTO " . $this->table_name . " (name, category, description) 
                  VALUES (:name, :category, :description)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':category', $data['category']);
        $stmt->bindParam(':description', $data['description']);
        
        return $stmt->execute();
    }

    public function updateSkill($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name, category = :category, description = :description,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':category', $data['category']);
        $stmt->bindParam(':description', $data['description']);
        
        return $stmt->execute();
    }

    public function deleteSkill($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getUserSkills($user_id) {
        $query = "SELECT s.*, us.level, us.years_experience 
                  FROM " . $this->table_name . " s
                  JOIN user_skills us ON s.id = us.skill_id
                  WHERE us.user_id = :user_id
                  ORDER BY s.category, s.name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function addUserSkill($user_id, $skill_id, $level, $years_experience = 0) {
    $query = "
      INSERT INTO user_skills (user_id, skill_id, level, years_experience) 
      VALUES (:user_id, :skill_id, :level, :years_experience)
      ON DUPLICATE KEY UPDATE 
        level = VALUES(level),
        years_experience = VALUES(years_experience),
        updated_at = CURRENT_TIMESTAMP
    ";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':user_id',         $user_id);
    $stmt->bindParam(':skill_id',        $skill_id);
    $stmt->bindParam(':level',           $level);
    $stmt->bindParam(':years_experience',$years_experience);
    
    return $stmt->execute();
}

    public function removeUserSkill($user_id, $skill_id) {
        $query = "DELETE FROM user_skills WHERE user_id = :user_id AND skill_id = :skill_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':skill_id', $skill_id);
        return $stmt->execute();
    }
}
?>