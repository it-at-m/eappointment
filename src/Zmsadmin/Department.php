<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

/**
  * Handle requests concerning services
  *
  */
class Department extends BaseController
{
    /**
     * @return String
     */
    public static function render($departmentId)
    {
        $department = \App::$http->readGetResult(
            '/department/'. $departmentId .'/'
        )->getEntity();
        \BO\Slim\Render::html('page/department.twig', array(
            'title' => 'Standort',
            'department' => $department,
            'menuActive' => 'owner'
        ));
    }
}
