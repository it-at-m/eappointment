<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/vendor/autoload.php';

if (!class_exists('App')) {
    class App extends \BO\Zmscitizenbackend\Application
    {
    }
}

App::$log = new class {
    public function warning(string $message, array $context = []): void
    {
    }
};
