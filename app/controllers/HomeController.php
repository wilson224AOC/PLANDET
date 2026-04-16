<?php
class HomeController {
    private function pullFlash(string $key): ?string {
        if (!isset($_SESSION[$key])) {
            return null;
        }

        $message = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $message;
    }

    public function index() {
        $success = $this->pullFlash('flash_success');
        $warning = $this->pullFlash('flash_warning');
        require_once '../app/views/home/index.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $meeting = new Meeting();

            $dni            = trim($_POST['dni'] ?? '');
            $nombres        = trim($_POST['nombres'] ?? '');
            $apellidos      = trim($_POST['apellidos'] ?? '');
            $telefono       = trim($_POST['telefono'] ?? '');
            $area           = trim($_POST['tipo_area'] ?? '');
            $correo         = trim($_POST['correo'] ?? '');
            $tipo_motivo    = trim($_POST['tipo_motivo'] ?? '');
            $descripcion    = trim($_POST['descripcion'] ?? '');
            $requested_date = !empty($_POST['requested_date']) ? $_POST['requested_date'] : null;

            if (
                empty($dni) || empty($nombres) || empty($apellidos) ||
                empty($telefono) || empty($correo) ||
                empty($tipo_motivo) || empty($descripcion)
            ) {
                $error = "Todos los campos son obligatorios";
                require_once '../app/views/home/index.php';
                return;
            }

            if (empty($area)) {
                $error = "Debe seleccionar una area";
                require_once '../app/views/home/index.php';
                return;
            }

            $fechaAVerificar = !empty($requested_date) ? $requested_date : date('Y-m-d');

            if ($meeting->hasActiveMeetingOnDate($dni, $fechaAVerificar)) {
                $error = "Ya tiene una solicitud pendiente o aprobada para el dia "
                         . date('d/m/Y', strtotime($fechaAVerificar))
                         . ". No puede registrar otra para la misma fecha.";
                require_once '../app/views/home/index.php';
                return;
            }

            $motivo = $tipo_motivo . ": " . $descripcion;

            $code = $meeting->create(
                $dni,
                $nombres,
                $apellidos,
                $telefono,
                $area,
                $correo,
                $motivo,
                $requested_date
            );

            if ($code) {
                $savedMeeting = $meeting->getByCode($code);
                if ($savedMeeting) {
                    $notificationService = new MeetingNotificationService();
                    $notifyResult = $notificationService->notifyCreated($savedMeeting);
                }

                $success = "Solicitud enviada con exito. Su codigo de seguimiento es: " . $code;
                if (isset($notifyResult) && !$notifyResult['success']) {
                    $warning = "La solicitud se registro, pero hubo un problema al enviar una o mas notificaciones.";
                }
            } else {
                $error = "Hubo un error al enviar la solicitud.";
            }

            require_once '../app/views/home/index.php';
        }
    }

    public function calendar() {
        $deviceModel = new Device();

        if (!isset($_COOKIE['device_token'])) {
            $token = bin2hex(random_bytes(32));
            setcookie('device_token', $token, time() + (86400 * 365), "/");
            $deviceModel->register($token, $_SERVER['HTTP_USER_AGENT']);
            $is_allowed = false;
            $msg = "Dispositivo no autorizado. Se ha enviado una solicitud de acceso al administrador.";
        } else {
            $token = $_COOKIE['device_token'];
            $device = $deviceModel->getByToken($token);

            if (!$device) {
                $deviceModel->register($token, $_SERVER['HTTP_USER_AGENT']);
                $is_allowed = false;
                $msg = "Dispositivo no registrado. Se ha enviado una solicitud.";
            } elseif ($device['is_approved']) {
                $is_allowed = true;
            } else {
                $is_allowed = false;
                $msg = "Su dispositivo esta pendiente de aprobacion por el administrador.";
            }
        }

        if ($is_allowed) {
            $meeting = new Meeting();
            $meeting->markCompletedMeetings();
            $meetings = $meeting->getApproved();
            require_once '../app/views/home/calendar.php';
        } else {
            require_once '../app/views/home/access_denied.php';
        }
    }

    public function seguimiento() {
        $q = trim($_GET['q'] ?? '');

        if ($q !== '') {
            $meeting = new Meeting();
            $meeting->markCompletedMeetings();
            $meetings = $meeting->getByCodeOrDni($q);

            if (empty($meetings)) {
                $errorMsg = "No se encontro ninguna solicitud con ese codigo o DNI.";
            }
        }

        require_once '../app/views/cliente/seguimiento.php';
    }
}
?>
