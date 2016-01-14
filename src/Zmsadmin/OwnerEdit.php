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
class OwnerEdit extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        \BO\Slim\Render::html('page/ownerEdit.twig', array(
            'title' => 'Administration Kunden',
            'menuActive' => 'owner'
        ));
    }
}
