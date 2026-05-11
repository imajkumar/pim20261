#!/usr/bin/env bash
# Clear Pimcore tree locks and edit locks in the MariaDB container (docker compose).
# Run from anywhere; resolves project root automatically.

set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
cd "${PROJECT_ROOT}"

docker compose exec -T db mysql -upimcore -ppimcore pimcore < "${SCRIPT_DIR}/clear-pimcore-locks.sql"
echo "Pimcore locks cleared (tree_locks + edit_lock). Reload Pimcore Studio in the browser."
