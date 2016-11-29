<?php
/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter;

use \BO\Zmsentities\Ticketprinter as Entity;

class NotificationAssign extends BaseController
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
        $validator = $request->getAttribute('validator');
        $processId = $validator->getParameter('processId')->isNumber()->getValue();
        $authKey = $validator->getParameter('authKey')->isString()->getValue();
        $client = new \BO\Zmsentities\Client($validator->getParameter('client')->isArray()->getValue());
        $process = \App::$http->readGetResult('/process/'. $processId .'/'. $authKey .'/')->getEntity();
        $process->updateClients($client);
        $process->status = 'queued';
        \App::$http->readPostResult(
            '/process/'. $process->id .'/'. $process->authKey .'/confirmation/notification/',
            $process
        );
        return \BO\Slim\Render::redirect('Home', [], []);
    }
}
