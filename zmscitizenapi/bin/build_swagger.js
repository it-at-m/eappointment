var fs = require('fs');
const swaggerJsdoc = require('swagger-jsdoc');
const yaml = require('js-yaml');
const path = require('path');

const options = {
    definition: {
      openapi: '2.0.0',
      info: {
        version: readApiVersion(),
        title: "ZMS API"
      },
    },
    apis: ['./routing.php']
  };

const openapiSpecification = swaggerJsdoc(options);

buildSwagger();

function buildSwagger() {
  let version = readFileContent('public/doc/partials/version.yaml') + "\n";
  let info = readFileContent('public/doc/partials/info.yaml');
  //append current api version to info
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
    fs.writeFileSync('public/doc/swagger.yaml', data, 'utf8');
    console.log("Build new swagger file successfully!");
  } catch (e) {
    console.log(e);
  }
}

function readFileContent(file) {
  try {
    const data = fs.readFileSync(file, 'utf8');
    return data;
  } catch (e) {
    console.log(e);
  }
}

function readApiVersion() {
  const versionFile = path.resolve(__dirname, '../VERSION');
  return fs.readFileSync(versionFile, 'utf8').trim();
}
