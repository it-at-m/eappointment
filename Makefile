COMPOSER=php -d suhosin.executor.include.whitelist=phar bin/composer.phar


.PHONY: help now dev live watch

help: # This help
	@echo "Possible Targets:"
	@grep -P "^\w+:" Makefile|sort|perl -pe 's/^(\w+):([^\#]+)(\#\s*(.*))?/ \1\n\t\4\n/'

now: # Dummy target

dev: # init development system
	$(COMPOSER) update

live: # init live system, delete unnecessary libs
	$(COMPOSER) update --no-dev
	
fix: # run code fixing
	php vendor/bin/phpcbf --standard=psr2 src/

tests: now # run tests
	bin/tests

coverage:
	php -dzend_extension=xdebug.so vendor/bin/phpunit --coverage-html public/_tests/coverage/
