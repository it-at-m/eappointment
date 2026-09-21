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

export function compatRootLoaderSource(template, nestedLoaderPath) {
  return template
    .replaceAll("{{path}}", nestedLoaderPath)
    .replaceAll("./../wrapper.js", "./wrapper.js");
}

export function generateSingleLoaderJs(loaderPath) {
  const loaderJsTemplate = fs.readFileSync(`${__dirname}/loader.js.template`, {
    encoding: "utf-8",
  });

  fs.writeFileSync(
    path.resolve("./dist/loader.js"),
    compatRootLoaderSource(loaderJsTemplate, loaderPath),
    {
      encoding: "utf-8",
    }
  );
}

