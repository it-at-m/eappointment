# Project Overview

## Context

This repository is used to maintain and publish Munich-specific changes for eAppointment.
It is developed and documented in the open to support municipal service delivery.

## Major Project Streams

### ZMS
The original project to replace commercial proprietary software with the open source Berlin solution ZMS and went live with each city agency/department. This foundational project established the core appointment management system infrastructure for Munich's municipal services.

### MPDZBS
The creation of the PHP zmscitizenapi and the replacement of the first open source Vue2 frontend ([eappointment-buergeransicht](https://github.com/it-at-m/eappointment-buergeransicht)) with the Vue3 zmscitizenview/[refarch](https://refarch.oss.muenchen.de/) citizen frontend, plus the creation of the city's Vue patternlab ([muc-patternlab-vue](https://github.com/it-at-m/muc-patternlab-vue)). This project modernized the citizen-facing components and established design system standards.

### ZMSKVR
To add still needed features and requirements for the city's agencies/departments and improve weaknesses in user experiences. This includes implementing features in zmscitizenview that were not completed by MPDZBS, ensuring comprehensive functionality for all municipal departments.

### MUXDBS
Builds on MPDZBS following the Reifegradmodell (Maturity Level Model) as an implementation framework for Onlinezugangsgesetz (OZG - Online Access Act) compliance ([digitale-verwaltung.de](https://www.digitale-verwaltung.de/Webs/DV/DE/onlinezugangsgesetz/ozg-grundlagen/info-reifegradmodell/info-reifegradmodell-node.html)) and adds additional components to zmscitizenview which will allow things such as login with BundID, BayernID and Elster for seamless online citizen appointments. This project represents the next maturity level of digital government services following federal implementation guidelines.


## Contacts

- Munich contact: `opensource@muenchen.de`
- Software overview: [https://opensource.muenchen.de/software/zeitmanagementsystem.html](https://opensource.muenchen.de/software/zeitmanagementsystem.html)
