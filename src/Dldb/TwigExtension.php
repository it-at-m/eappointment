<?php
/**
 * @package   BO Slim
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb;

/**
  * Extension for Twig and Slim
  *
  */
class TwigExtension extends \Slim\Views\TwigExtension
{
    public function getName()
    {
        return 'dldb';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('convertOpeningTimes', array($this, 'convertOpeningTimes')),
            new \Twig_SimpleFunction('csvAppointmentLocations', array($this, 'csvAppointmentLocations')),
            new \Twig_SimpleFunction('getAppointmentForService', array($this, 'getAppointmentForService')),
            new \Twig_SimpleFunction('isAppointmentBookable', array($this, 'isAppointmentBookable')),
            new \Twig_SimpleFunction('kindOfPayment', array($this, 'kindOfPayment')),
            new \Twig_SimpleFunction('formatDateTime', array($this, 'formatDateTime')),
            new \Twig_SimpleFunction('dateToTS', array($this, 'dateToTS')),
            new \Twig_SimpleFunction('tsToDate', array($this, 'tsToDate')),
            new \Twig_SimpleFunction('dayIsBookable', array($this, 'dayIsBookable')),
        );
    }

    public function convertOpeningTimes($name)
    {
        $days = array(
            'monday'=>'Montag',
            'tuesday'=>'Dienstag',
            'wednesday'=>'Mittwoch',
            'thursday'=>'Donnerstag',
            'friday'=>'Freitag',
            'saturday'=>'Samstag',
            'sunday'=>'Sonntag',
            'special'=>''
        );
        return $days[$name];
    }
    
    public function dateToTS($datetime)
    {
        $timestamp = ($datetime === (int)$datetime) ? $datetime : strtotime($datetime);
        return $timestamp;
    }
    
    public function tsToDate($timestamp)
    {
        $date =  date('Y-m-d', $timestamp);
        return $date;
    }
    
    public function formatDateTime($datetime)
    {
        $formatDate['date']     = strftime('%a, %d. %B %Y', $datetime);
        $formatDate['fulldate'] = strftime('%A, den %d. %B %Y', $datetime);
        $formatDate['weekday']  = (date('w', $datetime) == 0) ? date('w', $datetime) + 6 : date('w', $datetime) - 1;
        $formatDate['time']     = (date('H:i', $datetime) != '00:00') ? strftime('%H:%M Uhr', $datetime): false;
        $formatDate['dateId']   = date('Y-m-d', $datetime);
        $formatDate['ym']      = date('Y-m', $datetime);
        $formatDate['timeId']   = (date('H:i', $datetime) != '00:00') ? date('H:i', $datetime) : false;
        return $formatDate;
    }

    public function kindOfPayment($code)
    {
        $result = '';
        if ($code == 0) {
            $result = 'eccash';
        } elseif ($code == 1) {
            $result = 'nocash';
        } elseif ($code == 2) {
            $result = 'ec';
        } elseif ($code == 3) {
            $result = 'cash';
        } elseif ($code == 4) {
            $result = 'subscribecash';
        }
        return $result;
    }

    public function csvAppointmentLocations($list, $service_id = '')
    {
        $propertylist = array();
        foreach ($list as $item) {
            if (!is_scalar($item) && array_key_exists('services', $item)) {
                $appointment = self::getAppointmentForService($item, $service_id);
                if (false === $appointment['external'] && true === $appointment['allowed']) {
                    $propertylist[] = $item['id'];
                }
            }
        }
        return implode(',', array_unique($propertylist));
    }

    public function getAppointmentForService($location, $service_id)
    {
        $servicecompare = explode(',', $service_id);
        $appointment = array(
            'allowed' => false,
            'external' => true,
            'link' => '#',
        );
        foreach ($location['services'] as $service) {
            if (in_array($service['service'], $servicecompare)) {
                $appointment = $service['appointment'];
                if ($appointment['allowed'] === false || $appointment['external'] === true) {
                    return $appointment;
                }
            }
        }
        return $appointment;
    }

    public function dayIsBookable($dateList, $day)
    {
        $result = false;
        if (count($dateList)) {
            foreach ($dateList as $date) {
                if ($date['datum'] == $day) {
                    $result = $date;
                }
            }
        }
        return $result;
    }
}
