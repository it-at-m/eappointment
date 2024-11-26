## How does the Open Api definition work
## Version 2.0

```
bin/configure
npm i
npm run build
npm run doc
swagger-cli bundle -o public/doc/swagger.json public/doc/swagger.yaml
python3 -m http.server 8001
```

Reachable at:
```
http://[::]:8001/public/doc/
https://zms.ddev.site/terminvereinbarung/api/2/doc/index.html
https://it-at-m.github.io/eappointment/zmsapi/public/doc/index.html
```

* Under /public/doc are the schema from zmsentities. A symbolic link points to the corresponding folder under vendor/eappointment/zmsentities/schema.

* Under /bin there is a build_swagger.js file. This is executed via ``npm run doc`` and validates the existing swagger.yaml file. If valid, the open api annotations are read from routing.php and the remaining information such as info, definitions, version and tags are compiled from the yaml files under ./partials into a complete swagger.yaml. 

* a bin/configure must be executed before a bin/doc so that the latest API version is in the ./VERSION file.

* To access all paths resolved via redoc or the open api documentation, a resolved swagger.json must be created from the swagger.yaml. This is done via the swagger cli with a call to ``bin/doc``. This call executes the above npm command ``npm run doc`` and subsequently creates a full swagger.json. 

To render the open-api doc by redoc and swagger, appropriate files such as swagger-ui files are fetched in the CI process and stored at https://eappointment.gitlab.io/zmsapi/.