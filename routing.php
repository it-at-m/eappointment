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
 * Availability
 * -------------------------------------------------------------------------
 */
 \App::$slim->post('/availability/', '\BO\Zmsadmin\AvailabilityUpdate')
     ->setName("AvailabilityUpdate");

 \App::$slim->delete('/availability/{id:\d{1,11}}/', '\BO\Zmsadmin\AvailabilityDelete')
     ->setName("AvailabilityDelete");

 \App::$slim->get('/availability/day/', '\BO\Zmsadmin\Availability')
     ->setName("availability_day");

 \App::$slim->get('/availability/month/', '\BO\Zmsadmin\AvailabilityMonth')
     ->setName("availability_month");

 /*
  * ---------------------------------------------------------------------------
  * Calldisplay
  * -------------------------------------------------------------------------
  */
 \App::$slim->get('/calldisplay/', '\BO\Zmsadmin\Calldisplay')
     ->setName("calldisplay");


 /*
  * ---------------------------------------------------------------------------
  * Calendar stuff
  * -------------------------------------------------------------------------
  */
 \App::$slim->get('/calendar/{year:\d\d\d\d}/kw{weeknr:\d{1,2}}/', '\BO\Zmsadmin\CalendarWeek')
     ->setName("calendar_week");


 /*
  * ---------------------------------------------------------------------------
  * Counter
  * -------------------------------------------------------------------------
  */
\App::$slim->get('/counter/', '\BO\Zmsadmin\Counter')
    ->setName("counter");

\App::$slim->get('/counter/queueInfo/[{date:\d}/]', '\BO\Zmsadmin\CounterQueueInfo')
    ->setName("counter_queue_info");


/*
 * ---------------------------------------------------------------------------
 * Dayoff
 * -------------------------------------------------------------------------
 */
 \App::$slim->get('/dayoff/', '\BO\Zmsadmin\Dayoff')
     ->setName("dayoff");

\App::$slim->map(['GET', 'POST'], '/dayoff/{year:\d+}/', '\BO\Zmsadmin\DayoffByYear')
    ->setName("dayoffByYear");

\App::$slim->get('/dayoff/{year:\d+}/{id:\d+}/', '\BO\Zmsadmin\DayoffEdit')
    ->setName("dayoffEdit");


/*
 * ---------------------------------------------------------------------------
 * Department
 * -------------------------------------------------------------------------
 */
\App::$slim->map(['GET','POST'], '/department/{id:\d+}/', '\BO\Zmsadmin\Department')
    ->setName("department");

\App::$slim->map(['GET','POST'], '/department/{departmentId:\d+}/cluster/{clusterId:\d+}/', '\BO\Zmsadmin\Cluster')
    ->setName("cluster");

\App::$slim->get('/department/{departmentId:\d+}/cluster/{clusterId:\d+}/delete/', '\BO\Zmsadmin\ClusterDelete')
    ->setName("clusterDelete");

\App::$slim->get('/department/{departmentId:\d+}/cluster/', '\BO\Zmsadmin\DepartmentAddCluster')
    ->setName("departmentAddCluster");

\App::$slim->map(['GET','POST'], '/department/{id:\d+}/scope/', '\BO\Zmsadmin\DepartmentAddScope')
    ->setName("departmentAddScope");

\App::$slim->get('/department/delete/{id:\d+}/', '\BO\Zmsadmin\DepartmentDelete')
    ->setName("departmentDelete");

\App::$slim->get('/department/{id:\d+}/useraccount/', '\BO\Zmsadmin\UseraccountByDepartment')
    ->setName("useraccountByDepartment");

\App::$slim->post('/department/{id:\d+}/useraccount/logout/', '\BO\Zmsadmin\LogoutBySuperuser')
    ->setName("logoutBySuperuser");


/*
 * ---------------------------------------------------------------------------
 * Index
 * -------------------------------------------------------------------------
 */
\App::$slim->map(['GET','POST'], '/', '\BO\Zmsadmin\Index')
    ->setName("index");


/*
 * ---------------------------------------------------------------------------
 * Links
 * -------------------------------------------------------------------------
 */
\App::$slim->get('/links/', '\BO\Zmsadmin\Links')
    ->setName("links");


/*
 * ---------------------------------------------------------------------------
 * Logout
 * -------------------------------------------------------------------------
 */
\App::$slim->get('/logout/', '\BO\Zmsadmin\Logout')
    ->setName("logout");


/*
 * ---------------------------------------------------------------------------
 * Notification
 * -------------------------------------------------------------------------
 */
\App::$slim->get('/notification/', '\BO\Zmsadmin\Notification')
    ->setName("notification");


/*
 * ---------------------------------------------------------------------------
 * Organisation
 * -------------------------------------------------------------------------
 */
\App::$slim->map(['GET','POST'], '/organisation/{id:\d+}/department/', '\BO\Zmsadmin\OrganisationAddDepartment')
    ->setName("organisationAddDepartment");

\App::$slim->map(['GET','POST'], '/organisation/{id:\d+}/', '\BO\Zmsadmin\Organisation')
    ->setName("organisation");

\App::$slim->get('/organisation/delete/{id:\d+}/', '\BO\Zmsadmin\OrganisationDelete')
    ->setName("organisationDelete");


/*
 * ---------------------------------------------------------------------------
 * Owner
 * -------------------------------------------------------------------------
 */
\App::$slim->map(['GET','POST'], '/owner/{id:\d+}/organisation/', '\BO\Zmsadmin\OwnerAddOrganisation')
    ->setName("ownerAddOrganisation");

\App::$slim->get('/owner/', '\BO\Zmsadmin\OwnerOverview')
    ->setName("owner_overview");

\App::$slim->map(['GET','POST'], '/owner/{id:\d+}/', '\BO\Zmsadmin\Owner')
    ->setName("owner");

\App::$slim->map(['GET','POST'], '/owner/add/', '\BO\Zmsadmin\OwnerAdd')
    ->setName("owner_add");

\App::$slim->get('/owner/delete/{id:\d+}/', '\BO\Zmsadmin\OwnerDelete')
    ->setName("ownerDelete");


/*
 * ---------------------------------------------------------------------------
 * Process
 * -------------------------------------------------------------------------
 */
\App::$slim->map(['GET'], '/process/{id:\d+}/{authkey}/delete/', '\BO\Zmsadmin\ProcessDelete')
    ->setName("processDelete");


/*
 * ---------------------------------------------------------------------------
 * Profile
 * -------------------------------------------------------------------------
 */
\App::$slim->map(['GET', 'POST'], '/profile/', '\BO\Zmsadmin\Profile')
    ->setName("profile");


/*
 * ---------------------------------------------------------------------------
 * Scope
 * -------------------------------------------------------------------------
 */
\App::$slim->map(['GET','POST'], '/scope/{id:\d+}/', '\BO\Zmsadmin\Scope')
    ->setName("scope");

\App::$slim->get('/scope/{id:\d+}/pickup/', '\BO\Zmsadmin\Pickup')
    ->setName("pickup");

\App::$slim->get('/scope/{id:\d+}/process/{date:\d\d\d\d-\d\d-\d\d}/', '\BO\Zmsadmin\ScopeAppointmentsByDay')
    ->setName("scopeAppointmentsByDay");

\App::$slim->get('/scope/{id:\d+}/process/{date:\d\d\d\d-\d\d-\d\d}/xlsx/', '\BO\Zmsadmin\ScopeAppointmentsByDayXlsExport')
    ->setName("scopeAppointmentsByDayXls");

\App::$slim->get('/scope/delete/{id:\d+}/', '\BO\Zmsadmin\ScopeDelete')
    ->setName("scopeDelete");

\App::$slim->map(['GET','POST'], '/scope/{id:\d+}/pickup/handheld/', '\BO\Zmsadmin\PickupHandheld')
    ->setName("pickup_handheld");

\App::$slim->get('/scope/{id:\d+}/pickup/keyboard/', '\BO\Zmsadmin\PickupKeyboard')
    ->setName("pickup_keyboard");

\App::$slim->get('/scope/{id:\d+}/availability/day/{date:\d\d\d\d-\d\d-\d\d}/', '\BO\Zmsadmin\ScopeAvailabilityDay')
    ->setName("scopeAvailabilityDay");

\App::$slim->get('/scope/{id:\d+}/availability/day/{date:\d\d\d\d-\d\d-\d\d}/conflicts/', '\BO\Zmsadmin\ScopeAvailabilityDayConflicts')
    ->setName("scopeAvailabilityDayConflict");

\App::$slim->get('/scope/{id:\d+}/availability/month/[{date:\d\d\d\d-\d\d}/]', '\BO\Zmsadmin\ScopeAvailabilityMonth')
    ->setName("scopeAvailabilityMonth");

\App::$slim->map(['DELETE','POST'], '/scope/{id:\d+}/emergency/', '\BO\Zmsadmin\ScopeEmergency')
    ->setName("scope_emergency");

\App::$slim->post('/scope/{id:\d+}/emergency/respond/', '\BO\Zmsadmin\ScopeEmergencyResponse')
    ->setName('scope_emergency_response');

\App::$slim->get('/scope/ticketprinter/', '\BO\Zmsadmin\TicketprinterConfig')
    ->setName("ticketprinter");

\App::$slim->map(['GET', 'POST'], '/scope/{id:\d+}/ticketprinter/', '\BO\Zmsadmin\TicketprinterStatusByScope')
    ->setName("ticketprinterStatusByScope");

/*
 * ---------------------------------------------------------------------------
 * Search
 * -------------------------------------------------------------------------
 */
\App::$slim->get('/search/', '\BO\Zmsadmin\Search')
    ->setName("search");


/*
 * ---------------------------------------------------------------------------
 * Useraccount
 * -------------------------------------------------------------------------
 */
\App::$slim->get('/useraccount/', '\BO\Zmsadmin\Useraccount')
    ->setName("useraccount");

\App::$slim->map(['GET', 'POST'], '/useraccount/add/', '\BO\Zmsadmin\UseraccountAdd')
    ->setName("useraccountAdd");

\App::$slim->map(['GET','POST'], '/useraccount/{loginname}/', '\BO\Zmsadmin\UseraccountEdit')
    ->setName("useraccountEdit");

\App::$slim->get('/useraccount/delete/{loginname}/', '\BO\Zmsadmin\UseraccountDelete')
    ->setName("useraccountDelete");


/*
 * ---------------------------------------------------------------------------
 * Workstation
 * -------------------------------------------------------------------------
 */
\App::$slim->map(['GET','POST'], '/workstation/select/', '\BO\Zmsadmin\WorkstationSelect')
    ->setName("workstationSelect");

\App::$slim->get('/workstation/status/', '\BO\Zmsadmin\WorkstationStatus')
    ->setName("workstationStatus");

\App::$slim->get('/workstation/{loginName}/', '\BO\Zmsadmin\WorkstationLogin')
    ->setName("workstationLogin");

\App::$slim->get('/workstation/process/next/', '\BO\Zmsadmin\WorkstationProcessNext')
    ->setName("workstationProcessNext");

\App::$slim->get('/workstation/process/{id:\d+}/{authkey}/precall/', '\BO\Zmsadmin\WorkstationProcessPreCall')
    ->setName("workstationProcessPreCall");

\App::$slim->get('/workstation/process/{id:\d+}/called/', '\BO\Zmsadmin\WorkstationProcessCalled')
    ->setName("workstationProcessCalled");

\App::$slim->get('/workstation/process/processing/', '\BO\Zmsadmin\WorkstationProcessProcessing')
    ->setName("workstationProcessProcessing");

\App::$slim->map(['GET','POST'], '/workstation/process/finished/', '\BO\Zmsadmin\WorkstationProcessFinished')
    ->setName("workstationProcessFinished");

\App::$slim->get('/workstation/call/{waitingnumber:\d+}/', '\BO\Zmsadmin\WorkstationCallProcess')
    ->setName("workstationCallProcess");

\App::$slim->get('/workstation/process/cancel/', '\BO\Zmsadmin\WorkstationProcessCancel')
    ->setName("workstationProcessCancel");

\App::$slim->get('/workstation/process/cancel/next/', '\BO\Zmsadmin\WorkstationProcessCancelNext')
    ->setName("workstationProcessCancelNext");

\App::$slim->get('/workstation/process/callbutton/', '\BO\Zmsadmin\WorkstationProcess')
    ->setName("workstationProcessCallButton");

\App::$slim->map(['GET','POST'], '/workstation/', '\BO\Zmsadmin\Workstation')
    ->setName("workstation");

/*
 * ---------------------------------------------------------------------------
 * Other Ajax Components
 * -------------------------------------------------------------------------
 */
\App::$slim->get('/calendarPage/[{date:\d}/]', '\BO\Zmsadmin\CalendarPage')
    ->setName("counter_calendar_page");

\App::$slim->get('/appointmentForm/[{date:\d}/]', '\BO\Zmsadmin\AppointmentForm')
    ->setName("appointment_form");

\App::$slim->get('/queueTable/[{date:\d}/]', '\BO\Zmsadmin\QueueTable')
    ->setName("queue_table");


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
\App::$slim->get('/testpage/', '\BO\Zmsadmin\Testpage')
    ->setName("testpage");

\App::$slim->get('/changelog/', '\BO\Zmsadmin\Changelog')
    ->setName("changelog");

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
        error_log('test');
        return function (RequestInterface $request, ResponseInterface $response, \Exception $exception) {
            return \BO\Zmsadmin\Helper\TwigExceptionHandler::withHtml($request, $response, $exception);
        };
    });
