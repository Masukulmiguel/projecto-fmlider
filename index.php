<?php

// API Router for fmlider.co.ao
// Routes /api/* to backend/index.php

error_log("fmlider.co.ao/index.php called: " . $_SERVER['REQUEST_URI']);

$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Handle API requests (from rewrite rule or direct)
if (strpos($path, '/api/') === 0 || isset($_GET['api'])) {
    if (isset($_GET['path'])) {
        $path = '/api/' . $_GET['path'];
    }
    // Get Authorization header (Apache may not pass it automatically)
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (empty($authHeader) && function_exists('getallheaders')) {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    }
    if (!empty($authHeader)) {
        $_SERVER['HTTP_AUTHORIZATION'] = $authHeader;
    }
    // Rewrite to backend - backend router expects path relative to /backend/
    $_SERVER['REQUEST_URI'] = '/fmlider.co.ao/backend' . $path;
    $_SERVER['SCRIPT_NAME'] = '/fmlider.co.ao/backend/index.php';
    require_once __DIR__ . '/backend/index.php';
    exit;
}

// Handle backend requests
if (strpos($path, '/backend/') === 0) {
    require_once __DIR__ . '/backend/index.php';
    exit;
}

// Serve frontend SPA
$frontendIndex = __DIR__ . '/frontend/dist/index.html';
if (file_exists($frontendIndex)) {
    // Check if it's a static asset
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    $assetPath = __DIR__ . '/frontend/dist' . $path;
    if ($ext && file_exists($assetPath)) {
        // Serve static asset
        $mimeTypes = [
            'js' => 'application/javascript',
            'css' => 'text/css',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject',
            'json' => 'application/json',
        ];
        if (isset($mimeTypes[$ext])) {
            header('Content-Type: ' . $mimeTypes[$ext]);
        }
        readfile($assetPath);
        exit;
    }
    // SPA fallback
    readfile($frontendIndex);
    exit;
}

http_response_code(404);
echo 'Not Found';