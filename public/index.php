<?php

// 自动加载类
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// 在顶部引入 Controller 类
use App\Controllers\HomeController;
use App\Controllers\PcBuilderController;

$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'builder':
        $controller = new PcBuilderController();
        $controller->index();
        break;

    case 'components':
        $controller = new PcBuilderController();
        $controller->listAll();
        break;

    case 'home':
    default:
        $controller = new HomeController();
        $controller->index();
        break;
}