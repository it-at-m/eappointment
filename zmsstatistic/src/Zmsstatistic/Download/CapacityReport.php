<?php

/**
 * @package zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic\Download;

use BO\Zmsstatistic\Helper\Download;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CapacityReport extends Base
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
        $title = !empty($args['downloadTitle'])
            ? (string) $args['downloadTitle']
            : 'terminkapazitaet';
        $download = (new Download($request))->setSpreadSheet($title);
        $spreadsheet = $download->getSpreadSheet();

        $this->writeInfoHeader($args, $spreadsheet);
        $this->writeFilteredExchangeReport(
            $args['report'],
            $spreadsheet,
            [],
            2
        );

        return $download->writeDownload($response);
    }
}
