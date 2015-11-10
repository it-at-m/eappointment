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
class SearchResult extends Base
{
    public static function create($item)
    {
        $type = explode('\\', get_class($item));
        $data = array(
            'id' => $item->getId(),
            'type' => end($type),
            'name' => $item->getName(),
            'path' => $item->getPath(),
            'locale' => $item->getLocale(),
            'link' => $item->getLink(),
        );
        return new self($data);
    }
}
