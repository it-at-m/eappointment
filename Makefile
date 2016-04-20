
default:
	node_modules/.bin/gulp

fix: # run code fixing
	php vendor/bin/phpcbf --standard=psr2 src/

watch:
	node_modules/.bin/gulp watch
