<?php

namespace BO\Zmsdldb\Importer\SQLite;

use BO\Zmsdldb\Importer\MySQL\Authorities as AuthoritiesBase;

/** @psalm-api */
class Authorities extends AuthoritiesBase
{
    /** @var class-string<\BO\Zmsdldb\Importer\MySQL\Entity\Base>|null */
    protected ?string $entityClass = \BO\Zmsdldb\Importer\SQLite\Entity\Authority::class;
}
