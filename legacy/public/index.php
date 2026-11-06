<?php

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

use App\Controllers\HomeController;
use App\Controllers\PcBuilderController;
use App\Controllers\PaymentController;
use App\Controllers\ReviewController;
use App\Controllers\CalculatorController;
use App\Controllers\ContactController;
use App\Controllers\ChatController;
use App\Controllers\OrderTrackingController;

$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'builder':
        (new PcBuilderController())->index();
        break;

    case 'saved-builds':
        (new PcBuilderController())->savedBuilds();
        break;

    case 'api-save-build':
        (new PcBuilderController())->saveBuild();
        break;

    case 'api-delete-build':
        (new PcBuilderController())->deleteBuild();
        break;

    case 'components':
        (new PcBuilderController())->listAll();
        break;

    case 'payment':
        (new PaymentController())->index();
        break;

    case 'api-process-payment':
        (new PaymentController())->process();
        break;

    case 'reviews':
        (new ReviewController())->index();
        break;

    case 'api-create-review':
        (new ReviewController())->create();
        break;

    case 'api-update-review':
        (new ReviewController())->update();
        break;

    case 'api-delete-review':
        (new ReviewController())->delete();
        break;

    case 'api-like-review':
        (new ReviewController())->like();
        break;

    case 'cpu-calc':
        (new CalculatorController())->index();
        break;

    case 'contact':
        (new ContactController())->index();
        break;

    case 'api-submit-ticket':
        (new ContactController())->submitTicket();
        break;

    case 'chat':
        (new ChatController())->index();
        break;

    case 'chat-send-api':
        (new ChatController())->sendMessage();
        break;

    case 'chat-history-api':
        (new ChatController())->getHistory();
        break;

    case 'chat-clear-api':
        (new ChatController())->clearHistory();
        break;

    case 'chat-del-api':
        (new ChatController())->deleteMessage();
        break;

    case 'tracking':
        (new OrderTrackingController())->index();
        break;

    case 'api-create-order':
        (new OrderTrackingController())->create();
        break;

    case 'api-update-order':
        (new OrderTrackingController())->update();
        break;

    case 'api-delete-order':
        (new OrderTrackingController())->delete();
        break;

    case 'home':
    default:
        (new HomeController())->index();
        break;
}
