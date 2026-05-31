<?php

namespace BO\Zmsdldb\Importer\SQLite;

use BO\Zmsdldb\Importer\MySQL\Topics as TopicsBase;

class Topics extends TopicsBase
{
    protected string $entityClass = 'BO\\Zmsdldb\\Importer\\SQLite\\Entity\\Topic';
}
