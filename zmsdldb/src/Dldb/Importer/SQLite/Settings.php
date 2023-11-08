<?php

namespace BO\Dldb\Importer\SQLite;

use BO\Dldb\Importer\MySQL\Settings as SettingsBase;

class Settings extends SettingsBase
{
    protected $entityClass = 'BO\\Dldb\\Importer\\SQLite\\Entity\\Setting';
}
