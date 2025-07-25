<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsadmin;

use League\Csv\Writer;
use League\Csv\Reader;
use League\Csv\EscapeFormula;

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
        $workstation = \App::$http->readGetResult('/workstation/', [
            'resolveReferences' => 1,
            'gql' => Helper\GraphDefaults::getWorkstation()
        ])->getEntity();
        $workstationRequest = new \BO\Zmsclient\WorkstationRequests(\App::$http, $workstation);
        $selectedDateTime = ScopeAppointmentsByDay::readSelectedDateTime($args['date']);
        $scope = ScopeAppointmentsByDay::readSelectedScope($workstation, $workstationRequest, $args['id']);
        $processList = ScopeAppointmentsByDay::readProcessList($workstationRequest, $selectedDateTime);

        $xlsSheetTitle = $selectedDateTime->format('d.m.Y');
        $clusterColumn = $workstation->isClusterEnabled() ? 'Kürzel' : 'Lfd. Nummer';
        $xlsHeaders = [
            $clusterColumn,
            'Uhrzeit/Ankunftszeit',
            'Nr.',
            'Name',
            'Telefon',
            'Email',
            'Dienstleistung',
            'Anmerkungen',
            'Freitextfeld',
            'Freitextfeld2'
        ];

        $rows = [];
        $key = 1;
        foreach ($processList as $queueItem) {
            $client = $queueItem->getFirstClient();
            $request = count($queueItem->requests) > 0 ? $queueItem->requests[0] : [];
            $rows[] = [
                $workstation->isClusterEnabled() ? $queueItem->getCurrentScope()->shortName : $key++ ,
                $queueItem->getArrivalTime()->setTimezone(\App::$now->getTimezone())->format('H:i:s'),
                $queueItem->queue['number'],
                $client['familyName'],
                $client['telephone'],
                $client['email'],
                $queueItem->requests->getCsvForProperty('name'),
                $queueItem->amendment,
                $queueItem->customTextfield,
                $queueItem->customTextfield2,
            ];
        }
        $writer = Writer::createFromString();
        $writer->setDelimiter(';');
        $escapeFormula = new EscapeFormula();
        $writer->addFormatter([$escapeFormula, 'escapeRecord']);
        $writer->insertOne($xlsHeaders);
        $writer->setOutputBOM(\League\Csv\Bom::Utf8);
        $writer->insertAll($rows);

        $response->getBody()->write($writer->toString());
        $fileName = sprintf("Tagesübersicht_%s_%s.csv", $scope->contact['name'], $xlsSheetTitle);
        return $response
            ->withHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->withHeader('Content-Description', 'File Transfer')
            ->withHeader(
                'Content-Disposition',
                sprintf('download; filename="' . $this->convertspecialChars($fileName) . '"')
            );
    }

    protected function convertspecialchars($string)
    {

        $convert = array (
            array ('ä','ae',),
            array ('ö','oe',),
            array ('ü','ue',),
            array ('ß','ss',),
        );


        foreach ($convert as $array) {
            $string = str_replace($array[0], $array[1], $string);
        }
        return $string;
    }
}
