<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Scope as Entity;
use BO\Mellon\Validator;

class TicketprinterStatusByScope extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        \BO\Slim\Render::html('page/ticketprinterStatus.twig', array(
            'title' => 'Wartenummernausgabe am Kiosk',
            'menuActive' => 'ticketprinterStatus'
        ));
    }

    /**
     *
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();

        $entityId = Validator::value($args['id'])->isNumber()->getValue();
        $entity = \App::$http->readGetResult('/scope/' . $entityId . '/')->getEntity();

        $input = $request->getParsedBody();
        if (is_array($input) && array_key_exists('save', $input)) {
            $entity->status['ticketprinter']['deactivated'] = $input['kioskausgabe'];
            $workstation->scope['status']['ticketprinter']['deactivated'] = $input['kioskausgabe'];
            if ($input['hinweis']) {
                if (!$entity->preferences['ticketprinter']) {
                    $entity->preferences['ticketprinter'] = [];
                }
            }
            $entity->preferences['ticketprinter']['deactivatedText'] = $input['hinweis'];
            $entity = \App::$http->readPostResult('/scope/' . $entity->id . '/', $entity)
                    ->getEntity();
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/ticketprinterStatus.twig',
            array(
                'title' => 'Standort',
                'title' => 'Wartenummernausgabe am Kiosk',
                'menuActive' => 'ticketprinterStatus',
                'workstation' => $workstation,
                'scope' => $entity->getArrayCopy(),
            )
        );
    }
}
