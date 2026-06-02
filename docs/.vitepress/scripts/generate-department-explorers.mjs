#!/usr/bin/env node
/**
 * Regenerates departmentExplorerToday.js and departmentExplorerTarget.js
 * from repo PHP sources and docs/.vitepress/theme/data/department-target/.
 */
import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const repoRoot = path.resolve(__dirname, "../../..");
const dataDir = path.resolve(__dirname, "../theme/data");
const targetRoot = path.join(dataDir, "department-target");

const LANG = { ".php": "php", ".json": "json", ".java": "java" };

const TODAY_REPO_FILES = [
  "zmsentities/schema/department.json",
  "zmsentities/src/Zmsentities/Department.php",
  "zmsentities/src/Zmsentities/Collection/DepartmentList.php",
  "zmsentities/src/Zmsentities/Schema/Validator.php",
  "zmsentities/src/Zmsentities/Exception/SchemaValidation.php",
  "zmsdb/src/Zmsdb/Department.php",
  "zmsdb/src/Zmsdb/Query/Department.php",
  "zmsdb/src/Zmsdb/Link.php",
  "zmsdb/src/Zmsdb/Query/Link.php",
  "zmsdb/src/Zmsdb/DayOff.php",
  "zmsdb/src/Zmsdb/Query/DayOff.php",
  "zmsdb/src/Zmsdb/Cluster.php",
  "zmsdb/src/Zmsdb/Query/Cluster.php",
  "zmsdb/src/Zmsdb/Scope.php",
  "zmsdb/src/Zmsdb/Query/Scope.php",
  "zmsdb/src/Zmsdb/Workstation.php",
  "zmsdb/src/Zmsdb/Query/Workstation.php",
  "zmsdb/src/Zmsdb/Organisation.php",
  "zmsdb/src/Zmsdb/Query/Organisation.php",
  "zmsdb/src/Zmsdb/Query/Useraccount.php",
  "zmsdb/src/Zmsdb/Exception/Department/ScopeListNotEmpty.php",
  "zmsdb/src/Zmsdb/Exception/Department/InvalidId.php",
  "zmsapi/src/Zmsapi/DepartmentGet.php",
  "zmsapi/src/Zmsapi/DepartmentList.php",
  "zmsapi/src/Zmsapi/DepartmentUpdate.php",
  "zmsapi/src/Zmsapi/DepartmentDelete.php",
  "zmsapi/src/Zmsapi/DepartmentAddScope.php",
  "zmsapi/src/Zmsapi/DepartmentAddCluster.php",
  "zmsapi/src/Zmsapi/DepartmentByScopeId.php",
  "zmsapi/src/Zmsapi/DepartmentWorkstationList.php",
  "zmsapi/src/Zmsapi/OrganisationByDepartment.php",
  "zmsapi/src/Zmsapi/OrganisationAddDepartment.php",
  "zmsapi/src/Zmsapi/UseraccountListByDepartments.php",
  "zmsapi/src/Zmsapi/UseraccountListByRoleAndDepartments.php",
  "zmsapi/src/Zmsapi/Exception/Department/DepartmentNotFound.php",
];

const ROUTING_RANGES = [
  [1246, 1537],
  [1590, 1642],
  [1993, 2093],
  [2838, 2880],
  [4464, 4505],
];

function readRepo(relativePath) {
  return fs.readFileSync(path.join(repoRoot, relativePath), "utf8");
}

function excerptEntityValidation() {
  const lines = readRepo("zmsentities/src/Zmsentities/Schema/Entity.php").split(
    "\n"
  );
  const header = `<?php

namespace BO\\Zmsentities\\Schema;

/**
 * Excerpt from zmsentities/src/Zmsentities/Schema/Entity.php — schema validation API.
 */
abstract class Entity extends \\ArrayObject
{
    // … constructor, addData, readJsonSchema, …

`;
  const body = lines.slice(88, 130).join("\n");
  return `${header}${body}

    // … remaining Entity methods …
}
`;
}

function excerptUseraccountDepartmentMethods() {
  const lines = readRepo("zmsdb/src/Zmsdb/Useraccount.php").split("\n");
  const header = `<?php

namespace BO\\Zmsdb;

/**
 * Excerpt from zmsdb/src/Zmsdb/Useraccount.php — department-related query methods.
 * Full class: ${lines.length} lines.
 */
class Useraccount extends Base
{
    // … other methods …

`;
  const body = lines.slice(910, 999).join("\n");
  const body2 = lines.slice(1060, 1119).join("\n");
  return `${header}${body}

    // … other methods …

${body2}

    // … remaining methods …
}
`;
}

function excerptRouting() {
  const lines = readRepo("zmsapi/routing.php").split("\n");
  const parts = ROUTING_RANGES.map(([start, end], index) => {
    const chunk = lines.slice(start - 1, end).join("\n");
    if (index === 0) {
      return `<?php

/**
 * Excerpt from zmsapi/routing.php — all department-related route registrations.
 * Full file: ${lines.length} lines; controllers are registered on \\App::$slim (Slim).
 */

${chunk}`;
    }
    return `\n\n// … other routes …\n\n${chunk}`;
  });
  return `${parts.join("")}\n\n// … remaining routes …\n`;
}

function buildTree(filePaths) {
  const root = [];

  for (const filePath of [...filePaths].sort()) {
    const parts = filePath.split("/");
    let level = root;

    for (let i = 0; i < parts.length - 1; i += 1) {
      let folder = level.find(
        (node) => node.name === parts[i] && node.type === "folder"
      );
      if (!folder) {
        folder = { name: parts[i], type: "folder", children: [] };
        level.push(folder);
      }
      level = folder.children;
    }

    const fileName = parts[parts.length - 1];
    level.push({
      name: fileName,
      type: "file",
      path: filePath,
      language: LANG[path.extname(fileName)] ?? "text",
    });
  }

  return root;
}

function collectTargetFiles(dir, prefix = "") {
  const entries = fs.readdirSync(dir, { withFileTypes: true });
  const files = [];

  for (const entry of entries) {
    const relative = prefix ? `${prefix}/${entry.name}` : entry.name;
    const absolute = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      files.push(...collectTargetFiles(absolute, relative));
    } else if (entry.isFile() && entry.name.endsWith(".java")) {
      files.push(relative);
    }
  }

  return files;
}

function writeExplorer(moduleName, tree, files, defaultPath) {
  const content = `export const tree = ${JSON.stringify(tree, null, 2)};

export const files = ${JSON.stringify(files, null, 2)};

export const defaultPath = ${JSON.stringify(defaultPath)};
`;
  fs.writeFileSync(path.join(dataDir, moduleName), content);
}

function buildTodayExplorer() {
  const filePaths = ["zmsapi/routing.php", ...TODAY_REPO_FILES];
  const files = {};

  files["zmsapi/routing.php"] = { language: "php", content: excerptRouting() };
  files["zmsdb/src/Zmsdb/Useraccount.php"] = {
    language: "php",
    content: excerptUseraccountDepartmentMethods(),
  };
  files["zmsentities/src/Zmsentities/Schema/Entity.validation.php"] = {
    language: "php",
    content: excerptEntityValidation(),
  };

  for (const relativePath of TODAY_REPO_FILES) {
    files[relativePath] = {
      language: LANG[path.extname(relativePath)] ?? "text",
      content: readRepo(relativePath),
    };
  }

  filePaths.push("zmsdb/src/Zmsdb/Useraccount.php");
  filePaths.push("zmsentities/src/Zmsentities/Schema/Entity.validation.php");

  writeExplorer(
    "departmentExplorerToday.js",
    buildTree(filePaths),
    files,
    "zmsapi/routing.php"
  );

  console.log(`today: ${Object.keys(files).length} files`);
}

function buildTargetExplorer() {
  let relativeFiles = collectTargetFiles(targetRoot);
  const files = {};

  for (const relativePath of relativeFiles) {
    files[relativePath] = {
      language: "java",
      content: fs.readFileSync(path.join(targetRoot, relativePath), "utf8"),
    };
  }

  writeExplorer(
    "departmentExplorerTarget.js",
    buildTree(relativeFiles),
    files,
    "src/main/java/de/muenchen/zms/department/api/DepartmentController.java"
  );

  console.log(`target: ${relativeFiles.length} files`);
}

buildTodayExplorer();
buildTargetExplorer();
