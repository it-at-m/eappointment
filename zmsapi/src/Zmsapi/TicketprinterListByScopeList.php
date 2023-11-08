<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;

class TicketprinterListByScopeList extends BaseController
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
        (new Helper\User($request))->checkRights();
        $scopeIdList = explode(',', $args['ids']);

        $ticketprinterList = new \BO\Zmsentities\Collection\TicketprinterList();
        foreach ($scopeIdList as $scopeId) {
            $isEnabled = (new \BO\Zmsdb\Scope)->readIsEnabled($scopeId, \App::$now);
            $entity = (new \BO\Zmsentities\Ticketprinter([
                'enabled' => $isEnabled,
                'buttonlist' => 's'. $scopeId
            ]));
            $ticketprinterList->addEntity($entity->toStructuredButtonList());
        }

        $message = Response\Message::create($request);
        $message->data = $ticketprinterList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
