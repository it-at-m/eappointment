<?php

namespace BO\Zmsentities\Tests;

use \BO\Zmsentities\Appointment;
use \BO\Zmsentities\Availability;
use \BO\Zmsentities\Process;
use \BO\Zmsentities\Validator\ProcessValidator;
use \BO\Mellon\Condition;
use \BO\Zmsentities\Collection\AvailabilityList;
use \BO\Zmsentities\Collection\ProcessList;
use BO\Mellon\Validator;

/**
 *
 */
class ValidatorProcessTest extends Base
{
    public function testInit()
    {
        $process = new Process();
        $processValidator = new ProcessValidator($process);
        $this->assertInstanceof(Process::class, $processValidator->getProcess());
    }

    public function testMail()
    {
        $parameters = [
            'mail' => 'test@berlinonline.de',
        ];
        $validator = new Validator($parameters);
        $process = new Process();
        $delegatedProcess = new \BO\Zmsentities\Helper\Delegate($process);
        $processValidator = new ProcessValidator($process);

        $processValidator->validateMail(
            $validator->getParameter('mail'),
            $delegatedProcess->setter('mail')
        );

        $this->assertEquals($process->toProperty()->mail->get(), $parameters['mail']);
    }

    public function testMailFormat()
    {
        $parameters = [
            'mail' => 'test#berlinonline.de',
        ];
        $validator = new Validator($parameters);
        $process = new Process();
        $delegatedProcess = new \BO\Zmsentities\Helper\Delegate($process);
        $processValidator = new ProcessValidator($process);

        $processValidator->validateMail(
            $validator->getParameter('mail'),
            $delegatedProcess->setter('mail')
        );

        $this->assertNotEquals($process->toProperty()->mail->get(), $parameters['mail']);
        $collectionStatus = $processValidator->getCollection()->getStatus();
        $this->assertTrue($collectionStatus['mail']['failed']);
    }

    public function testMailRequiredByParameter()
    {
        $parameters = [
            'sendMailConfirmation' => 1,
            'mail' => '',
        ];
        $validator = new Validator($parameters);
        $process = new Process();
        $delegatedProcess = new \BO\Zmsentities\Helper\Delegate($process);
        $processValidator = new ProcessValidator($process);

        $processValidator->validateMail(
            $validator->getParameter('mail'),
            $delegatedProcess->setter('mail'),
            new Condition(
                $validator->getParameter('sendMailConfirmation')->isNumber()->isNotEqualTo(1)
            )
        );

        $this->assertEquals($process->toProperty()->mail->get(), $parameters['mail']);
        $collectionStatus = $processValidator->getCollection()->getStatus();
        $this->assertTrue($collectionStatus['mail']['failed']);
    }

    public function testMailNotRequiredByParameter()
    {
        $parameters = [
            'sendMailConfirmation' => 0,
            'mail' => '',
        ];
        $validator = new Validator($parameters);
        $process = new Process();
        $delegatedProcess = new \BO\Zmsentities\Helper\Delegate($process);
        $processValidator = new ProcessValidator($process);

        $processValidator->validateMail(
            $validator->getParameter('mail'),
            $delegatedProcess->setter('mail'),
            new Condition(
                $validator->getParameter('sendMailConfirmation')->isNumber()->isNotEqualTo(1)
            )
        );

        $this->assertEquals($process->toProperty()->mail->get(), $parameters['mail']);
        $collectionStatus = $processValidator->getCollection()->getStatus();
        $this->assertFalse($collectionStatus['mail']['failed']);
    }
}
