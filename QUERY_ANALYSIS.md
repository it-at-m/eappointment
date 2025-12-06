# Query Analysis for User List Endpoints

## 1. Superuser slow: `/admin/users/`
**API Call:** `/api/2/useraccount/` with `resolveReferences=0`

**Queries:**
1. `SELECT * FROM nutzer` (all users, no filtering for superuser)
   - Returns: ALL users in system
   - No JOINs, no WHERE clauses for superuser

**Total: 1 query**

---

## 2. Superuser slow: `/admin/users/role/##/`
**API Call:** `/api/2/role/##/useraccount/` with `resolveReferences=0`

**Queries:**
1. `SELECT * FROM nutzer WHERE Berechtigung = ?` (role filter)
   - Returns: All users with that role level
   - No JOINs

**Total: 1 query**

---

## 3. Superuser slow: `/admin/users/department/##/`
**API Call:** `/api/2/department/##/useraccount/` with `resolveReferences=1`

**Queries:**
1. `SELECT DISTINCT nutzer.* FROM nutzer 
   LEFT JOIN nutzerzuordnung ON nutzer.NutzerID = nutzerzuordnung.nutzerid
   WHERE nutzerzuordnung.behoerdenid IN (?)`
   - Returns: Users in that department

2. **If resolveReferences > 0, for each user:**
   - Loop through users, separate superusers from regular users
   - For superusers: `QUERY_READ_SUPERUSER_DEPARTMENTS` (SELECT all departments)
   - For regular users: `QUERY_READ_ASSIGNED_DEPARTMENTS_FOR_ALL` (bulk query with IN clause)
   - Then: `Department::readEntitiesByIds()` to load department entities

**Total: 3+ queries** (1 user query + 1-2 department queries + department entity loading)

---

## 4. Non-superuser fast: `/admin/users/`
**API Call:** `/api/2/department/{ids}/useraccount/` with `resolveReferences=0`

**Queries:**
1. `SELECT DISTINCT nutzer.* FROM nutzer 
   LEFT JOIN nutzerzuordnung ON nutzer.NutzerID = nutzerzuordnung.nutzerid
   WHERE nutzerzuordnung.behoerdenid IN (user's department IDs)
   AND nutzer.Berechtigung != 90`
   - Returns: Users in user's accessible departments (filtered)
   - Excludes superusers

**Total: 1 query** (no department loading since resolveReferences=0)

---

## 5. Non-superuser fast: `/admin/users/role/##/`
**API Call:** `/api/2/role/##/department/{ids}/useraccount/` with `resolveReferences=1`

**Queries:**
1. `SELECT DISTINCT nutzer.* FROM nutzer 
   LEFT JOIN nutzerzuordnung ON nutzer.NutzerID = nutzerzuordnung.nutzerid
   WHERE nutzer.Berechtigung = ?
   AND nutzerzuordnung.behoerdenid IN (user's department IDs)
   AND nutzer.Berechtigung != 90`
   - Returns: Users with that role in user's accessible departments

2. **If resolveReferences > 0:**
   - `QUERY_READ_ASSIGNED_DEPARTMENTS_FOR_ALL` (bulk query for all regular users)
   - `Department::readEntitiesByIds()` (bulk load all unique departments)

**Total: 3 queries** (1 user query + 1 department assignment query + 1 department entity query)

---

## 6. Non-superuser slow: `/admin/users/department/##/`
**API Call:** `/api/2/department/##/useraccount/` with `resolveReferences=1`

**Queries:**
1. `SELECT DISTINCT nutzer.* FROM nutzer 
   LEFT JOIN nutzerzuordnung ON nutzer.NutzerID = nutzerzuordnung.nutzerid
   WHERE nutzerzuordnung.behoerdenid IN (?)
   AND nutzer.Berechtigung != 90`
   - Returns: Users in that specific department

2. **If resolveReferences > 0:**
   - `QUERY_READ_ASSIGNED_DEPARTMENTS_FOR_ALL` (bulk query for all regular users)
   - `Department::readEntitiesByIds()` (bulk load all unique departments)

**Total: 3 queries** (same as #5, but might return more users if department is large)

---

## Key Differences:

**Fast paths (#4, #5):**
- Filtered results (non-superuser gets only their departments)
- Smaller result sets
- Bulk department loading is efficient

**Slow paths (#1, #2, #3, #6):**
- **#1, #2**: Superuser gets ALL users (huge result set)
- **#3**: Superuser + resolveReferences=1 = loads ALL departments for ALL superusers
- **#6**: Single department might have many users, and resolveReferences=1 loads departments for all

**The bottleneck:**
- For superusers: Getting ALL users without filtering
- For resolveReferences=1: Loading departments for all users (especially superusers who get ALL departments)

