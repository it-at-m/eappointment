<?php

namespace BO\Zmsentities\Useraccount;

interface AccessInterface
{
    public function hasAccess(\BO\Zmsentities\Useraccount $useraccount);
}
