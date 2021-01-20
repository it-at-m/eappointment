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
        $validator = $request->getAttribute('validator');
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $selectedScope = $validator->getParameter('selectedscope')->isNumber()->getValue();
        $scopeId = ($selectedScope) ? $selectedScope : $workstation->scope['id'];
        $scope = \App::$http->readGetResult('/scope/'. $scopeId .'/', ['resolveReferences' => 1])->getEntity();
        $processList = \App::$http
            ->readGetResult('/workstation/process/pickup/', ['resolveReferences' => 1, 'selectedScope' => $scopeId])
            ->getCollection();
        $processList = ($processList) ? $processList : new \BO\Zmsentities\Collection\ProcessList();
        $department = \App::$http->readGetResult('/scope/'. $scopeId .'/department/')->getEntity();

        $providerName = $scope['provider']['name'];

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
            $department->name .' - '. $providerName,
            '','','','','','','',''
        ]);
        $writer->writeSheetRow($xlsSheetTitle, $xlsHeaders);

        $rowCount = 1;
        foreach ($processList->getArrayCopy() as $processItem) {
            $client = $processItem->getFirstClient();
            $requestNameList = [];
            if (count($processItem->getRequests())) {
                foreach ($processItem->getRequests() as $requestItem) {
                    $requestNameList[] = $requestItem->getName();
                }
            }

            $date = new \DateTime('@' . $processItem->queue['arrivalTime']);
            $date->setTimezone(\App::$now->getTimezone());

            $row = [
                $rowCount,
                $date->format('d.m.Y'),
                $processItem->queue['number'],
                $client['familyName'],
                $client['telephone'],
                $client['email'],
                join(', ', $requestNameList),
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
