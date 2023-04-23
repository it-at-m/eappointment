
help: # This help
	@echo "Possible Targets:"
	@grep -P "^\w+:" Makefile|sort|perl -pe 's/^(\w+):([^\#]+)(\#\s*(.*))?/ \1\n\t\4\n/'

now: # Dummy target

fix: # run code fixing
	php ../../bin/phpcbf --standard=psr2 src/
	php ../../bin/phpcbf --standard=psr2 tests/

live: # init live system, delete unnecessary libs
	composer install --no-dev --prefer-dist

dev: # init development system
	composer update

coverage:
	php ../../bin/phpunit --coverage-html public/_tests/coverage/

paratest: # init parallel unit testing with 5 processes
	../../bin/paratest --coverage-html public/_tests/coverage/

test:
	bin/test
