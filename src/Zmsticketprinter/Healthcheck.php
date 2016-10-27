<?php
/**
 *
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter;

/**
 * Handle requests concerning services
 */
class Healthcheck extends BaseController
{

    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @return String
     */
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $status = \App::$http->readGetResult('/status/')->getEntity();
        \BO\Slim\Render::withLastModified($response, time(), '0');
        \BO\Zmsclient\Status::testStatus($status);
        $result = "OK - DB=" . $status['database']['nodeConnections'] . "%";
        return $result;
    }
}
