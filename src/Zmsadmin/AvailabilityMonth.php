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
class AvailabilityMonth extends BaseController
{

    /**
     * @SuppressWarnings(ExcessiveMethodLength)
     * @return String
     */
    public static function render()
    {
        $data = array(
            '2016-04-1'  => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-04-2'  => array(
                'closed' => true,
                'holiday' => false,
                'hasOpeningTimes' => false,
                'hasAppointmentTimes' => false,
                'hasConflict'=>false
            ),
            '2016-04-3'  => array(
                'closed' => true,
                'holiday' => false,
                'hasOpeningTimes' => false,
                'hasAppointmentTimes' => false,
                'hasConflict'=>false
            ),
            '2016-04-4'  => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => false,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-04-5'  => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-04-6'  => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-04-7'  => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => false,
                'hasConflict'=>false
            ),
            '2016-04-8'  => array(
                'closed' => true,
                'holiday' => true,
                'hasOpeningTimes' => false,
                'hasAppointmentTimes' => false,
                'hasConflict'=>false
            ),
            '2016-04-9'  => array(
                'closed' => true,
                'holiday' => false,
                'hasOpeningTimes' => false,
                'hasAppointmentTimes' => false,
                'hasConflict'=>false
            ),
            '2016-04-10' => array(
                'closed' => true,
                'holiday' => false,
                'hasOpeningTimes' => false,
                'hasAppointmentTimes' => false,
                'hasConflict'=>false
            ),
            '2016-04-11' => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => false,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-04-12' => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-04-13' => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-04-14' => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-04-15' => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-04-16' => array(
                'closed' => true,
                'holiday' => false,
                'hasOpeningTimes' => false,
                'hasAppointmentTimes' => false,
                'hasConflict'=>false
            ),
            '2016-04-17' => array(
                'closed' => true,
                'holiday' => false,
                'hasOpeningTimes' => false,
                'hasAppointmentTimes' => false,
                'hasConflict'=>false
            ),
            '2016-04-18' => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => false,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-04-19' => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-04-20' => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-04-21' => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-04-22' => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-04-23' => array(
                'closed' => true,
                'holiday' => false,
                'hasOpeningTimes' => false,
                'hasAppointmentTimes' => false,
                'hasConflict'=>false
            ),
            '2016-04-24' => array(
                'closed' => true,
                'holiday' => false,
                'hasOpeningTimes' => false,
                'hasAppointmentTimes' => false,
                'hasConflict'=>false
            ),
            '2016-04-25' => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => false,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-04-26' => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-04-27' => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-04-28' => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-04-29' => array(
                'closed' => false,
                'holiday' => false,
                'hasOpeningTimes' => true,
                'hasAppointmentTimes' => true,
                'hasConflict'=>false
            ),
            '2016-04-30' => array(
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
