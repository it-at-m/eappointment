<?php

namespace BO\Zmsmessaging\Tests;

use \BO\Mellon\Validator;

class SendTest extends Base
{
    public function testSendMailQueue()
    {
        $this->writeTestLogin();
        $entity = \BO\Zmsentities\Mail::createExample();
        $entity->process = \App::$http->readGetResult('/process/82252/12a2/')->getEntity();
        $result = \App::$http->readPostResult('/mails/', $entity);
        $entity = $result->getEntity();
        $this->assertTrue($entity instanceof \BO\Zmsentities\Mail);

        \App::$messaging = new \BO\Zmsmessaging\SendQueue();
        $resultList = \App::$messaging->startMailTransmission();
        foreach ($resultList as $mail) {
            \App::$messaging->deleteFromQueue($entity);
            if (isset($mail['errorInfo'])) {
                echo "ERROR OCCURED: ". $mail['errorInfo'] ."\n";
            } else {
                $this->assertContains('text/html', trim($mail['mime']));
                $this->assertContains('test@example.com', json_encode($mail['recipients']));
                $this->assertContains('calendar', json_encode($mail['attachments'][0]));
            }
        }
        $this->writeTestLogout();
    }

    public function testSendMailQueueFailed()
    {
        \App::$messaging = new \BO\Zmsmessaging\SendQueue();
        $resultList = \App::$messaging->startMailTransmission();
        foreach ($resultList as $mail) {
            $this->assertContains('No mail entry found in Database', $mail['errorInfo']);
        }
    }

    public function testSendNotificationQueue()
    {
        $this->writeTestLogin();
        $entity = \BO\Zmsentities\Notification::createExample();
        $entity->process = \App::$http->readGetResult('/process/82252/12a2/')->getEntity();
        $entity->process->getFirstClient()->telephone = '0123456789';
        $result = \App::$http->readPostResult('/notification/', $entity);
        $entity = $result->getEntity();
        $this->assertTrue($entity instanceof \BO\Zmsentities\Notification);

        \App::$messaging = new \BO\Zmsmessaging\SendQueue('notification');
        $resultList = \App::$messaging->startNotificationTransmission();
        foreach ($resultList as $notification) {
            \App::$messaging->deleteFromQueue($entity);
            if (isset($notification['errorInfo'])) {
                echo "ERROR OCCURED: ". $notification['errorInfo'] ."\n";
            } else {
                $this->assertContains('Content-Transfer-Encoding: base64', trim($notification['mime']));
                $this->assertContains('sms=test@example.com', json_encode($notification['recipients']));
            }
        }
        $this->writeTestLogout();
    }

    public function testSendNotificationQueueFailed()
    {
        \App::$messaging = new \BO\Zmsmessaging\SendQueue();
        $resultList = \App::$messaging->startNotificationTransmission();
        foreach ($resultList as $notification) {
            $this->assertContains('No notification entry found in Database', $notification['errorInfo']);
        }
    }

    public function testDeleteFromQueueFailed()
    {
        $this->setExpectedException('\Exception');
        $entity = \BO\Zmsentities\Mail::createExample();
        \App::$messaging = new \BO\Zmsmessaging\SendQueue();
        \App::$messaging->deleteFromQueue($entity);
    }
}
