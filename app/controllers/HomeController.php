<?php
class HomeController {
    private const FORM_SESSION_KEY = 'meeting_form_data';

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
        $error = $this->pullFlash('flash_error');
        $warning = $this->pullFlash('flash_warning');
        $info = $this->pullFlash('flash_info');
        $formData = $this->getFormData();
        $verificationService = new EmailVerificationService();
        $verificationStatus = $verificationService->getStatus($formData['correo'] ?? null);
        require_once '../app/views/home/index.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->storeFormData($_POST);

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

            $emailValidator = new EmailValidationService();
            $emailValidation = $emailValidator->validate($correo);
            if (!$emailValidation['valid']) {
                $error = $emailValidation['error'];
                $formData = $this->getFormData();
                $verificationStatus = (new EmailVerificationService())->getStatus($formData['correo'] ?? null);
                require_once '../app/views/home/index.php';
                return;
            }

            $correo = $emailValidation['normalized_email'];

            $verificationService = new EmailVerificationService();
            if (!$verificationService->isVerified($correo)) {
                $error = "Debe verificar su correo con el codigo antes de enviar la solicitud.";
                $formData = $this->getFormData();
                $verificationStatus = $verificationService->getStatus($formData['correo'] ?? null);
                require_once '../app/views/home/index.php';
                return;
            }

            $_SESSION[self::FORM_SESSION_KEY]['correo'] = $correo;

            $fechaAVerificar = !empty($requested_date) ? $requested_date : date('Y-m-d');

            if ($meeting->hasActiveMeetingOnDate($dni, $fechaAVerificar, $area)) {
                $error = "Ya tiene una solicitud pendiente o aprobada para el area <strong>{$area}</strong> "
                         . "el dia " . date('d/m/Y', strtotime($fechaAVerificar))
                         . ". No puede registrar otra para la misma area y fecha.";
                $formData = $this->getFormData();
                $verificationStatus = $verificationService->getStatus($formData['correo'] ?? null);
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
                $this->clearFormData();
                $verificationService->clear();
                // Redirigir a la página de confirmación
                header('Location: index.php?controller=home&action=confirmacion&codigo=' . urlencode($code));
                exit;
            } else {
                $error = "Hubo un error al enviar la solicitud.";
                $formData = $this->getFormData();
                $verificationStatus = $verificationService->getStatus($formData['correo'] ?? null);
                require_once '../app/views/home/index.php';
            }
            // Nueva acción para mostrar la confirmación
            public function confirmacion() {
                $codigo = isset($_GET['codigo']) ? $_GET['codigo'] : '';
                require_once '../app/views/home/confirmacion.php';
            }
        }
    }

    public function requestEmailVerification() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php');
            exit;
        }

        $this->storeFormData($_POST);

        $correo = trim($_POST['correo'] ?? '');
        $fullName = trim(
            trim($_POST['nombres'] ?? '') . ' ' . trim($_POST['apellidos'] ?? '')
        );

        $verificationService = new EmailVerificationService();
        $result = $verificationService->requestCode($correo, $fullName);

        if ($result['success']) {
            $_SESSION['flash_info'] = 'Enviamos un codigo de verificacion a ' . $result['email'] . '. Revise su bandeja y luego ingrese el codigo.';
        } else {
            $_SESSION['flash_error'] = $result['error'];
        }

        header('Location: index.php');
        exit;
    }

    public function confirmEmailVerification() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php');
            exit;
        }

        $this->storeFormData($_POST);

        $correo = trim($_POST['correo'] ?? '');
        $verificationCode = trim($_POST['verification_code'] ?? '');

        $verificationService = new EmailVerificationService();
        $result = $verificationService->verifyCode($correo, $verificationCode);

        if ($result['success']) {
            $_SESSION['flash_success'] = 'Correo verificado correctamente. Ya puede enviar la solicitud.';
        } else {
            $_SESSION['flash_error'] = $result['error'];
        }

        header('Location: index.php');
        exit;
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

    private function storeFormData(array $source): void {
        $_SESSION[self::FORM_SESSION_KEY] = [
            'dni' => trim($source['dni'] ?? ''),
            'nombres' => trim($source['nombres'] ?? ''),
            'apellidos' => trim($source['apellidos'] ?? ''),
            'telefono' => trim($source['telefono'] ?? ''),
            'tipo_area' => trim($source['tipo_area'] ?? ''),
            'correo' => trim($source['correo'] ?? ''),
            'requested_date' => trim($source['requested_date'] ?? ''),
            'tipo_motivo' => trim($source['tipo_motivo'] ?? ''),
            'descripcion' => trim($source['descripcion'] ?? ''),
            'verification_code' => trim($source['verification_code'] ?? ''),
        ];
    }

    private function getFormData(): array {
        $data = $_SESSION[self::FORM_SESSION_KEY] ?? [];
        return is_array($data) ? $data : [];
    }

    private function clearFormData(): void {
        unset($_SESSION[self::FORM_SESSION_KEY]);
    }
}
?>
