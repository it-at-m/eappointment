var fs = require('fs');
const swaggerJsdoc = require('swagger-jsdoc');
const yaml = require('js-yaml');

const routingFile = './routing.php';
const swaggerFile = 'public/doc/swagger.yaml';

const options = {
    definition: {
      openapi: '2.0.0',
      info: {
        version: readApiVersion(),
        title: "ZMS Backend API"
      },
    },
    apis: [routingFile]
  };

const annotationErrors = validateSwaggerAnnotations(routingFile);
if (annotationErrors.length > 0) {
    console.error('Swagger annotation errors:');
    annotationErrors.forEach(function (error) {
        console.error(error);
    });
    process.exit(1);
}

const openapiSpecification = swaggerJsdoc(options);
buildSwagger(openapiSpecification);

const stats = fs.statSync(swaggerFile);
console.log('Found ' + swaggerFile + ' with ' + stats.size + ' bytes');

function validateSwaggerAnnotations(sourceFile) {
    const content = fs.readFileSync(sourceFile, 'utf8');
    const blocks = content.split('@swagger');
    const errors = [];

    for (let index = 1; index < blocks.length; index++) {
        const block = blocks[index].split('*/')[0];
        const yamlText = block.replace(/^\s*\*\s?/gm, '').trim();
        if (yamlText === '') {
            continue;
        }

        try {
            yaml.load(yamlText);
        } catch (error) {
            const firstLine = yamlText.split('\n')[0];
            errors.push('Error in ' + sourceFile + ':\n' + firstLine + '\n' + error.message);
        }
    }

    return errors;
}

function buildSwagger(openapiSpecification) {
  let version = readFileContent('public/doc/partials/version.yaml') + "\n";
  let info = readFileContent('public/doc/partials/info.yaml');
  info = info + "\n  version: '" + readFileContent("./VERSION").trim() + "'\n";

  let basics = readFileContent('public/doc/partials/basic.yaml') + "\n";
  let paths = {
    paths:
      openapiSpecification.paths,
  }
  let tags = readFileContent('public/doc/partials/tags.yaml');
  let definitions = readFileContent('public/doc/partials/definitions.yaml');
  writeSwaggerFile(version + info + basics + tags + yaml.dump(paths) + definitions)
}

function writeSwaggerFile(data)
{
  try {
    fs.writeFileSync(swaggerFile, data, 'utf8');
    console.log("Build new swagger file successfully!");
  } catch (error) {
    console.error(error);
    process.exit(1);
  }
}

function readFileContent(file) {
  try {
    const data = fs.readFileSync(file, 'utf8');
    return data;
  } catch (error) {
    console.error(error);
    process.exit(1);
  }
}

function readApiVersion() {
    const version = readFileContent('./VERSION');
    return version !== undefined ? version.trim() : '';
}
