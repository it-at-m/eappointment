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

## Published Reports

Coverage, API HTML documentation, diagrams, and security reports are published to:
[https://it-at-m.github.io/eappointment/](https://it-at-m.github.io/eappointment/)
