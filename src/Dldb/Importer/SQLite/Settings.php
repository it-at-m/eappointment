<?php

namespace BO\Dldb\Importer\SQLite;

use BO\Dldb\Importer\MySQL\Settings AS SettingsBase;

class Settings extends SettingsBase
{
    protected $entityClass = 'BO\\Dldb\\Importer\\SQLite\\Entity\\Setting';
}
