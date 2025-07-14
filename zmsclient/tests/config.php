<?php
// @codingStandardsIgnoreFile

// Ensure zmsslim Application class is available in PHP 8.3
if (!class_exists('\\BO\\Slim\\Application')) {
    // Try multiple possible paths for zmsslim
    $possiblePaths = [
        __DIR__ . '/../vendor/eappointment/zmsslim/src/Slim/Application.php',
        __DIR__ . '/../../zmsslim/src/Slim/Application.php',
        __DIR__ . '/../../../zmsslim/src/Slim/Application.php',
    ];
    
    $loaded = false;
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $loaded = true;
            break;
        }
    }
    
    // If still not found, create a minimal stub for testing
    if (!$loaded && !class_exists('\\BO\\Slim\\Application')) {
        eval('
        namespace BO\\Slim {
            class Application {
                const IDENTIFIER = "ZMS";
                public static $http = null;
            }
        }
        ');
    }
}

class App extends \BO\Slim\Application
{
    const IDENTIFIER = "ZMS";
    public static $http = null;
}