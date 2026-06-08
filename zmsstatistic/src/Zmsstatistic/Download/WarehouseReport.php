<?php

/**
 * @package zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic\Download;

use BO\Zmsstatistic\Helper\Download;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class WarehouseReport extends Base
{
    /**
     * @SuppressWarnings(Param)
     * @return ResponseInterface
     */
    #[\Override]
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $title = 'raw_statistic_' . $args['subject'] . '_' . $args['subjectid'] . '_' . $args['period'];
        $download = (new Download($request))->setSpreadSheet($title);

        $this->writeRawReport($args['report'], $download->getSpreadSheet());

        return $download->writeDownload($response);
    }
}
