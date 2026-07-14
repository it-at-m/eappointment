#!/usr/bin/env bash
set -euo pipefail

repo_root="$(cd "$(dirname "$0")/../.." && pwd)"
env_file="${1:-$repo_root/.devcontainer/.env}"

append_env_default() {
  local key="$1"
  local value="$2"
  if [[ ! -f "$env_file" ]]; then
    return 0
  fi
  if ! grep -q "^${key}=" "$env_file"; then
    echo "${key}=${value}" >> "$env_file"
  fi
}

append_env_default ZMS_BACKEND_TWIG_CACHE false

module_dir="$repo_root/zmsbackend"
mkdir -p "$module_dir/cache" "$module_dir/data"
chmod -R 777 "$module_dir/cache" 2>/dev/null || true

if [[ ! -f "$module_dir/config.php" ]]; then
  "$module_dir/bin/configure"
fi
