<?php
// Единая точка входа. Здесь нет разметки: только подключение, создание объектов и запуск Router.

use App\Controller\CartCheckController;
use App\Controller\CartController;
use App\Controller\CatalogController;
use App\Controller\ErrorController;
use App\Controller\HomeController;
use App\Core\Flash;
use App\Core\Router;
use App\Core\View;
use App\Model\PageModel;
use App\Repository\CartScenarioRepository;
use App\Repository\CatalogCaseRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\CartService;

// Для встроенного сервера PHP: реальные файлы (css, svg) отдаём как есть
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (is_file($file) && substr($file, -4) !== '.php') {
        return false;
    }
}

// Сессия запускается один раз здесь, до любого вывода. Представления session не запускают.
session_start();

define('DATA_DIR', __DIR__ . '/../data');

require_once __DIR__ . '/../src/Core/helpers.php';
require_once __DIR__ . '/../src/Core/autoload.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = Router::normalizePath($_SERVER['REQUEST_URI'] ?? '/');

// Общие данные для всех страниц (запасное название на случай ошибки в pages.json)
$view = new View(__DIR__ . '/../views', [
    'storeName'   => 'Интернет-магазин',
    'currentPath' => $path,
    'flash'       => Flash::pull(),   // одноразовое сообщение: читается и сразу удаляется
    'cartCount'   => 0,
]);
$errorController = new ErrorController($view);

try {
    $pages = new PageModel(DATA_DIR . '/pages.json');
    $view->share('storeName', $pages->getStoreName());

    // Репозитории получают путь к своему файлу данных
    $productRepository = new ProductRepository(DATA_DIR . '/products.json');
    $categoryRepository = new CategoryRepository(DATA_DIR . '/categories.json');
    $caseRepository = new CatalogCaseRepository(DATA_DIR . '/catalog_cases.csv');
    $scenarioRepository = new CartScenarioRepository(DATA_DIR . '/cart_scenarios.json');

    // Корзина хранит своё состояние в $_SESSION, данные товаров берёт у репозитория
    $cart = new CartService($productRepository, $_SESSION);
    $view->share('cartCount', $cart->count());

    $controllers = [
        'HomeController'    => new HomeController($view, $pages),
        'CatalogController' => new CatalogController($view, $productRepository, $categoryRepository, $caseRepository),
        'CartController'    => new CartController($view, $cart),
        'CartCheckController' => new CartCheckController($view, $scenarioRepository, $productRepository),
        'ErrorController'   => $errorController,
    ];

    $router = new Router(Router::readCsv(DATA_DIR . '/routes.csv'), $controllers);
    $router->dispatch($method, $path);
} catch (RuntimeException $e) {
    $errorController->serverError($e->getMessage());
}
