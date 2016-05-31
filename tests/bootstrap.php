<?php

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // catch errors on bootstrapping
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

require(dirname(__DIR__) . '/bootstrap.php');
