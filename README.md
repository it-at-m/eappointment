
# ZMS Administration


## Add SCSS module

First add DEPENDENCY to bower

    ./node_modules/.bin/bower install --save-dev DEPENDENCY

Then add `@import()` rules to `scss/admin.scss`.

Finally generate the CSS:

    make
    # or
    ./node_modules/.bin/gulp

