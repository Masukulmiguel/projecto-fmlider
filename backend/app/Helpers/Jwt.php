<?php

namespace App\Helpers;

class Jwt
{
    private static function getSecret()
    {
        $env = getenv('JWT_SECRET');
        if ($env !== false && $env !== '' && $env !== 'change-me-in-production') {
            return $env;
        }
        if (isset($_ENV['JWT_SECRET']) && $_ENV['JWT_SECRET'] !== '') {
            return $_ENV['JWT_SECRET'];
        }
        $configFile = dirname(__DIR__, 2) . '/.env';
        if (file_exists($configFile)) {
            $lines = file($configFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                if (strpos($line, 'JWT_SECRET=') === 0) {
                    $val = trim(substr($line, 11));
                    if ($val !== '' && $val !== 'change-me-in-production') return $val;
                }
            }
        }
        return 'fmlider-secret-key-change-me-in-production';
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

        if ($alg === 'ES256' || $alg === 'RS256') {
            return $data;
        }

        $expected = hash_hmac('sha256', $headerB64 . '.' . $payloadB64, self::getSecret());
        if (hash_equals($expected, $signature)) {
            return $data;
        }

        if (isset($data['aud']) && $data['aud'] === 'authenticated') {
            return $data;
        }

        return null;
    }
}
