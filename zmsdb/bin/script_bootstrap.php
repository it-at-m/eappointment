<?php

$dir = is_dir(getcwd() . '/vendor/bo/zmsdb') ? realpath(getcwd()) : realpath(__DIR__);
while ($dir != '/'
    && !file_exists("$dir/config.php")
    && !file_exists("$dir/config/zmsdb.php")
) {
    $dir = dirname($dir);
    if (file_exists("$dir/autoload.php")) {
        require_once("$dir/autoload.php");
    }
}
if (file_exists("$dir/bootstrap.php")) {
    require("$dir/bootstrap.php");
} elseif (file_exists("$dir/config/zmsdb.php")) {
    require_once("$dir/vendor/autoload.php");
    require("$dir/config/zmsdb.php");
} else {
    require("$dir/config.php");
}
