<?php

namespace BO\Dldb\Importer\SQLite;

use BO\Dldb\Importer\MySQL\Authorities AS AuthoritiesBase;

class Authorities extends AuthoritiesBase
{
    protected $entityClass = 'BO\\Dldb\\Importer\\SQLite\\Entity\\Authority';
}