
COMPOSER=php -d suhosin.executor.include.whitelist=phar bin/composer.phar

help:
	grep -P "^\w+:" Makefile|sort|perl -pe 's/^(\w+):([^\#]+)(\#\s*(.*))?/\1\n\t\4\n/'

now: # Dummy target

build: # Build javascript and css
	node_modules/.bin/gulp
	
css:
	node_modules/.bin/gulp scss

js:
	node_modules/.bin/gulp js

fix: # run code fixing
	php vendor/bin/phpcbf --standard=psr2 src/
	php vendor/bin/phpcbf --standard=psr2 tests/

watch:
	node_modules/.bin/gulp watch
	
live: # init live system
	$(COMPOSER) update --no-dev

dev: # init development system
	$(COMPOSER) update
	npm install
	node_modules/.bin/bower --config.directory=vendor install
	
coverage:
	php -dzend_extension=xdebug.so vendor/bin/phpunit --coverage-html public/_tests/coverage/
