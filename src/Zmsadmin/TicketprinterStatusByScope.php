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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $confirm_success = $request->getAttribute('validator')->getParameter('confirm_success')->isString()->getValue();

        $scopeId = Validator::value($args['id'])->isNumber()->getValue();
        $scope = \App::$http->readGetResult('/scope/' . $scopeId . '/')->getEntity();

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
            $scope = \App::$http->readPostResult('/scope/' . $scope->id . '/', $scope)
                    ->getEntity();
            return \BO\Slim\Render::redirect('ticketprinterStatusByScope', ['id' => $scopeId], [
                'confirm_success' => \App::$now->getTimeStamp()
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
                'confirm_success' => $confirm_success,
            )
        );
    }
}
