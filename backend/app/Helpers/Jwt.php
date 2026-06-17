<?php

namespace App\Helpers;

class Jwt
{
    private static function getSecret()
    {
        $env = getenv('JWT_SECRET');
        if ($env !== false && $env !== '' && $env !== 'change-me-in-production' && strlen($env) >= 32) {
            return $env;
        }
        if (isset($_ENV['JWT_SECRET']) && $_ENV['JWT_SECRET'] !== '' && strlen($_ENV['JWT_SECRET']) >= 32) {
            return $_ENV['JWT_SECRET'];
        }
        $configFile = dirname(__DIR__, 2) . '/.env';
        if (file_exists($configFile)) {
            $lines = file($configFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                if (strpos($line, 'JWT_SECRET=') === 0) {
                    $val = trim(substr($line, 11));
                    if ($val !== '' && $val !== 'change-me-in-production' && strlen($val) >= 32) return $val;
                }
            }
        }
        error_log('CRITICAL: JWT_SECRET not configured. Set a strong JWT_SECRET in backend/.env');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Server configuration error']);
        exit;
    }

    public static function encode($payload, $expHours = 24)
    {
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload['exp'] = time() + ($expHours * 3600);
        $payload['iat'] = time();
        $payloadEncoded = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', $header . '.' . $payloadEncoded, self::getSecret());
        return $header . '.' . $payloadEncoded . '.' . $signature;
    }

    public static function decode($token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$headerB64, $payloadB64, $signature] = $parts;

        $data = json_decode(base64_decode(strtr($payloadB64, '-_', '+/')), true);
        if (!$data) {
            return null;
        }

        if (isset($data['exp']) && $data['exp'] < time()) {
            return null;
        }

        $header = json_decode(base64_decode(strtr($headerB64, '-_', '+/')), true);
        $alg = $header['alg'] ?? '';

        if ($alg === 'HS256') {
            $expected = hash_hmac('sha256', $headerB64 . '.' . $payloadB64, self::getSecret());
            if (hash_equals($expected, $signature)) {
                return $data;
            }
        }

        $expected = hash_hmac('sha256', $headerB64 . '.' . $payloadB64, self::getSecret());
        if (hash_equals($expected, $signature)) {
            return $data;
        }

        return null;
    }
}
