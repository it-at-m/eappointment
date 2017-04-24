<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Mail as Entity;

use BO\Mellon\Validator;

/**
 * Send notification, API proxy
 *
 */
class MailSend extends BaseController
{
    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $input = $request->getParsedBody();
        $selectedProcessId = Validator::value($input['selectedProcess'])->isNumber()->getValue();

        $process = \App::$http->readGetResult('/workstation/process/'. $selectedProcessId .'/get/')->getEntity();

        $mail = (new Entity)->toCustomMessageEntity($process, $input);
        $mail = \App::$http->readPostResult('/mails/', $mail)->getEntity();

        return \BO\Slim\Render::redirect(
            'mail',
            [],
            [
                'selectedprocess' => $process->id,
                'result' => ('form' == $input['submit'] && $mail->hasId()) ? 'success' : 'error'
            ]
        );
    }
}
