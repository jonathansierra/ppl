<?php
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

  require_once '../vendor/autoload.php';

  use Phroute\Phroute\RouteCollector;
  use Illuminate\Database\Capsule\Manager as Capsule;

  session_start();

  $baseDir = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
  $baseURL = 'http://' . $_SERVER['HTTP_HOST'] . $baseDir;
  define('BASE_URL', $baseURL);

  $dotenv = new \Dotenv\Dotenv(__DIR__ . '/..');
  $dotenv->load();

  $capsule = new Capsule;

  $capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => getenv('DB_HOST'),
    'database'  => getenv('DB_NAME'),
    'username'  => getenv('DB_USER'),
    'password'  => getenv('DB_PASS'),
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
    ]);

  $capsule->setAsGlobal();
  $capsule->bootEloquent();

  $route = $_GET['route'] ?? '/';
  $router = new RouteCollector();

  $router->filter('/', function() {
        if(!isset($_SESSION['name']) && !isset($_SESSION['username'])) {
            header('Location: ' . BASE_URL);
            return false;
        }
    });

  $router->controller('/', App\Controllers\AuthController::class);
  $router->group(['before' => '/'], function($router) {
    $router->controller('/admin', App\Controllers\Admin\IndexController::class);
    $router->controller('/admin/profile', App\Controllers\Admin\ProfileController::class);
    $router->controller('/admin/form', App\Controllers\Admin\FormController::class);
    $router->controller('/panel', App\Controllers\Panel\IndexController::class);
    $router->controller('/panel/admin', App\Controllers\Panel\AdminController::class);
  });

  $dispatcher = new Phroute\Phroute\Dispatcher($router->getData());
  $response = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $route);
  echo $response;
