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
        $source = Validator::param('source')->isString()->getValue();

        $department = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/department/')->getEntity();
        $config = \App::$http->readGetResult('/config/')->getEntity();
        $input = $request->getParsedBody();
        $process = ($selectedProcessId) ?
            \App::$http->readGetResult('/process/'. $selectedProcessId .'/')->getEntity() :
            null;

        $formResponse = null;

        if ($process && $sendStatus) {
            $process->status = $sendStatus;
        };
        if (array_key_exists('submit', (array)$input) && 'reminder' == $input['submit']) {
            $formResponse = $this->getReminderNotification($process, $config, $department);
        } elseif (array_key_exists('submit', (array)$input) && 'form' == $input['submit']) {
            $formResponse = $this->getCustomNotification($process, $department);
        }

        if ($formResponse instanceof Entity) {
            return \BO\Slim\Render::redirect(
                'notification',
                [],
                [
                    'selectedprocess' => $process->id,
                    'dialog' => $dialog,
                    'status' => $sendStatus,
                    'result' => ($formResponse->hasId()) ? 'success' : 'error',
                    'source' => $input['submit']
                ]
            );
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/notification.twig',
            array(
                'title' => 'SMS-Versand',
                'menuActive' => $workstation->getVariantName(),
                'workstation' => $workstation,
                'department' => $department,
                'process' => $process,
                'status' => $sendStatus,
                'dialog' => $dialog,
                'result' => $success,
                'form' => $formResponse,
                'source' => $source,
                'redirect' => $workstation->getVariantName()
            )
        );
    }

    protected function getReminderNotification($process, $config, $department)
    {
        $notification = (new Entity)->toResolvedEntity($process, $config, $department);
        return $this->writeNotification($notification);
    }

    protected function getCustomNotification($process, $department)
    {
        $collection = array();
        $collection['message'] = Validator::param('message')->isString()
            ->isBiggerThan(2, "Es muss eine aussagekräftige Nachricht eingegeben werden");
        $collection = Validator::collection($collection);
        if (! $collection->hasFailed()) {
            $notification = (new Entity)->toCustomMessageEntity($process, $collection->getValues(), $department);
            return $this->writeNotification($notification);
        }
        return $collection->getStatus();
    }

    private function writeNotification($notification)
    {
        return \App::$http->readPostResult('/notification/', $notification)->getEntity();
    }
}
