<?php

namespace BO\Zmsapi\Cli;

use Ulrichsg\Getopt\Getopt;
use Ulrichsg\Getopt\Option;

class Info extends Base
{

    /**
     * @SuppressWarnings(Parameter)
     *
     */
    public function cli(array $argv, \League\CLImate\CLImate $climate)
    {
        $version = \App::VERSION_MAJOR . '.' . \App::VERSION_MINOR . '.' . \App::VERSION_PATCH;
        $climate->out("API Version $version");
    }
}
