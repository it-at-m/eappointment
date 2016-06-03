<?php

require(__DIR__ . '/../config.php');

$defaults = new \BO\Zmsentities\Config();
$config = (new \BO\Zmsdb\Config())->updateEntity($defaults);
