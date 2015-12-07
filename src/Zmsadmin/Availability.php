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
class Availability extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $times = array(
            'conflict_times' => array(
                array('title' => 'Konflikt', 'start' => '12.15', 'end' => '12.45'),
            ), 
            'numberofappointment_times' => array(
                array('title' => '31 von 64 Terminen verfügbar', 'max'=>'64', 'busy'=> '31', 'start' => '8.00', 'end' => '13.00'),
                array('title' => '11 von 144 Terminen verfügbar', 'max'=>'144', 'busy'=> '11','start' => '13.00', 'end' => '19.00'),
            ), 
            'opening_times' => array(
                array('title' => 'Ganztags', 'start' => '8.00', 'end' => '19.00'),
            ), 
            'appointment_times' => array(
                array('title' => 'Terminvergabe 1', 'workstations' => '6', 'height' => '3.0rem', 'start' => '8.00', 'end' => '12.45'),
                array('title' => 'Terminvergabe 2', 'workstations' => '8', 'height' => '4.6rem', 'start' => '12.15', 'end' => '19.00'),
            ),
        );
        \BO\Slim\Render::html('page/availability.twig', array(
            'title' => 'Öffnungszeiten',
            'times' => $times,
            'menuActive' => 'availability'
        ));
    }
}
