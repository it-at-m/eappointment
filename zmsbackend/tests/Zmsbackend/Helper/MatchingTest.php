<?php

namespace BO\Zmsbackend\Tests\Helper;

use BO\Zmsbackend\Helper\Matching;
use BO\Zmsbackend\Matching\Exception\RequestNotFound;
use BO\Zmsbackend\RequestRelation\Service\RequestRelation;
use BO\Zmsentities\Process;

class MatchingTest extends \BO\Zmsbackend\Tests\Service\Base
{
    /**
     * Scope 65991 / provider 9999997 has request 9999997 in request_provider
     * but provider.data has no services array (ZMSKVR-1049).
     */
    public function testAcceptsRequestFromRequestProviderWithoutDataServices(): void
    {
        $this->expectNotToPerformAssertions();
        Matching::testCurrentScopeHasRequest($this->processWithRequest('9999997'));
    }

    public function testRejectsRequestMissingFromRequestProviderEvenIfInProviderData(): void
    {
        (new RequestRelation())->perform(
            'UPDATE `provider` SET `data` = :data WHERE `id` = :id AND `source` = :source',
            [
                'data' => '{"services":[{"service":"9999998"}]}',
                'id' => '9999997',
                'source' => 'unittest',
            ]
        );

        $this->expectException(RequestNotFound::class);
        Matching::testCurrentScopeHasRequest($this->processWithRequest('9999998'));
    }

    public function testRejectsUnknownRequest(): void
    {
        $this->expectException(RequestNotFound::class);
        Matching::testCurrentScopeHasRequest($this->processWithRequest('9999999'));
    }

    public function testSkipsWhenProcessHasNoRequests(): void
    {
        $this->expectNotToPerformAssertions();
        $process = new Process([
            'scope' => ['id' => 65991],
            'requests' => [],
        ]);
        Matching::testCurrentScopeHasRequest($process);
    }

    public function testAcceptsDldbRequestLinkedInRequestProvider(): void
    {
        $this->expectNotToPerformAssertions();
        $process = new Process([
            'scope' => ['id' => 141],
            'requests' => [
                [
                    'id' => '120703',
                    'source' => 'dldb',
                    'name' => 'Personalausweis beantragen',
                ],
            ],
        ]);
        Matching::testCurrentScopeHasRequest($process);
    }

    private function processWithRequest(string $requestId): Process
    {
        return new Process([
            'scope' => ['id' => 65991],
            'requests' => [
                [
                    'id' => $requestId,
                    'source' => 'unittest',
                    'name' => 'History Test Service',
                ],
            ],
        ]);
    }
}
