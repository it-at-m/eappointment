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

 /*
  * ---------------------------------------------------------------------------
  * Calldisplay
  * -------------------------------------------------------------------------
  */
 \App::$slim->get('/calldisplay/', '\BO\Zmsadmin\Calldisplay')
     ->setName("calldisplay");

 /*
  * ---------------------------------------------------------------------------
  * Calendar
  * -------------------------------------------------------------------------
  */
 \App::$slim->get('/calendar/{year:\d\d\d\d}/{weeknr:\d{1,2}}/', '\BO\Zmsadmin\CalendarWeek')
     ->setName("calendar_week");


 /*
  * ---------------------------------------------------------------------------
  * Config
  * -------------------------------------------------------------------------
  */
 \App::$slim->get('/config/', '\BO\Zmsadmin\ConfigInfo')
     ->setName("configinfo");

 /*
  * ---------------------------------------------------------------------------
  * Counter
  * -------------------------------------------------------------------------
  */
\App::$slim->get('/counter/', '\BO\Zmsadmin\Counter')
    ->setName("counter");

\App::$slim->get('/counter/queueInfo/', '\BO\Zmsadmin\CounterQueueInfo')
    ->setName("counter_queue_info");

\App::$slim->get('/counter/appointmentTimes/', '\BO\Zmsadmin\CounterAppointmentTimes')
    ->setName("counter_appointment_times");

/*
 * ---------------------------------------------------------------------------
 * Dayoff
 * -------------------------------------------------------------------------
 */
 \App::$slim->get('/dayoff/', '\BO\Zmsadmin\Dayoff')
     ->setName("dayoff");

\App::$slim->map(['GET', 'POST'], '/dayoff/{year:\d+}/', '\BO\Zmsadmin\DayoffByYear')
    ->setName("dayoffByYear");


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

\App::$slim->map(['GET','POST'], '/department/{departmentId:\d+}/cluster/', '\BO\Zmsadmin\DepartmentAddCluster')
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
 * Login
 * -------------------------------------------------------------------------
 */
\App::$slim->map(['GET','POST'], '/', '\BO\Zmsadmin\Index')
    ->setName("index");

\App::$slim->get('/workstation/quicklogin/', '\BO\Zmsadmin\QuickLogin')
    ->setName("quickLogin");

/*
 * ---------------------------------------------------------------------------
 * Logout
 * -------------------------------------------------------------------------
 */
\App::$slim->get('/logout/', '\BO\Zmsadmin\Logout')
    ->setName("logout");

/*
 * ---------------------------------------------------------------------------
 * Mail
 * -------------------------------------------------------------------------
 */
\App::$slim->map(['GET', 'POST'], '/mail/', '\BO\Zmsadmin\Mail')
    ->setName("mail");

/*
 * ---------------------------------------------------------------------------
 * Notification
 * -------------------------------------------------------------------------
 */

\App::$slim->map(['GET', 'POST'], '/notification/', '\BO\Zmsadmin\Notification')
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
 * Pickup
 * -------------------------------------------------------------------------
 */

 \App::$slim->get('/pickup/', '\BO\Zmsadmin\Pickup')
     ->setName("pickup");

 \App::$slim->get('/pickup/queue/', '\BO\Zmsadmin\PickupQueue')
     ->setName("pickup_queue");

 \App::$slim->delete('/pickup/delete/{id:\d+}/', '\BO\Zmsadmin\PickupDelete')
     ->setName("pickup_delete");

 \App::$slim->map(['GET','POST'], '/pickup/handheld/', '\BO\Zmsadmin\PickupHandheld')
     ->setName("pickup_handheld");

 \App::$slim->get('/pickup/keyboard/', '\BO\Zmsadmin\PickupKeyboard')
     ->setName("pickup_keyboard");

 \App::$slim->get('/pickup/spreadsheet/', '\BO\Zmsadmin\PickupSpreadSheet')
     ->setName("pickup_spreadsheet");

 \App::$slim->get('/pickup/mail/', '\BO\Zmsadmin\PickupMail')
     ->setName("pickup_mail");

 \App::$slim->get('/pickup/notification/', '\BO\Zmsadmin\PickupNotification')
     ->setName("pickup_notification");

 \App::$slim->get('/pickup/call/{id:\d+}/', '\BO\Zmsadmin\PickupCall')
     ->setName("pickup_call");

 \App::$slim->get('/pickup/call/cancel/', '\BO\Zmsadmin\PickupCallCancel')
     ->setName("pickup_call_cancel");

/*
 * ---------------------------------------------------------------------------
 * Process
 * -------------------------------------------------------------------------
 */

 \App::$slim->post('/process/reserve/', '\BO\Zmsadmin\ProcessReserve')
     ->setName("processReserve");

 \App::$slim->map(['GET','POST'], '/process/queue/', '\BO\Zmsadmin\ProcessQueue')
     ->setName("processQueue");

 \App::$slim->get('/process/queue/reset/', '\BO\Zmsadmin\ProcessQueueReset')
     ->setName("processQueueReset");

 \App::$slim->post('/process/{id:\d+}/save/', '\BO\Zmsadmin\ProcessSave')
     ->setName("processSave");

 \App::$slim->get('/process/{id:\d+}/delete/', '\BO\Zmsadmin\ProcessDelete')
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

\App::$slim->get('/scope/{id:\d+}/process/{date:\d\d\d\d-\d\d-\d\d}/', '\BO\Zmsadmin\ScopeAppointmentsByDay')
    ->setName("scopeAppointmentsByDay");

\App::$slim->get('/scope/{id:\d+}/process/{date:\d\d\d\d-\d\d-\d\d}/xlsx/', '\BO\Zmsadmin\ScopeAppointmentsByDayXlsExport')
    ->setName("scopeAppointmentsByDayXls");

\App::$slim->get('/scope/delete/{id:\d+}/', '\BO\Zmsadmin\ScopeDelete')
    ->setName("scopeDelete");

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
 * Source
 * -------------------------------------------------------------------------
 */
\App::$slim->get('/source/', '\BO\Zmsadmin\SourceIndex')
    ->setName("sourceindex");

\App::$slim->map(['GET','POST'], '/source/{name}/', '\BO\Zmsadmin\SourceEdit')
    ->setName("sourceEdit");

\App::$slim->map(['POST'], '/source/delete/{loginname}/', '\BO\Zmsadmin\SourceDelete')
    ->setName("sourceDelete");


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

\App::$slim->get('/workstation/process/{id:\d+}/precall/', '\BO\Zmsadmin\WorkstationProcessPreCall')
    ->setName("workstationProcessPreCall");

\App::$slim->get('/workstation/process/{id:\d+}/called/', '\BO\Zmsadmin\WorkstationProcessCalled')
    ->setName("workstationProcessCalled");

\App::$slim->get('/workstation/process/processing/', '\BO\Zmsadmin\WorkstationProcessProcessing')
    ->setName("workstationProcessProcessing");

\App::$slim->map(['GET','POST'], '/workstation/process/finished/', '\BO\Zmsadmin\WorkstationProcessFinished')
    ->setName("workstationProcessFinished");

\App::$slim->get('/workstation/call/{id:\d+}/', '\BO\Zmsadmin\WorkstationProcessCall')
    ->setName("workstationProcessCall");

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

\App::$slim->map(['GET', 'POST'], '/appointmentForm/', '\BO\Zmsadmin\AppointmentForm')
    ->setName("appointment_form");

\App::$slim->get('/appointmentForm/processlist/free/', '\BO\Zmsadmin\AppointmentFormFreeProcessList')
    ->setName("appointment_form_free_processes");

\App::$slim->get('/appointmentForm/buttons/', '\BO\Zmsadmin\AppointmentFormButtons')
    ->setName("appointment_form_buttons");

\App::$slim->get('/queueTable/', '\BO\Zmsadmin\QueueTable')
    ->setName("queue_table");

\App::$slim->get('/dialog/', '\BO\Zmsadmin\Helper\DialogHandler')
    ->setName("dialogHandler");

\App::$slim->get('/provider/{source}/', '\BO\Zmsadmin\Helper\ProviderHandler')
->setName("providerHandler");

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

\App::$slim->getContainer()->offsetSet('notFoundHandler', function ($container) {
    return function (RequestInterface $request, ResponseInterface $response) {
        return \BO\Slim\Render::withHtml($response, 'page/404.twig');
    };
});

\App::$slim->getContainer()->offsetSet('errorHandler', function ($container) {
    return new \BO\Zmsadmin\Helper\TwigExceptionHandler($container);
});
\App::$slim->getContainer()->offsetSet('phpErrorHandler', function ($container) {
    return new \BO\Zmsadmin\Helper\TwigExceptionHandler($container);
});
