<?php

namespace BO\Dldb\Importer\SQLite;

use BO\Dldb\Importer\MySQL\Authorities as AuthoritiesBase;

class Authorities extends AuthoritiesBase
{
    protected $entityClass = 'BO\\Dldb\\Importer\\SQLite\\Entity\\Authority';
}
