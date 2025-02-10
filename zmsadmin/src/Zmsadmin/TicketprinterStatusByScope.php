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
     *
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', [
            'resolveReferences' => 1,
            'gql' => Helper\GraphDefaults::getWorkstation()
        ])->getEntity();
        $success = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();

        $scopeId = Validator::value($args['id'])->isNumber()->getValue();
        $scope = \App::$http->readGetResult('/scope/' . $scopeId . '/', [
            'gql' => Helper\GraphDefaults::getScope()
        ])->getEntity();

        $input = $request->getParsedBody();
        if (is_array($input) && array_key_exists('save', $input)) {
            $scope->status['ticketprinter']['deactivated'] = $input['kioskausgabe'];
            $workstation->scope['status']['ticketprinter']['deactivated'] = $input['kioskausgabe'];
            if ($input['hinweis']) {
                if (! isset($scope->preferences['ticketprinter'])) {
                    $scope->preferences['ticketprinter'] = [];
                }
            }
            $scope->preferences['ticketprinter']['deactivatedText'] = $input['hinweis'];
            $scope = \App::$http->readPostResult('/scope/' . $scope->id . '/', $scope)->getEntity();

            return \BO\Slim\Render::redirect('ticketprinterStatusByScope', ['id' => $scopeId], [
                'success' => 'ticketprinter_deactivated_' . $scope->status['ticketprinter']['deactivated']
            ]);
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/ticketprinterStatus.twig',
            array(
                'title' => 'Wartenummernausgabe am Kiosk',
                'menuActive' => 'ticketprinterStatus',
                'workstation' => $workstation,
                'scope' => $scope->getArrayCopy(),
                'success' => $success
            )
        );
    }
}
