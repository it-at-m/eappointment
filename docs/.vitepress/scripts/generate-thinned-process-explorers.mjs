#!/usr/bin/env node
/**
 * Regenerates thinnedProcessExplorerToday.js and thinnedProcessExplorerTarget.js
 * from zmscitizenapi + zmsbackend PHP sources and docs/.vitepress/theme/data/citizen-target/.
 */
import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const repoRoot = path.resolve(__dirname, "../../..");
const dataDir = path.resolve(__dirname, "../theme/data");
const targetRoot = path.join(dataDir, "citizen-target");

const LANG = { ".php": "php", ".json": "json", ".java": "java" };

const TODAY_REPO_FILES = [
  "zmsentities/schema/citizenapi/thinnedProcess.json",
  "zmsentities/schema/citizenapi/thinnedScope.json",
  "zmsentities/schema/process.json",
  "zmsentities/src/Zmsentities/Process.php",
  "zmscitizenapi/src/Zmscitizenapi/Models/ThinnedProcess.php",
  "zmscitizenapi/src/Zmscitizenapi/Models/ThinnedScope.php",
  "zmscitizenapi/src/Zmscitizenapi/Models/ThinnedProvider.php",
  "zmscitizenapi/src/Zmscitizenapi/Controllers/Appointment/AppointmentByIdController.php",
  "zmscitizenapi/src/Zmscitizenapi/Controllers/Appointment/AppointmentReserveController.php",
  "zmscitizenapi/src/Zmscitizenapi/Controllers/Appointment/AppointmentUpdateController.php",
  "zmscitizenapi/src/Zmscitizenapi/Controllers/Appointment/AppointmentConfirmController.php",
  "zmscitizenapi/src/Zmscitizenapi/Controllers/Appointment/AppointmentPreconfirmController.php",
  "zmscitizenapi/src/Zmscitizenapi/Controllers/Appointment/AppointmentCancelController.php",
  "zmscitizenapi/src/Zmscitizenapi/Controllers/Appointment/MyAppointmentsController.php",
  "zmscitizenapi/src/Zmscitizenapi/Services/Appointment/AppointmentByIdService.php",
  "zmscitizenapi/src/Zmscitizenapi/Services/Appointment/AppointmentReserveService.php",
  "zmscitizenapi/src/Zmscitizenapi/Services/Appointment/AppointmentUpdateService.php",
  "zmscitizenapi/src/Zmscitizenapi/Services/Appointment/AppointmentConfirmService.php",
  "zmscitizenapi/src/Zmscitizenapi/Services/Appointment/AppointmentPreconfirmService.php",
  "zmscitizenapi/src/Zmscitizenapi/Services/Appointment/AppointmentCancelService.php",
  "zmscitizenapi/src/Zmscitizenapi/Services/Appointment/MyAppointmentsService.php",
  "zmsbackend/src/Zmsbackend/Process/Api/ProcessGet.php",
  "zmsbackend/src/Zmsbackend/Process/Api/ProcessGetByExternalUserId.php",
  "zmsbackend/src/Zmsbackend/Process/Api/ProcessListByExternalUserId.php",
  "zmsbackend/src/Zmsbackend/Process/Api/ProcessUpdate.php",
  "zmsbackend/src/Zmsbackend/Process/Api/ProcessReserve.php",
  "zmsbackend/src/Zmsbackend/Process/Api/ProcessPreconfirm.php",
  "zmsbackend/src/Zmsbackend/Process/Api/ProcessConfirm.php",
  "zmsbackend/src/Zmsbackend/Process/Api/ProcessDelete.php",
  "zmsbackend/src/Zmsbackend/Process/Api/ProcessConfirmationMail.php",
  "zmsbackend/src/Zmsbackend/Process/Api/ProcessPreconfirmationMail.php",
  "zmsbackend/src/Zmsbackend/Process/Api/ProcessDeleteMail.php",
  "zmsbackend/src/Zmsbackend/Process/Exception/ProcessNotFound.php",
  "zmsbackend/src/Zmsbackend/Process/Exception/AuthKeyMatchFailed.php",
  "zmsbackend/src/Zmsbackend/Process/Service/Process.php",
  "zmsbackend/src/Zmsbackend/Process/Repository/Process.php",
  "zmsbackend/src/Zmsbackend/Process/Service/ProcessStatusQueued.php",
  "zmsbackend/src/Zmsbackend/Process/Repository/ProcessStatusFree.php",
];

const BACKEND_ROUTING_RANGES = [
  [2882, 3740],
  [6566, 6597],
];

function readRepo(relativePath) {
  return fs.readFileSync(path.join(repoRoot, relativePath), "utf8");
}

function excerptLines(
  relativePath,
  start,
  end,
  header,
  footer = "\n    // … remaining methods …\n}\n"
) {
  const lines = readRepo(relativePath).split("\n");
  const body = lines.slice(start - 1, end).join("\n");
  return `${header}\n${body}${footer}`;
}

function excerptMapperProcess() {
  return excerptLines(
    "zmscitizenapi/src/Zmscitizenapi/Services/Core/MapperService.php",
    377,
    641,
    `<?php

namespace BO\\Zmscitizenapi\\Services\\Core;

/**
 * Excerpt from MapperService.php — Process ↔ ThinnedProcess mapping (~260 lines).
 * today: maps giant zmsentities\\Process into citizen ThinnedProcess.
 */
class MapperService
{
    // … office/scope mapping methods …

`,
    "\n    // … remaining MapperService methods …\n}\n"
  );
}

function excerptZmsApiClientProcess() {
  return excerptLines(
    "zmscitizenapi/src/Zmscitizenapi/Services/Core/ZmsApiClientService.php",
    57,
    75,
    `<?php

namespace BO\\Zmscitizenapi\\Services\\Core;

/**
 * Excerpt from ZmsApiClientService.php — HTTP calls to zmsbackend for process/appointment flows.
 */
class ZmsApiClientService
{
    // … other methods …

`,
    ""
  )
    .concat(
      excerptLines(
        "zmscitizenapi/src/Zmscitizenapi/Services/Core/ZmsApiClientService.php",
        236,
        425,
        "",
        ""
      )
    )
    .concat(
      excerptLines(
        "zmscitizenapi/src/Zmscitizenapi/Services/Core/ZmsApiClientService.php",
        498,
        520,
        "",
        "\n    // … remaining methods …\n}\n"
      )
    );
}

function excerptZmsApiFacadeProcess() {
  return excerptLines(
    "zmscitizenapi/src/Zmscitizenapi/Services/Core/ZmsApiFacadeService.php",
    789,
    934,
    `<?php

namespace BO\\Zmscitizenapi\\Services\\Core;

/**
 * Excerpt from ZmsApiFacadeService.php — orchestrates zmsbackend calls + MapperService for appointments.
 */
class ZmsApiFacadeService
{
    // … office/scope/availability methods …

`,
    "\n    // … remaining methods …\n}\n"
  );
}

function excerptValidationProcess() {
  return excerptLines(
    "zmscitizenapi/src/Zmscitizenapi/Services/Core/ValidationService.php",
    173,
    340,
    `<?php

namespace BO\\Zmscitizenapi\\Services\\Core;

/**
 * Excerpt from ValidationService.php — process/appointment validation helpers.
 */
class ValidationService
{
    // … other validation methods …

`,
    "\n    // … remaining methods …\n}\n"
  );
}

function excerptCitizenRouting() {
  const lines = readRepo("zmscitizenapi/routing.php").split("\n");
  const chunk = lines.slice(307, 699).join("\n");
  return `<?php

/**
 * Excerpt from zmscitizenapi/routing.php — all appointment-related route registrations.
 * Full file: ${lines.length} lines.
 */

${chunk}

// … remaining routes …
`;
}

function excerptBackendRouting() {
  const lines = readRepo("zmsbackend/routing.php").split("\n");
  const parts = BACKEND_ROUTING_RANGES.map(([start, end], index) => {
    const chunk = lines.slice(start - 1, end).join("\n");
    if (index === 0) {
      return `<?php

/**
 * Excerpt from zmsbackend/routing.php — process/appointment routes called by zmscitizenapi.
 * Full file: ${lines.length} lines.
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
  const excerptPaths = [
    "zmscitizenapi/routing.php",
    "zmsbackend/routing.process.php",
    "zmscitizenapi/src/Zmscitizenapi/Services/Core/MapperService.process.php",
    "zmscitizenapi/src/Zmscitizenapi/Services/Core/ZmsApiClientService.process.php",
    "zmscitizenapi/src/Zmscitizenapi/Services/Core/ZmsApiFacadeService.process.php",
    "zmscitizenapi/src/Zmscitizenapi/Services/Core/ValidationService.process.php",
  ];

  const filePaths = [...excerptPaths, ...TODAY_REPO_FILES];
  const files = {};

  files["zmscitizenapi/routing.php"] = {
    language: "php",
    content: excerptCitizenRouting(),
  };
  files["zmsbackend/routing.process.php"] = {
    language: "php",
    content: excerptBackendRouting(),
  };
  files[
    "zmscitizenapi/src/Zmscitizenapi/Services/Core/MapperService.process.php"
  ] = {
    language: "php",
    content: excerptMapperProcess(),
  };
  files[
    "zmscitizenapi/src/Zmscitizenapi/Services/Core/ZmsApiClientService.process.php"
  ] = {
    language: "php",
    content: excerptZmsApiClientProcess(),
  };
  files[
    "zmscitizenapi/src/Zmscitizenapi/Services/Core/ZmsApiFacadeService.process.php"
  ] = {
    language: "php",
    content: excerptZmsApiFacadeProcess(),
  };
  files[
    "zmscitizenapi/src/Zmscitizenapi/Services/Core/ValidationService.process.php"
  ] = {
    language: "php",
    content: excerptValidationProcess(),
  };

  for (const relativePath of TODAY_REPO_FILES) {
    files[relativePath] = {
      language: LANG[path.extname(relativePath)] ?? "text",
      content: readRepo(relativePath),
    };
  }

  writeExplorer(
    "thinnedProcessExplorerToday.js",
    buildTree(filePaths),
    files,
    "zmscitizenapi/routing.php"
  );

  console.log(`today: ${Object.keys(files).length} files`);
}

function buildTargetExplorer() {
  const relativeFiles = collectTargetFiles(targetRoot);
  const files = {};

  for (const relativePath of relativeFiles) {
    files[relativePath] = {
      language: "java",
      content: fs.readFileSync(path.join(targetRoot, relativePath), "utf8"),
    };
  }

  writeExplorer(
    "thinnedProcessExplorerTarget.js",
    buildTree(relativeFiles),
    files,
    "src/main/java/de/muenchen/zms/citizen/thinnedprocess/api/ThinnedProcessController.java"
  );

  console.log(`target: ${relativeFiles.length} files`);
}

buildTodayExplorer();
buildTargetExplorer();
