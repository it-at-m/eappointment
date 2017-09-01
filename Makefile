
COMPOSER=php -d suhosin.executor.include.whitelist=phar bin/composer.phar

help:
	@echo "Possible Targets:"
	@grep -P "^\w+:" Makefile|sort|perl -pe 's/^(\w+):([^\#]+)(\#\s*(.*))?/\1\n\t\4\n/'

now: # Dummy target

build: css vendorjs js # Build javascript and css

css: now
	node_modules/.bin/gulp scss

css-print: now
	node_modules/.bin/gulp scss-print

js: now
	node_modules/.bin/gulp js

vendorjs: now
	node_modules/.bin/gulp vendor

fix: # run code fixing
	php vendor/bin/phpcbf --standard=psr2 src/

watch:
	node_modules/.bin/gulp watch

live: # init live system
	$(COMPOSER) install --no-dev --prefer-dist

dev: # init development system
	$(COMPOSER) update
	npm install

coverage:
	php vendor/bin/phpunit --coverage-html public/_tests/coverage/
