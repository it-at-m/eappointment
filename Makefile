COMPOSER=php -d suhosin.executor.include.whitelist=phar bin/composer.phar


.PHONY: help now dev live watch

help: # This help
	@echo "Possible Targets:"
	@grep -P "^\w+:" Makefile|sort|perl -pe 's/^(\w+):([^\#]+)(\#\s*(.*))?/ \1\n\t\4\n/'

now: # Dummy target

dev: # init development system
	$(COMPOSER) update
	npm install

live: # init live system, delete unnecessary libs
	$(COMPOSER) update --no-dev

watch: # Build CSS, JS and Swagger docs on changes
	./node_modules/.bin/gulp
