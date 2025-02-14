<?php

/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Collection;

class Services extends Base
{
    public function containsLocation($locationCsv = null)
    {
        $list = new self();
        foreach ($this as $service) {
            if ($service->containsLocation($locationCsv)) {
                $list[] = $service;
            }
        }
        return $list;
    }

    public function getIds()
    {
        $idList = array();
        foreach ($this as $service) {
            $idList[] = $service['id'];
        }
        return $idList;
    }

    public function getNames()
    {
        $nameList = array();
        foreach ($this as $service) {
            $nameList[$service['id']] = $service['name'];
        }
        return $nameList;
    }

    public function getCSV()
    {
        return implode(',', $this->getIds());
    }

    public function isLocale($locale)
    {
        $list = new self();
        foreach ($this as $service) {
            if ($service->isLocale($locale)) {
                $list[] = $service;
            }
        }
        return (count($list)) ? $list : null;
    }
}
