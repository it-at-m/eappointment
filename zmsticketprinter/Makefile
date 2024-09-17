COMPOSER=composer

help:
	grep -P "^\w+:" Makefile|sort|perl -pe 's/^(\w+):([^\#]+)(\#\s*(.*))?/\1\n\t\4\n/'

now: # Dummy target

build: css js # Build javascript and css

update: # update with devel composer.json
	COMPOSER=composer.devel.json $(COMPOSER) update

css:
	npm run css

js: now
	npm run js

watch:
	npm run jswatch

fix: # run code fixing
	php vendor/bin/phpcbf --standard=psr2 src/
	php vendor/bin/phpcbf --standard=psr2 tests/
	npm run fix

live: # init live system
	$(COMPOSER) install --no-dev --prefer-dist
	npm install --legacy-peer-deps
	npm run build
	npm install requirejs --no-save --legacy-peer-deps
	mkdir -p public/_libs/requirejs && cp -r node_modules/requirejs/* public/_libs/requirejs/

dev: # init development system
	$(COMPOSER) update
	npm install

coverage:
	php -dzend_extension=xdebug.so vendor/bin/phpunit --coverage-html public/_tests/coverage/

paratest: # init parallel unit testing with 5 processes
	vendor/bin/paratest --coverage-html public/_tests/coverage/
