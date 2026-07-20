
help:
	@echo "Possible Targets:"
	@grep -P "^\w+:" Makefile|sort|perl -pe 's/^(\w+):([^\#]+)(\#\s*(.*))?/\1\n\t\4\n/'

now: # Dummy target

build: css js # Build javascript and css

libs: now # Copy monaco-editor and requirejs for mail template editor
	mkdir -p public/_libs/requirejs && cp -r node_modules/requirejs/* public/_libs/requirejs/
	mkdir -p public/_libs/monaco-editor && cp -r node_modules/monaco-editor/* public/_libs/monaco-editor/

css: now
	npm run css

js: now
	npm run js

fix: # run code fixing
	php vendor/bin/phpcbf --standard=psr2 src/
	php vendor/bin/phpcbf --standard=psr2 tests/
	npm run fix

materialize-zmslayout-deps: # copy file:../zmslayout deps (no symlinks; safe for Docker image export)
	@for spec in bo-zms-layout-js:js bo-zms-layout-scss:scss; do \
		name=$${spec%%:*}; dir=$${spec##*:}; \
		if [ -L "node_modules/$$name" ]; then \
			rm -f "node_modules/$$name"; \
			cp -a "../zmslayout/$$dir" "node_modules/$$name"; \
		fi; \
	done

live: # init live system
	composer install --no-dev --prefer-dist --optimize-autoloader
	npm install --legacy-peer-deps
	$(MAKE) materialize-zmslayout-deps
	npm run build
	$(MAKE) libs


dev: # init development system
	composer update
	npm install

coverage:
	php vendor/bin/phpunit --coverage-html public/_tests/coverage/

paratest: # init parallel unit testing with 5 processes
	vendor/bin/paratest --coverage-html public/_tests/coverage/
