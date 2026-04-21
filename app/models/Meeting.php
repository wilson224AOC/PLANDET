<?php
class Meeting {
    private $conn;
    private $table = "meetings";
    private ?bool $notificationTableExists = null;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create($dni, $nombres, $apellidos, $telefono, $area, $correo, $motivo, $requested_date) {
        $query = "INSERT INTO " . $this->table . " (code, dni, nombres, apellidos, telefono, area, correo, motivo, requested_date) VALUES (:code, :dni, :nombres, :apellidos, :telefono, :area, :correo, :motivo, :requested_date)";
        $stmt = $this->conn->prepare($query);
        $code = uniqid('MEET-');
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':dni', $dni);
        $stmt->bindParam(':nombres', $nombres);
        $stmt->bindParam(':apellidos', $apellidos);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':area', $area);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':motivo', $motivo);
        $stmt->bindParam(':requested_date', $requested_date);
        if ($stmt->execute()) return $code;
        return false;
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByCode(string $code) {
        $query = "SELECT * FROM " . $this->table . " WHERE code = :code LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getApproved() {
        $query = "SELECT * FROM " . $this->table . " WHERE status = 'approved' ORDER BY scheduled_start ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $status, $start, $end) {
        $current = $this->getById($id);
        $motivoLimpio = explode(' | Rechazo: ', $current['motivo'])[0];
        $query = "UPDATE " . $this->table . " 
                  SET status = :status, scheduled_start = :start, scheduled_end = :end, motivo = :motivo
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':start',  $start);
        $stmt->bindParam(':end',    $end);
        $stmt->bindParam(':motivo', $motivoLimpio);
        $stmt->bindParam(':id',     $id);
        return $stmt->execute();
    }

    public function reject($id, $motivo_rechazo = '') {
        $current = $this->getById($id);
        $motivo_actualizado = $current['motivo'];
        if (!empty($motivo_rechazo)) {
            $motivo_actualizado .= ' | Rechazo: ' . $motivo_rechazo;
        }
        $query = "UPDATE " . $this->table . " 
                  SET status = 'rejected', scheduled_start = NULL, scheduled_end = NULL, motivo = :motivo
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':motivo', $motivo_actualizado);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function checkConflict($start, $end, $excludeId = null, $area = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . "
                  WHERE status = 'approved'
                  AND (scheduled_start < :end AND scheduled_end > :start)";
        if ($excludeId) $query .= " AND id != :id";
        if ($area)      $query .= " AND area = :area";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start', $start);
        $stmt->bindParam(':end',   $end);
        if ($excludeId) $stmt->bindParam(':id',   $excludeId);
        if ($area)      $stmt->bindParam(':area', $area);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    }

    public function getOccupiedSlotsByAreaAndDate(string $area, string $date): array {
        $query = "SELECT scheduled_start, scheduled_end FROM " . $this->table . "
                  WHERE status = 'approved'
                  AND area = :area
                  AND DATE(scheduled_start) = :date";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':area' => $area, ':date' => $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByCodeOrDni(string $query): array {
        $stmt = $this->conn->prepare("
            SELECT * FROM " . $this->table . "
            WHERE code = :q OR dni = :q
            ORDER BY created_at DESC
        ");
        $stmt->execute([':q' => $query]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markCompletedMeetings() {
        $query = "UPDATE " . $this->table . " 
                  SET status = 'completed'
                  WHERE status = 'approved'
                  AND scheduled_end IS NOT NULL
                  AND scheduled_end < NOW()";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }

    public function hasActiveMeetingOnDate(string $dni, string $date, string $area = ''): bool {
    $query = "SELECT COUNT(*) as count FROM " . $this->table . "
              WHERE dni = :dni
              AND status IN ('pending', 'approved')
              AND DATE(requested_date) = :date
              AND LOWER(area) = LOWER(:area)";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':dni',  $dni);
    $stmt->bindParam(':date', $date);
    $stmt->bindParam(':area', $area);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['count'] > 0;
}

    public function logNotification(int $meetingId, string $channel, string $event, string $status, string $recipient, string $message, ?string $providerResponse = null): bool {
        if (!$this->hasNotificationTable()) return false;
        $query = "INSERT INTO meeting_notifications
                  (meeting_id, channel, event_type, status, recipient, message_body, provider_response)
                  VALUES (:meeting_id, :channel, :event_type, :status, :recipient, :message_body, :provider_response)";
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':meeting_id'       => $meetingId,
                ':channel'          => $channel,
                ':event_type'       => $event,
                ':status'           => $status,
                ':recipient'        => $recipient,
                ':message_body'     => $message,
                ':provider_response'=> $providerResponse,
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() === '42S02') { $this->notificationTableExists = false; return false; }
            throw $e;
        }
    }

    private function hasNotificationTable(): bool {
        if ($this->notificationTableExists !== null) return $this->notificationTableExists;
        $stmt = $this->conn->query("SELECT DATABASE()");
        $databaseName = (string) $stmt->fetchColumn();
        if ($databaseName === '') { $this->notificationTableExists = false; return false; }
        $query = "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = 'meeting_notifications'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':schema' => $databaseName]);
        $this->notificationTableExists = ((int) $stmt->fetchColumn()) > 0;
        return $this->notificationTableExists;
    }
}
?>