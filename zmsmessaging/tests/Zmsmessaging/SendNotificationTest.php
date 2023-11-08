<?php

namespace BO\Zmsmessaging\Tests;

use \BO\Mellon\Validator;

class SendNotificationTest extends Base
{
    public function testSendNotificationQueue()
    {
        $this->setApiCalls([
            [
                'function' => 'readGetResult',
                'url' => '/config/',
                'xtoken' => 'a9b215f1-e460-490c-8a0b-6d42c274d5e4',
                'response' => $this->readFixture("GET_config.json"),
            ],
            [
                'function' => 'readGetResult',
                'url' => '/notification/',
                'response' => $this->readFixture("GET_notifications_queue.json")
            ],
            [
                'function' => 'readPostResult',
                'url' => '/log/process/123456/',
                'response' => $this->readFixture("POST_log.json")
            ],
        ]);
        \App::$messaging = new \BO\Zmsmessaging\Notification();
        $resultList = \App::$messaging->initQueueTransmission();
        foreach ($resultList as $notification) {
            if (isset($notification['errorInfo'])) {
                echo "ERROR OCCURED: ". $notification['errorInfo'] ."\n";
            } else {
                $this->assertStringContainsString('Content-Transfer-Encoding: base64', trim($notification['mime']));
                $this->assertStringContainsString(
                    'sms=+4917012345678@sms.verwalt-berlin.de',
                    json_encode($notification['recipients'])
                );
            }
        }
    }

    public function testMessageEncoding()
    {
        $item =  new \BO\Zmsentities\Notification(
            json_decode($this->readFixture("GET_notification_appointment.json"), 1)
        );
        $process = (new \BO\Zmsentities\Process())->getExample();
        $process['queue']['withAppointment'] = 1;
        $process['id'] = 4567;
        $config = (new \BO\Zmsentities\Config())->getExample();
        $department = (new \BO\Zmsentities\Department())->getExample();

        $resolvedEntity = $item->toResolvedEntity($process, $config, $department, 'appointment');



        $preferences = (new \BO\Zmsentities\Config())->getNotificationPreferences();
        $url = $preferences['gatewayUrl'] .
            urlencode($resolvedEntity->getMessage()) .
            '&sender='. urlencode($resolvedEntity->getIdentification()) .
            '&recipient=' .
            urlencode($resolvedEntity->client['telephone'])
        ;
        $this->assertStringContainsString('Bürgeramt', urldecode($url));
    }
}
