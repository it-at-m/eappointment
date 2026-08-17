<?php

namespace BO\Zmsbackend\Cli;

class Info extends \BO\Zmsbackend\Base
{
    /**
     * @SuppressWarnings(Parameter)
     *
     * @codeCoverageIgnore
     */
    public function cli(array $argv, \League\CLImate\CLImate $climate): void
    {
        $version = \BO\Zmsbackend\Helper\Version::getString();
        $climate->out("API Version $version");
    }
}
