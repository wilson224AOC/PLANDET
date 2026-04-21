<?php
class AdminController {
    public function __construct() {
        if (!isset($_SESSION['admin_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
    }

    public function index() {
        $meeting = new Meeting();
        $meeting->markCompletedMeetings();
        $meetings = $meeting->getAll();

        $occupiedSlotsMap = [];
        foreach ($meetings as $m) {
            if ($m['status'] === 'approved' && $m['scheduled_start'] && $m['area']) {
                $fecha = date('Y-m-d', strtotime($m['scheduled_start']));
                $key   = strtolower($m['area']) . '|' . $fecha;
                if (!isset($occupiedSlotsMap[$key])) $occupiedSlotsMap[$key] = [];
                $occupiedSlotsMap[$key][] = [
                    'start' => date('H:i', strtotime($m['scheduled_start'])),
                    'end'   => date('H:i', strtotime($m['scheduled_end'])),
                ];
            }
        }

        if (isset($_GET['error']) && $_GET['error'] === 'motivo_requerido') {
            $error = "Debe ingresar un motivo de rechazo.";
        }

        require_once '../app/views/admin/dashboard.php';
    }

    public function schedule() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id    = $_POST['id'];
            $start = $_POST['scheduled_start'];
            $end   = $_POST['scheduled_end'];

            $meeting     = new Meeting();
            $meetingData = $meeting->getById($id);

            if (strtotime($start) < time()) {
                $error    = "No se puede programar una reunión en una fecha u hora pasada.";
                $meeting->markCompletedMeetings();
                $meetings = $meeting->getAll();
                $occupiedSlotsMap = $this->buildOccupiedSlotsMap($meetings);
                require_once '../app/views/admin/dashboard.php';
                return;
            }

            $area = $meetingData['area'] ?? null;
            if ($meeting->checkConflict($start, $end, $id, $area)) {
                $error    = "Conflicto detectado: Ya existe una reunión en ese horario para el área «{$area}».";
                $meeting->markCompletedMeetings();
                $meetings = $meeting->getAll();
                $occupiedSlotsMap = $this->buildOccupiedSlotsMap($meetings);
                require_once '../app/views/admin/dashboard.php';
                return;
            }

            if ($meeting->update($id, 'approved', $start, $end)) {
                $success = "Reunión programada con éxito.";
                $updatedMeeting = $meeting->getById($id);
                if ($updatedMeeting) {
                    $notificationService = new MeetingNotificationService();
                    $notificationService->notifyScheduled($updatedMeeting);
                }
            } else {
                $error = "Error al programar la reunión.";
            }

            header('Location: index.php?controller=admin&action=index');
            exit;
        }
    }

    public function reject() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id             = $_POST['id'] ?? null;
            $motivo_rechazo = trim($_POST['motivo_rechazo'] ?? '');

            if (!$id) { header('Location: index.php?controller=admin&action=index'); exit; }

            if (empty($motivo_rechazo)) {
                header('Location: index.php?controller=admin&action=index&error=motivo_requerido');
                exit;
            }

            $meeting = new Meeting();
            $meeting->reject($id, $motivo_rechazo);
            $updatedMeeting = $meeting->getById($id);
            if ($updatedMeeting) {
                $notificationService = new MeetingNotificationService();
                $notificationService->notifyRejected($updatedMeeting);
            }
            header('Location: index.php?controller=admin&action=index');
            exit;

        } elseif (isset($_GET['id'])) {
            $meeting = new Meeting();
            $meeting->reject($_GET['id']);
            $updatedMeeting = $meeting->getById($_GET['id']);
            if ($updatedMeeting) {
                $notificationService = new MeetingNotificationService();
                $notificationService->notifyRejected($updatedMeeting);
            }
            header('Location: index.php?controller=admin&action=index');
            exit;
        }
    }

    public function devices() {
        if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
            header('Location: index.php?controller=admin&action=index'); exit;
        }
        $deviceModel = new Device();
        $devices     = $deviceModel->getAll();
        require_once '../app/views/admin/devices.php';
    }

    public function approveDevice() {
        if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
            header('Location: index.php?controller=admin&action=index'); exit;
        }
        if (isset($_GET['id'])) {
            $deviceModel = new Device();
            $deviceModel->approve($_GET['id']);
            header('Location: index.php?controller=admin&action=devices'); exit;
        }
    }

    public function deleteDevice() {
        if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
            header('Location: index.php?controller=admin&action=index'); exit;
        }
        if (isset($_GET['id'])) {
            $deviceModel = new Device();
            $deviceModel->delete($_GET['id']);
            header('Location: index.php?controller=admin&action=devices'); exit;
        }
    }

    private function buildOccupiedSlotsMap(array $meetings): array {
        $map = [];
        foreach ($meetings as $m) {
            if ($m['status'] === 'approved' && $m['scheduled_start'] && $m['area']) {
                $fecha = date('Y-m-d', strtotime($m['scheduled_start']));
                $key   = strtolower($m['area']) . '|' . $fecha;
                if (!isset($map[$key])) $map[$key] = [];
                $map[$key][] = [
                    'start' => date('H:i', strtotime($m['scheduled_start'])),
                    'end'   => date('H:i', strtotime($m['scheduled_end'])),
                ];
            }
        }
        return $map;
    }
}
?>