<?php

class EmailValidationService {
    public function validate(string $email): array {
        $email = trim($email);

        if ($email === '') {
            return [
                'valid' => false,
                'error' => 'Debe ingresar un correo electronico.',
            ];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'valid' => false,
                'error' => 'El correo ingresado no tiene un formato valido.',
            ];
        }

        $domain = substr(strrchr($email, '@'), 1);
        if ($domain === false || $domain === '') {
            return [
                'valid' => false,
                'error' => 'No se pudo identificar el dominio del correo.',
            ];
        }

        $asciiDomain = $this->toAsciiDomain($domain);
        if ($asciiDomain === null || $asciiDomain === '') {
            return [
                'valid' => false,
                'error' => 'El dominio del correo no es valido.',
            ];
        }

        if (!$this->domainAcceptsMail($asciiDomain)) {
            return [
                'valid' => false,
                'error' => 'El dominio del correo no existe o no recibe mensajes.',
            ];
        }

        return [
            'valid' => true,
            'normalized_email' => $email,
            'domain' => $asciiDomain,
        ];
    }

    private function toAsciiDomain(string $domain): ?string {
        $domain = trim($domain);

        if ($domain === '') {
            return null;
        }

        if (function_exists('idn_to_ascii')) {
            $converted = idn_to_ascii($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            if ($converted !== false) {
                return $converted;
            }
        }

        return $domain;
    }

    private function domainAcceptsMail(string $domain): bool {
        if (function_exists('checkdnsrr')) {
            if (checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A') || checkdnsrr($domain, 'AAAA')) {
                return true;
            }
        }

        if (function_exists('dns_get_record')) {
            $mx = @dns_get_record($domain, DNS_MX);
            if (!empty($mx)) {
                return true;
            }

            $a = @dns_get_record($domain, DNS_A);
            if (!empty($a)) {
                return true;
            }

            if (defined('DNS_AAAA')) {
                $aaaa = @dns_get_record($domain, DNS_AAAA);
                if (!empty($aaaa)) {
                    return true;
                }
            }
        }

        return false;
    }
}
