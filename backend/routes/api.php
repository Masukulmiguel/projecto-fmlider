<?php

class Router
{
    private $routes = [];

    public function post($path, $controller, $method)
    {
        $this->routes['POST'][$path] = ['controller' => $controller, 'method' => $method];
    }

    public function get($path, $controller, $method)
    {
        $this->routes['GET'][$path] = ['controller' => $controller, 'method' => $method];
    }

    public function put($path, $controller, $method)
    {
        $this->routes['PUT'][$path] = ['controller' => $controller, 'method' => $method];
    }

    public function delete($path, $controller, $method)
    {
        $this->routes['DELETE'][$path] = ['controller' => $controller, 'method' => $method];
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $path = $requestUri;
        if ($scriptDir !== '' && $scriptDir !== '/' && $scriptDir !== '.') {
            if (strpos($path, $scriptDir) === 0) {
                $path = substr($path, strlen($scriptDir));
            }
        }
        $path = preg_replace('#^/api#', '', $path);
        if ($path === '' || $path[0] !== '/') {
            $path = '/' . $path;
        }

        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $route => $handler) {
                $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>\w+)', $route);
                if (preg_match('#^' . $pattern . '$#', $path, $matches)) {
                    $controller = new $handler['controller'];
                    $method_name = $handler['method'];

                    $args = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                    echo call_user_func_array([$controller, $method_name], array_values($args));
                    exit;
                }
            }
        }

        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Route not found']);
    }
}

// Initialize router
$router = new Router();

// Public routes
$router->post('/auth/login', 'App\Controllers\AuthController', 'login');
$router->post('/auth/register', 'App\Controllers\AuthController', 'register');
$router->post('/auth/forgot-password', 'App\Controllers\AuthController', 'resetPassword');

// Public API routes
$router->get('/services', 'App\Controllers\ServiceController', 'index');
$router->get('/services/{id}', 'App\Controllers\ServiceController', 'show');
$router->get('/news', 'App\Controllers\NewsController', 'index');
$router->get('/news/{id}', 'App\Controllers\NewsController', 'show');
$router->get('/gallery', 'App\Controllers\GalleryController', 'index');
$router->get('/partners', 'App\Controllers\PartnerController', 'index');
$router->get('/testimonials', 'App\Controllers\TestimonialController', 'index');
$router->get('/faqs', 'App\Controllers\FAQController', 'index');
$router->post('/contacts', 'App\Controllers\ContactController', 'store');

// Admin/Authenticated routes
$router->get('/auth/profile', 'App\Controllers\AuthController', 'getProfile');
$router->post('/auth/logout', 'App\Controllers\AuthController', 'logout');
$router->post('/auth/change-password', 'App\Controllers\AuthController', 'changePassword');
$router->put('/auth/profile', 'App\Controllers\AuthController', 'updateProfile');

// Users CRUD
$router->get('/admin/users', 'App\Controllers\UserController', 'index');
$router->post('/admin/users', 'App\Controllers\UserController', 'store');
$router->get('/admin/users/{id}', 'App\Controllers\UserController', 'show');
$router->put('/admin/users/{id}', 'App\Controllers\UserController', 'update');
$router->delete('/admin/users/{id}', 'App\Controllers\UserController', 'destroy');

// Services CRUD
$router->post('/admin/services', 'App\Controllers\ServiceController', 'store');
$router->put('/admin/services/{id}', 'App\Controllers\ServiceController', 'update');
$router->delete('/admin/services/{id}', 'App\Controllers\ServiceController', 'destroy');

// News CRUD
$router->post('/admin/news', 'App\Controllers\NewsController', 'store');
$router->put('/admin/news/{id}', 'App\Controllers\NewsController', 'update');
$router->delete('/admin/news/{id}', 'App\Controllers\NewsController', 'destroy');

// Gallery CRUD
$router->post('/admin/gallery', 'App\Controllers\GalleryController', 'store');
$router->delete('/admin/gallery/{id}', 'App\Controllers\GalleryController', 'destroy');
$router->post('/admin/gallery/upload', 'App\Controllers\GalleryController', 'upload');

// Partners CRUD
$router->post('/admin/partners', 'App\Controllers\PartnerController', 'store');
$router->put('/admin/partners/{id}', 'App\Controllers\PartnerController', 'update');
$router->delete('/admin/partners/{id}', 'App\Controllers\PartnerController', 'destroy');

// Contacts
$router->get('/admin/contacts', 'App\Controllers\ContactController', 'index');
$router->get('/admin/contacts/{id}', 'App\Controllers\ContactController', 'show');
$router->put('/admin/contacts/{id}/mark-read', 'App\Controllers\ContactController', 'markAsRead');
$router->post('/admin/contacts/{id}/reply', 'App\Controllers\ContactController', 'reply');
$router->delete('/admin/contacts/{id}', 'App\Controllers\ContactController', 'destroy');

// Testimonials CRUD
$router->post('/admin/testimonials', 'App\Controllers\TestimonialController', 'store');
$router->put('/admin/testimonials/{id}', 'App\Controllers\TestimonialController', 'update');
$router->delete('/admin/testimonials/{id}', 'App\Controllers\TestimonialController', 'destroy');

// FAQs CRUD
$router->post('/admin/faqs', 'App\Controllers\FAQController', 'store');
$router->put('/admin/faqs/{id}', 'App\Controllers\FAQController', 'update');
$router->delete('/admin/faqs/{id}', 'App\Controllers\FAQController', 'destroy');

// Banners CRUD
$router->get('/admin/banners', 'App\Controllers\BannerController', 'index');
$router->post('/admin/banners', 'App\Controllers\BannerController', 'store');
$router->put('/admin/banners/{id}', 'App\Controllers\BannerController', 'update');
$router->delete('/admin/banners/{id}', 'App\Controllers\BannerController', 'destroy');

// Dispatch the request
$router->dispatch();
