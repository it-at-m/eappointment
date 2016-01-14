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
class Search extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        \BO\Slim\Render::html('page/search.twig', array(
            'title' => 'Suche',
            'menuActive' => 'search'
        ));
    }
}
