<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use BO\Slim\Render;
use BO\Zmsentities\Exception\UserAccountMissingRights;

class TicketprinterStatusByScope extends BaseController
{
    /**
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ): \Psr\Http\Message\ResponseInterface {
        $workstation = \App::$http->readGetResult('/workstation/', [
            'resolveReferences' => 1,
            'gql' => Helper\GraphDefaults::getWorkstation()
        ])->getEntity();
        $success = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();
        if (!$workstation->getUseraccount()->hasPermissions(['ticketprinter', 'scope'])) {
            throw new UserAccountMissingRights();
        }
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

            return Render::redirect('ticketprinterStatusByScope', ['id' => $scopeId], [
                'success' => 'ticketprinter_deactivated_' . $scope->status['ticketprinter']['deactivated']
            ]);
        }

        return Render::withHtml(
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
