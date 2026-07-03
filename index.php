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

    // Handle bi-lookup directly (external APIs)
    if (preg_match('#^/api/bi-lookup/(\d{9}[A-Za-z]{2}\d{3})$#', $path, $m)) {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        $bi = strtoupper($m[1]);
        $apis = [
            'http://consulta.edgarsingui.ao/consultar/' . $bi,
            'https://buscador.ao/search/document?type=BI&number=' . $bi,
        ];
        foreach ($apis as $url) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 8,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT => 'FMLider-App/1.0',
            ]);
            $resp = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($resp !== false && $code === 200) {
                $data = json_decode($resp, true);
                if ($data && isset($data['name']) && !$data['error']) {
                    echo json_encode(['success' => true, 'message' => 'OK', 'data' => ['nome' => $data['name'], 'bi' => $bi, 'fonte' => 'Edgar Singui API']]);
                    exit;
                }
                if ($data && isset($data['data']['name'])) {
                    echo json_encode(['success' => true, 'message' => 'OK', 'data' => ['nome' => $data['data']['name'], 'bi' => $bi, 'fonte' => 'Buscador.ao']]);
                    exit;
                }
            }
        }
        echo json_encode(['success' => false, 'message' => 'BI não encontrado. Verifique o número.']);
        exit;
    }

    // Handle nif-lookup via AGT scraping
    if (preg_match('#^/api/nif-lookup/(\d{10})$#', $path, $m)) {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        $nif = $m[1];
        require_once __DIR__ . '/backend/app/Controllers/NifController.php';
        $ctrl = new \App\Controllers\NifController();
        $ctrl->lookup($nif);
        exit;
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