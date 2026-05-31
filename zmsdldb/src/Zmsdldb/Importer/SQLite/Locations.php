<?php

namespace BO\Zmsdldb\Importer\SQLite;

use BO\Zmsdldb\Importer\MySQL\Locations as LocationsBase;

class Locations extends LocationsBase
{
    protected string $entityClass = 'BO\\Zmsdldb\\Importer\\SQLite\\Entity\\Location';
}
