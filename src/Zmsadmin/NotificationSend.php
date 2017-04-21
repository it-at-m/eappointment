<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Notification as Entity;

use BO\Mellon\Validator;

/**
 * Send notification, API proxy
 *
 */
class NotificationSend extends BaseController
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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $input = $request->getParsedBody();
        $selectedProcessId = Validator::value($input['selectedProcess'])->isNumber()->getValue();

        $process = \App::$http->readGetResult('/workstation/process/'. $selectedProcessId .'/get/')->getEntity();
        // update process status via api to queued does not work for withAppointment true - zmsdb/query/process:336
        $process->status = 'queued';
        $department = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/department/')->getEntity();
        $config = \App::$http->readGetResult('/config/')->getEntity();

        $notification = (new Entity)->toResolvedEntity($process, $config, $department);
        if (array_key_exists('message', $input) && '' != $input['message']) {
            $notification->message = $input['message'];
        }
        $notification = \App::$http->readPostResult('/notification/', $notification)->getEntity();
        return \BO\Slim\Render::withHtml(
            $response,
            'page/notification.twig',
            array(
                'notification' => $notification,
                'isFromForm' => ('form' == $input['submit'])
            )
        );
    }
}
