import { generateLoaderJs } from './lib/loaderjsGenerator.js';
import manifest from '../dist/.vite/manifest.json' with {type: 'json'};

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

// read filename from manifest.json
const filename = manifest['index.html'].file;

// generate loaderJs with the app script's filename
generateLoaderJs(filename);
