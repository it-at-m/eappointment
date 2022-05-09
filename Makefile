
COMPOSER=php -d suhosin.executor.include.whitelist=phar bin/composer.phar

help:
	@echo "Possible Targets:"
	@grep -P "^\w+:" Makefile|sort|perl -pe 's/^(\w+):([^\#]+)(\#\s*(.*))?/\1\n\t\4\n/'

now: # Dummy target

build: css vendorjs js # Build javascript and css

css: now
	npm run css

js: now
	npm run js

fix: # run code fixing
	php ../../bin/phpcbf --standard=psr2 src/
	php ../../bin/phpcbf --standard=psr2 tests/

live: # init live system
	$(COMPOSER) install --no-dev --prefer-dist

dev: # init development system
	$(COMPOSER) update
	npm install

coverage:
	php vendor/bin/phpunit tests/ --coverage-html public/_tests/coverage/
