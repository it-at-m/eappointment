<?php

namespace BO\Dldb\Importer\SQLite;

use BO\Dldb\Importer\MySQL\Services as ServicesBase;

class Services extends ServicesBase
{
    protected $entityClass = 'BO\\Dldb\\Importer\\SQLite\\Entity\\Service';
}
