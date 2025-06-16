<?php

namespace BO\Zmsapi\Route;

use BO\Zmsapi\ProcessFree;
use BO\Zmsapi\ProcessFreeUnique;

class Process
{
    public function getRoutes()
    {
        return [
            [
                'pattern' => '/process/status/free/',
                'methods' => ['POST'],
                'handler' => ProcessFree::class,
                'name' => 'processFree',
            ],
            [
                'pattern' => '/process/status/free/unique/',
                'methods' => ['POST'],
                'handler' => ProcessFreeUnique::class,
                'name' => 'processFreeUnique',
            ],
        ];
    }
} 