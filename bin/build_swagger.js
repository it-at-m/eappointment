var fs = require('fs');
const swaggerParser = require('swagger-parser');
const swaggerJsdoc = require('swagger-jsdoc');
const yaml = require('js-yaml');

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
validateSwagger();

function validateSwagger() {

    fs.stat('public/doc/swagger.yaml', function(error, stats) {
        var routessize = stats.size;
        if (error) {
            console.log(error);
        } else {
            console.log("Found public/doc/swagger.yaml with " + routessize + " bytes");
        }
       
        swaggerParser.validate('public/doc/swagger.yaml', (err, api) => {
            if (err) {
                console.error(err);
              }
              else {
                console.log("Validated API %s, Version: %s", api.info.title, api.info.version);
              }
        })
    });
}

function buildSwagger() {
    let basics = readFileContent('public/doc/partials/basic.yaml') + "\n";
    let sources = {
        paths: 
          openapiSpecification.paths,
    }
    let definitions = readFileContent('public/doc/partials/definitions.yaml');
    writeSwaggerFile(basics + yaml.dump(sources) + definitions)
    
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
    fs.readFile('./VERSION', 'utf8' , (err, data) => {
        if (err) {
          console.error(err)
          return
        }
        return data;
      })
}


