<?php

class MeetingNotificationService {
    private Meeting $meetingModel;
    private WhatsAppService $whatsApp;
    private SmtpMailerService $mailer;

    public function __construct() {
        $this->meetingModel = new Meeting();
        $this->whatsApp = new WhatsAppService();
        $this->mailer = new SmtpMailerService();
    }

    public function notifyCreated(array $meeting): array {
        $textMessage = $this->buildCreatedTextMessage($meeting);
        $email = $this->buildCreatedEmail($meeting);
        return $this->sendAndLog($meeting, 'created', $textMessage, $email);
    }

    public function notifyScheduled(array $meeting): array {
        $textMessage = $this->buildScheduledTextMessage($meeting);
        $email = $this->buildScheduledEmail($meeting);
        return $this->sendAndLog($meeting, 'scheduled', $textMessage, $email);
    }

    public function notifyRejected(array $meeting): array {
        $textMessage = $this->buildRejectedTextMessage($meeting);
        $email = $this->buildRejectedEmail($meeting);
        return $this->sendAndLog($meeting, 'rejected', $textMessage, $email);
    }

    private function sendAndLog(array $meeting, string $event, string $message, array $email): array {
        $results = [];

        $results['whatsapp'] = $this->whatsApp->sendTextMessage((string) ($meeting['telefono'] ?? ''), $message);

        $this->meetingModel->logNotification(
            (int) $meeting['id'],
            'whatsapp',
            $event,
            (string) ($results['whatsapp']['status'] ?? 'failed'),
            (string) ($results['whatsapp']['phone'] ?? $meeting['telefono'] ?? ''),
            $message,
            json_encode($results['whatsapp'], JSON_UNESCAPED_UNICODE)
        );

        $results['email'] = $this->mailer->sendEmail(
            (string) ($meeting['correo'] ?? ''),
            trim((string) (($meeting['nombres'] ?? '') . ' ' . ($meeting['apellidos'] ?? ''))),
            $email['subject'],
            $email['html'],
            $email['text']
        );

        $this->meetingModel->logNotification(
            (int) $meeting['id'],
            'email',
            $event,
            (string) ($results['email']['status'] ?? 'failed'),
            (string) ($meeting['correo'] ?? ''),
            $email['text'],
            json_encode($results['email'], JSON_UNESCAPED_UNICODE)
        );

        $hasErrors = false;
        foreach ($results as $channelResult) {
            if (($channelResult['status'] ?? '') === 'failed') {
                $hasErrors = true;
                break;
            }
        }

        return [
            'success' => !$hasErrors,
            'results' => $results,
        ];
    }

    private function buildCreatedTextMessage(array $meeting): string {
        $lines = [
            'PLANDET: Hemos recibido su solicitud de reunion.',
            'Codigo: ' . ($meeting['code'] ?? ''),
            'Estado: Pendiente',
        ];

        if (!empty($meeting['requested_date'])) {
            $lines[] = 'Fecha sugerida: ' . date('d/m/Y', strtotime($meeting['requested_date']));
        }

        $lines[] = 'Puede consultar su estado con su codigo o DNI en el modulo de seguimiento.';

        return implode("\n", $lines);
    }

    private function buildScheduledTextMessage(array $meeting): string {
        $start = !empty($meeting['scheduled_start']) ? strtotime($meeting['scheduled_start']) : null;
        $end = !empty($meeting['scheduled_end']) ? strtotime($meeting['scheduled_end']) : null;

        $lines = [
            'PLANDET: Su solicitud fue aprobada y programada.',
            'Codigo: ' . ($meeting['code'] ?? ''),
        ];

        if ($start) {
            $lines[] = 'Fecha: ' . date('d/m/Y', $start);
            $lines[] = 'Hora: ' . date('H:i', $start) . ($end ? ' - ' . date('H:i', $end) : '');
        }

        $lines[] = 'Por favor llegue unos minutos antes de la hora indicada.';

        return implode("\n", $lines);
    }

    private function buildRejectedTextMessage(array $meeting): string {
        $reason = $this->extractRejectReason((string) ($meeting['motivo'] ?? ''));

        $lines = [
            'PLANDET: Su solicitud no pudo ser atendida en esta ocasion.',
            'Codigo: ' . ($meeting['code'] ?? ''),
            'Estado: Rechazado',
        ];

        if ($reason !== null) {
            $lines[] = 'Motivo: ' . $reason;
        }

        $lines[] = 'Si lo necesita, puede registrar una nueva solicitud.';

        return implode("\n", $lines);
    }

    private function buildCreatedEmail(array $meeting): array {
        $subject = 'PLANDET - Solicitud recibida ' . ($meeting['code'] ?? '');
        $requestedDate = !empty($meeting['requested_date']) ? date('d/m/Y', strtotime($meeting['requested_date'])) : 'No registrada';
        $text = "Estimado/a {$meeting['nombres']} {$meeting['apellidos']},\n\n"
              . "Hemos recibido su solicitud de reunion en PLANDET.\n"
              . "Codigo de seguimiento: {$meeting['code']}\n"
              . "Estado actual: Pendiente\n"
              . "Fecha sugerida: {$requestedDate}\n\n"
              . "Puede consultar el estado de su tramite con su codigo o DNI en el modulo de seguimiento.\n\n"
              . "Atentamente,\nPLANDET";

        $html = $this->wrapFormalEmail(
            'Solicitud recibida',
            '<p>Estimado/a <strong>' . $this->escape($meeting['nombres'] . ' ' . $meeting['apellidos']) . '</strong>,</p>'
            . '<p>Hemos recibido su solicitud de reunion en PLANDET.</p>'
            . $this->buildInfoTable([
                'Codigo de seguimiento' => $meeting['code'] ?? '',
                'Estado actual' => 'Pendiente',
                'Fecha sugerida' => $requestedDate,
            ])
            . '<p>Puede consultar el estado de su tramite con su codigo o DNI en el modulo de seguimiento.</p>'
            . '<p>Atentamente,<br>PLANDET</p>'
        );

        return compact('subject', 'text', 'html');
    }

    private function buildScheduledEmail(array $meeting): array {
        $subject = 'PLANDET - Solicitud aprobada ' . ($meeting['code'] ?? '');
        $date = !empty($meeting['scheduled_start']) ? date('d/m/Y', strtotime($meeting['scheduled_start'])) : 'Por definir';
        $time = !empty($meeting['scheduled_start']) ? date('H:i', strtotime($meeting['scheduled_start'])) : 'Por definir';
        if (!empty($meeting['scheduled_end'])) {
            $time .= ' - ' . date('H:i', strtotime($meeting['scheduled_end']));
        }

        $text = "Estimado/a {$meeting['nombres']} {$meeting['apellidos']},\n\n"
              . "Su solicitud ha sido aprobada y programada.\n"
              . "Codigo de seguimiento: {$meeting['code']}\n"
              . "Fecha: {$date}\n"
              . "Hora: {$time}\n\n"
              . "Le recomendamos presentarse unos minutos antes de la hora indicada.\n\n"
              . "Atentamente,\nPLANDET";

        $html = $this->wrapFormalEmail(
            'Solicitud aprobada y programada',
            '<p>Estimado/a <strong>' . $this->escape($meeting['nombres'] . ' ' . $meeting['apellidos']) . '</strong>,</p>'
            . '<p>Su solicitud ha sido aprobada y programada.</p>'
            . $this->buildInfoTable([
                'Codigo de seguimiento' => $meeting['code'] ?? '',
                'Estado actual' => 'Aprobado',
                'Fecha' => $date,
                'Hora' => $time,
            ])
            . '<p>Le recomendamos presentarse unos minutos antes de la hora indicada.</p>'
            . '<p>Atentamente,<br>PLANDET</p>'
        );

        return compact('subject', 'text', 'html');
    }

    private function buildRejectedEmail(array $meeting): array {
        $subject = 'PLANDET - Solicitud rechazada ' . ($meeting['code'] ?? '');
        $reason = $this->extractRejectReason((string) ($meeting['motivo'] ?? '')) ?? 'No especificado';

        $text = "Estimado/a {$meeting['nombres']} {$meeting['apellidos']},\n\n"
              . "Lamentamos informarle que su solicitud no pudo ser atendida en esta ocasion.\n"
              . "Codigo de seguimiento: {$meeting['code']}\n"
              . "Estado actual: Rechazado\n"
              . "Motivo: {$reason}\n\n"
              . "Si lo considera necesario, puede registrar una nueva solicitud.\n\n"
              . "Atentamente,\nPLANDET";

        $html = $this->wrapFormalEmail(
            'Solicitud rechazada',
            '<p>Estimado/a <strong>' . $this->escape($meeting['nombres'] . ' ' . $meeting['apellidos']) . '</strong>,</p>'
            . '<p>Lamentamos informarle que su solicitud no pudo ser atendida en esta ocasion.</p>'
            . $this->buildInfoTable([
                'Codigo de seguimiento' => $meeting['code'] ?? '',
                'Estado actual' => 'Rechazado',
                'Motivo' => $reason,
            ])
            . '<p>Si lo considera necesario, puede registrar una nueva solicitud.</p>'
            . '<p>Atentamente,<br>PLANDET</p>'
        );

        return compact('subject', 'text', 'html');
    }

    private function extractRejectReason(string $motivo): ?string {
        $parts = explode(' | Rechazo: ', $motivo, 2);
        return isset($parts[1]) && trim($parts[1]) !== '' ? trim($parts[1]) : null;
    }

    private function wrapFormalEmail(string $title, string $body): string {
        return '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>' . $this->escape($title) . '</title></head>'
            . '<body style="margin:0;padding:24px;background:#f4f7fb;font-family:Arial,sans-serif;color:#1f2937;">'
            . '<div style="max-width:680px;margin:0 auto;background:#ffffff;border:1px solid #dbe3ec;border-radius:12px;overflow:hidden;">'
            . '<div style="background:#193938;color:#ffffff;padding:20px 28px;">'
            . '<h1 style="margin:0;font-size:22px;">PLANDET</h1>'
            . '<p style="margin:6px 0 0;font-size:14px;opacity:.9;">Notificacion de estado de tramite</p>'
            . '</div>'
            . '<div style="padding:28px;">'
            . '<h2 style="margin:0 0 18px;font-size:20px;color:#193938;">' . $this->escape($title) . '</h2>'
            . $body
            . '</div>'
            . '<div style="padding:18px 28px;background:#f9fafb;border-top:1px solid #e5e7eb;font-size:12px;color:#6b7280;">'
            . 'Este correo fue generado automaticamente por el sistema de tramites de PLANDET.'
            . '</div></div></body></html>';
    }

    private function buildInfoTable(array $rows): string {
        $html = '<table style="width:100%;border-collapse:collapse;margin:18px 0;">';
        foreach ($rows as $label => $value) {
            $html .= '<tr>'
                . '<td style="padding:10px 12px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:bold;width:36%;">' . $this->escape((string) $label) . '</td>'
                . '<td style="padding:10px 12px;border:1px solid #e5e7eb;">' . $this->escape((string) $value) . '</td>'
                . '</tr>';
        }
        $html .= '</table>';
        return $html;
    }

    private function escape(string $value): string {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
