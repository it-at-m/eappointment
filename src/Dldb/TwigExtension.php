<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb;

/**
  * Extension for Twig
  *
  */
class TwigExtension extends \Twig_Extension
{

    public function __construct($container = null)
    {
        $this->container = $container;
    }

    public function getName()
    {
        return 'dldb';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('convertOpeningTimes', array($this, 'convertOpeningTimes')),
            new \Twig_SimpleFunction('csvProperty', array($this, 'csvProperty')),
            new \Twig_SimpleFunction('csvAppointmentLocations', array($this, 'csvAppointmentLocations')),
            new \Twig_SimpleFunction('getAppointmentForService', array($this, 'getAppointmentForService')),
            new \Twig_SimpleFunction('getLocationHintByServiceId', array($this, 'getLocationHintByServiceId')),
            new \Twig_SimpleFunction('isAppointmentBookable', array($this, 'isAppointmentBookable')),
            new \Twig_SimpleFunction('kindOfPayment', array($this, 'kindOfPayment')),
            new \Twig_SimpleFunction('formatDateTime', array($this, 'formatDateTime')),
            new \Twig_SimpleFunction('dateToTS', array($this, 'dateToTS')),
            new \Twig_SimpleFunction('tsToDate', array($this, 'tsToDate')),
            new \Twig_SimpleFunction('dayIsBookable', array($this, 'dayIsBookable')),
            new \Twig_SimpleFunction('azPrefixList', array($this, 'azPrefixList')),
            new \Twig_SimpleFunction('formatPhoneNumber', array($this, 'formatPhoneNumber')),
            new \Twig_SimpleFunction('getOSMOptions', array($this, 'getOSMOptions')),
            new \Twig_SimpleFunction('getOSMAccessToken', array($this, 'getOSMAccessToken')),
            new \Twig_SimpleFunction('getD115Enabeld', array($this, 'getD115Enabeld')),
            new \Twig_SimpleFunction('getD115OpeningTimes', array($this, 'getD115OpeningTimes')),
            new \Twig_SimpleFunction('getD115Text', array($this, 'getD115Text')),
            new \Twig_SimpleFunction('getBobbiChatButtonEnabeld', array($this, 'getBobbiChatButtonEnabeld')),
        );
    }

    
    public function getD115Enabeld()
    {
        $settingsRepository = \App::$repository->fromSetting();
        $active = (bool)($settingsRepository->fetchName('d115.active') ?? true);

        return $active;
    }
    
    public function getD115OpeningTimes()
    {
        $settingsRepository = \App::$repository->fromSetting();
        $openinTimes = $settingsRepository->fetchName('d115.openingTime') ?? \APP::D115_DEFAULT_OPENINGTIME;

        return $openinTimes;
    }

    public function getD115Text()
    {
        $settingsRepository = \App::$repository->fromSetting();
        $text = $settingsRepository->fetchName('d115.messageHtml') ?? \APP::D115_DEFAULT_TEXT;

        return $text;
    }

    public function getBobbiChatButtonEnabeld()
    {
        $settingsRepository = \App::$repository->fromSetting();
        $buttonEnabled = (bool)($settingsRepository->fetchName('frontend.bobbi.chatbutton.enabled') ?? false);

        return $buttonEnabled;
    }

    public function getOSMAccessToken() {
        return \APP::OSM_ACCESS_TOKEN;
    }

    public function getOSMOptions() {
        return 'gestureHandling: ' . \APP::OSM_GESTURE_HANDLING;
    }

    public function formatPhoneNumber($phoneNumber) {
        preg_match_all('/(^\+)?[\d]+/', $phoneNumber, $matches);
        $number = implode($matches[0]);
        $phonenumber = preg_replace('/^030/', '+4930', $number);
        return $phonenumber;
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

    public function azPrefixList($list, $property)
    {
        $azList = array();
        foreach ((array)$list as $item) {
            if (!is_scalar($item) && array_key_exists($property, $item)) {
                $currentPrefix = self::sortFirstChar($item[$property]);
                if (!array_key_exists($currentPrefix, $azList)) {
                    $azList[$currentPrefix] = array(
                        'prefix' => $currentPrefix,
                        'sublist' => array(),
                    );
                }
                $azList[$currentPrefix]['sublist'][] = $item;
                uasort($azList[$currentPrefix]['sublist'], array($this,'sortByName'));
                ksort($azList);
            }
        }
        return $azList;
    }

    protected static function sortFirstChar($string)
    {
        $firstChar = mb_substr($string, 0, 1);
        $firstChar = mb_strtoupper($firstChar);
        $firstChar = strtr($firstChar, array('Ä' => 'A', 'Ö' => 'O', 'Ü' => 'U'));
        return $firstChar;
    }

    protected static function sortByName($left, $right)
    {
        return strcmp(
            self::toSortableString(strtolower($left['name'])),
            strtolower(self::toSortableString($right['name']))
        );
    }

    protected static function toSortableString($string)
    {
        $string = strtr($string, array(
            'Ä' => 'Ae',
            'Ö' => 'Oe',
            'Ü' => 'Ue',
            'ä' => 'ae',
            'ö' => 'oe',
            'ü' => 'ue',
            'ß' => 'ss',
            '€' => 'E',
        ));
        return $string;
    }

    public function csvProperty($list, $property)
    {
        $propertylist = array();
        foreach ($list as $item) {
            if (!is_scalar($item) && array_key_exists($property, $item)) {
                $propertylist[] = $item[$property];
            }
        }
        return implode(',', array_unique($propertylist));
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
        foreach ($location['services'] as $service) {
            if (in_array($service['service'], $servicecompare)) {
                $appointment = $service['appointment'];
                if ($appointment['allowed'] === true || $appointment['external'] === true) {
                    return $appointment;
                }
            }
        }
        return false;
    }

    public function getLocationHintByServiceId($location, $service_id)
    {
        $service = array_filter($location['services'], function ($item) use ($service_id) {
            return ($item['service'] == $service_id);
        });
        return array_values($service)[0]['hint'];
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
