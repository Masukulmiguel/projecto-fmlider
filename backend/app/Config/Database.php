<?php

namespace App\Config;

class Database
{
    private static $instance = null;

    public static function connection()
    {
        if (self::$instance === null) {
            $config = require BASE_PATH . '/config/database.php';
            $conn = $config['connections']['mysql'];

            self::$instance = new \mysqli(
                $conn['host'],
                $conn['username'],
                $conn['password'],
                $conn['database'],
                $conn['port']
            );

            if (self::$instance->connect_error) {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Database connection failed']);
                exit;
            }

            self::$instance->set_charset('utf8mb4');
        }

        return self::$instance;
    }
}
