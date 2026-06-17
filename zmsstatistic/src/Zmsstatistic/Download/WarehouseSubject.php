<?php

/**
 * @package zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic\Download;

use BO\Zmsstatistic\Helper\Download;

class WarehouseSubject extends Base
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ): \Psr\Http\Message\ResponseInterface {
        $title = 'raw_statistic_' . $args['subject'];
        $download = (new Download($request))->setSpreadSheet($title);

        $this->writeRawReport($args['reports'][0], $download->getSpreadSheet());

        return $download->writeDownload($response);
    }
}
