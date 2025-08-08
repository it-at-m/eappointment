<?php

namespace BO\Zmsdldb\Importer\SQLite;

use BO\Zmsdldb\Importer\MySQL\Services as ServicesBase;

class Services extends ServicesBase
{
    protected $entityClass = 'BO\\Zmsdldb\\Importer\\SQLite\\Entity\\Service';
}
