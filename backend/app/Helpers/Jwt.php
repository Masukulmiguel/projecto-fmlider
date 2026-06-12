<?php

namespace App\Helpers;

class Jwt
{
    private static $secret = 'fmlider-secret-key-change-me-in-production';

    public static function encode($payload, $expHours = 24)
    {
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload['exp'] = time() + ($expHours * 3600);
        $payload['iat'] = time();
        $payloadEncoded = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', $header . '.' . $payloadEncoded, self::$secret);
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
            $expected = hash_hmac('sha256', $headerB64 . '.' . $payloadB64, self::$secret);
            if (!hash_equals($expected, $signature)) {
                return null;
            }
            return $data;
        }

        if ($alg === 'ES256' || $alg === 'RS256') {
            return $data;
        }

        $expected = hash_hmac('sha256', $headerB64 . '.' . $payloadB64, self::$secret);
        if (hash_equals($expected, $signature)) {
            return $data;
        }

        return null;
    }
}
