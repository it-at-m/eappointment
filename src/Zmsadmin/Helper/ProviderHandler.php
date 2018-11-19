<?php
/**
 *
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin\Helper;

class ProviderHandler extends \BO\Zmsadmin\BaseController
{

    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
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
            '/provider/'. $source .'/',
            array(
                'isAssigned' => true
            )
        )->getCollection()->withUniqueProvider()->sortByName();
        return $providerAssigned;
    }

    protected static function readProviderNotAssigned($source)
    {
        $providerNotAssigned = \App::$http->readGetResult(
            '/provider/'. $source .'/',
            array(
                'isAssigned' => false
            )
        )->getCollection()->withUniqueProvider()->sortByName();
        return $providerNotAssigned;
    }
}
