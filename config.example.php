<?php

require(__DIR__ . '/vendor/autoload.php');

\BO\Zmsdb\Connection\Select::$enableProfiling = true;
\BO\Zmsdb\Connection\Select::$readSourceName = 'mysql:dbname=zmsbo;host=127.0.0.1';
\BO\Zmsdb\Connection\Select::$writeSourceName = 'mysql:dbname=zmsbo;host=127.0.0.1';
\BO\Zmsdb\Connection\Select::$username = 'server';
\BO\Zmsdb\Connection\Select::$password = 'internet';
\BO\Zmsdb\Connection\Select::$pdoOptions = [
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
];
