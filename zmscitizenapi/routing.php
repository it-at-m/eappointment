<?php
// @codingStandardsIgnoreFile

use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

\App::$slim->get(
    '/services/',
    '\BO\Zmscitizenapi\ServicesList'
)
    ->setName("ServicesList");

\App::$slim->get(
    '/scopes/',
    '\BO\Zmscitizenapi\ScopesList'
)
    ->setName("ScopesList");

\App::$slim->get(
    '/offices/',
    '\BO\Zmscitizenapi\OfficesList'
)
    ->setName("OfficesList");

\App::$slim->get(
    '/offices-and-services/',
    '\BO\Zmscitizenapi\OfficesServicesRelations'
)
    ->setName("OfficesServicesRelations");

\App::$slim->get(
    '/scope-by-id/',
    '\BO\Zmscitizenapi\ScopeByIdGet'
)
    ->setName("ScopeByIdGet");

\App::$slim->get(
    '/services-by-office/',
    '\BO\Zmscitizenapi\ServicesByOfficeList'
)
    ->setName("ServicesByOfficeList");

\App::$slim->get(
    '/offices-by-service/',
    '\BO\Zmscitizenapi\OfficesByServiceList'
)
    ->setName("OfficesByServiceList");

\App::$slim->get(
    '/available-days/',
    '\BO\Zmscitizenapi\AvailableDaysList'
)
    ->setName("AvailableDaysList");

\App::$slim->get(
    '/available-appointments/',
    '\BO\Zmscitizenapi\AvailableAppointmentsList'
)
    ->setName("AvailableAppointmentsList");

\App::$slim->get(
    '/appointment/',
    '\BO\Zmscitizenapi\AppointmentGet'
)
    ->setName("AppointmentGet");

\App::$slim->post(
    '/reserve-appointment/',
    '\BO\Zmscitizenapi\AppointmentReserve'
)
    ->setName("AppointmentReserve");

\App::$slim->post(
    '/update-appointment/',
    '\BO\Zmscitizenapi\AppointmentUpdate'
)
    ->setName("AppointmentUpdate");

\App::$slim->post(
    '/confirm-appointment/',
    '\BO\Zmscitizenapi\AppointmentConfirm'
)
    ->setName("AppointmentConfirm");

\App::$slim->post(
    '/preconfirm-appointment/',
    '\BO\Zmscitizenapi\AppointmentPreconfirm'
)
    ->setName("AppointmentPreconfirm");

\App::$slim->post(
    '/cancel-appointment/',
    '\BO\Zmscitizenapi\AppointmentCancel'
)
    ->setName("AppointmentCancel");
