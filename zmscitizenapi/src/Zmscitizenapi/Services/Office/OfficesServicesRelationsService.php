<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Office;

use BO\Zmscitizenapi\Models\Collections\OfficeServiceAndRelationList;
use BO\Zmscitizenapi\Services\Core\LoggerService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;

class OfficesServicesRelationsService
{
    public function getServicesAndOfficesList(bool $showUnpublished = false): OfficeServiceAndRelationList|array
    {
        return $this->getServicesAndOffices($showUnpublished);
    }

    private function getServicesAndOffices(bool $showUnpublished = false): array|OfficeServiceAndRelationList
    {
        // Include showUnpublished in the cache key to differentiate between published/unpublished results
        $cacheKey = 'processed_offices_and_services_' . ($showUnpublished ? 'with_unpublished' : 'published_only');
        
        // Try to get the processed data from cache first
        if (\App::$cache && ($processedData = \App::$cache->get($cacheKey))) {
            return $processedData;
        }
        
        // If not in cache, get the transformed data (which internally uses the source_dldb cache)
        $result = ZmsApiFacadeService::getServicesAndOffices($showUnpublished);
        
        // Cache the processed result with the same TTL as source cache
        if (\App::$cache && !is_array($result)) {
            \App::$cache->set($cacheKey, $result, \App::$SOURCE_CACHE_TTL);
            LoggerService::logInfo('Cache set for processed data', [
                'key' => $cacheKey,
                'ttl' => \App::$SOURCE_CACHE_TTL,
                'show_unpublished' => $showUnpublished
            ]);
        }
        
        return $result;
    }
}