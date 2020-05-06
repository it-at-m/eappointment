<?php

namespace BO\Zmsadmin\Tests;

class DialogHandlerTest extends Base
{
    protected $arguments = [];

    protected $parameters = ['template' => 'confirm_delete', 'parameter' => ['id' => 100044, 'name' => 'unittest']];

    protected $classname = "\BO\Zmsadmin\Helper\DialogHandler";

    public function testRendering()
    {
        $response = $this->render([], $this->parameters, []);
        $this->assertContains('100044', (string)$response->getBody());
        $this->assertContains('unittest', (string)$response->getBody());
        $this->assertContains('data-action-ok data-id="100044" data-name="unittest"', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testConfirmFinishList()
    {
        $response = $this->render([], ['template' => 'confirm_finish_list'], []);
        $this->assertContains(
            'Wollen Sie wirklich alle Abholer aus dieser Liste löschen?',
            (string)$response->getBody()
        );
        $this->assertContains('data-action-finishList', (string)$response->getBody());
        $this->assertContains('data-action-abort', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testConfirmFinish()
    {
        $response = $this->render([], [
            'template' => 'confirm_finish',
            'parameter' => ['id' => 100044, 'name' => 'unittest']
        ], []);
        $this->assertContains(
            'Wenn Sie den Kunden Nr. 100044 (unittest) löschen wollen, klicken Sie auf OK',
            (string)$response->getBody()
        );
        $this->assertContains('data-action-finish data-id="100044"', (string)$response->getBody());
        $this->assertContains('data-action-abort', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testNotificationReminder()
    {
        $response = $this->render([], [
            'template' => 'confirm_notification_reminder',
            'parameter' => ['id' => 100044]
        ], []);
        $this->assertContains(
            'Möchten Sie dem Kunden per SMS mitteilen, dass er/sie bald an der Reihe ist, dann klicken Sie auf OK.',
            (string)$response->getBody()
        );
        $this->assertContains('data-action-sendNotificationReminder data-id="100044"', (string)$response->getBody());
        $this->assertContains('data-action-abort', (string)$response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
