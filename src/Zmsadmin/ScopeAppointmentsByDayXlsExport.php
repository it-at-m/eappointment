<?php
/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin;

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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $workstationRequest = new \BO\Zmsclient\WorkstationRequests(\App::$http, $workstation);
        $selectedDateTime = ScopeAppointmentsByDay::readSelectedDateTime($args['date']);
        $scope = ScopeAppointmentsByDay::readSelectedScope($workstation, $workstationRequest, $args['id']);
        $processList = ScopeAppointmentsByDay::readProcessList($workstationRequest, $selectedDateTime);
        
        $xlsSheetTitle = $selectedDateTime->format('d.m.Y');
        $clusterColumn = $workstation->isClusterEnabled() ? 'Kürzel' : 'Lfd. Nummer';
        $xlsHeaders = [
            $clusterColumn => $workstation->isClusterEnabled() ? 'string' : 'integer',
            'Uhrzeit/Ankunftszeit' => 'string',
            'Nr.' => 'integer',
            'Name' => 'string',
            'Telefon' => 'string',
            'Email' => 'string',
            'Dienstleistung' => 'string',
            'Anmerkungen' => 'string'
        ];
        $writer = new XLSXWriter();
        $writer->writeSheetHeader($xlsSheetTitle, $xlsHeaders);

        $key = 1;
        foreach ($processList as $queueItem) {
            $client = $queueItem->getFirstClient();
            $request = count($queueItem->requests) > 0 ? $queueItem->requests[0] : [];
            $row = [
                $workstation->isClusterEnabled() ? $queueItem->getCurrentScope()->shortName : $key++ ,
                $queueItem->getArrivalTime()->setTimezone(\App::$now->getTimezone())->format('H:i:s'),
                $queueItem->queue['number'],
                $client['familyName'],
                $client['telephone'],
                $client['email'],
                $queueItem->requests->getCsvForProperty('name'),
                $queueItem->amendment
            ];

            $writer->writeSheetRow($xlsSheetTitle, $row);
        }

        $response->getBody()->write($writer->writeToString());
        $fileName = sprintf("Tagesübersicht_%s_%s.xlsx", $scope->contact['name'], $xlsSheetTitle);
        return $response
            ->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->withHeader(
                'Content-Disposition',
                sprintf('download; filename="'. $this->convertspecialChars($fileName) .'"')
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
