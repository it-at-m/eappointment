import path from "node:path";
import {generateLoaderJs, generateSingleLoaderJs} from './lib/loaderjsGenerator.js';
import manifest from '../dist/src/.vite/manifest.json' with {type: 'json'};

/**
 * Why this?
 *
 * When we build our custom web componente, vite automatically adds a
 * "cache busting" mechanic to our minified JS-File.
 *
 * Cache-busting is a way of making sure, that a user will always get the
 * newest version of a file, no matter which caching-mechanisms are in the way.
 * Be it the caching of a browser, a plugin, or even a CDN or the server itself.
 *
 * It works by generating a hash for every file and adding that hash to the name of the file.
 *
 * Normally it would work this way simply by not caching your "index.html"-File and thus all needed
 * assets would be automatically loaded.
 *
 * But we have a web-component! So the "index.js" IS our entry-point but we WANT to have cache-busting
 * so the Users don't have to download megabytes of useless and unchanged data.
 *
 * This is why we generate ourselfs a "loader.js.template"-File. This file will be the "new" entry point when someone wants
 * to use our web component. The file will only contain one single import and that import is our index.js
 * but will automatically get it's new hash-value added every time we build our code.
 */

// Required filename content to be treated as webcomponent
const REQUIRED_PREFIX = 'src/';
const REQUIRED_SUFFIX = 'webcomponent.ts';

for (const key in manifest) {
  if (key.startsWith(REQUIRED_PREFIX) && key.endsWith(REQUIRED_SUFFIX)) {
    // read filename from manifest
    const entrypoint = manifest[key].file;

    // get fileName and directory for generating loader file
    const fileName = path.basename(key, path.extname(key));
    const dirName = path.dirname(key);
    generateLoaderJs(entrypoint, dirName, fileName);

    // Can be deleted after all loader.js have been replaced
    if (key.includes('src/zms-appointment-webcomponent.ts')){
      generateSingleLoaderJs(`${fileName}/loader.js`)
    }
  }
}
