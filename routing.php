<?php
// @codingStandardsIgnoreFile
/**
 *
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
use BO\Slim\Helper;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

/*
 * ---------------------------------------------------------------------------
 * html, basic routes
 * -------------------------------------------------------------------------
 */

\App::$slim->map([
    'GET','POST'
], '/', '\BO\Zmsadmin\Index')
    ->setName("index");

\App::$slim->map(['GET','POST'], '/workstation/select/', '\BO\Zmsadmin\WorkstationSelect')
    ->setName("workstationSelect");

\App::$slim->get('/logout/', '\BO\Zmsadmin\Logout')
    ->setName("logout");

\App::$slim->get('/workstation/status/', '\BO\Zmsadmin\WorkstationStatus')
    ->setName("workstationStatus");

\App::$slim->get('/workstation/{loginName}/', '\BO\Zmsadmin\WorkstationLogin')
    ->setName("workstationLogin");

\App::$slim->get('/workstation/process/{id:\d+}/precall/', '\BO\Zmsadmin\WorkstationClientPreCall')
    ->setName("workstationClientPreCall");

\App::$slim->get('/workstation/process/{id:\d+}/called/', '\BO\Zmsadmin\WorkstationClientCalled')
    ->setName("workstationClientCalled");

\App::$slim->get('/workstation/process/{id:\d+}/processed/', '\BO\Zmsadmin\WorkstationClientProcessed')
    ->setName("workstationClientProcessed");

\App::$slim->get('/workstation/process/{id:\d+}/', '\BO\Zmsadmin\WorkstationClientActive')
    ->setName("workstationClientActive");

\App::$slim->map([
    'GET','POST'
], '/workstation/', '\BO\Zmsadmin\Workstation')
    ->setName("workstation");

\App::$slim->get('/counter/', '\BO\Zmsadmin\Counter')
    ->setName("counter");

\App::$slim->map([
    'GET','POST'
], '/scope/{id:\d+}/', '\BO\Zmsadmin\Scope')
    ->setName("scope");

\App::$slim->get('/scope/{id:\d+}/pickup/', '\BO\Zmsadmin\Pickup')
    ->setName("pickup");

\App::$slim->map([
    'GET','POST'
], '/department/{id:\d+}/scope/', '\BO\Zmsadmin\DepartmentAddScope')
    ->setName("departmentAddScope");

\App::$slim->get('/scope/delete/{id:\d+}/', '\BO\Zmsadmin\ScopeDelete')
    ->setName("scopeDelete");

\App::$slim->map([
    'GET','POST'
], '/scope/{id:\d+}/pickup/handheld/', '\BO\Zmsadmin\PickupHandheld')
    ->setName("pickup_handheld");

\App::$slim->get('/scope/{id:\d+}/pickup/keyboard/', '\BO\Zmsadmin\PickupKeyboard')
    ->setName("pickup_keyboard");

\App::$slim->get('/scope/{id:\d+}/availability/day/{date:\d\d\d\d-\d\d-\d\d}/', '\BO\Zmsadmin\ScopeAvailabilityDay')
    ->setName("scopeAvailabilityDay");

\App::$slim->get('/scope/{id:\d+}/availability/day/{date:\d\d\d\d-\d\d-\d\d}/conflicts/', '\BO\Zmsadmin\ScopeAvailabilityDayConflicts')
    ->setName("scopeAvailabilityDayConflict");

\App::$slim->get('/scope/{id:\d+}/availability/month/[{date:\d\d\d\d-\d\d}/]', '\BO\Zmsadmin\ScopeAvailabilityMonth')
    ->setName("scopeAvailabilityMonth");

\App::$slim->map([
    'DELETE','POST'
], '/scope/{id:\d+}/emergency/', '\BO\Zmsadmin\ScopeEmergency')
    ->setName("scope_emergency");

\App::$slim->post('/scope/{id:\d+}/emergency/respond/', '\BO\Zmsadmin\ScopeEmergencyResponse')
    ->setName('scope_emergency_response');

\App::$slim->map([
    'GET','POST'
], '/department/{id:\d+}/', '\BO\Zmsadmin\Department')
    ->setName("department");

\App::$slim->map([
    'GET','POST'
], '/department/{departmentId:\d+}/cluster/{clusterId:\d+}/', '\BO\Zmsadmin\Cluster')
    ->setName("cluster");

\App::$slim->get('/department/{departmentId:\d+}/cluster/{clusterId:\d+}/delete/', '\BO\Zmsadmin\ClusterDelete')
    ->setName("clusterDelete");

\App::$slim->get('/department/{departmentId:\d+}/cluster/', '\BO\Zmsadmin\DepartmentAddCluster')
    ->setName("departmentAddCluster");

\App::$slim->map([
    'GET','POST'
], '/organisation/{id:\d+}/department/', '\BO\Zmsadmin\OrganisationAddDepartment')
    ->setName("organisationAddDepartment");

\App::$slim->get('/department/delete/{id:\d+}/', '\BO\Zmsadmin\DepartmentDelete')
    ->setName("departmentDelete");

\App::$slim->map([
    'GET','POST'
], '/organisation/{id:\d+}/', '\BO\Zmsadmin\Organisation')
    ->setName("organisation");

\App::$slim->map([
    'GET','POST'
], '/owner/{id:\d+}/organisation/', '\BO\Zmsadmin\OwnerAddOrganisation')
    ->setName("ownerAddOrganisation");

\App::$slim->get('/organisation/delete/{id:\d+}/', '\BO\Zmsadmin\OrganisationDelete')
    ->setName("organisationDelete");

\App::$slim->get('/owner/', '\BO\Zmsadmin\OwnerOverview')
    ->setName("owner_overview");

\App::$slim->map([
    'GET','POST'
], '/owner/{id:\d+}/', '\BO\Zmsadmin\Owner')
    ->setName("owner");

\App::$slim->map([
    'GET','POST'
], '/owner/add/', '\BO\Zmsadmin\OwnerAdd')
    ->setName("owner_add");

\App::$slim->get('/owner/delete/{id:\d+}/', '\BO\Zmsadmin\OwnerDelete')
    ->setName("ownerDelete");

\App::$slim->post('/availability/',
                  '\BO\Zmsadmin\AvailabilityUpdate')
    ->setName("AvailabilityUpdate");

\App::$slim->delete('/availability/{id:\d{1,11}}/',
                    '\BO\Zmsadmin\AvailabilityDelete')
    ->setName("AvailabilityDelete");

\App::$slim->get('/availability/day/', '\BO\Zmsadmin\Availability')
    ->setName("availability_day");

\App::$slim->get('/availability/month/', '\BO\Zmsadmin\AvailabilityMonth')
    ->setName("availability_month");

\App::$slim->get('/calendar/[{date:\d}/]', '\BO\Zmsadmin\CalendarPage')
    ->setName("calendar_page");

\App::$slim->get('/calendar/{year:\d\d\d\d}/kw{weeknr:\d{1,2}}/', '\BO\Zmsadmin\CalendarWeek')
    ->setName("calendar_week");

\App::$slim->map(['GET', 'POST'], '/profile/', '\BO\Zmsadmin\Profile')
    ->setName("profile");

\App::$slim->get('/useraccount/', '\BO\Zmsadmin\Useraccount')
    ->setName("useraccount");

\App::$slim->map(['GET', 'POST'], '/useraccount/add/', '\BO\Zmsadmin\UseraccountAdd')
    ->setName("useraccountAdd");

\App::$slim->get('/department/{id:\d+}/useraccount/', '\BO\Zmsadmin\UseraccountByDepartment')
    ->setName("useraccountByDepartment");

\App::$slim->map([
    'GET','POST'
], '/useraccount/{loginname}/', '\BO\Zmsadmin\UseraccountEdit')
    ->setName("useraccountEdit");

\App::$slim->get('/useraccount/delete/{loginname}/', '\BO\Zmsadmin\UseraccountDelete')
    ->setName("useraccountDelete");

\App::$slim->get('/calldisplay/', '\BO\Zmsadmin\Calldisplay')
    ->setName("calldisplay");

\App::$slim->get('/scope/ticketprinter/', '\BO\Zmsadmin\TicketprinterConfig')
    ->setName("ticketprinter");

\App::$slim->map(
    ['GET', 'POST'],
    '/scope/{id:\d+}/ticketprinter/', '\BO\Zmsadmin\TicketprinterStatusByScope')
    ->setName("ticketprinterStatusByScope");

\App::$slim->get('/notification/', '\BO\Zmsadmin\Notification')
    ->setName("notification");

\App::$slim->get('/links/', '\BO\Zmsadmin\Links')
    ->setName("links");

\App::$slim->get('/search/', '\BO\Zmsadmin\Search')
    ->setName("search");

\App::$slim->get('/dayoff/', '\BO\Zmsadmin\Dayoff')
    ->setName("dayoff");

\App::$slim->map([
    'GET', 'POST'
], '/dayoff/{year:\d+}/', '\BO\Zmsadmin\DayoffByYear')
    ->setName("dayoffByYear");

\App::$slim->get('/dayoff/{year:\d+}/{id:\d+}/', '\BO\Zmsadmin\DayoffEdit')
    ->setName("dayoffEdit");

\App::$slim->get('/testpage/', '\BO\Zmsadmin\Testpage')
    ->setName("testpage");

\App::$slim->get('/changelog/', '\BO\Zmsadmin\Changelog')
    ->setName("changelog");

// \App::$slim->get('/dienstleistung{service_id:\d+}', '\BO\D115Mandant\Controller\ServiceDetail')
// ->setName("servicedetail");

/*
 * ---------------------------------------------------------------------------
 * externals
 * -------------------------------------------------------------------------
 */

// external link to stadplan
\App::$slim->get('http://www.Berlin.de/stadtplan/', function () {
})
    ->setName("citymap");

/*
 * ---------------------------------------------------------------------------
 * maintenance
 * -------------------------------------------------------------------------
 */

\App::$slim->get('/status/', '\BO\Zmsadmin\Status')
    ->setName("status");

\App::$slim->get('/healthcheck/', '\BO\Zmsadmin\Healthcheck')
    ->setName("healthcheck");

\App::$slim->getContainer()
    ->offsetSet('notFoundHandler',
    function ($container) {
        return function (RequestInterface $request, ResponseInterface $response) {
            return \BO\Slim\Render::withHtml($response, 'page/404.twig');
        };
    });

\App::$slim->getContainer()
    ->offsetSet('errorHandler',
    function ($container) {
        return function (RequestInterface $request, ResponseInterface $response, \Exception $exception) {
            return \BO\Zmsadmin\Helper\TwigExceptionHandler::withHtml($request, $response, $exception);
        };
    });
