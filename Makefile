COMPOSER=php -d suhosin.executor.include.whitelist=phar bin/composer.phar


.PHONY: help now dev live watch

help: # This help
	@echo "Possible Targets:"
	@grep -P "^\w+:" Makefile|sort|perl -pe 's/^(\w+):([^\#]+)(\#\s*(.*))?/ \1\n\t\4\n/'

now: # Dummy target

dev: # init development system
	COMPOSER=composer.devel.json $(COMPOSER) update
	npm install

live: # init live system, delete unnecessary libs
	$(COMPOSER) install --no-dev --prefer-dist

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
	php vendor/bin/phpunit --coverage-html public/_tests/coverage/

paratest: # init parallel unit testing with 5 processes
	vendor/bin/paratest -p20 --coverage-html public/_tests/coverage/
