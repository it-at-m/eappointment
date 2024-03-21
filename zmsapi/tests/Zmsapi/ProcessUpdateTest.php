<?php

namespace BO\Zmsapi\Tests;

class ProcessUpdateTest extends Base
{
    protected $classname = "ProcessUpdate";

    const PROCESS_ID = 10029;

    const AUTHKEY = '1c56';

    public function testRendering()
    {
        $process = json_decode($this->readFixture("GetProcess_10029.json"), 1);
        $process['amendment'] = "Test Update";
        $response = $this->render([], [
            '__body' => json_encode($process),
        ], []);
        $this->assertStringContainsString('Test Update', (string)$response->getBody());
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testInternalWithSlotsRequired()
    {
        $this->setWorkstation();
        $process = json_decode($this->readFixture("GetProcess_10029.json"), 1);
        $process['amendment'] = 'Unittest update process with loggedin user and slotsrequired';
        $response = $this->render([], [
            '__body' => json_encode($process),
            'slotsRequired' => 1,
            'slotType' => 'intern'
        ], []);

        $this->assertStringContainsString('with loggedin user and slotsrequired', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testPublicWithSlotsRequired()
    {
        $this->setWorkstation();
        $process = json_decode($this->readFixture("GetProcess_10029.json"), 1);
        $process['amendment'] = 'Unittest update process from public user and slotsrequired';
        $response = $this->render([], [
            '__body' => json_encode($process),
        ], []);

        $this->assertStringContainsString('update process from public user', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWithClientkey()
    {
        $process = json_decode($this->readFixture("GetProcess_10029.json"), 1);
        $response = $this->render([], [
            '__body' => json_encode($process),
           'clientkey' => 'default'
        ], []);

        $this->assertStringContainsString('"slotCount":"1"', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }

    public function testWithClientkeyBlocked()
    {
        $query = new \BO\Zmsdb\Process();
        $this->expectException('BO\Zmsapi\Exception\Process\ApiclientInvalid');
        $this->expectExceptionCode(403);
        $this->render([], [
            '__body' => '{
                "id": '. self::PROCESS_ID .',
                "authKey": "'. self::AUTHKEY .'",
                "amendment": "Beispiel Termin"
            }',
            'clientkey' => '8pnaRHkUBYJqz9i9NPDEeZq6mUDMyRHE'
        ], []);
    }
    // To do add adminMailOnUpdated
    /*public function testRenderingWithInitiator()
    {
        $process = json_decode($this->readFixture("GetProcess_27758.json"), 1);
        $response = $this->render([], [
            '__body' => json_encode($process),
            'initiator' => 1,
            'adminMailOnUpdated' => 1
        ], []);
        $mailList = (new \BO\Zmsdb\Mail)->readList();
        $this->assertStringContainsString('Information Terminänderung', $mailList->getFirst()['subject']);
        $this->assertStringContainsString(
            'Geändert wurde der Termin von W45265 (Vorgangsnummer: 27758)',
            $mailList->getFirst()->multipart[0]['content']
        );
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }*/


    public function testWithClientkeyInvalid()
    {
        $query = new \BO\Zmsdb\Process();
        $this->expectException('BO\Zmsapi\Exception\Process\ApiclientInvalid');
        $this->expectExceptionCode(403);
        $this->render([], [
            '__body' => '{
                "id": '. self::PROCESS_ID .',
                "authKey": "'. self::AUTHKEY .'",
                "amendment": "Beispiel Termin"
            }',
            'clientkey' => '__invalid'
        ], []);
    }

    public function testEmpty()
    {
        $this->expectException('\BO\Mellon\Failure\Exception');
        $this->render([], [], []);
    }

    public function testProcessNotFound()
    {
        $this->expectException('\BO\Zmsapi\Exception\Process\ProcessNotFound');
        $this->render([], [
            '__body' => '{
                "id": 123456,
                "authKey": "abcd",
                "amendment": "Beispiel Termin"
            }'
        ], []);
    }

    public function testAuthKeyMatchFailed()
    {
        $this->expectException('\BO\Zmsapi\Exception\Process\AuthKeyMatchFailed');
        $this->render([], [
            '__body' => '{
                "id": '. self::PROCESS_ID .',
                "authKey": "abcd",
                "amendment": "Beispiel Termin"
            }'
        ], []);
    }

    /**
     * Test a complete dataset for saving an appointment
     */
    public function testAppointment()
    {
        $response = $this->render([], [
            '__body' => $this->readFixture('PostProcessAppointment.json'),
        ], []);
        $this->assertStringContainsString('process.json', (string)$response->getBody());
        $this->assertTrue(200 == $response->getStatusCode());
    }
}
