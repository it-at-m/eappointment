<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsadmin;

use BO\Zmsentities\Source as Entity;
use BO\Mellon\Validator;

class SourceEdit extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $success = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();
        if (!$workstation->hasSuperUseraccount()) {
            throw new Exception\NotAllowed();
        }

        if ('add' != $args['name']) {
            $source = \App::$http
                ->readGetResult('/source/' . $args['name'] . '/', ['resolveReferences' => 2])
                ->getEntity();
        }

        [$parentProviders, $parentRequests] = $this->loadParentsFromAllSources();

        try {
            $apiRes  = \App::$http->readGetResult('/requestvariants/');
            $body    = (string) $apiRes->getResponse()->getBody();
            $payload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            $requestVariants = $payload['data'] ?? [];
        } catch (\JsonException $e) {
            \BO\Log::error('requestvariants JSON decode failed', ['error' => $e->getMessage()]);
            $requestVariants = [];
        }

        $input = $request->getParsedBody();
        if (is_array($input) && array_key_exists('save', $input)) {
            $result = $this->writeUpdatedEntity($input);
            if ($result instanceof Entity) {
                return \BO\Slim\Render::redirect('sourceEdit', ['name' => $result->getSource()], [
                    'success' => 'source_saved'
                ]);
            }
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/sourceedit.twig',
            array(
                'title' => 'Mandanten bearbeiten',
                'menuActive' => 'source',
                'workstation' => $workstation,
                'source' => (isset($source)) ? $source : null,
                'parentProviders' => $parentProviders,
                'parentRequests' => $parentRequests,
                'requestVariants' => $requestVariants,
                'success' => $success,
                'exception' => (isset($result)) ? $result : null
            )
        );
    }

    protected function writeUpdatedEntity($input)
    {
        $entity = (new Entity($input))->withCleanedUpFormData();
        return $this->handleEntityWrite(function () use ($entity) {
            return \App::$http->readPostResult('/source/', $entity)->getEntity();
        });
    }

    private function loadParentsFromAllSources(): array
    {
        $sourceList = \App::$http->readGetResult('/source/', ['resolveReferences' => 0])->getCollection();

        $parentProviders = [];
        $parentRequests  = [];

        foreach ($sourceList as $src) {
            $srcName = $src->source ?? null;
            if (!$srcName) {
                continue;
            }

            $fullSource = \App::$http
                ->readGetResult('/source/' . $srcName . '/', ['resolveReferences' => 2])
                ->getEntity();

            foreach (($fullSource->providers ?? []) as $provider) {
                $parentProviders[] = $provider;
            }
            foreach (($fullSource->requests ?? []) as $req) {
                $parentRequests[] = $req;
            }
        }

        usort($parentProviders, fn($a, $b) => strcasecmp($a->name ?? '', $b->name ?? ''));
        usort($parentRequests, fn($a, $b) => strcasecmp($a->name ?? '', $b->name ?? ''));

        return [$parentProviders, $parentRequests];
    }
}
