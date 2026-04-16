<?php

class WhatsAppService {
    private array $config;

    public function __construct() {
        $this->config = require __DIR__ . '/../../config/whatsapp.php';
    }

    public function sendTextMessage(string $phone, string $message): array {
        if (empty($this->config['enabled'])) {
            return [
                'success' => true,
                'status' => 'skipped',
                'phone' => $phone,
                'error' => 'WhatsApp deshabilitado en config/whatsapp.php',
            ];
        }

        $normalizedPhone = $this->normalizePhone($phone);
        if ($normalizedPhone === null) {
            return [
                'success' => false,
                'status' => 'failed',
                'phone' => $phone,
                'error' => 'Numero de telefono invalido',
            ];
        }

        if (($this->config['provider'] ?? '') !== 'meta_cloud') {
            return [
                'success' => false,
                'status' => 'failed',
                'phone' => $normalizedPhone,
                'error' => 'Proveedor de WhatsApp no soportado',
            ];
        }

        $accessToken = trim((string) ($this->config['access_token'] ?? ''));
        $phoneNumberId = trim((string) ($this->config['phone_number_id'] ?? ''));

        if ($accessToken === '' || $phoneNumberId === '') {
            return [
                'success' => false,
                'status' => 'failed',
                'phone' => $normalizedPhone,
                'error' => 'Falta access_token o phone_number_id en config/whatsapp.php',
            ];
        }

        $url = sprintf(
            'https://graph.facebook.com/%s/%s/messages',
            $this->config['api_version'] ?? 'v23.0',
            $phoneNumberId
        );

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $normalizedPhone,
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $message,
            ],
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => (int) ($this->config['timeout'] ?? 15),
        ]);

        $responseBody = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($responseBody === false) {
            return [
                'success' => false,
                'status' => 'failed',
                'phone' => $normalizedPhone,
                'error' => $curlError !== '' ? $curlError : 'No se pudo enviar el mensaje',
            ];
        }

        $decoded = json_decode($responseBody, true);

        if ($httpCode >= 200 && $httpCode < 300 && isset($decoded['messages'][0]['id'])) {
            return [
                'success' => true,
                'status' => 'sent',
                'phone' => $normalizedPhone,
                'message_id' => $decoded['messages'][0]['id'],
                'response' => $decoded,
            ];
        }

        $errorMessage = $decoded['error']['message'] ?? ('Respuesta HTTP ' . $httpCode);

        return [
            'success' => false,
            'status' => 'failed',
            'phone' => $normalizedPhone,
            'error' => $errorMessage,
            'response' => $decoded ?: $responseBody,
        ];
    }

    public function normalizePhone(string $phone): ?string {
        $digits = preg_replace('/\D+/', '', $phone);
        if ($digits === '') {
            return null;
        }

        $countryCode = preg_replace('/\D+/', '', (string) ($this->config['default_country_code'] ?? '51'));

        if ($countryCode !== '' && strpos($digits, $countryCode) !== 0) {
            if (strlen($digits) === 9) {
                $digits = $countryCode . $digits;
            } elseif (strlen($digits) === 10 && $digits[0] === '0') {
                $digits = $countryCode . substr($digits, 1);
            }
        }

        return strlen($digits) >= 11 ? $digits : null;
    }
}
