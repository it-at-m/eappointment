<?php
namespace BO\Zmsentities\Collection;

class UseraccountList extends Base
{
    public function withRights($requiredRights)
    {
        $collection = new static();
        foreach ($this as $useraccount) {
            if ($useraccount->hasRights($requiredRights)) {
                $collection[] = clone $useraccount;
            }
        }
        return $collection;
    }
}
