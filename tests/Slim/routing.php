<?php
// @codingStandardsIgnoreFile

\App::$slim->get('/unittest/{id}/[{lang}/]', '\BO\Slim\Tests\Get')
    ->setName("getroute");

\App::$slim->post('/unittest/', '\BO\Slim\Controller\Post')
    ->setName("postroute");