<?php
class Device {
    private $conn;
    private $table = "allowed_devices";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getByToken($token) {
        $query = "SELECT * FROM " . $this->table . " WHERE device_token = :token LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function register($token, $description) {
        // Verificar si ya existe
        if ($this->getByToken($token)) return false;

        $query = "INSERT INTO " . $this->table . " (device_token, description) VALUES (:token, :description)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':description', $description);
        return $stmt->execute();
    }

    public function approve($id) {
        $query = "UPDATE " . $this->table . " SET is_approved = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
