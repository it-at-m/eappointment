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
use Slim\Routing\RouteCollectorProxy;

/*
 * ---------------------------------------------------------------------------
 * Availability
 * -------------------------------------------------------------------------
 */
\App::$slim->post('/availability/', \BO\Zmsadmin\AvailabilityUpdateList::class)
    ->setName("AvailabilityUpdateList");

\App::$slim->post('/availability/slots/', \BO\Zmsadmin\Helper\AvailabilityCalcSlots::class)
    ->setName("AvailabilityCalcSlots");

\App::$slim->get('/availability/delete/{id:\d{1,11}}/', \BO\Zmsadmin\AvailabilityDelete::class)
    ->setName("AvailabilityDelete");

\App::$slim->post('/availability/save/{id:\d{1,11}}/', \BO\Zmsadmin\AvailabilityUpdateSingle::class)
    ->setName("AvailabilityUpdateSingle");

\App::$slim->post('/availability/conflicts/', \BO\Zmsadmin\AvailabilityConflicts::class)
    ->setName("AvailabilityConflicts");

\App::$slim->get('/scope/{id:\d+}/availability/', \BO\Zmsadmin\AvailabilityListByScope::class)
    ->setName("AvailabilityListByScope");

/*
 * ---------------------------------------------------------------------------
 * Calldisplay
 * -------------------------------------------------------------------------
 */
\App::$slim->get('/calldisplay/', \BO\Zmsadmin\Calldisplay::class)
    ->setName("calldisplay");

/*
 * ---------------------------------------------------------------------------
 * Calendar
 * -------------------------------------------------------------------------
 */
\App::$slim->get('/calendar/{year:\d\d\d\d}/{weeknr:\d{1,2}}/', \BO\Zmsadmin\CalendarWeek::class)
    ->setName("calendar_week");


/*
 * ---------------------------------------------------------------------------
 * Config
 * -------------------------------------------------------------------------
 */
\App::$slim->map(['GET', 'POST'], '/config/', \BO\Zmsadmin\ConfigInfo::class)
    ->setName("configinfo");

\App::$slim->get('/mailtemplates/{scopeId:\d+}/', \BO\Zmsadmin\MailTemplates::class)
    ->setName("mailtemplatesScope");

\App::$slim->get('/mailtemplates/', \BO\Zmsadmin\MailTemplates::class)
    ->setName("mailtemplates");

\App::$slim->post('/mailtemplates/{id:\d+}/', \BO\Zmsadmin\Helper\MailTemplateHandler::class)
    ->setName("MailTemplateHandler");

\App::$slim->post('/mailtemplates/deleteCustomization/{id:\d+}/', \BO\Zmsadmin\Helper\MailTemplateDeleteCustomization::class)
    ->setName("MailTemplateDeleteCustomization");

\App::$slim->post('/mailtemplates/createCustomization/{id:\d+}/', \BO\Zmsadmin\Helper\MailTemplateCreateCustomization::class)
    ->setName("MailTemplateCreateCustomization");

\App::$slim->get('/mailtemplates/dummyPreview/{mailStatus}/', \BO\Zmsadmin\Helper\MailTemplateDummyPreview::class)
    ->setName("MailTemplateDummyPreview");

\App::$slim->post('/mailtemplates/previewEmail/{mailStatus}/{scopeId:\d+}/', \BO\Zmsadmin\Helper\MailTemplatePreviewMail::class)
    ->setName("MailTemplatePreviewMail");


/*
 * ---------------------------------------------------------------------------
 * Counter
 * -------------------------------------------------------------------------
 */
\App::$slim->get('/counter/', \BO\Zmsadmin\Counter::class)
    ->setName("counter");

\App::$slim->get('/counter/queueInfo/', \BO\Zmsadmin\CounterQueueInfo::class)
    ->setName("counter_queue_info");

\App::$slim->get('/counter/appointmentTimes/', \BO\Zmsadmin\CounterAppointmentTimes::class)
    ->setName("counter_appointment_times");

/*
 * ---------------------------------------------------------------------------
 * Dayoff
 * -------------------------------------------------------------------------
 */
\App::$slim->get('/dayoff/', \BO\Zmsadmin\Dayoff::class)
    ->setName("dayoff");

\App::$slim->map(['GET', 'POST'], '/dayoff/{year:\d+}/', \BO\Zmsadmin\DayoffByYear::class)
    ->setName("dayoffByYear");


/*
 * ---------------------------------------------------------------------------
 * Department
 * -------------------------------------------------------------------------
 */
\App::$slim->map(['GET', 'POST'], '/department/{id:\d+}/', \BO\Zmsadmin\Department::class)
    ->setName("department");

\App::$slim->map(['GET', 'POST'], '/department/{departmentId:\d+}/cluster/{clusterId:\d+}/', \BO\Zmsadmin\Cluster::class)
    ->setName("cluster");

\App::$slim->get('/department/{departmentId:\d+}/cluster/{clusterId:\d+}/delete/', \BO\Zmsadmin\ClusterDelete::class)
    ->setName("clusterDelete");

\App::$slim->map(['GET', 'POST'], '/department/{departmentId:\d+}/cluster/', \BO\Zmsadmin\DepartmentAddCluster::class)
    ->setName("departmentAddCluster");

\App::$slim->map(['GET', 'POST'], '/department/{id:\d+}/scope/', \BO\Zmsadmin\DepartmentAddScope::class)
    ->setName("departmentAddScope");

\App::$slim->get('/department/delete/{id:\d+}/', \BO\Zmsadmin\DepartmentDelete::class)
    ->setName("departmentDelete");

\App::$slim->get('/department/{id:\d+}/useraccount/', \BO\Zmsadmin\UseraccountByDepartment::class)
    ->setName("useraccountByDepartment");

\App::$slim->get('/role/{level:\d+}/useraccount/', \BO\Zmsadmin\UseraccountByRole::class)
    ->setName("useraccountByRole");

\App::$slim->get('/useraccount/search/', \BO\Zmsadmin\UseraccountSearch::class)
    ->setName("useraccountSearch");

\App::$slim->post('/department/{id:\d+}/useraccount/logout/', \BO\Zmsadmin\LogoutBySuperuser::class)
    ->setName("logoutBySuperuser");


/*
 * ---------------------------------------------------------------------------
 * Login
 * -------------------------------------------------------------------------
 */
\App::$slim->map(['GET', 'POST'], '/', \BO\Zmsadmin\Index::class)
    ->setName("index");

\App::$slim->get('/workstation/quicklogin/', \BO\Zmsadmin\QuickLogin::class)
    ->setName("quickLogin");

\App::$slim->map(['GET', 'POST'], '/oidc/', \BO\Zmsadmin\Oidc::class)
    ->setName("oidc")->add(new \BO\Slim\Middleware\OAuthMiddleware('login'));



/*
 * ---------------------------------------------------------------------------
 * Logout
 * -------------------------------------------------------------------------
 */
\App::$slim->get('/logout/', \BO\Zmsadmin\Logout::class)
    ->setName("logout");

/*
 * ---------------------------------------------------------------------------
 * Mail
 * -------------------------------------------------------------------------
 */
\App::$slim->map(['GET', 'POST'], '/mail/', \BO\Zmsadmin\Mail::class)
    ->setName("mail");

/*
 * ---------------------------------------------------------------------------
 * Notification
 * -------------------------------------------------------------------------
 */

\App::$slim->map(['GET', 'POST'], '/notification/', \BO\Zmsadmin\Notification::class)
    ->setName("notification");

/*
 * ---------------------------------------------------------------------------
 * Organisation
 * -------------------------------------------------------------------------
 */
\App::$slim->map(['GET', 'POST'], '/organisation/{id:\d+}/department/', \BO\Zmsadmin\OrganisationAddDepartment::class)
    ->setName("organisationAddDepartment");

\App::$slim->map(['GET', 'POST'], '/organisation/{id:\d+}/', \BO\Zmsadmin\Organisation::class)
    ->setName("organisation");

\App::$slim->get('/organisation/delete/{id:\d+}/', \BO\Zmsadmin\OrganisationDelete::class)
    ->setName("organisationDelete");


/*
 * ---------------------------------------------------------------------------
 * Owner
 * -------------------------------------------------------------------------
 */
\App::$slim->map(['GET', 'POST'], '/owner/{id:\d+}/organisation/', \BO\Zmsadmin\OwnerAddOrganisation::class)
    ->setName("ownerAddOrganisation");

\App::$slim->get('/owner/', '\BO\Zmsadmin\OwnerOverview')
    ->setName("owner_overview");

\App::$slim->map(['GET', 'POST'], '/owner/{id:\d+}/', \BO\Zmsadmin\Owner::class)
    ->setName("owner");

\App::$slim->map(['GET', 'POST'], '/owner/add/', \BO\Zmsadmin\OwnerAdd::class)
    ->setName("owner_add");

\App::$slim->get('/owner/delete/{id:\d+}/', \BO\Zmsadmin\OwnerDelete::class)
    ->setName("ownerDelete");

/*
 * ---------------------------------------------------------------------------
 * Pickup
 * -------------------------------------------------------------------------
 */

\App::$slim->group('/pickup', function (RouteCollectorProxy $group) {
    $group->get('/', \BO\Zmsadmin\Pickup::class)
        ->setName("pickup");

    $group->get('/queue/', \BO\Zmsadmin\PickupQueue::class)
        ->setName("pickup_queue");

    $group->get('/delete/{id:\d+}/', \BO\Zmsadmin\PickupDelete::class)
        ->setName("pickup_delete");

    $group->map(['GET', 'POST'], '/handheld/', \BO\Zmsadmin\PickupHandheld::class)
        ->setName("pickup_handheld");

    $group->get('/keyboard/', \BO\Zmsadmin\PickupKeyboard::class)
        ->setName("pickup_keyboard");

    $group->get('/spreadsheet/', \BO\Zmsadmin\PickupSpreadSheet::class)
        ->setName("pickup_spreadsheet");

    $group->get('/mail/', \BO\Zmsadmin\PickupMail::class)
        ->setName("pickup_mail");

    $group->get('/notification/', \BO\Zmsadmin\PickupNotification::class)
        ->setName("pickup_notification");

    $group->get('/call/{id:\d+}/', \BO\Zmsadmin\PickupCall::class)
        ->setName("pickup_call");

    $group->get('/call/cancel/', \BO\Zmsadmin\PickupCallCancel::class)
        ->setName("pickup_call_cancel");
});

/*
 * ---------------------------------------------------------------------------
 * Process
 * -------------------------------------------------------------------------
 */

\App::$slim->post('/process/reserve/', \BO\Zmsadmin\ProcessReserve::class)
    ->setName("processReserve");

\App::$slim->post('/process/change/', \BO\Zmsadmin\ProcessChange::class)
    ->setName("processChange");

\App::$slim->map(['GET', 'POST'], '/process/queue/', \BO\Zmsadmin\ProcessQueue::class)
    ->setName("processQueue");

\App::$slim->get('/process/queue/reset/', \BO\Zmsadmin\ProcessQueueReset::class)
    ->setName("processQueueReset");

\App::$slim->post('/process/{id:\d+}/save/', \BO\Zmsadmin\ProcessSave::class)
    ->setName("processSave");

\App::$slim->get('/process/{id:\d+}/delete/', \BO\Zmsadmin\ProcessDelete::class)
    ->setName("processDelete");


/*
 * ---------------------------------------------------------------------------
 * Profile
 * -------------------------------------------------------------------------
 */
\App::$slim->map(['GET', 'POST'], '/profile/', \BO\Zmsadmin\Profile::class)
    ->setName("profile");


/*
 * ---------------------------------------------------------------------------
 * Scope
 * -------------------------------------------------------------------------
 */

\App::$slim->group('/scope', function (RouteCollectorProxy $group) {
    $group->map(['GET', 'POST'], '/{id:\d+}/', \BO\Zmsadmin\Scope::class)
        ->setName("scope");

    $group->get('/{id:\d+}/process/{date:\d\d\d\d-\d\d-\d\d}/', \BO\Zmsadmin\ScopeAppointmentsByDay::class)
        ->setName("scopeAppointmentsByDay");

    $group->get('/{id:\d+}/process/{date:\d\d\d\d-\d\d-\d\d}/spreadsheet/', \BO\Zmsadmin\ScopeAppointmentsByDayXlsExport::class)
        ->setName("scopeAppointmentsByDaySpreadsheet");

    $group->get('/delete/{id:\d+}/', \BO\Zmsadmin\ScopeDelete::class)
        ->setName("scopeDelete");

    $group->get('/{id:\d+}/availability/day/{date:\d\d\d\d-\d\d-\d\d}/', \BO\Zmsadmin\ScopeAvailabilityDay::class)
        ->setName("scopeAvailabilityDay");

    $group->get('/{id:\d+}/availability/day/{date:\d\d\d\d-\d\d-\d\d}/conflicts/', \BO\Zmsadmin\ScopeAvailabilityDayConflicts::class)
        ->setName("scopeAvailabilityDayConflict");

    $group->get('/{id:\d+}/availability/month/[{date:\d\d\d\d-\d\d}/]', \BO\Zmsadmin\ScopeAvailabilityMonth::class)
        ->setName("scopeAvailabilityMonth");

    $group->map(['GET', 'POST'], '/{id:\d+}/emergency/', \BO\Zmsadmin\ScopeEmergency::class)
        ->setName("scope_emergency");

    $group->post('/{id:\d+}/emergency/respond/', \BO\Zmsadmin\ScopeEmergencyResponse::class)
        ->setName('scope_emergency_response');

    $group->get('/ticketprinter/', \BO\Zmsadmin\TicketprinterConfig::class)
        ->setName("ticketprinter");

    $group->map(['GET', 'POST'], '/{id:\d+}/ticketprinter/', \BO\Zmsadmin\TicketprinterStatusByScope::class)
        ->setName("ticketprinterStatusByScope");
});

/*
 * ---------------------------------------------------------------------------
 * Search
 * -------------------------------------------------------------------------
 */
\App::$slim->get('/search/', \BO\Zmsadmin\ProcessSearch::class)
    ->setName("search");

/*
 * ---------------------------------------------------------------------------
 * Signing
 * -------------------------------------------------------------------------
 */
\App::$slim->post('/sign/parameters/', \BO\Zmsadmin\UrlParameterSigning::class)
    ->setName("signParameters");

/*
 * ---------------------------------------------------------------------------
 * Source
 * -------------------------------------------------------------------------
 */
\App::$slim->get('/source/', \BO\Zmsadmin\SourceIndex::class)
    ->setName("sourceindex");

\App::$slim->map(['GET', 'POST'], '/source/{name}/', \BO\Zmsadmin\SourceEdit::class)
    ->setName("sourceEdit");

\App::$slim->map(['POST'], '/source/delete/{loginname}/', \BO\Zmsadmin\SourceDelete::class)
    ->setName("sourceDelete");


/*
 * ---------------------------------------------------------------------------
 * Useraccount
 * -------------------------------------------------------------------------
 */
\App::$slim->get('/useraccount/', \BO\Zmsadmin\Useraccount::class)
    ->setName("useraccount");

\App::$slim->map(['GET', 'POST'], '/useraccount/add/', \BO\Zmsadmin\UseraccountAdd::class)
    ->setName("useraccountAdd");

\App::$slim->map(['GET', 'POST'], '/useraccount/{loginname}/', \BO\Zmsadmin\UseraccountEdit::class)
    ->setName("useraccountEdit");

\App::$slim->get('/useraccount/delete/{loginname}/', \BO\Zmsadmin\UseraccountDelete::class)
    ->setName("useraccountDelete");


/*
 * ---------------------------------------------------------------------------
 * Workstation
 * -------------------------------------------------------------------------
 */
\App::$slim->map(['GET', 'POST'], '/workstation/select/', \BO\Zmsadmin\WorkstationSelect::class)
    ->setName("workstationSelect");

\App::$slim->get('/workstation/status/', \BO\Zmsadmin\WorkstationStatus::class)
    ->setName("workstationStatus");

\App::$slim->get('/workstation/process/next/', \BO\Zmsadmin\WorkstationProcessNext::class)
    ->setName("workstationProcessNext");

\App::$slim->get('/workstation/process/{id:\d+}/precall/', \BO\Zmsadmin\WorkstationProcessPreCall::class)
    ->setName("workstationProcessPreCall");

\App::$slim->get('/workstation/process/{id:\d+}/called/', \BO\Zmsadmin\WorkstationProcessCalled::class)
    ->setName("workstationProcessCalled");

\App::$slim->get('/workstation/process/processing/', \BO\Zmsadmin\WorkstationProcessProcessing::class)
    ->setName("workstationProcessProcessing");

\App::$slim->map(['GET', 'POST'], '/workstation/process/finished/', \BO\Zmsadmin\WorkstationProcessFinished::class)
    ->setName("workstationProcessFinished");

\App::$slim->map(['GET', 'POST'], '/workstation/process/redirect/', \BO\Zmsadmin\WorkstationProcessRedirect::class)
    ->setName("workstationProcessRedirect");

\App::$slim->get('/workstation/call/{id:\d+}/', \BO\Zmsadmin\WorkstationProcessCall::class)
    ->setName("workstationProcessCall");

\App::$slim->get('/workstation/process/parked/', \BO\Zmsadmin\WorkstationProcessParked::class)
    ->setName("workstationProcessParked");

\App::$slim->get('/workstation/process/cancel/', \BO\Zmsadmin\WorkstationProcessCancel::class)
    ->setName("workstationProcessCancel");

\App::$slim->get('/workstation/process/cancel/next/', \BO\Zmsadmin\WorkstationProcessCancelNext::class)
    ->setName("workstationProcessCancelNext");

\App::$slim->get('/workstation/process/callbutton/', \BO\Zmsadmin\WorkstationProcess::class)
    ->setName("workstationProcessCallButton");

\App::$slim->map(['GET', 'POST'], '/workstation/', \BO\Zmsadmin\Workstation::class)
    ->setName("workstation");

/*
 * ---------------------------------------------------------------------------
 * Other Ajax Components
 * -------------------------------------------------------------------------
 */
\App::$slim->get('/calendarPage/[{date:\d}/]', \BO\Zmsadmin\CalendarPage::class)
    ->setName("counter_calendar_page");

\App::$slim->map(['GET', 'POST'], '/appointmentForm/', \BO\Zmsadmin\AppointmentForm::class)
    ->setName("appointment_form");

\App::$slim->get('/appointmentForm/processlist/free/', \BO\Zmsadmin\AppointmentFormFreeProcessList::class)
    ->setName("appointment_form_free_processes");

\App::$slim->get('/appointmentForm/buttons/', \BO\Zmsadmin\AppointmentFormButtons::class)
    ->setName("appointment_form_buttons");

\App::$slim->get('/queueTable/', \BO\Zmsadmin\QueueTable::class)
    ->setName("queue_table");

\App::$slim->get('/dialog/', \BO\Zmsadmin\Helper\DialogHandler::class)
    ->setName("dialogHandler");

\App::$slim->get('/provider/{source}/', \BO\Zmsadmin\Helper\ProviderHandler::class)
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
\App::$slim->get('/testpage/', \BO\Zmsadmin\Testpage::class)
    ->setName("testpage");

\App::$slim->get('/changelog/', \BO\Zmsadmin\Changelog::class)
    ->setName("changelog");

\App::$slim->get('/status/', \BO\Zmsadmin\Status::class)
    ->setName("status");

\App::$slim->get('/healthcheck/', \BO\Zmsadmin\Healthcheck::class)
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


\App::$slim->post('/scope/{id:\d+}/availability/day/{date:\d\d\d\d-\d\d-\d\d}/closure/toggle/', \BO\Zmsadmin\ScopeAvailabilityDayClosure::class)
    ->setName("scopeAvailabilityDayClosure");