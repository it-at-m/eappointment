<?php

/**
 * @package ClientDldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsdldb\MySQL;

use BO\Zmsdldb\MySQL\Collection\Authorities as Collection;
use BO\Zmsdldb\MySQL\Entity\Authority as Entity;
use BO\Zmsdldb\Elastic\Authority as Base;
use BO\Zmsdldb\Entity\Authority as AuthorityEntity;
use BO\Zmsdldb\Entity\Location as LocationEntity;

/** @psalm-api */
class Authority extends Base
{
    /**
     * fetch locations for a list of service and group by authority
     *
     * @return Collection
     */
    #[\Override]
    public function fetchList($servicelist = []): Collection
    {
        try {
            $authorityList = new Collection();

            $sqlArgs = [];

            if (!empty($servicelist)) {
                $sqlArgs = ['de',$this->locale];
                $questionMarks = array_fill(0, count($servicelist), '?');

                $sql = "SELECT a.data_json
                FROM authority_service AS aservice
                LEFT JOIN authority AS a ON a.id = aservice.authority_id AND a.locale = ?
                WHERE aservice.locale = ? AND aservice.service_id IN (" . implode(', ', $questionMarks) . ")";

                array_push($sqlArgs, ...$servicelist);

                $stm = $this->access()->prepare($sql);
                $stm->execute($sqlArgs);
                $stm->fetchAll(\PDO::FETCH_FUNC, function (?string $data_json) use ($authorityList): void {
                    $authority = new Entity();
                    $authority->offsetSet('data_json', $data_json);
                    $authorityList[$authority['id']] = $authority;
                    $authority->clearLocations();
                });

                $sqlArgs = [$this->locale];
                $questionMarks = array_fill(0, count($servicelist), '?');

                $sql = "SELECT ls.location_id AS id
                    FROM location_service ls
                    -- LEFT JOIN location AS l ON l.id = ls.location_id AND l.locale = ?
                    WHERE ls.locale = ? AND ls.service_id IN (" . implode(', ', $questionMarks) . ")
                    GROUP BY ls.location_id 
                    ";

                array_push($sqlArgs, ...$servicelist);

                $stm = $this->access()->prepare($sql);
                $stm->setFetchMode(\PDO::FETCH_OBJ);
                $stm->execute($sqlArgs);

                $locations = $stm->fetchAll();
                $locationsIds = [];

                foreach ($locations as $location) {
                    $locationsIds[] = $location->id;
                }

                $locations = $this->access()->fromLocation($this->locale)
                    ->fetchFromCsv(implode(',', $locationsIds), true);

                $this->addLocationsToAuthorities($authorityList, $locations);
            } else {
                $sqlArgs = ['de'];
                $sql = 'SELECT data_json FROM authority WHERE locale = ?';
                $stm = $this->access()->prepare($sql);
                $stm->execute($sqlArgs);
                $stm->fetchAll(\PDO::FETCH_FUNC, function (?string $data_json) use ($authorityList): void {
                    $authority = new Entity();
                    $authority->offsetSet('data_json', $data_json);
                    $authorityList[$authority['id']] = $authority;
                    $authority->clearLocations();
                });

                $locations = $this->access()->fromLocation($this->locale)->fetchList(false, true);

                $this->addLocationsToAuthorities($authorityList, $locations);
            }
            return $authorityList;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param Collection $authorityList
     * @param iterable<mixed> $locations
     */
    private function addLocationsToAuthorities(Collection $authorityList, iterable $locations): void
    {
        foreach ($locations as $location) {
            if (!$location instanceof LocationEntity) {
                continue;
            }
            $authority = $authorityList[$location['authority']['id']] ?? null;
            if ($authority instanceof AuthorityEntity) {
                $authority->addLocation($location);
            }
        }
    }

    /**
     * fetch a single authority by id
     *
     * @return Entity|false
     */
    #[\Override]
    public function fetchId(mixed $itemId): Entity|false
    {
        try {
            $sqlArgs = [$this->locale, $itemId];

            $sql = 'SELECT data_json FROM authority WHERE locale = ? AND id = ?';
            $stm = $this->access()->prepare($sql);
            $stm->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, Entity::class);
            $stm->execute($sqlArgs);
            if ($stm->rowCount() === 0) {
                return false;
            }
            $authority = $stm->fetch();
            return $authority instanceof Entity ? $authority : false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     *
     * @return Collection
     */
    #[\Override]
    public function readListByOfficePath($officepath): Collection
    {
        $authorityList = new Collection();

        $locations = $this->access()->fromLocation($this->locale)->fetchListByOffice($officepath);

        foreach ($locations as $location) {
            if ($location instanceof LocationEntity) {
                $authorityList->addLocation($location);
            }
        }

        return $authorityList;
    }
}
