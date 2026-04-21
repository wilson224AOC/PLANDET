<?php

class EmailVerificationService {
    private const SESSION_KEY = 'email_verification';
    private const CODE_TTL_SECONDS = 600;

    private EmailValidationService $emailValidator;
    private SmtpMailerService $mailer;

    public function __construct() {
        $this->emailValidator = new EmailValidationService();
        $this->mailer = new SmtpMailerService();
    }

    public function requestCode(string $email, string $name = ''): array {
        $validation = $this->emailValidator->validate($email);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'error' => $validation['error'],
            ];
        }

        $normalizedEmail = $validation['normalized_email'];
        $code = (string) random_int(100000, 999999);
        $expiresAt = time() + self::CODE_TTL_SECONDS;

        $subject = 'PLANDET - Codigo de verificacion';
        $text = "Su codigo de verificacion es: {$code}\n\n"
              . "Este codigo vence en 10 minutos.\n"
              . "Si usted no solicito este codigo, ignore este mensaje.";
        $html = '<p>Su codigo de verificacion es:</p>'
              . '<p style="font-size:28px;font-weight:bold;letter-spacing:4px;">' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '</p>'
              . '<p>Este codigo vence en <strong>10 minutos</strong>.</p>'
              . '<p>Si usted no solicito este codigo, ignore este mensaje.</p>';

        $sendResult = $this->mailer->sendEmail(
            $normalizedEmail,
            $name !== '' ? $name : $normalizedEmail,
            $subject,
            $html,
            $text
        );

        if (($sendResult['status'] ?? '') !== 'sent') {
            return [
                'success' => false,
                'error' => (string) ($sendResult['error'] ?? 'No se pudo enviar el codigo de verificacion.'),
            ];
        }

        $_SESSION[self::SESSION_KEY] = [
            'email' => $normalizedEmail,
            'code_hash' => hash('sha256', $code),
            'expires_at' => $expiresAt,
            'verified' => false,
            'verified_at' => null,
        ];

        return [
            'success' => true,
            'email' => $normalizedEmail,
            'expires_at' => $expiresAt,
        ];
    }

    public function verifyCode(string $email, string $code): array {
        $validation = $this->emailValidator->validate($email);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'error' => $validation['error'],
            ];
        }

        $normalizedEmail = $validation['normalized_email'];
        $state = $this->getState();

        if ($state === null || ($state['email'] ?? '') !== $normalizedEmail) {
            return [
                'success' => false,
                'error' => 'Primero debe solicitar un codigo para ese correo.',
            ];
        }

        if (($state['expires_at'] ?? 0) < time()) {
            $this->clear();
            return [
                'success' => false,
                'error' => 'El codigo ha vencido. Solicite uno nuevo.',
            ];
        }

        if (!preg_match('/^\d{6}$/', $code)) {
            return [
                'success' => false,
                'error' => 'El codigo debe tener 6 digitos.',
            ];
        }

        if (!hash_equals((string) $state['code_hash'], hash('sha256', $code))) {
            return [
                'success' => false,
                'error' => 'El codigo ingresado no es correcto.',
            ];
        }

        $_SESSION[self::SESSION_KEY]['verified'] = true;
        $_SESSION[self::SESSION_KEY]['verified_at'] = time();

        return [
            'success' => true,
            'email' => $normalizedEmail,
        ];
    }

    public function isVerified(string $email): bool {
        $validation = $this->emailValidator->validate($email);
        if (!$validation['valid']) {
            return false;
        }

        $normalizedEmail = $validation['normalized_email'];
        $state = $this->getState();

        if ($state === null) {
            return false;
        }

        if (($state['expires_at'] ?? 0) < time()) {
            $this->clear();
            return false;
        }

        return ($state['email'] ?? '') === $normalizedEmail && !empty($state['verified']);
    }

    public function getStatus(?string $email = null): array {
        $state = $this->getState();
        if ($state === null) {
            return [
                'requested' => false,
                'verified' => false,
                'matches_email' => false,
                'expires_at' => null,
            ];
        }

        if (($state['expires_at'] ?? 0) < time()) {
            $this->clear();
            return [
                'requested' => false,
                'verified' => false,
                'matches_email' => false,
                'expires_at' => null,
            ];
        }

        $matchesEmail = false;
        if ($email !== null && $email !== '') {
            $validation = $this->emailValidator->validate($email);
            $matchesEmail = $validation['valid'] && ($state['email'] ?? '') === $validation['normalized_email'];
        }

        return [
            'requested' => true,
            'verified' => !empty($state['verified']),
            'matches_email' => $matchesEmail,
            'email' => $state['email'] ?? '',
            'expires_at' => $state['expires_at'] ?? null,
        ];
    }

    public function clear(): void {
        unset($_SESSION[self::SESSION_KEY]);
    }

    private function getState(): ?array {
        if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            return null;
        }

        return $_SESSION[self::SESSION_KEY];
    }
}
