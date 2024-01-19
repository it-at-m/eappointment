<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Helper\AppointmentDeleteByCron;
use \BO\Zmsdb\Process as Query;
use \BO\Zmsdb\ProcessStatusArchived;

class AppointmentDeleteByCronTest extends Base
{

    public function testConstructor()
    {
        $now = new \DateTimeImmutable('2016-04-02 11:55');
        $helper = new AppointmentDeleteByCron(0, $now, false);
        $this->assertInstanceOf(AppointmentDeleteByCron::class, $helper);
    }

    public function testStartProcessingByCron()
    {
        $now = new \DateTimeImmutable('2016-04-02 00:10');
        $expired = new \DateTimeImmutable('2016-04-02 00:10');
        $helper = new AppointmentDeleteByCron(0, $now, false); // verbose
        $helper->setLimit(10);
        $helper->setLoopCount(5);
        $helper->startProcessing(false, false);
        $this->assertEquals(10, $helper->getCount()['preconfirmed']);
    }

    public function testStartProcessingExpiredExakt()
    {
        $now = new \DateTimeImmutable('2016-04-01 07:00');
        $expired = new \DateTimeImmutable('2016-04-01 07:00');
        $helper = new AppointmentDeleteByCron(0, $now, false); // verbose
        $helper->setLimit(10);
        $helper->setLoopCount(5);
        $helper->startProcessing(false, false);
        $this->assertEquals(8, $helper->getCount()['preconfirmed']);
        $this->assertEquals(8, count((new Query())->readExpiredProcessListByStatus($expired, 'preconfirmed')));
     
        $helper->startProcessing(true, false);
        $this->assertEquals(0, count((new Query())->readExpiredProcessListByStatus($expired, 'preconfirmed')));
    }

    public function testStartProcessingBlockedPickup()
    {
        $now = static::$now;
                
        $scope = (new \BO\Zmsdb\Scope())->readEntity(141);
        $process = (new Query())->writeNewPickup($scope, $now);
        $process = (new Query())->readEntity($process->id, $process->authKey, 0);
        $process->status = 'finished';

        error_log($process->id);

        error_log(json_encode( (new \BO\Zmsdb\Request())->readRequestByProcessId(10251250, 2)));
        //print_r( (new \BO\Zmsdb\Request())->readRequestByProcessId($process->id, 2), true);

        $requests = "[{\"$schema\":\"https:\/\/schema.berlin.de\/queuemanagement\/request.json\",\"id\":\"121151\",\"link\":\"https:\/\/service.berlin.de\/dienstleistung\/121151\/\",\"name\":\"Reisepass beantragen\",\"group\":\"Meldewesen und Ordnung\",\"source\":\"dldb\",\"data\":{\"id\":\"121151\",\"name\":\"Reisepass beantragen\",\"relation\":{\"root_topic\":\"324835\"},\"meta\":{\"url\":\"https:\/\/service.berlin.de\/dienstleistung\/121151\/\",\"locale\":\"de\",\"lastupdate\":\"2018-02-05T11:22:16+01:00\"},\"locations\":[{\"location\":\"122231\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122252\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122260\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122238\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122262\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122243\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122254\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"327539\",\"appointment\":{\"slots\":\"1\",\"allowed\":false}},{\"location\":\"122271\",\"appointment\":{\"slots\":\"2\",\"allowed\":true}},{\"location\":\"327278\",\"appointment\":{\"slots\":\"0\",\"allowed\":false}},{\"location\":\"122291\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122210\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122217\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122312\",\"appointment\":{\"slots\":\"2\",\"allowed\":true}},{\"location\":\"122273\",\"appointment\":{\"slots\":\"2\",\"allowed\":true}},{\"location\":\"327274\",\"appointment\":{\"slots\":\"0\",\"allowed\":false}},{\"location\":\"122219\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122208\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122226\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"150230\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122276\",\"appointment\":{\"slots\":\"1\",\"allowed\":true}},{\"location\":\"122246\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122314\",\"appointment\":{\"slots\":\"2\",\"allowed\":true}},{\"location\":\"122277\",\"appointment\":{\"slots\":\"2\",\"allowed\":true}},{\"location\":\"327276\",\"appointment\":{\"slots\":\"0\",\"allowed\":false}},{\"location\":\"122301\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122297\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122280\",\"appointment\":{\"slots\":\"1\",\"allowed\":true}},{\"location\":\"122285\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122304\",\"appointment\":{\"slots\":\"2\",\"allowed\":true}},{\"location\":\"122282\",\"appointment\":{\"slots\":\"1\",\"allowed\":true}},{\"location\":\"122311\",\"appointment\":{\"slots\":\"2\",\"allowed\":true}},{\"location\":\"122251\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"327653\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122286\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122281\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"324414\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122283\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122279\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122274\",\"appointment\":{\"slots\":\"1\",\"allowed\":true}},{\"location\":\"122309\",\"appointment\":{\"slots\":\"2\",\"allowed\":true}},{\"location\":\"122257\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122284\",\"appointment\":{\"slots\":\"1\",\"allowed\":true}},{\"location\":\"122294\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122227\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122267\",\"appointment\":{\"slots\":\"1\",\"allowed\":true}},{\"location\":\"122296\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"327262\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"327972\",\"appointment\":{\"slots\":\"0\",\"allowed\":false}},{\"location\":\"327753\",\"appointment\":{\"slots\":\"0\",\"allowed\":false}},{\"location\":\"327761\",\"appointment\":{\"slots\":\"0\",\"allowed\":false}},{\"location\":\"327751\",\"appointment\":{\"slots\":\"0\",\"allowed\":false}},{\"location\":\"317869\",\"appointment\":{\"slots\":\"2\",\"allowed\":true}},{\"location\":\"327759\",\"appointment\":{\"slots\":\"0\",\"allowed\":false}},{\"location\":\"324433\",\"appointment\":{\"slots\":\"2\",\"allowed\":true}},{\"location\":\"325341\",\"appointment\":{\"slots\":\"2\",\"allowed\":true}},{\"location\":\"324434\",\"appointment\":{\"slots\":\"2\",\"allowed\":true}},{\"location\":\"325657\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"327757\",\"appointment\":{\"slots\":\"0\",\"allowed\":false}},{\"location\":\"327755\",\"appointment\":{\"slots\":\"0\",\"allowed\":false}}],\"group\":\"Meldewesen und Ordnung\"}}][{\"$schema\":\"https:\/\/schema.berlin.de\/queuemanagement\/request.json\",\"id\":\"121151\",\"link\":\"https:\/\/service.berlin.de\/dienstleistung\/121151\/\",\"name\":\"Reisepass beantragen\",\"group\":\"Meldewesen und Ordnung\",\"source\":\"dldb\",\"data\":{\"id\":\"121151\",\"name\":\"Reisepass beantragen\",\"relation\":{\"root_topic\":\"324835\"},\"meta\":{\"url\":\"https:\/\/service.berlin.de\/dienstleistung\/121151\/\",\"locale\":\"de\",\"lastupdate\":\"2018-02-05T11:22:16+01:00\"},\"locations\":[{\"location\":\"122231\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122252\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122260\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122238\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122262\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122243\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122254\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"327539\",\"appointment\":{\"slots\":\"1\",\"allowed\":false}},{\"location\":\"122271\",\"appointment\":{\"slots\":\"2\",\"allowed\":true}},{\"location\":\"327278\",\"appointment\":{\"slots\":\"0\",\"allowed\":false}},{\"location\":\"122291\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122210\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122217\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122312\",\"appointment\":{\"slots\":\"2\",\"allowed\":true}},{\"location\":\"122273\",\"appointment\":{\"slots\":\"2\",\"allowed\":true}},{\"location\":\"327274\",\"appointment\":{\"slots\":\"0\",\"allowed\":false}},{\"location\":\"122219\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122208\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122226\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"150230\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122276\",\"appointment\":{\"slots\":\"1\",\"allowed\":true}},{\"location\":\"122246\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122314\",\"appointment\":{\"slots\":\"2\",\"allowed\":true}},{\"location\":\"122277\",\"appointment\":{\"slots\":\"2\",\"allowed\":true}},{\"location\":\"327276\",\"appointment\":{\"slots\":\"0\",\"allowed\":false}},{\"location\":\"122301\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122297\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122280\",\"appointment\":{\"slots\":\"1\",\"allowed\":true}},{\"location\":\"122285\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122304\",\"appointment\":{\"slots\":\"2\",\"allowed\":true}},{\"location\":\"122282\",\"appointment\":{\"slots\":\"1\",\"allowed\":true}},{\"location\":\"122311\",\"appointment\":{\"slots\":\"2\",\"allowed\":true}},{\"location\":\"122251\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"327653\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122286\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122281\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"324414\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122283\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122279\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122274\",\"appointment\":{\"slots\":\"1\",\"allowed\":true}},{\"location\":\"122309\",\"appointment\":{\"slots\":\"2\",\"allowed\":true}},{\"location\":\"122257\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122284\",\"appointment\":{\"slots\":\"1\",\"allowed\":true}},{\"location\":\"122294\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122227\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"122267\",\"appointment\":{\"slots\":\"1\",\"allowed\":true}},{\"location\":\"122296\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"327262\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"327972\",\"appointment\":{\"slots\":\"0\",\"allowed\":false}},{\"location\":\"327753\",\"appointment\":{\"slots\":\"0\",\"allowed\":false}},{\"location\":\"327761\",\"appointment\":{\"slots\":\"0\",\"allowed\":false}},{\"location\":\"327751\",\"appointment\":{\"slots\":\"0\",\"allowed\":false}},{\"location\":\"317869\",\"appointment\":{\"slots\":\"2\",\"allowed\":true}},{\"location\":\"327759\",\"appointment\":{\"slots\":\"0\",\"allowed\":false}},{\"location\":\"324433\",\"appointment\":{\"slots\":\"2\",\"allowed\":true}},{\"location\":\"325341\",\"appointment\":{\"slots\":\"2\",\"allowed\":true}},{\"location\":\"324434\",\"appointment\":{\"slots\":\"2\",\"allowed\":true}},{\"location\":\"325657\",\"appointment\":{\"slots\":\"0\",\"allowed\":true}},{\"location\":\"327757\",\"appointment\":{\"slots\":\"0\",\"allowed\":false}},{\"location\":\"327755\",\"appointment\":{\"slots\":\"0\",\"allowed\":false}}],\"group\":\"Meldewesen und Ordnung\"}}]";

        error_log($process->id);

        
        $process['requests'] = json_decode($requests);


        $json = json_encode($process);
        $maxLength = 1024; // Set maximum length of each chunk
        
        for ($i = 0; $i < ceil(strlen($json) / $maxLength); $i++) {
            error_log(substr($json, $i * $maxLength, $maxLength));
        }
        

        (new ProcessStatusArchived())->writeEntityFinished($process, $now);

        $helper = new AppointmentDeleteByCron(0, $now, false); // verbose

        $helper->startProcessing(false, false);
        $this->assertEquals(1, count((new Query())->readProcessListByScopeAndStatus(0, 'blocked', 0, 10, 0)));
     
        $helper->startProcessing(true, false);
        $appointmentUnits = count((new Query())->readProcessListByScopeAndStatus(0, 'blocked', 0, 10, 0));
        $this->assertEquals(0, $appointmentUnits);
    }
}
