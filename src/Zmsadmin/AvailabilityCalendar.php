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
class AvailabilityCalendar extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $data = array(
            '2015-12-1'  => array('closed' => false, 'holiday' => false, 'hasOpeningTimes' => true, 'hasAppointmentTimes' => true, 'hasConflict'=>false),
            '2015-12-2'  => array('closed' => false, 'holiday' => false, 'hasOpeningTimes' => true, 'hasAppointmentTimes' => true, 'hasConflict'=>false),
            '2015-12-3'  => array('closed' => false, 'holiday' => false, 'hasOpeningTimes' => true, 'hasAppointmentTimes' => true, 'hasConflict'=>false),
            '2015-12-4'  => array('closed' => false, 'holiday' => false, 'hasOpeningTimes' => false, 'hasAppointmentTimes' => true, 'hasConflict'=>false),
            '2015-12-5'  => array('closed' => true, 'holiday' => false, 'hasOpeningTimes' => false, 'hasAppointmentTimes' => false, 'hasConflict'=>false),
            '2015-12-6'  => array('closed' => true, 'holiday' => false, 'hasOpeningTimes' => false, 'hasAppointmentTimes' => false, 'hasConflict'=>false),
            '2015-12-7'  => array('closed' => false, 'holiday' => false, 'hasOpeningTimes' => true, 'hasAppointmentTimes' => false, 'hasConflict'=>false),
            '2015-12-8'  => array('closed' => false, 'holiday' => false, 'hasOpeningTimes' => true, 'hasAppointmentTimes' => true, 'hasConflict'=>false),
            '2015-12-9'  => array('closed' => false, 'holiday' => false, 'hasOpeningTimes' => true, 'hasAppointmentTimes' => true, 'hasConflict'=>true),
            '2015-12-10' => array('closed' => false, 'holiday' => false, 'hasOpeningTimes' => true, 'hasAppointmentTimes' => true, 'hasConflict'=>false),
            '2015-12-11' => array('closed' => false, 'holiday' => false, 'hasOpeningTimes' => false, 'hasAppointmentTimes' => true, 'hasConflict'=>false),
            '2015-12-12' => array('closed' => true, 'holiday' => false, 'hasOpeningTimes' => false, 'hasAppointmentTimes' => false, 'hasConflict'=>false),
            '2015-12-13' => array('closed' => true, 'holiday' => false, 'hasOpeningTimes' => false, 'hasAppointmentTimes' => false, 'hasConflict'=>false),
            '2015-12-14' => array('closed' => false, 'holiday' => false, 'hasOpeningTimes' => true, 'hasAppointmentTimes' => false, 'hasConflict'=>false),
            '2015-12-15' => array('closed' => false, 'holiday' => false, 'hasOpeningTimes' => true, 'hasAppointmentTimes' => true, 'hasConflict'=>false),
            '2015-12-16' => array('closed' => false, 'holiday' => false, 'hasOpeningTimes' => true, 'hasAppointmentTimes' => true, 'hasConflict'=>false),
            '2015-12-17' => array('closed' => false, 'holiday' => false, 'hasOpeningTimes' => true, 'hasAppointmentTimes' => true, 'hasConflict'=>false),
            '2015-12-18' => array('closed' => false, 'holiday' => false, 'hasOpeningTimes' => true, 'hasAppointmentTimes' => true, 'hasConflict'=>false),
            '2015-12-19' => array('closed' => true, 'holiday' => false, 'hasOpeningTimes' => false, 'hasAppointmentTimes' => false, 'hasConflict'=>true),
            '2015-12-20' => array('closed' => true, 'holiday' => false, 'hasOpeningTimes' => false, 'hasAppointmentTimes' => false, 'hasConflict'=>false),
            '2015-12-21' => array('closed' => false, 'holiday' => false, 'hasOpeningTimes' => true, 'hasAppointmentTimes' => false, 'hasConflict'=>false),
            '2015-12-22' => array('closed' => false, 'holiday' => false, 'hasOpeningTimes' => true, 'hasAppointmentTimes' => true, 'hasConflict'=>false),
            '2015-12-23' => array('closed' => false, 'holiday' => false, 'hasOpeningTimes' => true, 'hasAppointmentTimes' => true, 'hasConflict'=>false),
            '2015-12-24' => array('closed' => true, 'holiday' => false, 'hasOpeningTimes' => false, 'hasAppointmentTimes' => false, 'hasConflict'=>false),
            '2015-12-25' => array('closed' => true, 'holiday' => true, 'hasOpeningTimes' => false, 'hasAppointmentTimes' => false, 'hasConflict'=>false),
            '2015-12-26' => array('closed' => true, 'holiday' => true, 'hasOpeningTimes' => false, 'hasAppointmentTimes' => false, 'hasConflict'=>false),
            '2015-12-27' => array('closed' => true, 'holiday' => false, 'hasOpeningTimes' => false, 'hasAppointmentTimes' => false, 'hasConflict'=>false),
            '2015-12-28' => array('closed' => false, 'holiday' => false, 'hasOpeningTimes' => false, 'hasAppointmentTimes' => true, 'hasConflict'=>false),
            '2015-12-29' => array('closed' => false, 'holiday' => false, 'hasOpeningTimes' => false, 'hasAppointmentTimes' => true, 'hasConflict'=>false),
            '2015-12-30' => array('closed' => false, 'holiday' => false, 'hasOpeningTimes' => false, 'hasAppointmentTimes' => true, 'hasConflict'=>false),
            '2015-12-31' => array('closed' => true, 'holiday' => false, 'hasOpeningTimes' => false, 'hasAppointmentTimes' => false, 'hasConflict'=>false),
        );
        \BO\Slim\Render::html('page/availability-calendar.twig', array(
            'title' => 'Öffnungszeiten',
            'data_days' => $data,
            'menuActive' => 'availabilityCalendar'
        ));
    }
}
