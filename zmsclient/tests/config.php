<?php
// @codingStandardsIgnoreFile

// Ensure zmsslim Application class is available in PHP 8.3
if (!class_exists('\\BO\\Slim\\Application')) {
    // Try to load from vendor if not already loaded
    $zmsslimPath = __DIR__ . '/../vendor/eappointment/zmsslim/src/Slim/Application.php';
    if (file_exists($zmsslimPath)) {
        require_once $zmsslimPath;
    }
}

class App extends \BO\Slim\Application
{
    const IDENTIFIER = "ZMS";
    public static $http = null;
}