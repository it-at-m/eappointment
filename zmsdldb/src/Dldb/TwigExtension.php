<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb;

use Error;

/**
 * Extension for Twig
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class TwigExtension extends \Twig\Extension\AbstractExtension
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
            new \Twig\TwigFunction('convertOpeningTimes', array($this, 'convertOpeningTimes')),
            new \Twig\TwigFunction('csvProperty', array($this, 'csvProperty')),
            new \Twig\TwigFunction('csvAppointmentLocations', array($this, 'csvAppointmentLocations')),
            new \Twig\TwigFunction('getAppointmentForService', array($this, 'getAppointmentForService')),
            new \Twig\TwigFunction('getLocationHintByServiceId', array($this, 'getLocationHintByServiceId')),
            new \Twig\TwigFunction('isAppointmentBookable', array($this, 'isAppointmentBookable')),
            new \Twig\TwigFunction('kindOfPayment', array($this, 'kindOfPayment')),
            new \Twig\TwigFunction('formatDateTime', array($this, 'formatDateTime')),
            new \Twig\TwigFunction('dateToTS', array($this, 'dateToTS')),
            new \Twig\TwigFunction('tsToDate', array($this, 'tsToDate')),
            new \Twig\TwigFunction('dayIsBookable', array($this, 'dayIsBookable')),
            new \Twig\TwigFunction('azPrefixList', array($this, 'azPrefixList')),
            new \Twig\TwigFunction('formatPhoneNumber', array($this, 'formatPhoneNumber')),
            new \Twig\TwigFunction('getOSMOptions', array($this, 'getOSMOptions')),
            new \Twig\TwigFunction('getOSMAccessToken', array($this, 'getOSMAccessToken')),
            new \Twig\TwigFunction('getD115Enabeld', array($this, 'getD115Enabeld')),
            new \Twig\TwigFunction('getD115OpeningTimes', array($this, 'getD115OpeningTimes')),
            new \Twig\TwigFunction('getD115Text', array($this, 'getD115Text')),
            new \Twig\TwigFunction('getBobbiChatButtonEnabeld', array($this, 'getBobbiChatButtonEnabeld')),
            new \Twig\TwigFunction('currentRoute', array($this, 'currentRoute')),
            new \Twig\TwigFunction('dump', array($this, 'dump')),
            new \Twig\TwigFunction(
                'getAppointmentForLocationFromServiceAppointmentLocations',
                array($this, 'getAppointmentForLocationFromServiceAppointmentLocations')
            ),
        );
    }

    public function dump($item)
    {
        return '<pre>' . print_r($item, 1) . '</pre>';
    }

    public function currentRoute($lang = null)
    {
        if ($this->container->has('currentRoute')) {
            $routeParams = $this->container->get('currentRouteParams');

            if (null !== $lang && 'de' == $lang) {
                unset($routeParams['lang']);
            } else {
                $routeParams['lang'] = ($lang !== null) ? $lang : \App::$language->getCurrentLanguage();
            }


            $routeName = $this->container->get('currentRoute');
            $route = array(
                'name' => $routeName,
                'params' => $routeParams
            );
        } else {
            $route = array(
                'name' => 'noroute',
                'params' => []
            );
        }
        return $route;
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

    public function getOSMAccessToken()
    {
        return \APP::OSM_ACCESS_TOKEN;
    }

    public function getOSMOptions()
    {
        return 'gestureHandling: ' . \APP::OSM_GESTURE_HANDLING;
    }

    public function formatPhoneNumber($phoneNumber)
    {
        preg_match_all('/(^\+)?[\d]+/', $phoneNumber, $matches);
        $number = implode($matches[0]);
        $phonenumber = preg_replace('/^030/', '+4930', $number);
        return $phonenumber;
    }

    public function convertOpeningTimes($name)
    {
        $days = array(
            'monday' => 'Montag',
            'tuesday' => 'Dienstag',
            'wednesday' => 'Mittwoch',
            'thursday' => 'Donnerstag',
            'friday' => 'Freitag',
            'saturday' => 'Samstag',
            'sunday' => 'Sonntag',
            'special' => ''
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

    public function formatDateTime($dateString)
    {
        $dateTime = new \DateTimeImmutable($dateString, new \DateTimezone('Europe/Berlin'));
        $formatDate['date']     = Helper\DateTime::getFormatedDates($dateTime, "EE, dd. MMMM yyyy");
        $formatDate['fulldate'] = Helper\DateTime::getFormatedDates($dateTime, "EEEE, 'den' dd. MMMM yyyy");
        $formatDate['weekday']  = ($dateTime->format('N') == 0) ?
            $dateTime->format('N') + 6 :
            $dateTime->format('N') - 1;
        $formatDate['weekdayfull'] = Helper\DateTime::getFormatedDates($dateTime, "EEEE");

        $time = $dateTime->format('H:i');
        $formatDate['time']     = ($time != '00:00') ?
            Helper\DateTime::getFormatedDates($dateTime, "HH:mm 'Uhr'") :
            false;
        $formatDate['timeId']   = ($time != '00:00') ? $time : false;
        $formatDate['ts']       = $dateTime->getTimestamp();
        $formatDate['dateId']   = $dateTime->format('Y-m-d');
        $formatDate['ym']      = $dateTime->format('Y-m');

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
            if (!is_scalar($item) && Entity\Base::hasValidOffset($item, $property)) {
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
            if (!is_scalar($item) && Entity\Base::hasValidOffset($item, $property)) {
                $propertylist[] = $item[$property];
            }
        }
        return implode(',', array_unique($propertylist));
    }

    public function csvAppointmentLocations($list, $service_id = '')
    {
        $propertylist = array();
        foreach ($list as $item) {
            if (!is_scalar($item) && Entity\Base::hasValidOffset($item, 'services')) {
                $appointment = $this->getAppointmentForService($item, $service_id);
                if ($appointment === false) {
                    continue;
                }
                if (false === $appointment['external'] && true === $appointment['allowed']) {
                    $propertylist[] = $item['id'];
                }
            }
        }
        return implode(',', array_unique($propertylist));
    }

    /**
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function getAppointmentForLocationFromServiceAppointmentLocations(
        array $serviceAppointmentLocationList,
        $locationId
    ) {
        if (isset($serviceAppointmentLocationList[$locationId])) {
            $appointment = $serviceAppointmentLocationList[$locationId]['appointment'];
            $appointment['responsibility_hint'] = $serviceAppointmentLocationList[$locationId]['responsibility_hint'];
            $appointment['responsibility'] = $serviceAppointmentLocationList[$locationId]['responsibility'];
            return $appointment;
        }
        return false;
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
        if (isset($location['services'])) {
            foreach ($location['services'] as $service) {
                if ($service['service'] == $service_id) {
                    return $service['hint'];
                }
            }
        }
        return false;
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
