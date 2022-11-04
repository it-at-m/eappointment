.PHONY: help now dev live watch

help: # This help
	@echo "Possible Targets:"
	@grep -P "^\w+:" Makefile|sort|perl -pe 's/^(\w+):([^\#]+)(\#\s*(.*))?/ \1\n\t\4\n/'

now: # Dummy target

dev: # init development system
	COMPOSER=composer.json composer update
	npm install

live: # init live system, delete unnecessary libs
	composer install --no-dev --prefer-dist

fix: # run code fixing
	php vendor/bin/phpcbf --standard=psr2 src/
	php vendor/bin/phpcbf --standard=psr2 tests/

openapi: # Swagger docs on changes
	./bin/doc

coverage:
	php vendor/bin/phpunit --coverage-html public/_tests/coverage/

paratest: # init parallel unit testing with 5 processes
	vendor/bin/paratest -p20 --coverage-html public/_tests/coverage/
