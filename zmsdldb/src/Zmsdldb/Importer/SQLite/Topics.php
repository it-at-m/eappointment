<?php

namespace BO\Zmsdldb\Importer\SQLite;

use BO\Zmsdldb\Importer\MySQL\Topics as TopicsBase;

/** @psalm-api */
class Topics extends TopicsBase
{
    /** @var class-string<\BO\Zmsdldb\Importer\MySQL\Entity\Base>|null */
    protected ?string $entityClass = \BO\Zmsdldb\Importer\SQLite\Entity\Topic::class;
}
