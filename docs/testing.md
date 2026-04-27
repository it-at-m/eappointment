# Testing

## PHPUnit (PHP Modules)

Run tests inside your container environment:

```bash
# DDEV
ddev ssh
cd zmsdb
./vendor/bin/phpunit

# Podman
podman exec -it zms-web bash
cd zmsdb
./vendor/bin/phpunit
```

## zmsclient Tests

`zmsclient` provides a helper script that detects Docker/Podman and executes tests:

```bash
cd zmsclient
./zmsclient-test
```

## zmsapi and zmsdb Fixture-Based Tests

When required, import fixture data before running tests.
Refer to module scripts and fixture paths in each module README.

## zmsautomation (API Tests)

```bash
podman exec -it zms-web bash
cd zmsautomation
bash zmsautomation-test
```

You can pass `-Dtest=...` to run specific tests.

## Published coverage reports

HTML coverage from CI is published under [https://it-at-m.github.io/eappointment/](https://it-at-m.github.io/eappointment/) (same site as this handbook). Direct links:

- [zmsadmin](https://it-at-m.github.io/eappointment/coverage/coverage-zmsadmin/html/)
- [zmscalldisplay](https://it-at-m.github.io/eappointment/coverage/coverage-zmscalldisplay/html/)
- [zmscitizenapi](https://it-at-m.github.io/eappointment/coverage/coverage-zmscitizenapi/html/)
- [zmsdldb](https://it-at-m.github.io/eappointment/coverage/coverage-zmsdldb/html/)
- [zmsentities](https://it-at-m.github.io/eappointment/coverage/coverage-zmsentities/html/)
- [zmsmessaging](https://it-at-m.github.io/eappointment/coverage/coverage-zmsmessaging/html/)
- [zmsslim](https://it-at-m.github.io/eappointment/coverage/coverage-zmsslim/html/)
- [zmsstatistic](https://it-at-m.github.io/eappointment/coverage/coverage-zmsstatistic/html/)
- [zmsticketprinter](https://it-at-m.github.io/eappointment/coverage/coverage-zmsticketprinter/html/)
- [zmsapi](https://it-at-m.github.io/eappointment/coverage/coverage-zmsapi/html/)
- [zmsdb](https://it-at-m.github.io/eappointment/coverage/coverage-zmsdb/html/)
- [zmsclient](https://it-at-m.github.io/eappointment/coverage/coverage-zmsclient/html/)
- [zmscitizenview](https://it-at-m.github.io/eappointment/coverage/coverage-zmscitizenview/)

API HTML docs and diagram pages are listed in [API reference](./api-reference.md).
