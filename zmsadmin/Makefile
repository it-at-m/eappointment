
help:
	@echo "Possible Targets:"
	@grep -P "^\w+:" Makefile|sort|perl -pe 's/^(\w+):([^\#]+)(\#\s*(.*))?/\1\n\t\4\n/'

now: # Dummy target

build: css js # Build javascript and css

css: now
	npm run css

js: now
	npm run js

fix: # run code fixing
	php vendor/bin/phpcbf --standard=psr2 src/
	php vendor/bin/phpcbf --standard=psr2 tests/
	npm run fix

live: # init live system
	composer install --no-dev --prefer-dist
	npm install --legacy-peer-deps --loglevel=verbose
	npm run build
	npm install requirejs --no-save --legacy-peer-deps
	mkdir -p public/_libs/requirejs && cp -r node_modules/requirejs/* public/_libs/requirejs/
	npm install monaco-editor --no-save --legacy-peer-deps
	mkdir -p public/_libs/monaco-editor && cp -r node_modules/monaco-editor/* public/_libs/monaco-editor/


dev: # init development system
	composer update
	npm install

coverage:
	php vendor/bin/phpunit --coverage-html public/_tests/coverage/

paratest: # init parallel unit testing with 5 processes
	vendor/bin/paratest --coverage-html public/_tests/coverage/
