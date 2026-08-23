<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Office\Service;

use BO\Zmscitizenbackend\Office\Model\Collections\OfficeServiceAndRelationList;
use BO\Zmscitizenbackend\Office\Repository\OfficesServicesRelationsRepository;

class OfficesServicesRelationsService
{
    private const string CACHE_KEY_OFFICES_AND_SERVICES = 'processed_offices_and_services';

    public function getServicesAndOfficesList(bool $showUnpublished = false): OfficeServiceAndRelationList|array
    {
        $cacheKey = self::CACHE_KEY_OFFICES_AND_SERVICES . ($showUnpublished ? '_unpublished' : '');

        if (\App::$cache && ($cachedData = \App::$cache->get($cacheKey))) {
            return $cachedData;
        }

        $result = OfficesServicesRelationsRepository::create()->readOfficesAndServices($showUnpublished);
        if (\App::$cache) {
            \App::$cache->set($cacheKey, $result, \App::$SOURCE_CACHE_TTL);
        }

        return $result;
    }
}
