<?php

namespace BO\Dldb\Importer\SQLite;

use BO\Dldb\Importer\MySQL\Services AS ServicesBase;

class Services extends ServicesBase
{
    protected $entityClass = 'BO\\Dldb\\Importer\\SQLite\\Entity\\Service';
}
