<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;

class Mail extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $selectedProcessId = Validator::param('selectedprocess')->isNumber()->getValue();
        $success = Validator::param('result')->isString()->getValue();
        if ($selectedProcessId) {
            $process = \App::$http->readGetResult('/workstation/process/'. $selectedProcessId .'/get/')->getEntity();
        }
        $department = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/department/')->getEntity();

        \BO\Slim\Render::html('page/mail.twig', array(
            'title' => 'eMail-Versand',
            'menuActive' => $workstation->getRedirect(),
            'workstation' => $workstation,
            'department' => $department,
            'process' => $process,
            'result' => $success,
            'source' => $workstation->getRedirect()
        ));
    }
}
