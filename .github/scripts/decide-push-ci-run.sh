#!/usr/bin/env bash
# Decide whether a push-triggered CI workflow should run its heavy jobs.
#
# Matrix (feature branches):
#   - New branch / tags / workflow_call / workflow_dispatch → run
#   - Later push matching MODE+PATH_PREFIXES → run
#   - Later push not matching → run until this branch has a prior success for
#     WORKFLOW_FILE, then skip (catch-up for branches created under path filters)
#
# Required env: EVENT_NAME, REF_TYPE, BEFORE, AFTER, BRANCH, THIS_RUN, GH_TOKEN,
#               GH_REPO, WORKFLOW_FILE, MODE (ignore|include), PATH_PREFIXES
# Optional: OUTPUT_KEY (default run), ALWAYS_ON_NEW_BRANCH (default true)
set -euo pipefail

OUTPUT_KEY="${OUTPUT_KEY:-run}"
ALWAYS_ON_NEW_BRANCH="${ALWAYS_ON_NEW_BRANCH:-true}"
ZERO="0000000000000000000000000000000000000000"

emit() {
  local value="$1"
  local reason="$2"
  echo "${OUTPUT_KEY}=${value}" >>"${GITHUB_OUTPUT}"
  echo "${reason}"
}

branch_has_success() {
  local count
  count="$(
    gh api \
      "repos/${GH_REPO}/actions/workflows/${WORKFLOW_FILE}/runs?branch=${BRANCH}&status=completed&per_page=30" \
      --jq "[.workflow_runs[] | select(.id != ${THIS_RUN} and .conclusion == \"success\")] | length"
  )"
  [ "${count}" -gt 0 ]
}

path_matches() {
  local f="$1"
  local p
  # shellcheck disable=SC2086
  for p in ${PATH_PREFIXES}; do
    if [[ "${p}" == */ ]]; then
      [[ "${f}" == "${p}"* ]] && return 0
    else
      [[ "${f}" == "${p}" || "${f}" == "${p}"/* ]] && return 0
    fi
  done
  return 1
}

# Non-push events (workflow_call, workflow_dispatch, …) always run
if [ "${EVENT_NAME}" != "push" ]; then
  emit true "Run: ${EVENT_NAME}"
  exit 0
fi

if [ "${REF_TYPE}" = "tag" ]; then
  emit true "Run: tag"
  exit 0
fi

if [ "${ALWAYS_ON_NEW_BRANCH}" = "true" ] &&
  { [ -z "${BEFORE:-}" ] || [ "${BEFORE}" = "${ZERO}" ]; }; then
  emit true "Run: new branch push"
  exit 0
fi

if ! git cat-file -e "${BEFORE}^{commit}" 2>/dev/null; then
  emit true "Run: before commit not reachable"
  exit 0
fi

file_count=0
path_hit=0
while IFS= read -r f; do
  [ -z "${f}" ] && continue
  file_count=$((file_count + 1))
  case "${MODE}" in
    include)
      if path_matches "${f}"; then
        path_hit=1
        break
      fi
      ;;
    ignore)
      if ! path_matches "${f}"; then
        path_hit=1
        break
      fi
      ;;
    *)
      echo "Unknown MODE=${MODE}" >&2
      exit 1
      ;;
  esac
done < <(git diff --name-only "${BEFORE}" "${AFTER}")

if [ "${file_count}" -eq 0 ]; then
  emit true "Run: empty diff"
  exit 0
fi

if [ "${path_hit}" -eq 1 ]; then
  emit true "Run: path filter matched (MODE=${MODE})"
  exit 0
fi

if branch_has_success; then
  emit false "Skip: paths did not match and ${WORKFLOW_FILE} already succeeded on ${BRANCH}"
else
  emit true "Run: paths did not match but no prior successful ${WORKFLOW_FILE} on ${BRANCH}"
fi
