<?php

namespace BO\Zmsapi\Cli;

use Ulrichsg\Getopt\Getopt;
use Ulrichsg\Getopt\Option;

class Status extends Base
{

    /**
     * @SuppressWarnings(Parameter)
     *
     */
    public function cli(array $argv, \League\CLImate\CLImate $climate)
    {
        $status = (new \BO\Zmsdb\Status())->readEntity();
        $climate->json($status);
    }
}
