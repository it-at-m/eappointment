<?php
// @codingStandardsIgnoreFile

// initialize the static \App singleton
include(realpath(__DIR__) .'/../bootstrap.php');

if (preg_grep('#--?v(erbose)?#', $argv))
{
    echo "Teste Versand\n";
    if (true === \App::$messaging->test()) {
        echo "Test Message erfolgreiche versandt.\n";
    } else {
        echo "Konnte Test Message nicht erfolgreich versenden.\n";
        exit(1);
    }
}
else
{
    echo "Verschicke Messages\n";
    if (true === \App::$messaging->init()) {
        echo "Messages erfolgreiche versandt.\n";
    } else {
        echo "Konnte Messages nicht erfolgreich versenden.\n";
        exit(1);
    }
}



