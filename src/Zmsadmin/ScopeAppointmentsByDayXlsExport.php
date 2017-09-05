<?php
/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin;

use BO\Zmsentities\Scope as Entity;
use BO\Mellon\Validator;

use Helper\AppointmentsByDayHelper;

use \XLSXWriter;

/**
 * Handle requests concerning services
 */
class ScopeAppointmentsByDayXlsExport extends BaseController
{

    /**
     *
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();

        $scopeId = $args['id'];
        $scope = \App::$http->readGetResult('/scope/' . $scopeId . '/')->getEntity();
        $selectedDate = $args['date'];

        $queueList = Helper\AppointmentsByDayHelper::getAppointmentsByDayForScope(
            $workstation,
            $scope,
            $selectedDate
        );

        $xlsSheetTitle = \DateTimeImmutable::createFromFormat('Y-m-d', $selectedDate)->format('d.m.Y');

        $xlsHeaders = [
            'Lfd. Nummer' => 'integer',
            'Uhrzeit' => 'string',
            'Nr.' => 'integer',
            'Name' => 'string',
            'Absagecode' => 'string',
            'Telefon' => 'string',
            'Email' => 'string',
            'Dienstleistung' => 'string',
            'Anmerkungen' => 'string'
        ];
        $writer = new XLSXWriter();

        $writer->writeSheetHeader($xlsSheetTitle, $xlsHeaders);

        foreach ($queueList->toProcessList()->getArrayCopy() as $key => $queueItem) {
            $client = count($queueItem->clients) > 0 ? $queueItem->clients[0] : [];
            $request = count($queueItem->requests) > 0 ? $queueItem->requests[0] : [];

            $date = new \DateTime('@' . $queueItem->queue['arrivalTime']);
            $date->setTimezone(\App::$now->getTimezone());

            $row = [
                $key + 1,
                $date->format('H:i:s'),
                $queueItem->queue['number'],
                $client['familyName'],
                $queueItem->authKey,
                $client['telephone'],
                $client['email'],
                (isset($request['name'])) ? $request['name'] : '',
                $queueItem->amendment
            ];

            $writer->writeSheetRow($xlsSheetTitle, $row);
        }

        $response->getBody()->write($writer->writeToString());

        return $response
            ->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->withHeader(
                'Content-Disposition',
                sprintf('download; filename="tagesuebersicht_%s.xlsx"', $xlsSheetTitle)
            );
    }
}
