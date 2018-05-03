COMPOSER=php -d suhosin.executor.include.whitelist=phar bin/composer.phar


.PHONY: help now dev live watch

help: # This help
	@echo "Possible Targets:"
	@grep -P "^\w+:" Makefile|sort|perl -pe 's/^(\w+):([^\#]+)(\#\s*(.*))?/ \1\n\t\4\n/'

build:
	node_modules/.bin/gulp

now: # Dummy target

dev: # init development system
	$(COMPOSER) update

update: # update with devel composer.json
	COMPOSER=composer.devel.json $(COMPOSER) update

live: # init live system, delete unnecessary libs
	$(COMPOSER) install --no-dev --prefer-dist

watch: # Build CSS, JS and Swagger docs on changes
	./node_modules/.bin/gulp watch

tests: now # run tests
	vendor/bin/phpmd src/ text phpmd.rules.xml
	vendor/bin/phpcs --standard=psr2 src/
	php vendor/bin/phpunit --coverage-html public/_tests/coverage/

paratest: # init parallel unit testing with 5 processes
	vendor/bin/paratest -c paratest.xml --coverage-html public/_tests/coverage/

fix: #f fix code
	php vendor/bin/phpcbf --standard=psr2 src/
	php vendor/bin/phpcbf --standard=psr2 tests/

coverage:
	php vendor/bin/phpunit --coverage-html public/_tests/coverage/
