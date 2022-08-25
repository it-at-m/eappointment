<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsapi\Exception\Process;

class ProcessListSummaryTooOften extends \Exception
{
    protected $code = 429;

    protected $message = 'Der letzte Versand der persönlichen Terminübersicht erfolgte vor kurzem. Bitte warten Sie.';
}
