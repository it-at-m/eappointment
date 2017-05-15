COMPOSER=php -d suhosin.executor.include.whitelist=phar bin/composer.phar


.PHONY: help now dev live watch

help: # This help
	@echo "Possible Targets:"
	@grep -P "^\w+:" Makefile|sort|perl -pe 's/^(\w+):([^\#]+)(\#\s*(.*))?/ \1\n\t\4\n/'

now: # Dummy target

dev: # init development system
	$(COMPOSER) update
	npm install

live: # init live system, delete unnecessary libs
	$(COMPOSER) install --no-dev

fix: # run code fixing
	php vendor/bin/phpcbf --standard=psr2 src/
	php vendor/bin/phpcbf --standard=psr2 tests/

build: # Build CSS, JS and Swagger docs on changes
	./node_modules/.bin/gulp

validate: # validate JSON Schemes
	./node_modules/.bin/gulp validate

watch: build # Build CSS, JS and Swagger docs on changes
	./node_modules/.bin/gulp watch

coverage:
	php -dzend_extension=xdebug.so vendor/bin/phpunit --coverage-html public/_tests/coverage/
