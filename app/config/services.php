<?php

// ส่วนที่ include ส่วน library อื่นๆ
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Flash\Direct as Flash;
use Phalcon\Flash\Session as FlashSession; // เรียกใช้งาน FlashSession
use Phalcon\Events\Manager as PhManager; // เรียกใช้งานส่วน Manager
use Phalcon\Mvc\Dispatcher as PhDispatcher; // เรียกใช้งานส่วน Dispatcher
use Phalcon\Http\Response\Cookies; // เรียกใช้งานส่วน Cookies
use Phalcon\Crypt; // เรียกใช้งานส่วนการเข้ารหัส
use Phalcon\Mvc\Router; // เรียกใช้งานส่วน router

/**
 * Shared configuration service
 */
$di->setShared('config', function () {
    return include APP_PATH . "/config/config.php";
});

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->setShared('url', function () {
    $config = $this->getConfig();

    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);

    return $url;
});

/**
 * Setting up the view component
 */
$di->setShared('view', function () {
    $config = $this->getConfig();

    $view = new View();
    $view->setDI($this);
    $view->setViewsDir($config->application->viewsDir);

    $view->registerEngines([
        '.volt' => function ($view) {
            $config = $this->getConfig();

            $volt = new VoltEngine($view, $this);

            $volt->setOptions([
                'compiledPath' => $config->application->cacheDir,
                'compiledSeparator' => '_'
            ]);

            $compiler = $volt->getCompiler(); // เรียกการตั้งค่าให้ใช้งานในส่วนของ volt
            $compiler->addFunction('number_format','number_format'); // กำหนด function ของ PHP ให้สามารถใช้งานใน volt ได้

            return $volt;
        },
        '.phtml' => PhpEngine::class

    ]);

    return $view;
});

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () {
    $config = $this->getConfig();

    $class = 'Phalcon\Db\Adapter\Pdo\\' . $config->database->adapter;
    $params = [
        'host'     => $config->database->host,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname'   => $config->database->dbname,
        'charset'  => $config->database->charset
    ];

    if ($config->database->adapter == 'Postgresql') {
        unset($params['charset']);
    }

    $connection = new $class($params);

    return $connection;
});


/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->setShared('modelsMetadata', function () {
    return new MetaDataAdapter();
});

/**
 * Register the session flash service with the Twitter Bootstrap classes
 */
$di->set('flash', function () {
    return new Flash([
        'error'   => 'alert alert-danger',
        'success' => 'alert alert-success',
        'notice'  => 'alert alert-info',
        'warning' => 'alert alert-warning'
    ]);
});

// Set up the flash session service
$di->set("flashSession",function () { // สร้างฟังก์ชั่น flashSession
  return new FlashSession([
    "error"   => "alert alert-danger",
    "success" => "alert alert-success",
    "notice"  => "alert alert-info",
    "warning" => "alert alert-warning",
  ]);
});

/**
 * Start the session the first time some component request the session service
 */
$di->setShared('session', function () {
    $session = new SessionAdapter();
    $session->start();

    return $session;
});

$di->set("cookies",function () { // สร้าง function cookies
  $cookies = new Cookies();

  $cookies->useEncryption(true);

  return $cookies;
});

$di->set("crypt",function () { // สร้าง function การเข้ารหัส
  $crypt = new Crypt();

  $crypt->setKey('#1234asdf'); // Use your own key!

  return $crypt;
});

$di->set('facebook', function () { // สร้าง function การเรียกใช้ library ภายนอก
  $config = $this->getConfig();

  include __DIR__ . "/../../public/lib/Facebook/autoload.php";

  return new Facebook\Facebook([
    'app_id' => $config->facebook->appId,
    'app_secret' => $config->facebook->appSecret,
    'default_graph_version' => $config->facebook->version,
  ]);
}, true);

$di->setShared('router', function () { // กำหนด rounter และการส่ง parameter
  $router = new Router();
  $router->removeExtraSlashes(true);

  $router->add("/:controller/:action/([0-9]+)",[
    "controller" => 1,
    "action"     => 2,
    "id"         => 3,
  ]);

  return $router;
});

$di->set('dispatcher',function() use ($di) { // สร้าง function handle เมื่อเกิด error
  $evManager = new PhManager();
  $evManager->attach("dispatch:beforeException",function($event, $dispatcher, $exception){
    switch ($exception->getCode()) {
        case PhDispatcher::EXCEPTION_HANDLER_NOT_FOUND:
        case PhDispatcher::EXCEPTION_ACTION_NOT_FOUND:
          $dispatcher->forward([
            'controller' => 'error',
            'action'     => 'show404',
          ]);
					//////////////////////////////
					$logger = new \Phalcon\Logger\Adapter\File('../app/404logs/'.date('Ymd').'.log', ['mode' => 'a+']);
					$logger->error( '['.$exception->getCode().']: '. $exception->getMessage()."\r\n");
					$logger->close();
					//////////////////////////////
          return false;
        break;
        case 500:
        	$dispatcher->forward([
            'controller' => 'error',
            'action' => 'uncaughtException',
          ]);
  			  //////////////////////////////
  				$logger = new \Phalcon\Logger\Adapter\File('../app/500logs/'.date('Ymd').'.log', ['mode' => 'a+']);
  				$logger->error( '['.$exception->getCode().']: '. $exception->getMessage()."\r\n");
  				$logger->close();
  			  //////////////////////////////
          return false;
        break;
        default:
        	$dispatcher->forward([
            'controller' => 'error',
            'action' => 'uncaughtException',
          ]);
  			  //////////////////////////////
  				$logger = new \Phalcon\Logger\Adapter\File('../app/500logs/'.date('Ymd').'.log', ['mode' => 'a+']);
  				$logger->error( '['.$exception->getCode().']: '. $exception->getMessage()."\r\n");
  				$logger->close();
  			  //////////////////////////////
          return false;
        break;
      }
  });

  $dispatcher = new PhDispatcher();
  $dispatcher->setEventsManager($evManager);
  return $dispatcher;
},true);
