<?php
do {
    $dir = dirname($dir);
    if (file_exists("$dir/autoload.php")) {
        require_once("$dir/autoload.php");
    }
} while ($dir != '/' && !file_exists("$dir/config.php"));
if (file_exists("$dir/bootstrap.php")) {
    require("$dir/bootstrap.php");
} else {
    require("$dir/config.php");
}

