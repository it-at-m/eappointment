<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \League\Csv\Writer;
use \League\Csv\EscapeFormula;

class PickupSpreadSheet extends BaseController
{
    public static $maxSqlLoops = 5;
    public static $maxLoopLimit = 1000;
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
        $scope = \App::$http->readGetResult('/scope/'. $scopeId .'/', [
            'resolveReferences' => 1
        ])->getEntity();
        $department = \App::$http->readGetResult('/scope/'. $scopeId .'/department/')->getEntity();

        $providerName = $scope['provider']['name'];

        $rows = [];
        $rowCount = 1;
        $loop = 0;
        do {
            $processList = PickupQueue::getProcessList($scopeId, static::$maxLoopLimit, $loop * static::$maxLoopLimit);
            $rows = $this->writeData($processList, $rowCount, $rows);
            $loop++;
            $rowCount = count($rows) + 1;
        } while (static::$maxSqlLoops > $loop);

        $writer = Writer::createFromString();
        $writer->addFormatter(new EscapeFormula());
        $writer->insertOne(['Abholer','','','','','','','','']);
        $writer->insertOne([$department->name .' - '. $providerName,'','','','','','','','']);
        $writer->insertOne(['','Datum','Nr.','Name','Telefonnr.','eMail','Dienstleistung','Anmerkung']);
        $writer->insertAll($rows);

        $response->getBody()->write($writer->toString());

        $fileName = 'abholer_'. $providerName;

        return $response
            ->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->withHeader(
                'Content-Disposition',
                sprintf('download; filename="%s.xlsx"', $this->convertspecialChars($fileName))
            );
    }

    protected function writeData($processList, $rowCount, $rows)
    {
        if ($processList) {
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

                $rows[] = [
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
            }
        }
        return $rows;
    }

    protected function convertspecialchars($string)
    {
    
        $convert = array (
            array ('ä','ae',),
            array ('ö','oe',),
            array ('ü','ue',),
            array ('ß','ss',),
            array (' ','_',),
        );
        
        
        foreach ($convert as $array) {
            $string = str_replace($array[0], $array[1], $string);
        }
        return $string;
    }
}
