<?php

namespace BO\Zmsdldb\Importer\SQLite;

use BO\Zmsdldb\Importer\MySQL\Authorities as AuthoritiesBase;

class Authorities extends AuthoritiesBase
{
    protected $entityClass = 'BO\\Zmsdldb\\Importer\\SQLite\\Entity\\Authority';
}
