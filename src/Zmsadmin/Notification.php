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

        $dialog = Validator::param('dialog')->isNumber()->getValue();
        $success = Validator::param('success')->isString()->getValue();
        $error = Validator::param('error')->isString()->getValue();
        $sendStatus = Validator::param('status')->isString()->isBiggerThan(2)->getValue();

        $department = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/department/')->getEntity();
        $config = \App::$http->readGetResult('/config/')->getEntity();
        $input = $request->getParsedBody();
        $process = $this->getProcessWithStatus($sendStatus);
        $formResponse = $this->getValidatedResponse($input, $process, $config, $department);
        if ($formResponse instanceof Entity) {
            $query = [
                'selectedprocess' => $process->id,
                'dialog' => $dialog,
                'status' => $sendStatus
            ];
            $message = ($formResponse->hasId())
                ? ['success' => 'notification_sent']
                : ['error' => 'notification_failed'];
            $query = array_merge($message, $query);
            return \BO\Slim\Render::redirect(
                'notification',
                [],
                $query
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
                'success' => $success,
                'error' => $error,
                'status' => $sendStatus,
                'dialog' => $dialog,
                'form' => $formResponse,
                'redirect' => $workstation->getVariantName()
            )
        );
    }

    protected function getProcessWithStatus($status = null)
    {
        $selectedProcessId = Validator::param('selectedprocess')->isNumber()->getValue();
        $process = ($selectedProcessId) ?
            \App::$http->readGetResult('/process/'. $selectedProcessId .'/')->getEntity() :
            null;
        if ($process && $status) {
            $process->status = $status;
        }
        return $process;
    }

    protected function getValidatedResponse($input, $process, $config, $department)
    {
        $formResponse = null;
        if (array_key_exists('submit', (array)$input) && 'reminder' == $input['submit']) {
            $formResponse = $this->getReminderNotification($process, $config, $department);
        } elseif (array_key_exists('submit', (array)$input) && 'form' == $input['submit']) {
            $formResponse = $this->getCustomNotification($process, $department);
        }
        return $formResponse;
    }

    protected function getReminderNotification($process, $config, $department)
    {
        $notification = (new Entity)->toResolvedEntity($process, $config, $department);
        return $this->writeNotification($notification, $process);
    }

    protected function getCustomNotification($process, $department)
    {
        $collection = array();
        $collection['message'] = Validator::param('message')->isString()
            ->isBiggerThan(2, "Es muss eine aussagekräftige Nachricht eingegeben werden");
        $collection = Validator::collection($collection);
        if (! $collection->hasFailed()) {
            $notification = (new Entity)->toCustomMessageEntity($process, $collection->getValues(), $department);
            return $this->writeNotification($notification, $process);
        }
        return $collection->getStatus();
    }

    private function writeNotification($notification, $process)
    {
        return ($process->scope->hasNotificationEnabled())
            ? \App::$http->readPostResult('/notification/', $notification)->getEntity()
            : null;
    }
}
