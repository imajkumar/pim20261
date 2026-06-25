#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
STUDIO_DIR="${SCRIPT_DIR}/assets/studio"

cd "${STUDIO_DIR}"

if [[ ! -d node_modules ]]; then
  echo "Installing npm dependencies..."
  npm install
fi

echo "Building SharedDriveBundle Studio assets..."
npm run build

echo "Done. Hard-refresh Pimcore Studio (Ctrl+Shift+R) to load the new build."
