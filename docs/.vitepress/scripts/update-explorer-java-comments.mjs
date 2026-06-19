#!/usr/bin/env node
/** Update "today:" javadoc in RefArch explorer demo Java sources after GH-2604. */
import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const dataRoot = path.resolve(
  path.dirname(fileURLToPath(import.meta.url)),
  "../theme/data"
);

const REPLACEMENTS = [
  [
    "zmsapi\\\\DepartmentList, zmsdb\\\\Department::readList",
    "zmsbackend\\\\Department\\\\Api\\\\DepartmentList, zmsbackend\\\\Department\\\\Service\\\\Department::readList",
  ],
  [
    "zmsapi\\\\DepartmentGet, zmsdb\\\\Department::readEntity",
    "zmsbackend\\\\Department\\\\Api\\\\DepartmentGet, zmsbackend\\\\Department\\\\Service\\\\Department::readEntity",
  ],
  [
    "zmsapi\\\\DepartmentUpdate, zmsdb\\\\Department::updateEntity",
    "zmsbackend\\\\Department\\\\Api\\\\DepartmentUpdate, zmsbackend\\\\Department\\\\Service\\\\Department::updateEntity",
  ],
  [
    "zmsapi\\\\DepartmentDelete, zmsdb\\\\Department::deleteEntity",
    "zmsbackend\\\\Department\\\\Api\\\\DepartmentDelete, zmsbackend\\\\Department\\\\Service\\\\Department::deleteEntity",
  ],
  [
    "zmsapi\\\\DepartmentWorkstationList, zmsdb\\\\Workstation::readCollectionByDepartmentId",
    "zmsbackend\\\\Department\\\\Api\\\\DepartmentWorkstationList, zmsbackend\\\\Workstation\\\\Service\\\\Workstation::readCollectionByDepartmentId",
  ],
  [
    "zmsapi\\\\UseraccountListByDepartments, zmsdb\\\\Useraccount::readSearchByDepartmentIds",
    "zmsbackend\\\\Useraccount\\\\Api\\\\UseraccountListByDepartments, zmsbackend\\\\Useraccount\\\\Service\\\\Useraccount::readSearchByDepartmentIds",
  ],
  [
    "zmsapi\\\\UseraccountListByRoleAndDepartments",
    "zmsbackend\\\\Useraccount\\\\Api\\\\UseraccountListByRoleAndDepartments",
  ],
  [
    "zmsapi\\\\OrganisationByDepartment, zmsdb\\\\Organisation::readByDepartmentId",
    "zmsbackend\\\\Organisation\\\\Api\\\\OrganisationByDepartment, zmsbackend\\\\Organisation\\\\Service\\\\Organisation::readByDepartmentId",
  ],
  [
    "zmsapi\\\\OrganisationAddDepartment, zmsdb\\\\Department::writeEntity",
    "zmsbackend\\\\Organisation\\\\Api\\\\OrganisationAddDepartment, zmsbackend\\\\Department\\\\Service\\\\Department::writeEntity",
  ],
  [
    "zmsapi\\\\DepartmentAddScope, zmsdb\\\\Scope::writeEntity",
    "zmsbackend\\\\Department\\\\Api\\\\DepartmentAddScope, zmsbackend\\\\Scope\\\\Service\\\\Scope::writeEntity",
  ],
  [
    "zmsapi\\\\DepartmentAddCluster, zmsdb\\\\Cluster::writeEntity",
    "zmsbackend\\\\Department\\\\Api\\\\DepartmentAddCluster, zmsbackend\\\\Cluster\\\\Service\\\\Cluster::writeEntity",
  ],
  [
    "zmsapi\\\\DepartmentByScopeId, zmsdb\\\\Department::readByScopeId",
    "zmsbackend\\\\Department\\\\Api\\\\DepartmentByScopeId, zmsbackend\\\\Department\\\\Service\\\\Department::readByScopeId",
  ],
  [
    "zmsdb\\\\Department writeDepartmentLinks/Dayoffs/Mail helpers",
    "zmsbackend\\\\Department\\\\Service\\\\Department writeDepartmentLinks/Dayoffs/Mail helpers",
  ],
  [
    "zmsdb\\\\Department::readResolvedReferences",
    "zmsbackend\\\\Department\\\\Service\\\\Department::readResolvedReferences",
  ],
  [
    "zmsapi\\\\Helper\\\\User::checkDepartment / checkDepartments",
    "zmsbackend\\\\Helper\\\\User::checkDepartment / checkDepartments",
  ],
  [
    "zmsdb\\\\Workstation::readCollectionByDepartmentId",
    "zmsbackend\\\\Workstation\\\\Service\\\\Workstation::readCollectionByDepartmentId",
  ],
  [
    "zmsdb\\\\Useraccount::readSearchByDepartmentIds, readListByRoleAndDepartmentIds",
    "zmsbackend\\\\Useraccount\\\\Service\\\\Useraccount::readSearchByDepartmentIds, readListByRoleAndDepartmentIds",
  ],
  [
    "zmsdb\\\\Scope::readByDepartmentId, writeEntity",
    "zmsbackend\\\\Scope\\\\Service\\\\Scope::readByDepartmentId, writeEntity",
  ],
  [
    "zmsdb\\\\Department + zmsdb\\\\Query\\\\Department",
    "zmsbackend\\\\Department\\\\Service\\\\Department + zmsbackend\\\\Department\\\\Repository\\\\Department",
  ],
  [
    "zmsdb\\\\Organisation::readByDepartmentId",
    "zmsbackend\\\\Organisation\\\\Service\\\\Organisation::readByDepartmentId",
  ],
  [
    "zmsdb\\\\Link::readByDepartmentId",
    "zmsbackend\\\\Link\\\\Service\\\\Link::readByDepartmentId",
  ],
  [
    "zmsdb\\\\DayOff::readOnlyByDepartmentId",
    "zmsbackend\\\\Dayoff\\\\Service\\\\DayOff::readOnlyByDepartmentId",
  ],
  [
    "zmsdb\\\\Cluster::readByDepartmentId, writeEntity",
    "zmsbackend\\\\Cluster\\\\Service\\\\Cluster::readByDepartmentId, writeEntity",
  ],
  [
    "zmsdb\\\\Process + zmsdb\\\\Query\\\\Process",
    "zmsbackend\\\\Process\\\\Service\\\\Process + zmsbackend\\\\Process\\\\Repository\\\\Process",
  ],
  [
    "zmsdb\\\\Query\\\\Request",
    "zmsbackend\\\\Request\\\\Repository\\\\Request",
  ],
  [
    "zmsdb\\\\Process status transitions (reserve, confirm, cancel)",
    "zmsbackend\\\\Process\\\\Service\\\\Process status transitions (reserve, confirm, cancel)",
  ],
  [
    "zmsdb\\\\Query\\\\Process::TABLE",
    "zmsbackend\\\\Process\\\\Repository\\\\Process::TABLE",
  ],
  [
    "zmsdb\\\\Query\\\\Request::BATABLE",
    "zmsbackend\\\\Request\\\\Repository\\\\Request::BATABLE",
  ],
];

function walk(dir, out = []) {
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const absolute = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      walk(absolute, out);
    } else if (entry.isFile() && entry.name.endsWith(".java")) {
      out.push(absolute);
    }
  }
  return out;
}

let updated = 0;
for (const file of walk(dataRoot)) {
  let content = fs.readFileSync(file, "utf8");
  let changed = false;
  for (const [from, to] of REPLACEMENTS) {
    if (content.includes(from)) {
      content = content.split(from).join(to);
      changed = true;
    }
  }
  if (changed) {
    fs.writeFileSync(file, content);
    updated += 1;
  }
}

console.log(`updated ${updated} Java files`);
