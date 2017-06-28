<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \XLSXWriter;

class PickupSpreadSheet extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $processList = \App::$http->readGetResult('/workstation/process/pickup/')->getCollection();
        $department = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/department/')->getEntity();

        $providerName = $workstation->scope['provider']['name'];
        $clusterInfo = '';
        $cluster = (new Helper\ClusterHelper($workstation))->getEntity();
        if ($cluster) {
            $providerName = $cluster->name;
            $clusterInfo = 'Cluster: ';
        }

        $xlsSheetTitle = 'abholer_'. str_replace(' ', '_', $providerName);

        $xlsHeaders = [
            '',
            'Datum',
            'Nr.',
            'Name',
            'Telefonnr.',
            'eMail',
            'Dienstleistung',
            'Anmerkung'
        ];
        $writer = new XLSXWriter();

        $writer->writeSheetRow($xlsSheetTitle, [
            'Abholer',
            '','','','','','','',''
        ]);
        $writer->writeSheetRow($xlsSheetTitle, [
            $department->name .' - '. $clusterInfo.$providerName,
            '','','','','','','',''
        ]);
        $writer->writeSheetRow($xlsSheetTitle, $xlsHeaders);

        $rowCount = 1;
        foreach ($processList->getArrayCopy() as $processItem) {
            $client = $processItem->getFirstClient();
            $request = $processItem->getRequests()->getFirst();

            $date = new \DateTime('@' . $processItem->queue['arrivalTime']);
            $date->setTimezone(\App::$now->getTimezone());

            $row = [
                $rowCount,
                $date->format('Y-m-d'),
                $processItem->queue['number'],
                $client['familyName'],
                $client['telephone'],
                $client['email'],
                $request['name'],
                $processItem->amendment
            ];
            $rowCount ++;
            $writer->writeSheetRow($xlsSheetTitle, $row);
        }

        $response->getBody()->write($writer->writeToString());

        return $response
            ->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->withHeader(
                'Content-Disposition',
                sprintf('download; filename="%s.xlsx"', $xlsSheetTitle)
            );
    }
}
