<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Availability\Api;

use App;
use BO\Mellon\Validator;
use BO\Slim\Render;
use BO\Zmsbackend\Availability\Service\AvailabilityHistory as AvailabilityHistoryService;
use BO\Zmsbackend\Helper\User;
use BO\Zmsentities\Exception\UserAccountMissingLogin;
use BO\Zmsentities\Exception\UserAccountMissingRights;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AvailabilityHistoryByScope extends \BO\Zmsbackend\Api\BaseController
{
    /**
     * @SuppressWarnings(Param)
     */
    #[\Override]
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $this->assertTechAdminAccess($request);

        $scope = (new \BO\Zmsbackend\Scope\Service\Scope())->readEntity($args['id'], 0);
        if (!$scope || !$scope->hasId()) {
            throw new \BO\Zmsbackend\Scope\Exception\ScopeNotFound();
        }

        [$from, $to] = $this->resolveDateRange();
        $availabilityId = Validator::param('availabilityId')->isNumber()->getValue();
        $action = Validator::param('action')->isString()->getValue();
        if ($action !== null && $action !== '') {
            if (
                !in_array(
                    $action,
                    \BO\Zmsentities\AvailabilityHistory::ACTIONS,
                    true
                )
            ) {
                throw new \BO\Zmsbackend\Exception\BadRequest(
                    'Parameter action must be one of: created, updated, deleted, dldb_slot_update'
                );
            }
        } else {
            $action = null;
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = (new AvailabilityHistoryService())->readListByScopeId(
            (int) $scope->getId(),
            $from,
            $to,
            $availabilityId ? (int) $availabilityId : null,
            $action
        );
        $message->setUpdatedMetaData();
        // Empty history is a valid list. setUpdatedMetaData() otherwise maps it to
        // meta.error = "Not found", which zmsadmin/zmsclient turns into HTTP 500.
        $message->statuscode = 200;
        $message->meta->error = false;
        $message->meta->message = '';

        $response = Render::withLastModified($response, time(), '0');
        return Render::withJson($response, $message, 200);
    }

    protected function assertTechAdminAccess(RequestInterface $request): void
    {
        new User($request, 1);
        $useraccount = User::readWorkstation()->getUseraccount();
        if (!$useraccount->hasId()) {
            throw new UserAccountMissingLogin();
        }
        if (!$useraccount->isSuperUser() && !$useraccount->hasRole('system_admin')) {
            throw new UserAccountMissingRights();
        }
    }

    protected function resolveDateRange(): array
    {
        $fromParam = Validator::param('from')->isString()->getValue();
        $toParam = Validator::param('to')->isString()->getValue();

        // History rows use MySQL CURRENT_TIMESTAMP (wall clock). Do not use App::$now:
        // ZMS_TIMEADJUST on remote freezes that clock, so GET misses real inserts.
        $timezone = App::$now instanceof \DateTimeInterface
            ? App::$now->getTimezone()
            : new \DateTimeZone('Europe/Berlin');
        $now = new \DateTimeImmutable('now', $timezone);
        $to = $toParam
            ? $this->parseDateParam($toParam, true)
            : $now->setTime(23, 59, 59);
        $from = $fromParam
            ? $this->parseDateParam($fromParam, false)
            : $to->modify('-' . AvailabilityHistoryService::DEFAULT_RETENTION_DAYS . ' days')->setTime(0, 0, 0);

        if ($from > $to) {
            throw new \BO\Zmsbackend\Exception\BadRequest('Parameter from must be before or equal to to');
        }

        return [$from, $to];
    }

    protected function parseDateParam(string $value, bool $endOfDay): \DateTimeImmutable
    {
        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $value);
        $errors = \DateTimeImmutable::getLastErrors();
        if (
            !$date
            || ($errors['warning_count'] ?? 0) > 0
            || ($errors['error_count'] ?? 0) > 0
        ) {
            throw new \BO\Zmsbackend\Exception\BadRequest('Date parameters must use Y-m-d format');
        }

        return $endOfDay ? $date->setTime(23, 59, 59) : $date->setTime(0, 0, 0);
    }
}
