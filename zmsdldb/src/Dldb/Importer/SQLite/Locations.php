<?php

namespace BO\Dldb\Importer\SQLite;

use BO\Dldb\Importer\MySQL\Locations as LocationsBase;

class Locations extends LocationsBase
{
    protected $entityClass = 'BO\\Dldb\\Importer\\SQLite\\Entity\\Location';
}
