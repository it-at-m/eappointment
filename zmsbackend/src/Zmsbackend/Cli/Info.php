<?php

namespace BO\Zmsbackend\Cli;

use Ulrichsg\Getopt\Getopt;
use Ulrichsg\Getopt\Option;

class Info extends \BO\Zmsbackend\Base
{
    /**
     * @SuppressWarnings(Parameter)
     * @codeCoverageIgnore
     *
     */
    public function cli(array $argv, \League\CLImate\CLImate $climate)
    {
        $version = \BO\Zmsbackend\Helper\Version::getString();
        $climate->out("API Version $version");
    }
}
