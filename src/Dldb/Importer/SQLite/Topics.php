<?php

namespace BO\Dldb\Importer\SQLite;

use BO\Dldb\Importer\MySQL\Topics as TopicsBase;

class Topics extends TopicsBase
{
    protected $entityClass = 'BO\\Dldb\\Importer\\SQLite\\Entity\\Topic';
}
