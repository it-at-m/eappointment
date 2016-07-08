<?php
namespace BO\Zmsentities\Collection;

class RequestList extends Base
{
    public function getIds()
    {
        $list = array();
        foreach ($this as $entity) {
            $list[] = $entity['id'];
        }
        return $list;
    }
}
