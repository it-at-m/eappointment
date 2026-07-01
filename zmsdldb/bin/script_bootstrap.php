<?php

$dir = is_dir(getcwd() . '/vendor/eappointment/zmsdldb') ? realpath(getcwd()) : realpath(__DIR__);
while ($dir != '/'
    && !file_exists("$dir/config.php")
) {
    $dir = dirname($dir);
    if (file_exists("$dir/vendor/autoload.php")) {
        require_once("$dir/vendor/autoload.php");
    }
}
if (file_exists("$dir/bootstrap.php")) {
    require("$dir/bootstrap.php");
} else {
    require("$dir/config.php");
}

\BO\Slim\Bootstrap::ensureLogger();
