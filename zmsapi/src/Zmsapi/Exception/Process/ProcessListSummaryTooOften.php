<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsapi\Exception\Process;

class ProcessListSummaryTooOften extends \Exception
{
    protected $code = 429;

    protected $message = 'The last mailing of the personal schedule was made recently. Please wait.';
}
