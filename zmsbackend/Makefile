.PHONY: help now dev live

help: # This help
	@echo "Possible Targets:"
	@grep -P "^\w+:" Makefile|sort|perl -pe 's/^(\w+):([^\#]+)(\#\s*(.*))?/ \1\n\t\4\n/'

now: # Dummy target

dev: # init development system
	composer update

live: # init live system, delete unnecessary libs
	composer install --no-dev --prefer-dist --optimize-autoloader
	bin/configure
