<?php
require(__DIR__ . '/../config.php');

\BO\Zmsdb\Helper\DldbData::$dataPath = __DIR__ . '/Zmsdb/fixtures';
\BO\Zmsdb\Helper\DldbData::getDataRepository();

setlocale(LC_ALL, 'de_DE.utf8', 'de_DE', 'de');
date_default_timezone_set('Europe/Berlin');
