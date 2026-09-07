<?php

/**
 * @package Zmsdldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsdldb\Collection;

use BO\Zmsdldb\Entity\SearchResult as Entity;

/**
 * @extends Base<\BO\Zmsdldb\Entity\SearchResult>
 */
class SearchResults extends Base
{
    /**
     * @psalm-api
     */
    public function getNames(): array
    {
        $nameList = array();
        foreach ($this as $item) {
            $nameList[$item->getId()] = $item->getName();
        }
        return $nameList;
    }

    /**
     * @psalm-api
     */
    public function toSearchResultData(): self
    {
        $list = new self();
        foreach ($this as $results) {
            foreach ($results as $data) {
                if (count($data)) {
                    $item = Entity::create($data);
                    $list->append($item);
                }
            }
            $list;
        }
        return $list;
    }

    /**
     * @psalm-api
     *
     * @return null|static
     */
    public function addSearchResultsData(mixed $data): static|null
    {
        if ($data) {
            $this->append($data);
            return $this;
        }
        return null;
    }

    /**
     * @psalm-api
     */
    public function sortByType(array $order): self
    {
        $list = new self();
        foreach ($order as $type) {
            foreach ($this as $item) {
                if ($item->getType() == $type) {
                    $list->append($item);
                }
            }
        }
        return $list;
    }
}
