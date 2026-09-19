#!/usr/bin/env bash
# Build GitHub Actions matrices for Combined PHP Build tests.
#
# Diffs the current HEAD against origin/next (three-dot) and expands a
# consumer graph from each module's composer.json require (internal packages
# only, including cycles). App-only changes test that app; dropping a
# composer require drops that consumer from later library diffs. Shared CI
# paths and tags/main force the full set.
#
# Usage:
#   FORCE_FULL=true .github/scripts/php-test-matrix.sh
#   .github/scripts/php-test-matrix.sh zmsadmin/src/foo.php
#
# Writes selected_modules (JSON array of modules that should actually run
# phpcs/phpunit) plus quality/unit matrices and run_* flags to $GITHUB_OUTPUT
# when that file is set. Combined PHP Build always instantiates the full
# default job names (required checks) and no-ops modules not in selected.
set -euo pipefail

PHP_VERSION="${PHP_VERSION:-8.3}"
DIFF_BASE="${DIFF_BASE:-origin/next}"
FORCE_FULL="${FORCE_FULL:-false}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

ALL_PHP=(
  mellon
  zmsadmin
  zmsbackend
  zmscalldisplay
  zmscitizenapi
  zmsclient
  zmsdldb
  zmsentities
  zmsmessaging
  zmsslim
  zmsstatistic
  zmsticketprinter
)

UNIT_STANDARD=(
  mellon
  zmsadmin
  zmscalldisplay
  zmscitizenapi
  zmsdldb
  zmsentities
  zmsmessaging
  zmsslim
  zmsstatistic
  zmsticketprinter
)

is_true() {
  case "${1:-}" in
    true|TRUE|yes|YES|1) return 0 ;;
    *) return 1 ;;
  esac
}

is_php_module() {
  local name="$1"
  local module
  for module in "${ALL_PHP[@]}"; do
    if [[ "$module" == "$name" ]]; then
      return 0
    fi
  done
  return 1
}

is_force_full_path() {
  local path="$1"
  case "$path" in
    .github/*|.github|.resources/*|.resources)
      return 0
      ;;
    phpmd.rules.xml|cli|cli_base.py|cli_test.py)
      return 0
      ;;
  esac
  return 1
}

is_ignored_path() {
  local path="$1"
  case "$path" in
    docs|docs/*|zmscitizenview|zmscitizenview/*|zmsautomation|zmsautomation/*|zmslayout|zmslayout/*|zmsbase|zmsbase/*)
      return 0
      ;;
    .vscode|.vscode/*|.devcontainer|.devcontainer/*|.husky|.husky/*)
      return 0
      ;;
    README.md|CHANGELOG.md|CONTRIBUTING.md|CODE_OF_CONDUCT.md|LICENSE|SECURITY.md|.gitignore|.gitattributes|.editorconfig)
      return 0
      ;;
  esac
  return 1
}

build_direct_consumers_json() {
  local module
  for module in "${ALL_PHP[@]}"; do
    jq -c --arg dir "$module" '{
        dir: $dir,
        name: (.name // ""),
        requires: ((.require // {}) | keys)
      }' "$REPO_ROOT/$module/composer.json"
  done | jq -s -c '
    (map(select(.name != "") | {(.name): .dir}) | add // {}) as $name2dir
    | reduce .[] as $mod ({};
        reduce ($mod.requires[] | select($name2dir[.] != null) | $name2dir[.]) as $dep (.;
          .[$dep] = ((.[$dep] // []) + [$mod.dir] | unique)
        )
      )
  '
}

DIRECT_CONSUMERS_JSON="$(build_direct_consumers_json)"

consumers_of() {
  local module="$1"
  jq -n -r --arg start "$module" --argjson graph "$DIRECT_CONSUMERS_JSON" '
    def closure:
      . as $in
      | ($in | map($graph[.] // []) | add // [] | unique) as $more
      | if ($more - $in | length) == 0 then $in
        else ($in + $more | unique | closure)
        end;
    [$start] | closure | unique[]
  '
}

modules_to_matrix() {
  local -a modules=("$@")
  if [[ "${#modules[@]}" -eq 0 ]]; then
    printf '%s' '{"include":[]}'
    return
  fi
  printf '%s\n' "${modules[@]}" \
    | jq -R -s -c --arg php "$PHP_VERSION" '
        split("\n")
        | map(select(length > 0))
        | unique
        | sort
        | {include: map({module: ., php_version: $php})}
      '
}

collect_changed_files() {
  if [[ $# -gt 0 ]]; then
    printf '%s\n' "$@"
    return
  fi

  echo "HEAD=$(git -C "$REPO_ROOT" rev-parse --short HEAD) $(git -C "$REPO_ROOT" rev-parse HEAD)" >&2

  # Do not use --prune: actions/checkout limits remote.origin.fetch to the
  # current branch, and prune then drops refs we just need (including next).
  # Fetch next into FETCH_HEAD even when origin/next is not a tracking ref.
  if ! git -C "$REPO_ROOT" fetch --no-tags origin refs/heads/next; then
    echo "git fetch origin refs/heads/next failed" >&2
    return 2
  fi

  local base
  base="$(git -C "$REPO_ROOT" rev-parse FETCH_HEAD)"
  echo "next=$(git -C "$REPO_ROOT" rev-parse --short FETCH_HEAD) ${base}" >&2

  if ! git -C "$REPO_ROOT" merge-base "$base" HEAD >/dev/null; then
    echo "No merge-base between next (${base}) and HEAD" >&2
    return 2
  fi

  git -C "$REPO_ROOT" diff --name-only "${base}...HEAD"
}

selected_has() {
  local needle="$1"
  local item
  for item in "${selected[@]+"${selected[@]}"}"; do
    if [[ "$item" == "$needle" ]]; then
      return 0
    fi
  done
  return 1
}

filter_present() {
  local -n _src=$1
  local -n _out=$2
  _out=()
  local item
  for item in "${_src[@]}"; do
    if selected_has "$item"; then
      _out+=("$item")
    fi
  done
}

force_full=false
if is_true "$FORCE_FULL"; then
  force_full=true
fi

changed_files=()
if [[ "$force_full" != true ]]; then
  changed_list=""
  if changed_list="$(collect_changed_files "$@")"; then
    if [[ -n "$changed_list" ]]; then
      mapfile -t changed_files <<< "$changed_list"
    fi
  else
    echo "Falling back to the full PHP test set." >&2
    force_full=true
  fi
fi

if [[ "$force_full" != true ]]; then
  echo "Changed files vs next:" >&2
  if [[ "${#changed_files[@]}" -eq 0 ]]; then
    echo "  (none)" >&2
  else
    printf '  %s\n' "${changed_files[@]}" >&2
  fi
fi

declare -a selected=()

if [[ "$force_full" == true ]]; then
  selected=("${ALL_PHP[@]}")
else
  for path in "${changed_files[@]}"; do
    [[ -z "$path" ]] && continue
    if is_ignored_path "$path"; then
      continue
    fi
    if is_force_full_path "$path"; then
      selected=("${ALL_PHP[@]}")
      break
    fi
    root="${path%%/*}"
    if [[ "$root" == "$path" ]]; then
      # Unknown repo-root file: keep the suite conservative.
      selected=("${ALL_PHP[@]}")
      break
    fi
    if is_php_module "$root"; then
      mapfile -t extra < <(consumers_of "$root")
      selected+=("${extra[@]}")
    fi
  done
  if [[ "${#selected[@]}" -gt 0 ]]; then
    mapfile -t selected < <(printf '%s\n' "${selected[@]}" | awk 'NF' | sort -u)
  fi
fi

quality_modules=()
unit_modules=()
filter_present ALL_PHP quality_modules
filter_present UNIT_STANDARD unit_modules

quality_matrix="$(modules_to_matrix "${quality_modules[@]+"${quality_modules[@]}"}")"
unit_matrix="$(modules_to_matrix "${unit_modules[@]+"${unit_modules[@]}"}")"

if [[ "${#quality_modules[@]}" -eq 0 ]]; then
  selected_modules='[]'
else
  selected_modules="$(printf '%s\n' "${quality_modules[@]}" | jq -R . | jq -s -c .)"
fi

run_quality=false
run_unit=false
run_backend=false
run_client=false
if [[ "${#quality_modules[@]}" -gt 0 ]]; then
  run_quality=true
fi
if [[ "${#unit_modules[@]}" -gt 0 ]]; then
  run_unit=true
fi
if selected_has zmsbackend; then
  run_backend=true
fi
if selected_has zmsclient; then
  run_client=true
fi

{
  echo "PHP test matrix (force_full=${force_full})"
  echo "  selected: ${selected_modules}"
  echo "  quality: ${quality_modules[*]:-(none)}"
  echo "  unit:    ${unit_modules[*]:-(none)}"
  echo "  backend: ${run_backend}"
  echo "  client:  ${run_client}"
} >&2

if [[ -n "${GITHUB_OUTPUT:-}" ]]; then
  {
    echo "quality_matrix<<EOF"
    echo "$quality_matrix"
    echo "EOF"
    echo "unit_matrix<<EOF"
    echo "$unit_matrix"
    echo "EOF"
    echo "selected_modules<<EOF"
    echo "$selected_modules"
    echo "EOF"
    echo "run_quality=${run_quality}"
    echo "run_unit=${run_unit}"
    echo "run_backend=${run_backend}"
    echo "run_client=${run_client}"
  } >> "$GITHUB_OUTPUT"
fi

printf 'quality_matrix=%s\n' "$quality_matrix"
printf 'unit_matrix=%s\n' "$unit_matrix"
printf 'selected_modules=%s\n' "$selected_modules"
printf 'run_quality=%s\n' "$run_quality"
printf 'run_unit=%s\n' "$run_unit"
printf 'run_backend=%s\n' "$run_backend"
printf 'run_client=%s\n' "$run_client"
