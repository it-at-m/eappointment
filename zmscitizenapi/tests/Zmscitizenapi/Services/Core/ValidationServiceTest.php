<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Tests\Services\Core;

use BO\Zmscitizenapi\Localization\ErrorMessages;
use BO\Zmscitizenapi\Services\Core\ValidationService;
use BO\Zmscitizenapi\Services\Core\ZmsApiFacadeService;
use BO\Zmsentities\Collection\ProcessList;
use BO\Zmsentities\Collection\ScopeList;
use BO\Zmsentities\Process;
use BO\Zmsentities\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class ValidationServiceTest extends TestCase
{
    public function testValidateServerGetRequest(): void
    {
        // Test valid GET request
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $this->assertEmpty(ValidationService::validateServerGetRequest($request));

        // Test invalid method
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('POST');
        $result = ValidationService::validateServerGetRequest($request);
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('invalidRequest')]],
            $result
        );

        // Test null request
        $result = ValidationService::validateServerGetRequest(null);
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('invalidRequest')]],
            $result
        );
    }

    public function testValidateServerPostRequest(): void
    {
        // Test valid POST request
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('POST');
        $request->method('getParsedBody')->willReturn(['data' => 'test']);
        $this->assertEmpty(ValidationService::validateServerPostRequest($request));

        // Test invalid method
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $result = ValidationService::validateServerPostRequest($request);
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('invalidRequest')]],
            $result
        );

        // Test null body
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('POST');
        $request->method('getParsedBody')->willReturn(null);
        $result = ValidationService::validateServerPostRequest($request);
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('invalidRequest')]],
            $result
        );
    }

    public function testValidateGetProcessById(): void
    {
        // Test valid input
        $result = ValidationService::validateGetProcessById(1, 'valid-key');
        $this->assertEmpty($result['errors']);

        // Test invalid process ID
        $result = ValidationService::validateGetProcessById(0, 'valid-key');
        $this->assertContains(
            ErrorMessages::get('invalidProcessId'),
            $result['errors']
        );

        // Test invalid auth key
        $result = ValidationService::validateGetProcessById(1, '');
        $this->assertContains(
            ErrorMessages::get('invalidAuthKey'),
            $result['errors']
        );
    }

    public function testValidateGetAvailableAppointments(): void
    {
        // Test valid input
        $result = ValidationService::validateGetAvailableAppointments(
            '2025-01-01',
            1,
            [1],
            [1]
        );
        $this->assertEmpty($result['errors']);

        // Test invalid date
        $result = ValidationService::validateGetAvailableAppointments(
            'invalid',
            1,
            [1],
            [1]
        );
        $this->assertContains(
            ErrorMessages::get('invalidDate'),
            $result['errors']
        );

        // Test invalid office ID
        $result = ValidationService::validateGetAvailableAppointments(
            '2025-01-01',
            0,
            [1],
            [1]
        );
        $this->assertContains(
            ErrorMessages::get('invalidOfficeId'),
            $result['errors']
        );

        // Test invalid service IDs
        $result = ValidationService::validateGetAvailableAppointments(
            '2025-01-01',
            1,
            ['invalid'],
            [1]
        );
        $this->assertContains(
            ErrorMessages::get('invalidServiceId'),
            $result['errors']
        );

        // Test invalid service counts
        $result = ValidationService::validateGetAvailableAppointments(
            '2025-01-01',
            1,
            [1],
            ['invalid']
        );
        $this->assertContains(
            ErrorMessages::get('invalidServiceCount'),
            $result['errors']
        );
    }

    public function testValidatePostAppointmentReserve(): void
    {
        // Test valid input
        $result = ValidationService::validatePostAppointmentReserve(
            1,
            [1],
            [1],
            time() + 3600
        );
        $this->assertEmpty($result['errors']);

        // Test invalid office ID
        $result = ValidationService::validatePostAppointmentReserve(
            0,
            [1],
            [1],
            time() + 3600
        );
        $this->assertContains(
            ErrorMessages::get('invalidOfficeId'),
            $result['errors']
        );

        // Test invalid service IDs
        $result = ValidationService::validatePostAppointmentReserve(
            1,
            ['invalid'],
            [1],
            time() + 3600
        );
        $this->assertContains(
            ErrorMessages::get('invalidServiceId'),
            $result['errors']
        );

        // Test invalid timestamp
        $result = ValidationService::validatePostAppointmentReserve(
            1,
            [1],
            [1],
            time() - 3600
        );
        $this->assertContains(
            ErrorMessages::get('invalidTimestamp'),
            $result['errors']
        );

        // Test invalid service counts
        $result = ValidationService::validatePostAppointmentReserve(
            1,
            [1],
            ['invalid'],
            time() + 3600
        );
        $this->assertContains(
            ErrorMessages::get('invalidServiceCount'),
            $result['errors']
        );
    }

    public function testValidateUpdateAppointmentInputs(): void
    {
        // Test valid input
        $result = ValidationService::validateUpdateAppointmentInputs(
            1,
            'valid-key',
            'John Doe',
            'john@example.com',
            '+1234567890',
            'Custom text'
        );
        $this->assertEmpty($result['errors']);

        // Test invalid process ID
        $result = ValidationService::validateUpdateAppointmentInputs(
            0,
            'valid-key',
            'John Doe',
            'john@example.com',
            '+1234567890',
            'Custom text'
        );
        $this->assertContains(
            ErrorMessages::get('invalidProcessId'),
            $result['errors']
        );

        // Test invalid auth key
        $result = ValidationService::validateUpdateAppointmentInputs(
            1,
            '',
            'John Doe',
            'john@example.com',
            '+1234567890',
            'Custom text'
        );
        $this->assertContains(
            ErrorMessages::get('invalidAuthKey'),
            $result['errors']
        );

        // Test invalid family name
        $result = ValidationService::validateUpdateAppointmentInputs(
            1,
            'valid-key',
            '',
            'john@example.com',
            '+1234567890',
            'Custom text'
        );
        $this->assertContains(
            ErrorMessages::get('invalidFamilyName'),
            $result['errors']
        );

        // Test invalid email
        $result = ValidationService::validateUpdateAppointmentInputs(
            1,
            'valid-key',
            'John Doe',
            'invalid-email',
            '+1234567890',
            'Custom text'
        );
        $this->assertContains(
            ErrorMessages::get('invalidEmail'),
            $result['errors']
        );

        // Test invalid telephone
        $result = ValidationService::validateUpdateAppointmentInputs(
            1,
            'valid-key',
            'John Doe',
            'john@example.com',
            'invalid',
            'Custom text'
        );
        $this->assertContains(
            ErrorMessages::get('invalidTelephone'),
            $result['errors']
        );

        // Test invalid custom textfield
        $result = ValidationService::validateUpdateAppointmentInputs(
            1,
            'valid-key',
            'John Doe',
            'john@example.com',
            '+1234567890',
            ''
        );
        $this->assertContains(
            ErrorMessages::get('invalidCustomTextfield'),
            $result['errors']
        );
    }

    public function testValidateGetScopeById(): void
    {
        // Test valid scope ID
        $this->assertEmpty(ValidationService::validateGetScopeById(1));

        // Test invalid scope ID
        $result = ValidationService::validateGetScopeById(0);
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('invalidScopeId')]],
            $result
        );
    }

    public function testValidateGetServicesByOfficeId(): void
    {
        // Test valid office ID
        $this->assertEmpty(ValidationService::validateGetServicesByOfficeId(1));

        // Test invalid office ID
        $result = ValidationService::validateGetServicesByOfficeId(0);
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('invalidOfficeId')]],
            $result
        );
    }

    public function testValidateGetOfficeListByServiceId(): void
    {
        // Test valid service ID
        $this->assertEmpty(ValidationService::validateGetOfficeListByServiceId(1));

        // Test invalid service ID
        $result = ValidationService::validateGetOfficeListByServiceId(0);
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('invalidServiceId')]],
            $result
        );
    }

    public function testValidateGetProcessByIdTimestamps(): void
    {
        // Test valid timestamps
        $this->assertEmpty(ValidationService::validateGetProcessByIdTimestamps([1234567890]));

        // Test empty timestamps
        $result = ValidationService::validateGetProcessByIdTimestamps([]);
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('appointmentNotAvailable')]],
            $result
        );

        // Test null timestamps
        $result = ValidationService::validateGetProcessByIdTimestamps(null);
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('appointmentNotAvailable')]],
            $result
        );
    }

    public function testValidateGetProcessNotFound(): void
    {
        // Test valid process
        $this->assertEmpty(ValidationService::validateGetProcessNotFound(new Process()));

        // Test null process
        $result = ValidationService::validateGetProcessNotFound(null);
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('appointmentNotAvailable')]],
            $result
        );
    }

    public function testValidateScopesNotFound(): void
    {
        // Test valid scope list
        $scopeList = new ScopeList([new \BO\Zmsentities\Scope()]);
        $this->assertEmpty(ValidationService::validateScopesNotFound($scopeList));

        // Test empty scope list
        $result = ValidationService::validateScopesNotFound(new ScopeList());
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('scopesNotFound')]],
            $result
        );

        // Test null scope list
        $result = ValidationService::validateScopesNotFound(null);
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('scopesNotFound')]],
            $result
        );
    }

    public function testValidateServicesNotFound(): void
    {
        // Test valid services array
        $this->assertEmpty(ValidationService::validateServicesNotFound(['service']));

        // Test empty services array
        $result = ValidationService::validateServicesNotFound([]);
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('requestNotFound')]],
            $result
        );

        // Test null services array
        $result = ValidationService::validateServicesNotFound(null);
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('requestNotFound')]],
            $result
        );
    }

    public function testValidateOfficesNotFound(): void
    {
        // Test valid offices array
        $this->assertEmpty(ValidationService::validateOfficesNotFound(['office']));

        // Test empty offices array
        $result = ValidationService::validateOfficesNotFound([]);
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('providerNotFound')]],
            $result
        );

        // Test null offices array
        $result = ValidationService::validateOfficesNotFound(null);
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('providerNotFound')]],
            $result
        );
    }

    public function testValidateAppointmentDaysNotFound(): void
    {
        // Test valid days array
        $this->assertEmpty(ValidationService::validateAppointmentDaysNotFound(['day']));

        // Test empty days array
        $result = ValidationService::validateAppointmentDaysNotFound([]);
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('noAppointmentForThisDay')]],
            $result
        );

        // Test null days array
        $result = ValidationService::validateAppointmentDaysNotFound(null);
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('noAppointmentForThisDay')]],
            $result
        );
    }

    public function testValidateNoAppointmentsAtLocation(): void
    {
        $result = ValidationService::validateNoAppointmentsAtLocation();
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('noAppointmentsAtLocation')]],
            $result
        );
    }

    public function testValidateServiceArrays(): void
    {
        // Test valid arrays
        $this->assertEmpty(ValidationService::validateServiceArrays([1], [1]));

        // Test empty arrays
        $result = ValidationService::validateServiceArrays([], []);
        $this->assertContains(
            ErrorMessages::get('emptyServiceArrays'),
            $result
        );

        // Test mismatched array lengths
        $result = ValidationService::validateServiceArrays([1], [1, 2]);
        $this->assertContains(
            ErrorMessages::get('mismatchedArrays'),
            $result
        );

        // Test invalid service IDs
        $result = ValidationService::validateServiceArrays(['invalid'], [1]);
        $this->assertContains(
            ErrorMessages::get('invalidServiceId'),
            $result
        );

        // Test invalid service counts
        $result = ValidationService::validateServiceArrays([1], ['invalid']);
        $this->assertContains(
            ErrorMessages::get('invalidServiceCount'),
            $result
        );
    }
}