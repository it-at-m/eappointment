<?php

/**
 *
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsadmin\Helper;

use BO\Zmsentities\Collection\ProviderList as Collection;

class ProviderHandler extends \BO\Zmsadmin\BaseController
{
    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ): \Psr\Http\Message\ResponseInterface {
        return \BO\Slim\Render::withJson(
            $response,
            static::readProviderList($args['source'])
        );
    }

    /**
     * @return (mixed|string)[][]
     *
     * @psalm-return list{array{name: 'assigned', items: mixed}, array{name: 'notAssigned', items: mixed}}
     */
    public static function readProviderList($source): array
    {
        return [
            ['name' => 'assigned', 'items' => static::readProviderAssigned($source)],
            ['name' => 'notAssigned', 'items' => static::readProviderNotAssigned($source)]
        ];
    }

    protected static function readProviderAssigned($source)
    {
        $providerAssigned = \App::$http->readGetResult(
            '/provider/' . $source . '/',
            array(
                'isAssigned' => true
            )
        )->getCollection();
        return ($providerAssigned) ?
            $providerAssigned->withUniqueProvider()->sortByName() :
            new Collection();
    }

    protected static function readProviderNotAssigned($source)
    {
        $providerNotAssigned = \App::$http->readGetResult(
            '/provider/' . $source . '/',
            array(
                'isAssigned' => false
            )
        )->getCollection();
        return ($providerNotAssigned) ?
            $providerNotAssigned->withUniqueProvider()->sortByName() :
            new Collection();
    }
}
