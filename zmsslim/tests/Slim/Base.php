<?php

/**
 *
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Slim\Tests;

abstract class Base extends \BO\Slim\PhpUnit\Base
{

    protected $namespace = '\\BO\\Slim\\Tests\\Controller\\';

    protected $sessionData =  [
        'status' => 'reserved',
        'human' => array (
            'step' => array (
                'dayselect' => 1,
                'timeselect' => 1,
                'register' => 1
            ),
            'client' => 1,
            'ts' => 1473339559,
            'origin' => 'captcha',
            'remoteAddress' => '127.0.0.1',
            'captcha_text' => 'nWGvKt',
        ),
        'basket' => array (
            'requests' => '120703',
            'providers' => '122217',
            'firstDay' => '2016-04-01',
            'lastDay' => '2016-05-31',
            'date' => 1464300000,
            'scope' => 141,
            'process' => '100005',
            'authKey' => '95a3',
        ),
        'entry' => array (
            'source' => 'unittest',
            'providerList' => '122217',
            'requestList' => '120703'
        )
    ];

    public function getTwigExtensions()
    {
        return $twigExtensionsClass = \App::$slim
            ->getContainer()
            ->get('view')
            ->getEnvironment()
            ->getExtension('\BO\Slim\TwigExtension');
    }
}
