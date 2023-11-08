COMPOSER=php -d suhosin.executor.include.whitelist=phar bin/composer.phar


.PHONY: help now dev live watch

dev: # init development system
	COMPOSER=composer.devel.json $(COMPOSER) update

live: # init live system, delete unnecessary libs
	$(COMPOSER) install --no-dev --prefer-dist

fix: # run code fixing
	php vendor/bin/phpcbf --standard=psr2 src/
	php vendor/bin/phpcbf --standard=psr2 tests/

coverage:
	php -dzend_extension=xdebug.so -dxdebug.mode=coverage vendor/bin/phpunit --coverage-html public/_tests/coverage/

