<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;

use BO\Zmsentities\Mail as Entity;

class Mail extends BaseController
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
        $department = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/department/')->getEntity();
        $formResponse = null;
        $input = $request->getParsedBody();
        $process = ($selectedProcessId) ?
            \App::$http->readGetResult('/process/'. $selectedProcessId .'/')->getEntity() :
            null;
        
        if (array_key_exists('submit', (array)$input) && 'form' == $input['submit']) {
            $formResponse = $this->writeValidatedMail($process, $department);
            if ($formResponse instanceof Entity) {
                return \BO\Slim\Render::redirect(
                    'mail',
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
            'page/mail.twig',
            array(
                'title' => 'eMail-Versand',
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

    private function writeValidatedMail($process, $department)
    {
        $collection = array();
        $collection['subject'] = Validator::param('subject')->isString()
            ->isBiggerThan(2, "Es muss ein aussagekräftiger Betreff eingegeben werden");
        $collection['message'] = Validator::param('message')->isString()
            ->isBiggerThan(2, "Es muss eine aussagekräftige Nachricht eingegeben werden");
        $collection = Validator::collection($collection);
        if (! $collection->hasFailed()) {
            $mail = (new Entity)->toCustomMessageEntity($process, $collection->getValues());
            $mail = \App::$http->readPostResult('/mails/', $mail->withDepartment($department))->getEntity();
            return $mail;
        }
        return $collection->getStatus();
    }
}
