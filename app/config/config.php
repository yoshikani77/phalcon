<?php
/*
 * Modified: prepend directory path of current file, because of this file own different ENV under between Apache and command line.
 * NOTE: please remove this comment.
 */
defined('BASE_PATH') || define('BASE_PATH', getenv('BASE_PATH') ?: realpath(dirname(__FILE__) . '/../..'));
defined('APP_PATH') || define('APP_PATH', BASE_PATH . '/app');
date_default_timezone_set('Asia/Bangkok'); // ตั้งค่า timezone การใช้งานระบบ
return new \Phalcon\Config([
    'database' => [
        'adapter'     => 'Mysql', // รูปแบบการเชื่อมต่อ
        'host'        => 'localhost', // ที่อยู่ที่ต้องการเชื่อมต่อฐานข้อมูล
        'username'    => 'root', // ชื่อผู้ใช้เข้า phpmyadmin
        'password'    => '', // รหัสผ่านเข้า phpmyadmin
        'dbname'      => 'event_db', // ชื่อ database
        'charset'     => 'utf8', // string encode เมื่อได้รับการแสดงผล
    ],
    'application' => [ // ลงทะเบียน โฟลเดอร์ ต่างๆที่ต้องการใช้งานใน โฟลเดอร์ app
        'appDir'         => APP_PATH . '/',
        'controllersDir' => APP_PATH . '/controllers/',
        'modelsDir'      => APP_PATH . '/models/',
        'migrationsDir'  => APP_PATH . '/migrations/',
        'viewsDir'       => APP_PATH . '/views/',
        'pluginsDir'     => APP_PATH . '/plugins/',
        'libraryDir'     => APP_PATH . '/library/',
        'cacheDir'       => BASE_PATH . '/cache/',

        // This allows the baseUri to be understand project paths that are not in the root directory
        // of the webpspace.  This will break if the public/index.php entry point is moved or
        // possibly if the web server rewrite rules are changed. This can also be set to a static path.
        'baseUri'        => preg_replace('/public([\/\\\\])index.php$/', '', $_SERVER["PHP_SELF"]),
    ]// สามารถเพิ่ม config ต่างๆได้ในนี้
]);
