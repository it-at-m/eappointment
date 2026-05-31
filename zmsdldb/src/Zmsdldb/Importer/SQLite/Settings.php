<?php

namespace BO\Zmsdldb\Importer\SQLite;

use BO\Zmsdldb\Importer\MySQL\Settings as SettingsBase;

class Settings extends SettingsBase
{
    protected string $entityClass = 'BO\\Zmsdldb\\Importer\\SQLite\\Entity\\Setting';
}
