<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Ticketprinter\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;

class TicketprinterListByScopeList extends \BO\Zmsbackend\Api\BaseController
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
        (new \BO\Zmsbackend\Helper\User($request))->checkPermissions();

        $scopeIdList = explode(',', $args['ids']);

        $ticketprinterList = new \BO\Zmsentities\Collection\TicketprinterList();
        foreach ($scopeIdList as $scopeId) {
            $isEnabled = (new \BO\Zmsbackend\Scope\Service\Scope())->readIsEnabled($scopeId, \App::$now);
            $entity = (new \BO\Zmsentities\Ticketprinter([
                'enabled' => $isEnabled,
                'buttonlist' => 's' . $scopeId
            ]));
            $ticketprinterList->addEntity($entity->toStructuredButtonList());
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $ticketprinterList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
