<?php
// @codingStandardsIgnoreFile

// initialize the static \App singleton
include(realpath(__DIR__) .'/../bootstrap.php');
\App::$messaging = new \BO\Zmsmessaging\SendQueue();

if (preg_grep('#--?v(erbose)?#', $argv))
{
    echo "Teste Versand\n";
    if (true === \App::$messaging->testMail()) {
        echo "Test Message erfolgreiche versandt.\n";
    } else {
        echo "Konnte Test Message nicht erfolgreich versenden.\n";
        exit(1);
    }
}
else if (preg_grep('#--?s(end)?#', $argv))
{
    echo "Verschicke Messages\n";
    if (true === \App::$messaging->startMailTransmission()) {
        echo "Messages erfolgreiche versandt.\n";
    } else {
        echo "Konnte Messages nicht erfolgreich versenden.\n";
        exit(1);
    }
}



