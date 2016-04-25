<?php
// @codingStandardsIgnoreFile
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

/* ---------------------------------------------------------------------------
 * html, basic routes
 * -------------------------------------------------------------------------*/

\App::$slim->get('/',
    '\BO\Zmsadmin\Index:render')
    ->name("pagesindex");

\App::$slim->get('/',
    '\BO\Zmsadmin\Index:render')
    ->name("login");
    
\App::$slim->get('/workstation/process/:id/precall/',
    '\BO\Zmsadmin\WorkstationClientPreCall:render')
    ->name("workstationClientPreCall");

\App::$slim->get('/workstation/process/:id/called/',
    '\BO\Zmsadmin\WorkstationClientCalled:render')
    ->name("workstationClientCalled");

\App::$slim->get('/workstation/process/:id/processed',
    '\BO\Zmsadmin\WorkstationClientProcessed:render')
    ->name("workstationClientProcessed");

\App::$slim->get('/workstation/process/:id/',
    '\BO\Zmsadmin\WorkstationClientActive:render')
    ->name("workstationClientActive");
    
\App::$slim->map('/workstation/', 
    '\BO\Zmsadmin\Workstation:render')
    ->via('GET', 'POST')->name("workstation");

\App::$slim->get('/counter/',
    '\BO\Zmsadmin\Counter:render')
    ->name("counter");

\App::$slim->get('/scope/',
    '\BO\Zmsadmin\Scope:render')
    ->name("scope");

\App::$slim->get('/scope/:id/pickup/',
    '\BO\Zmsadmin\Pickup:render')
    ->name("pickup");

\App::$slim->map('/scope/:id/pickup/handheld/', 
    '\BO\Zmsadmin\PickupHandheld:render')
    ->via('GET', 'POST')
    ->name("pickup_handheld");
    
\App::$slim->get('/scope/:id/pickup/keyboard/',
    '\BO\Zmsadmin\PickupKeyboard:render')
    ->name("pickup_keyboard");

\App::$slim->get('/scope/:scope_id/availability/day/',
    '\BO\Zmsadmin\ScopeAvailabilityDay:render')
    ->conditions([
        'scope_id' => '\d+'
    ])
    ->name("scopeavailabilityday");

\App::$slim->get('/cluster/',
    '\BO\Zmsadmin\Cluster:render')
    ->name("cluster");

\App::$slim->get('/department/',
    '\BO\Zmsadmin\Department:render')
    ->name("department");

\App::$slim->get('/organisation/',
    '\BO\Zmsadmin\Organisation:render')
    ->name("organisation");

\App::$slim->get('/owner/',
    '\BO\Zmsadmin\Owner:render')
    ->name("owner");

\App::$slim->get('/owner/:id/',
    '\BO\Zmsadmin\OwnerEdit:render')
    ->name("ownerEdit");
    
\App::$slim->get('/availability/day/',
    '\BO\Zmsadmin\Availability:render')
    ->name("availability_day");

\App::$slim->get('/availability/month/',
    '\BO\Zmsadmin\AvailabilityMonth:render')
    ->name("availability_month");

\App::$slim->get('/calendar/:year/kw/:weeknr/',
    '\BO\Zmsadmin\CalendarWeek:render')
    ->name("calendar_week");

\App::$slim->get('/profile/',
    '\BO\Zmsadmin\Profile:render')
    ->name("profile");

\App::$slim->get('/useraccount/',
    '\BO\Zmsadmin\Useraccount:render')
    ->name("useraccount");

\App::$slim->get('/department/:id/useraccount/',
    '\BO\Zmsadmin\UseraccountByDepartment:render')
    ->name("useraccountByDdepartment");

\App::$slim->get('/useraccount/:id/',
    '\BO\Zmsadmin\UseraccountEdit:render')
    ->name("useraccountEdit");

\App::$slim->get('/calldisplay/',
    '\BO\Zmsadmin\Calldisplay:render')
    ->name("calldisplay");

\App::$slim->get('/scope/ticketprinter/',
    '\BO\Zmsadmin\TicketprinterConfig:render')
    ->name("ticketprinter");
    
\App::$slim->get('/scope/:id/ticketprinter/',
    '\BO\Zmsadmin\TicketprinterStatusByScope:render')
    ->name("ticketprinterStatusByScope");

\App::$slim->get('/notification/',
    '\BO\Zmsadmin\Notification:render')
    ->name("notification");

\App::$slim->get('/links/',
    '\BO\Zmsadmin\Links:render')
    ->name("links");

\App::$slim->get('/search/',
    '\BO\Zmsadmin\Search:render')
    ->name("search");

\App::$slim->get('/dayoff/',
    '\BO\Zmsadmin\Dayoff:render')
    ->name("dayoff");

\App::$slim->get('/dayoff/:year/',
    '\BO\Zmsadmin\DayoffByYear:render')
    ->name("dayoffByYear");

\App::$slim->get('/dayoff/:year/:id/',
    '\BO\Zmsadmin\DayoffEdit:render')
    ->name("dayoffEdit");
    
\App::$slim->get('/department/:id/dayoff/',
    '\BO\Zmsadmin\DayoffByDepartment:render')
    ->name("dayoffByDepartment");

\App::$slim->get('/department/:id/dayoff/:year/',
    '\BO\Zmsadmin\DayoffByDepartmentAndYear:render')
    ->name("dayoffByDepartmentAndYear");

\App::$slim->get('/testpage/',
    '\BO\Zmsadmin\Testpage:render')
    ->name("testpage");


//\App::$slim->get('/dienstleistung/:service_id',
//    '\BO\D115Mandant\Controller\ServiceDetail:render')
//    ->conditions([
//        'service_id' => '\d{3,10}',
//        ])
//    ->name("servicedetail");

/* ---------------------------------------------------------------------------
 * externals
 * -------------------------------------------------------------------------*/

// external link to stadplan
\App::$slim->get('http://www.Berlin.de/stadtplan/',
    function () {})
    ->name("citymap");

/* ---------------------------------------------------------------------------
 * maintenance
 * -------------------------------------------------------------------------*/

\App::$slim->get('/healthcheck/',
    '\BO\Zmsadmin\Healthcheck:render')
    ->name("healthcheck");

\App::$slim->notfound(function () {
    \BO\Slim\Render::html('\page\404.twig');
});

\App::$slim->error(function (\Exception $exception) {
    \BO\Slim\Render::lastModified(time(), '0');
    \BO\Slim\Render::html('\page\failed.twig', array(
        "failed" => $exception->getMessage(),
        "error" => $exception,
    ));
    \App::$slim->stop();
});
