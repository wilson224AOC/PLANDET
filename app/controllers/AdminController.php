<?php
class AdminController {
    public function __construct() {
        if (!isset($_SESSION['admin_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
    }

    public function index() {
        $meeting  = new Meeting();
        $meeting->markCompletedMeetings();
        $meetings = $meeting->getAll();

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

            $meeting = new Meeting();

            if ($meeting->checkConflict($start, $end, $id)) {
                $error    = "Conflicto detectado: Ya existe una reunión en ese horario.";
                $meetings = $meeting->getAll();
                require_once '../app/views/admin/dashboard.php';
                return;
            }

            if ($meeting->update($id, 'approved', $start, $end)) {
                $success = "Reunión programada con éxito.";
                
                // Obtener datos actualizado de la reunión y enviar notificación
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

            if (!$id) {
                header('Location: index.php?controller=admin&action=index');
                exit;
            }

            if (empty($motivo_rechazo)) {
                header('Location: index.php?controller=admin&action=index&error=motivo_requerido');
                exit;
            }

            $meeting = new Meeting();
            $meeting->reject($id, $motivo_rechazo);
            
            // Obtener datos actualizado de la reunión y enviar notificación
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
            
            // Obtener datos actualizado de la reunión y enviar notificación
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
            header('Location: index.php?controller=admin&action=index');
            exit;
        }
        $deviceModel = new Device();
        $devices     = $deviceModel->getAll();
        require_once '../app/views/admin/devices.php';
    }

    public function approveDevice() {
        if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
            header('Location: index.php?controller=admin&action=index');
            exit;
        }
        if (isset($_GET['id'])) {
            $deviceModel = new Device();
            $deviceModel->approve($_GET['id']);
            header('Location: index.php?controller=admin&action=devices');
            exit;
        }
    }

    public function deleteDevice() {
        if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
            header('Location: index.php?controller=admin&action=index');
            exit;
        }
        if (isset($_GET['id'])) {
            $deviceModel = new Device();
            $deviceModel->delete($_GET['id']);
            header('Location: index.php?controller=admin&action=devices');
            exit;
        }
    }
}
?>