<?php

namespace BO\Zmsbackend\Exchange\Service;

trait ScopeStatisticTitle
{
    private function scopeStatisticTitle(string $label, mixed $scope): string
    {
        $contactName = '';
        $shortName = '';
        if (is_object($scope)) {
            $contact = $scope->contact ?? null;
            if (is_object($contact)) {
                $contactName = trim((string) ($contact->name ?? ''));
            }
            $shortName = trim((string) ($scope->shortName ?? ''));
        }

        return $label . ' ' . ($contactName !== '' ? $contactName : 'Unknown')
            . ' ' . ($shortName !== '' ? $shortName : 'Unknown');
    }
}
