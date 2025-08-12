import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

export function generateLoaderJs(filename, suffix) {
  // read contents of loader.js.template.template as string
  const loaderJsTemplate = fs.readFileSync(`${__dirname}/loader.js.template`, {
    encoding: "utf-8",
  });
  // replace the correct placeholder with the actual filename
  const loaderJsReplaced = loaderJsTemplate.replaceAll(
    "{{path}}",
    `../${filename}`
  );
  // write script to the dist folder as loader.js.template
  fs.mkdirSync(`./dist/${suffix}`, { recursive: true })
  fs.writeFileSync(
    path.resolve(`./dist/${suffix}/loader.js`),
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

  const loaderContent = loaderJsTemplate
    .replaceAll("{{path}}", `${loaderPath}`)
    .replaceAll("/../src/wrapper.js", "/src/wrapper.js");

  fs.writeFileSync(path.resolve('./dist/loader.js'), loaderContent, {
    encoding: 'utf-8',
  });
}

