<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

/**
  * Handle requests concerning services
  *
  */
class AvailabilityMonth extends BaseController
{

    /**
     * @SuppressWarnings(ExcessiveMethodLength)
     * @return String
     */
    public static function render()
    {
        $data = array(
            '2016-08-1'  => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-08-2'  => array(
                'closed' => true,
                'holiday' => false,
                'hasOpeningTimes' => false,
                'hasAppointmentTimes' => false,
                'hasConflict'=>false
            ),
            '2016-08-3'  => array(
                'closed' => true,
                'holiday' => false,
                'hasOpeningTimes' => false,
                'hasAppointmentTimes' => false,
                'hasConflict'=>false
            ),
            '2016-08-4'  => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => false,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-08-5'  => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-08-6'  => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-08-7'  => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => false,
                'hasConflict'=>false
            ),
            '2016-08-8'  => array(
                'closed' => true,
                'holiday' => true,
                'hasOpeningTimes' => false,
                'hasAppointmentTimes' => false,
                'hasConflict'=>false
            ),
            '2016-08-9'  => array(
                'closed' => true,
                'holiday' => false,
                'hasOpeningTimes' => false,
                'hasAppointmentTimes' => false,
                'hasConflict'=>false
            ),
            '2016-08-10' => array(
                'closed' => true,
                'holiday' => false,
                'hasOpeningTimes' => false,
                'hasAppointmentTimes' => false,
                'hasConflict'=>false
            ),
            '2016-08-11' => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => false,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-08-12' => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-08-13' => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-08-14' => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-08-15' => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-08-16' => array(
                'closed' => true,
                'holiday' => false,
                'hasOpeningTimes' => false,
                'hasAppointmentTimes' => false,
                'hasConflict'=>false
            ),
            '2016-08-17' => array(
                'closed' => true,
                'holiday' => false,
                'hasOpeningTimes' => false,
                'hasAppointmentTimes' => false,
                'hasConflict'=>false
            ),
            '2016-08-18' => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => false,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-08-19' => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-08-20' => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-08-21' => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-08-22' => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-08-23' => array(
                'closed' => true,
                'holiday' => false,
                'hasOpeningTimes' => false,
                'hasAppointmentTimes' => false,
                'hasConflict'=>false
            ),
            '2016-08-24' => array(
                'closed' => true,
                'holiday' => false,
                'hasOpeningTimes' => false,
                'hasAppointmentTimes' => false,
                'hasConflict'=>false
            ),
            '2016-08-25' => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => false,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-08-26' => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-08-27' => array(
                'closed' => false,
                'holiday' => false,
                'free' => true,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-08-28' => array(
                'closed' => false,
                'holiday' => false,
                'busy' => true,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-08-29' => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-08-30' => array(
                'closed' => true,
                'holiday' => false,
                'hasOpeningTimes' => false,
                'hasAppointmentTimes' => false,
                'hasConflict'=>false
            ),
        );
        \BO\Slim\Render::html('page/availabilityMonth.twig', array(
            'title' => 'Öffnungszeiten',
            'data_days' => $data,
            'menuActive' => 'availability'
        ));
    }
}
