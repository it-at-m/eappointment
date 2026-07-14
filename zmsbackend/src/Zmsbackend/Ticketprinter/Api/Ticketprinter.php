<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Ticketprinter\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Ticketprinter\Service\Ticketprinter as Query;
use BO\Zmsentities\Ticketprinter as Entity;

/**
 *
 * @SuppressWarnings(Coupling)
 *
 */
class Ticketprinter extends \BO\Zmsbackend\Api\BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        \BO\Zmsbackend\Connection\Select::setCriticalReadSession();

        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new Entity($input);
        $entity->testValid();

        if (! $entity->toProperty()->buttons->isAvailable()) {
            $entity = $entity->toStructuredButtonList();
        }

        $ticketprinter = (new Query())->readByButtonList($entity, \App::$now);
        \BO\Zmsbackend\Helper\TicketprinterAccess::testTicketprinter($ticketprinter);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $ticketprinter;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
