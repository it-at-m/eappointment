<?php
declare(strict_types=1);

namespace BO\Zmscitizenbackend\Tests\Services\Core;

use BO\Zmscitizenbackend\Utils\ErrorMessages;
use BO\Zmscitizenbackend\Models\ThinnedScope;
use BO\Zmscitizenbackend\Repository\OfficesServicesRelationsRepository;
use BO\Zmscitizenbackend\Services\Core\ValidationService;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class ValidationServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        OfficesServicesRelationsRepository::use(null);
        ValidationService::clearOfficeServicesCacheForTesting();
        parent::tearDown();
    }

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
        // Test valid 4-hex (legacy) auth key
        $result = ValidationService::validateGetProcessById(1, 'fb43');
        $this->assertEmpty($result['errors']);

        $longHex = str_repeat('a', 64);
        $result = ValidationService::validateGetProcessById(1, $longHex);
        $this->assertEmpty($result['errors']);

        // Test invalid process ID
        $result = ValidationService::validateGetProcessById(0, 'fb43');
        $this->assertContains(
            ErrorMessages::get('invalidProcessId'),
            $result['errors']
        );

        // Test invalid auth key (empty)
        $result = ValidationService::validateGetProcessById(1, '');
        $this->assertContains(
            ErrorMessages::get('invalidAuthKey'),
            $result['errors']
        );

        // Not hex / wrong length
        $result = ValidationService::validateGetProcessById(1, 'valid-key');
        $this->assertContains(
            ErrorMessages::get('invalidAuthKey'),
            $result['errors']
        );
        $result = ValidationService::validateGetProcessById(1, 'abcdef');
        $this->assertContains(
            ErrorMessages::get('invalidAuthKey'),
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
    
    public function testValidateAppointmentUpdateFields(): void
    {
        $scope = new ThinnedScope();
        $scope->customTextfieldActivated = true;
        $scope->customTextfieldRequired = true;
        $scope->customTextfield2Activated = true;
        $scope->customTextfield2Required = true;
        $scope->telephoneActivated = true;
        $scope->telephoneRequired = true;
        $scope->emailRequired = true;
    
        $result = ValidationService::validateAppointmentUpdateFields(
            'John Doe',
            'john@example.com',
            '+1234567890',
            'Custom text',
            'Another Custom text',
            $scope
        );
        $this->assertEmpty($result['errors']);
    
        $result = ValidationService::validateAppointmentUpdateFields(
            '',
            'john@example.com',
            '+1234567890',
            'Custom text',
            'Another Custom text',
            $scope
        );
        $this->assertContains(
            ErrorMessages::get('invalidFamilyName'),
            $result['errors']
        );
    
        $result = ValidationService::validateAppointmentUpdateFields(
            'John Doe',
            'invalid-email',
            '+1234567890',
            'Custom text',
            'Another Custom text',
            $scope
        );
        $this->assertContains(
            ErrorMessages::get('invalidEmail'),
            $result['errors']
        );
    
        $result = ValidationService::validateAppointmentUpdateFields(
            'John Doe',
            'john@example.com',
            'invalid',
            'Custom text',
            'Another Custom text',
            $scope
        );
        $this->assertContains(
            ErrorMessages::get('invalidTelephone'),
            $result['errors']
        );
    
        $result = ValidationService::validateAppointmentUpdateFields(
            'John Doe',
            'john@example.com',
            '+1234567890',
            '',
            'Another Custom text',
            $scope
        );
        $this->assertContains(
            ErrorMessages::get('invalidCustomTextfield'),
            $result['errors']
        );

        $result = ValidationService::validateAppointmentUpdateFields(
            'John Doe',
            'john@example.com',
            '+1234567890',
            'Custom Textfield',
            '',
            $scope
        );
        $this->assertContains(
            ErrorMessages::get('invalidCustomTextfield2'),
            $result['errors']
        );
    
        $optionalScope = new ThinnedScope();
        $result = ValidationService::validateAppointmentUpdateFields(
            'John Doe',
            null,
            null,
            null,
            null,
            $optionalScope
        );
        $this->assertEmpty($result['errors']);

        $long = str_repeat('x', 251);
        $result = ValidationService::validateAppointmentUpdateFields(
            'John Doe',
            'john@example.com',
            '+1234567890',
            $long,
            'ok',
            $scope
        );
        $this->assertContains(
            ErrorMessages::get('invalidCustomTextfield'),
            $result['errors']
        );

        $result = ValidationService::validateAppointmentUpdateFields(
            'John Doe',
            'john@example.com',
            '+1234567890',
            'ok',
            $long,
            $scope
        );
        $this->assertContains(
            ErrorMessages::get('invalidCustomTextfield2'),
            $result['errors']
        );
    }

    public function testValidateServiceLocationCombination(): void
    {
        ValidationService::clearOfficeServicesCacheForTesting();
        $repository = $this->createStub(OfficesServicesRelationsRepository::class);
        $repository->method('readServiceIdsByOfficeId')->willReturn(['1063424', '1']);
        OfficesServicesRelationsRepository::use($repository);

        $this->assertEmpty(
            ValidationService::validateServiceLocationCombination(102522, [1063424])
        );
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('invalidLocationAndServiceCombination')]],
            ValidationService::validateServiceLocationCombination(102522, [999])
        );
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('invalidOfficeId')]],
            ValidationService::validateServiceLocationCombination(0, [1063424])
        );
        $this->assertEquals(
            ['errors' => [ErrorMessages::get('invalidServiceId')]],
            ValidationService::validateServiceLocationCombination(102522, [])
        );

        OfficesServicesRelationsRepository::use(null);
        ValidationService::clearOfficeServicesCacheForTesting();
    }
}
