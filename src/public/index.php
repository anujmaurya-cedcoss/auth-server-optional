<?php
use Phalcon\Di\FactoryDefault;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Url;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Config;
use Phalcon\Session\Manager;
use Phalcon\Session\Adapter\Stream;
use Phalcon\Http\Response\Cookies; // for cookies

$config = new Config([]);

// Define some absolute path constants to aid in locating resources
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

// Register an autoloader
$loader = new Loader();

$loader->registerDirs(
    [
        APP_PATH . "/controllers/",
        APP_PATH . "/models/",
    ]
);

$loader->register();

$container = new FactoryDefault();

$container->set(
    'view',
    function () {
        $view = new View();
        $view->setViewsDir(APP_PATH . '/views/');
        return $view;
    }
);

$container->set(
    'url',
    function () {
        $url = new Url();
        $url->setBaseUri('/');
        return $url;
    }
);

$application = new Application($container);

$container->set(
    'db',
    function () {
        return new Mysql(
            [
                'host' => 'mysql-server',
                'username' => 'root',
                'password' => 'secret',
                'dbname' => 'firstDB',
            ]
        );
    }
);
// injecting session in container
$container->set(
    'session',
    function () {
        $session = new Manager();
        $files = new Stream(
            [
                'savePath' => '/tmp',
            ]
        );
        $session->setAdapter($files);
        $session->start();
        return $session;
    }
);
// injecting response
$container->set(
    'response',
    [
        'className' => 'Phalcon\Http\Response'
    ]
);
$container->set(
    'cookies',
    function () {
        $cookies = new Cookies();
        $cookies->useEncryption(false);
        return $cookies;
    }
);
// injecting time in container
$container->set(
    'time',
    function() {
        date_default_timezone_set('Asia/Kolkata');
        return date("Y-m-d h:iA");
    }
);
try {
    // Handle the request
    $response = $application->handle(
        $_SERVER["REQUEST_URI"]
    );

    $response->send();
} catch (\Exception $e) {
    echo 'Exception: ', $e->getMessage();
}
