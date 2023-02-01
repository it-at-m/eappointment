<?php
/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Entity;

/**
  * Helper for service export
  *
  */
class Location extends Base
{

    /**
     * @return Bool
     */
    public function containsService($service_csv)
    {
        $location = $this->getArrayCopy();
        $servicecompare = explode(',', $service_csv);
        $servicecount = array();
        foreach ($location['services'] as $serviceinfo) {
            $service_id = $serviceinfo['service'];
            if (in_array($service_id, $servicecompare)) {
                $servicecount[$service_id] = $service_id;
            }
        }
        return count($servicecount) == count($servicecompare);
    }
    
    /**
     * @return Bool
     */
    public function isLocale($locale)
    {
        $location = $this->getArrayCopy();
        return $location['meta']['locale'] == $locale;
    }

    /**
     * @var Int $service_id
     *
     * @return FALSE or Array
     */
    public function getServiceInfo($service_id)
    {
        foreach ($this['services'] as $service) {
            if ($service['service'] == $service_id) {
                return $service;
            }
        }
        return false;
    }

    /**
     * @param String $serviceCsv only check for this serviceCsv
     *
     * @return Array
     */
    public function getServiceInfoList($serviceCsv = null)
    {
        if (null === $serviceCsv) {
            return $this['services'];
        }
        $location = $this->getArrayCopy();
        $servicecompare = explode(',', $serviceCsv);
        
        $serviceList = array();
        foreach ($location['services'] as $serviceinfo) {
            $service_id = $serviceinfo['service'];
            if (in_array($service_id, $servicecompare)) {
                $serviceList[$service_id] = $serviceinfo;
            }
        }
        return $serviceList;
    }

    /**
     * Check if appointments are available
     *
     * @param String $serviceCsv only check for this serviceCsv
     * @param Bool $external allow external links, default false
     *
     * @return Bool
     */
    public function hasAppointments($serviceCsv = null, $external = false)
    {
        $serviceList = $this->getServiceInfoList($serviceCsv);
        $servicecount = array();
        foreach ($serviceList as $serviceinfo) {
            if (true === static::hasValidOffset($serviceinfo, 'appointment')
                && $serviceinfo['appointment']['allowed']
                && ($external || $serviceinfo['appointment']['external'] === false)
            ) {
                $service_id = $serviceinfo['service'];
                $servicecount[$service_id] = $service_id;
            }
        }
        if (null === $serviceCsv) {
            return count($servicecount) ? true : false;
        } else {
            return count($serviceList) > 0 && count($servicecount) == count($serviceList);
        }
    }
    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getAppointmentForService($service_id, $external = false)
    {
        $serviceList = $this->getServiceInfoList($service_id);
        
        if (!empty($serviceList)) {
            $service = end($serviceList);
            return $service['appointment'];
        }
        return false;
    }

    /**
     * return geoJson
     *
     *
     * @return string
     */
    public function getGeoJson()
    {
        return [
            'type' => 'Feature',
            'id' => $this['id'],
            'properties' => [
                'name' => $this['name'],
                'description' => '<div>'
                    . ($this['authority']['name'] ?? $this['authority_name'] ?? '')
                    . '</div><p>'
                    . $this['address']['street']
                    . ' '
                    . $this['address']['house_number']
                    . ', '
                    . $this['address']['postal_code']
                    . ' '
                    . $this['address']['city']
                    . '<br /><a href="'
                    . ($this['meta']['url'] ?? $this['url'])
                    . '" class="gmap-marker-link">Zum Standort</a>',
                'categoryIdentifier' => $this['category']['identifier'],
            ],
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [$this['geo']['lon'], $this['geo']['lat']],
            ]
        ];
    }
}
