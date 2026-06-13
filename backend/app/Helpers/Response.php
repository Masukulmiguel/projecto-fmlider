<?php

namespace App\Helpers;

use App\Config\Database;

class Response
{
    public static function json($data, $status = 200)
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function success($data = [], $message = 'OK')
    {
        self::json(['success' => true, 'message' => $message, 'data' => $data]);
    }

    public static function error($message, $status = 400, $data = [])
    {
        self::json(['success' => false, 'message' => $message, 'data' => $data], $status);
    }

    public static function input()
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $data = $_POST;
        }
        return $data ?: [];
    }

    public static function sanitize($value)
    {
        return htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
    }
}
