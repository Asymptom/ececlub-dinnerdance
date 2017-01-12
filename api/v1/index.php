<?php

require_once 'utils/session_utils.php';
require_once 'utils/password_utils.php';

require '../../vendor/autoload.php';

require '../config.php';

$app = new \Slim\App(["settings" => $config]);

$container = $app->getContainer();

$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $conn = new mysqli($db['host'], $db['user'], $db['pass'], $db['dbname'] );

    // Check for database connection error
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }

    return $conn;
};

require_once 'authentication.php';

require_once 'profile.php';

require_once 'password.php';

require_once 'tables.php';

$app->run();
?>