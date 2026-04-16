<?php

class SmtpMailerService {
    private array $config;
    private $socket = null;

    public function __construct() {
        $this->config = require __DIR__ . '/../../config/smtp.php';
    }

    public function sendEmail(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody): array {
        if (empty($this->config['enabled'])) {
            return [
                'success' => true,
                'status' => 'skipped',
                'error' => 'SMTP deshabilitado en config/smtp.php',
            ];
        }

        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'status' => 'failed',
                'error' => 'Correo de destino invalido',
            ];
        }

        $host = trim((string) ($this->config['host'] ?? ''));
        $username = trim((string) ($this->config['username'] ?? ''));
        $password = (string) ($this->config['password'] ?? '');
        $fromEmail = trim((string) ($this->config['from_email'] ?? ''));

        if ($host === '' || $username === '' || $password === '' || $fromEmail === '') {
            return [
                'success' => false,
                'status' => 'failed',
                'error' => 'Falta completar host, username, password o from_email en config/smtp.php',
            ];
        }

        try {
            $this->connect();
            $this->expect([220]);
            $this->command('EHLO localhost', [250]);

            if (($this->config['encryption'] ?? 'tls') === 'tls') {
                $this->command('STARTTLS', [220]);
                if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new RuntimeException('No se pudo iniciar TLS con el servidor SMTP');
                }
                $this->command('EHLO localhost', [250]);
            }

            $this->command('AUTH LOGIN', [334]);
            $this->command(base64_encode($username), [334]);
            $this->command(base64_encode($password), [235]);

            $this->command('MAIL FROM:<' . $fromEmail . '>', [250]);
            $this->command('RCPT TO:<' . $toEmail . '>', [250, 251]);
            $this->command('DATA', [354]);
            $this->write($this->buildMimeMessage($toEmail, $toName, $subject, $htmlBody, $textBody) . "\r\n.\r\n");
            $this->expect([250]);
            $this->command('QUIT', [221]);

            return [
                'success' => true,
                'status' => 'sent',
                'recipient' => $toEmail,
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'status' => 'failed',
                'recipient' => $toEmail,
                'error' => $e->getMessage(),
            ];
        } finally {
            if (is_resource($this->socket)) {
                fclose($this->socket);
            }
            $this->socket = null;
        }
    }

    private function connect(): void {
        $host = trim((string) $this->config['host']);
        $port = (int) ($this->config['port'] ?? 587);
        $timeout = (int) ($this->config['timeout'] ?? 20);
        $encryption = strtolower((string) ($this->config['encryption'] ?? 'tls'));

        $transport = $encryption === 'ssl' ? 'ssl://' . $host : $host;
        $this->socket = @stream_socket_client(
            $transport . ':' . $port,
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT
        );

        if (!$this->socket) {
            throw new RuntimeException('No se pudo conectar al servidor SMTP: ' . $errstr . ' (' . $errno . ')');
        }

        stream_set_timeout($this->socket, $timeout);
    }

    private function buildMimeMessage(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody): string {
        $boundary = 'b1_' . bin2hex(random_bytes(8));
        $fromName = $this->encodeHeader((string) ($this->config['from_name'] ?? 'PLANDET'));
        $fromEmail = trim((string) $this->config['from_email']);
        $replyTo = trim((string) ($this->config['reply_to'] ?? ''));
        $safeToName = $this->encodeHeader($toName !== '' ? $toName : $toEmail);

        $headers = [
            'Date: ' . date('r'),
            'From: ' . $fromName . ' <' . $fromEmail . '>',
            'To: ' . $safeToName . ' <' . $toEmail . '>',
            'Subject: ' . $this->encodeHeader($subject),
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        ];

        if ($replyTo !== '') {
            $headers[] = 'Reply-To: <' . $replyTo . '>';
        }

        $parts = [];
        $parts[] = '--' . $boundary;
        $parts[] = 'Content-Type: text/plain; charset=UTF-8';
        $parts[] = 'Content-Transfer-Encoding: base64';
        $parts[] = '';
        $parts[] = chunk_split(base64_encode($textBody));
        $parts[] = '--' . $boundary;
        $parts[] = 'Content-Type: text/html; charset=UTF-8';
        $parts[] = 'Content-Transfer-Encoding: base64';
        $parts[] = '';
        $parts[] = chunk_split(base64_encode($htmlBody));
        $parts[] = '--' . $boundary . '--';
        $parts[] = '';

        return implode("\r\n", $headers) . "\r\n\r\n" . implode("\r\n", $parts);
    }

    private function command(string $command, array $expectedCodes): string {
        $this->write($command . "\r\n");
        return $this->expect($expectedCodes);
    }

    private function write(string $data): void {
        $written = fwrite($this->socket, $data);
        if ($written === false) {
            throw new RuntimeException('No se pudo escribir en la conexion SMTP');
        }
    }

    private function expect(array $expectedCodes): string {
        $response = '';
        while (($line = fgets($this->socket, 515)) !== false) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }

        if ($response === '') {
            throw new RuntimeException('Servidor SMTP sin respuesta');
        }

        $code = (int) substr($response, 0, 3);
        if (!in_array($code, $expectedCodes, true)) {
            throw new RuntimeException('SMTP respondio con codigo ' . $code . ': ' . trim($response));
        }

        return $response;
    }

    private function encodeHeader(string $value): string {
        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }
}
