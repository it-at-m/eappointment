<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Ticketprinter as Query;
use \BO\Zmsdb\Scope;
use \BO\Zmsdb\ProcessStatusQueued;

class TicketprinterWaitingnumberByScope extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $ticketprinter = (new Query())->readByHash($args['hash']);

        if (! $ticketprinter->hasId()) {
            throw new Exception\Ticketprinter\TicketprinterHashNotValid();
        }
        if (! $ticketprinter->isEnabled()) {
            throw new Exception\Ticketprinter\TicketprinterNotEnabled();
        }

        $scope = (new Scope())->readEntity($args['id'], 0);
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }

        $process = ProcessStatusQueued::init()->writeNewFromTicketprinter($scope, \App::$now);

        $message = Response\Message::create($request);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
