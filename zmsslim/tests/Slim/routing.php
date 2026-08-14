<?php
// @codingStandardsIgnoreFile

\App::$slim->get('/unittest/{id}/[{lang}/]', \BO\Slim\Tests\Get::class)
    ->setName("getroute");

\App::$slim->post('/unittest/', \BO\Slim\Controller\Post::class)
    ->setName("postroute");
