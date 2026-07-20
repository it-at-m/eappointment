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
  "zmsbackend/src/Zmsbackend/Department/Service/Department.php",
  "zmsbackend/src/Zmsbackend/Department/Repository/Department.php",
  "zmsbackend/src/Zmsbackend/Link/Service/Link.php",
  "zmsbackend/src/Zmsbackend/Link/Repository/Link.php",
  "zmsbackend/src/Zmsbackend/Dayoff/Service/DayOff.php",
  "zmsbackend/src/Zmsbackend/Dayoff/Repository/DayOff.php",
  "zmsbackend/src/Zmsbackend/Cluster/Service/Cluster.php",
  "zmsbackend/src/Zmsbackend/Cluster/Repository/Cluster.php",
  "zmsbackend/src/Zmsbackend/Scope/Service/Scope.php",
  "zmsbackend/src/Zmsbackend/Query/Scope.php",
  "zmsbackend/src/Zmsbackend/Workstation/Service/Workstation.php",
  "zmsbackend/src/Zmsbackend/Workstation/Repository/Workstation.php",
  "zmsbackend/src/Zmsbackend/Organisation/Service/Organisation.php",
  "zmsbackend/src/Zmsbackend/Organisation/Repository/Organisation.php",
  "zmsbackend/src/Zmsbackend/Useraccount/Repository/Useraccount.php",
  "zmsbackend/src/Zmsbackend/Department/Exception/ScopeListNotEmpty.php",
  "zmsbackend/src/Zmsbackend/Department/Exception/InvalidId.php",
  "zmsbackend/src/Zmsbackend/Department/Api/DepartmentGet.php",
  "zmsbackend/src/Zmsbackend/Department/Api/DepartmentList.php",
  "zmsbackend/src/Zmsbackend/Department/Api/DepartmentUpdate.php",
  "zmsbackend/src/Zmsbackend/Department/Api/DepartmentDelete.php",
  "zmsbackend/src/Zmsbackend/Department/Api/DepartmentAddScope.php",
  "zmsbackend/src/Zmsbackend/Department/Api/DepartmentAddCluster.php",
  "zmsbackend/src/Zmsbackend/Department/Api/DepartmentByScopeId.php",
  "zmsbackend/src/Zmsbackend/Department/Api/DepartmentWorkstationList.php",
  "zmsbackend/src/Zmsbackend/Organisation/Api/OrganisationByDepartment.php",
  "zmsbackend/src/Zmsbackend/Organisation/Api/OrganisationAddDepartment.php",
  "zmsbackend/src/Zmsbackend/Useraccount/Api/UseraccountListByDepartments.php",
  "zmsbackend/src/Zmsbackend/Useraccount/Api/UseraccountListByRoleAndDepartments.php",
  "zmsbackend/src/Zmsbackend/Department/Exception/DepartmentNotFound.php",
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
  const relativePath =
    "zmsbackend/src/Zmsbackend/Useraccount/Service/Useraccount.php";
  const lines = readRepo(relativePath).split("\n");
  const header = `<?php

namespace BO\\Zmsbackend\\Useraccount\\Service;

/**
 * Excerpt from ${relativePath} — department-related query methods.
 * Full class: ${lines.length} lines.
 */
class Useraccount extends Base
{
    // … other methods …

`;
  const body = lines.slice(992, 1017).join("\n");
  const body2 = lines.slice(1077, 1140).join("\n");
  return `${header}${body}

    // … other methods …

${body2}

    // … remaining methods …
}
`;
}

function excerptRouting() {
  const lines = readRepo("zmsbackend/routing.php").split("\n");
  const parts = ROUTING_RANGES.map(([start, end], index) => {
    const chunk = lines.slice(start - 1, end).join("\n");
    if (index === 0) {
      return `<?php

/**
 * Excerpt from zmsbackend/routing.php — all department-related route registrations.
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
  const useraccountExcerptPath =
    "zmsbackend/src/Zmsbackend/Useraccount/Service/Useraccount.php";
  const filePaths = ["zmsbackend/routing.php", ...TODAY_REPO_FILES];
  const files = {};

  files["zmsbackend/routing.php"] = {
    language: "php",
    content: excerptRouting(),
  };
  files[useraccountExcerptPath] = {
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

  filePaths.push(useraccountExcerptPath);
  filePaths.push("zmsentities/src/Zmsentities/Schema/Entity.validation.php");

  writeExplorer(
    "departmentExplorerToday.js",
    buildTree(filePaths),
    files,
    "zmsbackend/routing.php"
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
