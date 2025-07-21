import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

export function generateLoaderJs(filename, subdirectory, suffix) {
  // read contents of loader.js.template.template as string
  const loaderJsTemplate = fs.readFileSync(`${__dirname}/loader.js.template`, {
    encoding: "utf-8",
  });
  // replace the correct placeholder with the actual filename
  const loaderJsReplaced = loaderJsTemplate.replaceAll(
    "{{path}}",
    `${subdirectory}/${filename}`
  );
  // write script to the dist folder as loader.js.template
  fs.writeFileSync(
    path.resolve(`./dist/loader-${suffix}.js`),
    loaderJsReplaced,
    {
      encoding: "utf-8",
    }
  );
}

export function generateSingleLoaderJs(loaderPath) {
  // read contents of loader.js.template.template as string
  const loaderJsTemplate = fs.readFileSync(`${__dirname}/loader.js.template`, {
    encoding: "utf-8",
  });

  const loaderContent = loaderJsTemplate.replaceAll("{{path}}", `${loaderPath}`);

  fs.writeFileSync(path.resolve('./dist/loader.js'), loaderContent, {
    encoding: 'utf-8',
  });
}

