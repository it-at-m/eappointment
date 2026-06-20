<?php

/**
 *
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsadmin\Helper;

use BO\Zmsadmin\BaseController;
use BO\Zmsentities\Collection\ProviderList;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ProviderHandler extends BaseController
{
    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @return ResponseInterface
     */
    #[\Override]
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        return \BO\Slim\Render::withJson(
            $response,
            static::readProviderList($args['source'])
        );
    }

    public static function readProviderList($source)
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
            new ProviderList();
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
            new ProviderList();
    }
}
