<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use BO\Slim\Render;
use BO\Zmsentities\Mail as Entity;

/**
 * Delete a process
 */
class PickupMail extends BaseController
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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $validator = $request->getAttribute('validator');
        $processId = $validator->getParameter('selectedprocess')->isNumber()->getValue();
        $process = \App::$http->readGetResult('/process/'. $processId .'/', ['resolveReferences' => 1])->getEntity();
        // disabled 2021-01-20 by TK because in ZMS1 it is allowed to send pickup mailings
        // to processes from another scope in department
        //$workstation->testMatchingProcessScope($workstation->getScopeList(), $process);
        $config = \App::$http->readGetResult('/config/')->getEntity();
        $department = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/department/')->getEntity();

        if (! $process->scope->hasEmailFrom()) {
            throw new \BO\Zmsadmin\Exception\MailFromMissing();
        }
        $mail = (new Entity)->toResolvedEntity($process, $config, 'pickup');
        $mail = \App::$http->readPostResult('/mails/', $mail->withDepartment($department))->getEntity();

        return \BO\Slim\Render::withHtml(
            $response,
            'block/pickup/mailSent.twig',
            array(
                'process' => $process,
                'mail' => $mail
            )
        );
    }
}
