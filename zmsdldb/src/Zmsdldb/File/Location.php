<?php

/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsdldb\File;

use BO\Zmsdldb\Entity\Location as Entity;
use BO\Zmsdldb\Collection\Locations as Collection;

/**
 * Common methods shared by access classes
 *
 * @extends Base<Collection, Entity>
 */
class Location extends Base
{
    /**
     * @return Collection
     */
    #[\Override]
    protected function parseData(mixed $data)
    {
        $itemList = new Collection();
        foreach ($data['data'] as $item) {
            $location = new Entity($item);
            if ($location->isLocale($this->locale)) {
                $itemList[$item['id']] = $location;
            }
        }
        return $itemList;
    }

    /**
     *
     * @return Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetchList(string|false $service_csv = false, bool $mixLanguages = false)
    {
        $locationlist = $this->getItemList();
        /** @psalm-suppress RiskyTruthyFalsyComparison */
        if ($service_csv) {
            $locationlist = new Collection(array_filter((array) $locationlist, function ($item) use ($service_csv) {
                $location = new Entity($item);
                return $location->containsService($service_csv);
            }));
        }
        return $locationlist;
    }

    /**
     *
     * @return Collection
     * @param bool $mixLanguages unused in file backend, kept for MySQL override compatibility
     * @psalm-api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetchFromCsv(mixed $location_csv, bool $mixLanguages = false)
    {
        $locationlist = new Collection();
        foreach (explode(',', $location_csv) as $location_id) {
            $location = $this->fetchId($location_id);
            if ($location && $location->isLocale($this->locale)) {
                $locationlist[$location_id] = $location;
            }
        }
        return $locationlist;
    }

    /**
     *
     * @return \BO\Zmsdldb\Collection\Authorities
     * @psalm-api
     */
    public function readSearchResultList(mixed $query, string $service_csv = '')
    {
        $locationlist = $this->fetchList($service_csv);
        $locationlist = new Collection(array_filter((array) $locationlist, function ($item) use ($query) {
            return false !== strpos($item['name'], $query);
        }));
        return $this->access()
            ->fromAuthority()
            ->fromLocationResults($locationlist);
    }

    /** @psalm-api */
    public function fetchListByOffice(mixed $office): mixed
    {
        return $this->access()->fromAuthority()
        ->readListByOfficePath($office)
        ->removeEmptyAuthorities()
        ->sortByName();
    }
}
