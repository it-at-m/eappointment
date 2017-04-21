<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;

class Notification extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $selectedProcessId = Validator::param('selectedprocess')->isNumber()->getValue();
        if ($selectedProcessId) {
            $process = \App::$http->readGetResult('/workstation/process/'. $selectedProcessId .'/get/')->getEntity();
        }
        $department = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/department/')->getEntity();

        \BO\Slim\Render::html('page/notification.twig', array(
            'title' => 'SMS-Versand',
            'menuActive' => 'notification',
            'workstation' => $workstation,
            'department' => $department,
            'process' => $process,
            'source' => $workstation->getRedirect()
        ));
    }
}
