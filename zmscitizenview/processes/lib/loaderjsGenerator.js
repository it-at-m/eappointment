import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);


export function generateLoaderJs(filename) {
  // read contents of loader.js.template.template as string
  const loaderJsTemplate = fs.readFileSync(`${__dirname}/loader.js.template`, {encoding: 'utf-8'});
  // replace the correct placeholder with the actual filename
  const loaderJsReplaced = loaderJsTemplate.replace('{{filename}}', filename);
  // write script to the dist folder as loader.js.template
  fs.writeFileSync(path.resolve('./dist/loader.js'), loaderJsReplaced, {encoding: 'utf-8'});

  // Generate slightly different loader.js for vitepress
  const vitepressLoaderJsTemplate = fs.readFileSync(`${__dirname}/vitepress-loader.js.template`, {encoding: 'utf-8'});
  const vitepressLoaderJsReplaced = vitepressLoaderJsTemplate.replace('{{filename}}', filename);
  fs.writeFileSync(path.resolve('./dist/vitepress-loader.js'), vitepressLoaderJsReplaced, {encoding: 'utf-8'});
}

