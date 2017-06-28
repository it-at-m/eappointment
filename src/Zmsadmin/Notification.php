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
class Notification extends BaseController
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

        $selectedProcessId = Validator::param('selectedprocess')->isNumber()->getValue();
        $dialog = Validator::param('dialog')->isNumber()->getValue();
        $success = Validator::param('result')->isString()->getValue();
        $sendStatus = Validator::param('status')->isString()->isBiggerThan(2)->getValue();
        $department = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/department/')->getEntity();
        $config = \App::$http->readGetResult('/config/')->getEntity();
        $formResponse = null;
        $input = $request->getParsedBody();
        $process = ($selectedProcessId) ?
            \App::$http->readGetResult('/process/'. $selectedProcessId .'/')->getEntity() :
            null;

        if (array_key_exists('submit', (array)$input) && 'form' == $input['submit']) {
            $formResponse = $this->writeValidatedNotification($process, $config, $department, $sendStatus);
            if ($formResponse instanceof Entity) {
                return \BO\Slim\Render::redirect(
                    'notification',
                    [],
                    [
                        'selectedprocess' => $process->id,
                        'dialog' => $dialog,
                        'result' => ('form' == $input['submit'] && $formResponse->hasId()) ? 'success' : 'error'
                    ]
                );
            }
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/notification.twig',
            array(
                'title' => 'SMS-Versand',
                'menuActive' => $workstation->getRedirect(),
                'workstation' => $workstation,
                'department' => $department,
                'process' => $process,
                'dialog' => $dialog,
                'result' => $success,
                'form' => $formResponse,
                'source' => $workstation->getRedirect()
            )
        );
    }

    private function writeValidatedNotification($process, $config, $department, $sendStatus = null)
    {
        $collection = array();
        $collection['message'] = Validator::param('message')->isString()
            ->isBiggerThan(2, "Es muss eine aussagekräftige Nachricht eingegeben werden");
        $collection = Validator::collection($collection);
        if (! $collection->hasFailed()) {
            if ($sendStatus) {
                $process->status = $sendStatus;
                $notification = (new Entity)->toResolvedEntity($process, $config, $department);
            } else {
                $notification = (new Entity)->toCustomMessageEntity($process, $collection->getValues(), $department);
            }
            $notification = \App::$http->readPostResult('/notification/', $notification)->getEntity();
            return $notification;
        }
        return $collection->getStatus();
    }
}
