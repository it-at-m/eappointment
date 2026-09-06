<?php

namespace BO\Zmsentities\Useraccount;

interface AccessInterface
{
    public function hasAccess(\BO\Zmsentities\Useraccount $useraccount): bool;

    public function getEntityName(): string;

    public function getId(): mixed;
}
