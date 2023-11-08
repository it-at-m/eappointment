<?php

namespace BO\Zmsapi\Cli;

use Ulrichsg\Getopt\Getopt;
use Ulrichsg\Getopt\Option;

class Info extends Base
{

    /**
     * @SuppressWarnings(Parameter)
     * @codeCoverageIgnore
     *
     */
    public function cli(array $argv, \League\CLImate\CLImate $climate)
    {
        $version = \BO\Zmsapi\Helper\Version::getString();
        $climate->out("API Version $version");
    }
}
