# Pimcore Studio bundle — full guide

Use this guide to **create a new Pimcore Studio bundle** from scratch, or **copy** `SharedDriveBundle` and rename it.

**Working example in this repo:** `bundles/SharedDriveBundle/`  
**Simpler sidebar example:** `bundles/ayra-bundle/doc/STUDIO_PLUGIN.md`

---

## What you get

A Symfony bundle that:

1. Registers with Pimcore via Composer + `config/bundles.php`
2. Tells Studio where your built JavaScript lives (`WebpackEntryPointProvider`)
3. Exposes a **Module Federation** remote that Studio loads on startup
4. Runs your plugin code in `plugins.tsx` → `onInit`

```text
  npm run build
       │
       ▼
  public/studio/build/<uuid>/entrypoints.json + JS
       │
       ▼
  WebpackEntryPointProvider (PHP) → pimcore_studio_ui.webpack_entry_point_provider
       │
       ▼
  Pimcore Studio loads remoteEntry.js → your plugin onInit()
```

---

## Naming rules (important)

Pick these **once** and use them consistently:

| Concept | Example (SharedDrive) | Rules |
|---------|----------------------|--------|
| Folder | `bundles/SharedDriveBundle/` | PascalCase + `Bundle` suffix |
| Composer package | `shareddrive/shareddrivebundle` | **lowercase only** (Composer requirement) |
| PHP namespace | `SharedDrive\Bundle\SharedDriveBundle\` | PSR-4 matches `src/` |
| Bundle class | `SharedDriveBundle` | In `src/SharedDriveBundle.php` |
| Public asset path | `/bundles/shareddrive/` | Lowercase; Symfony `assets:install` symlink name |
| Module Federation `name` | `SharedDriveBundle` | In `rsbuild.config.ts` + `plugins.tsx` `name` — **must match** |
| Dev server port | `3034` | Use a **unique** port per bundle (Ayra: 3033, Quill: 3032) |

---

## Folder structure

```text
bundles/YourBundle/
├── composer.json
├── build-studio.sh
├── config/
│   └── services.yaml
├── doc/
│   └── STUDIO_PLUGIN.md
├── public/
│   └── studio/
│       └── build/
│           └── <uuid>/          # created by npm run build
│               ├── entrypoints.json
│               ├── exposeRemote.js
│               └── static/js/remoteEntry.js
├── src/
│   ├── YourBundle.php
│   ├── DependencyInjection/
│   │   └── YourExtension.php
│   └── Webpack/
│       └── WebpackEntryPointProvider.php
└── assets/
    └── studio/
        ├── package.json
        ├── rsbuild.config.ts
        ├── tsconfig.json
        └── js/
            └── src/
                ├── main.ts
                ├── plugins.tsx              # export your IAbstractPlugin
                └── yourFeature.tsx            # optional: register hooks
```

---

## Step 1 — Create the bundle folder

Copy the existing bundle and rename, or create files manually:

```bash
cp -r bundles/SharedDriveBundle bundles/YourBundle
# Then search-replace names (see checklist at bottom)
```

---

## Step 2 — Bundle `composer.json`

```json
{
  "name": "yourvendor/yourbundle",
  "description": "Pimcore Studio plugin for …",
  "type": "pimcore-bundle",
  "license": "MIT",
  "require": {
    "php": "~8.4.0 || ~8.5.0",
    "pimcore/pimcore": "^2026.1",
    "pimcore/studio-ui-bundle": "^2026.1"
  },
  "autoload": {
    "psr-4": {
      "YourVendor\\Bundle\\YourBundle\\": "src/"
    }
  },
  "extra": {
    "pimcore": {
      "bundles": [
        "YourVendor\\Bundle\\YourBundle\\YourBundle"
      ]
    }
  }
}
```

---

## Step 3 — PHP bundle class

`src/YourBundle.php`:

```php
<?php
declare(strict_types=1);

namespace YourVendor\Bundle\YourBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;
use YourVendor\Bundle\YourBundle\DependencyInjection\YourExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

final class YourBundle extends AbstractPimcoreBundle
{
    use PackageVersionTrait;

    public function getContainerExtension(): ExtensionInterface
    {
        return new YourExtension();
    }

    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}
```

`src/DependencyInjection/YourExtension.php`:

```php
<?php
declare(strict_types=1);

namespace YourVendor\Bundle\YourBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class YourExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../config')
        );
        $loader->load('services.yaml');
    }
}
```

---

## Step 4 — Webpack entry point provider

Studio does not guess your JS path. This class points at `entrypoints.json` after build.

`src/Webpack/WebpackEntryPointProvider.php`:

```php
<?php
declare(strict_types=1);

namespace YourVendor\Bundle\YourBundle\Webpack;

use Pimcore\Bundle\StudioUiBundle\Webpack\WebpackEntryPointProviderInterface;

/**
 * @internal
 */
if (interface_exists(WebpackEntryPointProviderInterface::class)) {
    final class WebpackEntryPointProvider implements WebpackEntryPointProviderInterface
    {
        public function getEntryPointsJsonLocations(): array
        {
            return glob(__DIR__ . '/../../public/studio/build/*/entrypoints.json') ?: [];
        }

        public function getEntryPoints(): array
        {
            return ['exposeRemote'];
        }

        public function getOptionalEntryPoints(): array
        {
            return [];
        }
    }
}
```

`config/services.yaml`:

```yaml
services:
  _defaults:
    autowire: true
    autoconfigure: true
    public: false

  YourVendor\Bundle\YourBundle\Webpack\WebpackEntryPointProvider:
    tags:
      - { name: 'pimcore_studio_ui.webpack_entry_point_provider' }
```

---

## Step 5 — Register in the project

### Root `composer.json`

Add a path repository and require the package:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "bundles/YourBundle",
      "options": { "symlink": true }
    }
  ],
  "require": {
    "yourvendor/yourbundle": "*"
  }
}
```

Then install (Docker example):

```bash
docker compose exec php composer update yourvendor/yourbundle --no-interaction
```

### `config/bundles.php`

```php
YourVendor\Bundle\YourBundle\YourBundle::class => ['all' => true],
```

**Do not** set a bundle to `false` — remove the line entirely to disable. `false` breaks Symfony’s bundle list.

---

## Step 6 — Studio frontend (`assets/studio/`)

### `package.json`

Key fields to customize:

```json
{
  "name": "YourBundle-studio",
  "scripts": {
    "dev": "NODE_ENV=development rsbuild build",
    "dev-server": "NODE_ENV=dev-server rsbuild dev",
    "build": "rsbuild build",
    "postbuild": "DEST=../../../../public/bundles/yourbundle/studio/build; SRC=../../public/studio/build; if [ -L ../../../../public/bundles/yourbundle ]; then exit 0; fi; mkdir -p \"$DEST\" && cp -r \"$SRC\"/* \"$DEST\"/",
    "check-types": "tsc --noEmit"
  },
  "dependencies": {
    "@pimcore/studio-ui-bundle": "^1.0.0-canary.20260325-113631-293aa9b",
    "antd": "5.22.x",
    "inversify": "6.1.x",
    "react": "18.3.x",
    "react-dom": "18.3.x",
    "reflect-metadata": "^0.2.2"
  },
  "devDependencies": {
    "@module-federation/rsbuild-plugin": "^2.2.3",
    "@rsbuild/core": "^1.3.21",
    "@rsbuild/plugin-react": "^1.3.1",
    "typescript": "^5.6.3"
  }
}
```

Replace `yourbundle` in `postbuild` with your **lowercase** public folder name.

### `tsconfig.json`

Copy from `bundles/SharedDriveBundle/assets/studio/tsconfig.json` unchanged.

### `js/src/main.ts`

```typescript
import 'reflect-metadata'

if (module.hot !== undefined) {
  module.hot.accept()
}
```

### `js/src/plugins.tsx` — minimum plugin

```typescript
import { type IAbstractPlugin } from '@pimcore/studio-ui-bundle'
import { type Container } from 'inversify'

export const YourStudioPlugin: IAbstractPlugin = {
  name: 'YourBundle',   // must match Module Federation name in rsbuild.config.ts

  onInit: ({ container }: { container: Container }) => {
    // Register sidebar icons, dynamic types, services, etc.
    console.log('YourBundle Studio plugin loaded')
  }
}

if (module.hot !== undefined) {
  module.hot.accept()
}
```

### `rsbuild.config.ts` — values to change

Copy `bundles/SharedDriveBundle/assets/studio/rsbuild.config.ts` and update:

| Line / setting | Change to |
|----------------|-----------|
| `assetBase` | `'/bundles/yourbundle/studio/build/' + buildId` |
| `server.port` / `dev.client.port` | Unique port (e.g. `3035`) |
| `chain.output.uniqueName` | `'YourBundle'` |
| `pluginModuleFederation({ name: … })` | `'YourBundle'` |
| `exposes: { '.': './js/src/plugins.tsx' }` | Keep as-is unless you rename `plugins.tsx` |

---

## Step 7 — Build script

`build-studio.sh`:

```bash
#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
STUDIO_DIR="${SCRIPT_DIR}/assets/studio"

cd "${STUDIO_DIR}"

if [[ ! -d node_modules ]]; then
  echo "Installing npm dependencies..."
  npm install
fi

echo "Building YourBundle Studio assets..."
npm run build

echo "Done. Hard-refresh Pimcore Studio (Ctrl+Shift+R) to load the new build."
```

```bash
chmod +x bundles/YourBundle/build-studio.sh
./bundles/YourBundle/build-studio.sh
```

---

## Step 8 — Symfony cache and assets

```bash
docker compose exec php php bin/console cache:clear
docker compose exec php php bin/console assets:install --symlink public
```

Open Pimcore Studio and hard-refresh (`Ctrl+Shift+R`).

---

## Adding features in `onInit`

### A) Left sidebar icon (simple)

See `bundles/ayra-bundle/assets/studio/js/src/plugins.tsx` — uses `ComponentRegistry` and a sidebar slot.

Official doc: [Add an Entry to the Left Sidebar](https://github.com/pimcore/studio-ui-bundle/blob/2026.x/doc/04_Extending/02_Plugin_Development_Examples/02_Add_an_Entry_to_the_Left_Sidebar.md)

### B) Override object / document field UI (advanced)

SharedDriveBundle replaces the image field footer for fields named `qrcode`:

- File: `assets/studio/js/src/registerDigitalAssetQrCodeDownloadButton.tsx`
- Pattern: override `DynamicTypeObjectDataRegistry` and `DynamicTypeDocumentEditableRegistry` for type `image`
- Uses Pimcore `useDownload()` from `@pimcore/studio-ui-bundle/modules/asset`

Register from `plugins.tsx`:

```typescript
import { registerDigitalAssetQrCodeDownloadButton } from './registerDigitalAssetQrCodeDownloadButton'

onInit: ({ container }) => {
  registerDigitalAssetQrCodeDownloadButton(container)
}
```

### C) Custom PHP (controllers, Twig, data object types)

Add under `src/`, register in `config/services.yaml`, routes in bundle `config/routes.yaml` or attribute routes. See `bundles/ayra-bundle/` for examples (Button field type, OAuth controller, Twig prepend pass).

---

## Local dev server (optional)

```bash
cd bundles/YourBundle/assets/studio
npm run dev-server
```

Set `NODE_ENV=dev-server` so Rsbuild serves with HMR. Use a port that does not clash with other bundles.

---

## Checklist — plugin not loading

| Check | What to verify |
|--------|----------------|
| Build output | `bundles/YourBundle/public/studio/build/<uuid>/entrypoints.json` exists |
| Public URL | Browser can open `/bundles/yourbundle/studio/build/<uuid>/exposeRemote.js` |
| Symlink | `public/bundles/yourbundle` → bundle `public/` (from `assets:install --symlink`) |
| Composer | `composer show yourvendor/yourbundle` succeeds |
| Bundle enabled | Class listed in `config/bundles.php` (not `false`) |
| Federation name | `rsbuild.config.ts` `name` === `plugins.tsx` `name` |
| Cache | `bin/console cache:clear` after PHP/YAML changes |
| Browser | Hard refresh Studio (`Ctrl+Shift+R`) after JS rebuild |

---

## Checklist — copy SharedDriveBundle to a new name

Search and replace across the new folder:

| Find | Replace with |
|------|----------------|
| `SharedDriveBundle` | `YourBundle` |
| `SharedDrive` (namespace) | `YourVendor` |
| `shareddrive/shareddrivebundle` | `yourvendor/yourbundle` |
| `shareddrive` (URL path) | `yourbundle` |
| `3034` (dev port) | unused port |
| `SharedDrive\Bundle\SharedDriveBundle` | `YourVendor\Bundle\YourBundle` |

Then:

1. `composer update yourvendor/yourbundle`
2. Add to `config/bundles.php`
3. Add path repo + require in root `composer.json`
4. `./bundles/YourBundle/build-studio.sh`
5. `cache:clear` + `assets:install --symlink public`

---

## Docker one-liners (this project)

```bash
# Install / update bundle
docker compose exec php composer update yourvendor/yourbundle --no-interaction

# Cache + assets
docker compose exec php php bin/console cache:clear
docker compose exec php php bin/console assets:install --symlink public

# Typecheck Studio TS
cd bundles/YourBundle/assets/studio && npm run check-types
```

---

## Generic Data Index & OpenSearch (important commands)

When you work with **data objects** in Studio (e.g. **DigitalAsset**, **Products**), saves can fail with:

```text
index_not_found_exception
no such index [pimcore_data-object_products]
```

OpenSearch is running, but the **search index for that class** was never created. Index names look like:

`{prefix}_data-object_{className}` → default **`pimcore_data-object_products`**

**Full project guide:** [doc/GENERIC_DATA_INDEX.md](../../../doc/GENERIC_DATA_INDEX.md)

### 1. Check OpenSearch is up

```bash
docker compose ps opensearch
docker compose up -d opensearch   # if stopped
```

### 2. Create / update missing indices (most common fix)

```bash
docker compose exec php php bin/console generic-data-index:update:index
```

Creates missing indices, updates mappings, and **queues** objects for indexing.

### 3. Process the index queue (Messenger)

Indexing runs asynchronously. Ensure **supervisord** consumers are running:

```bash
docker compose ps supervisord
docker compose up -d supervisord
```

Or run a consumer manually until the queue is empty:

```bash
docker compose exec php php bin/console messenger:consume pimcore_generic_data_index_queue --time-limit=3600
```

Then **retry saving** in Studio.

### 4. One class only (e.g. Products / DigitalAsset)

`-c` is the **class definition ID** (integer), not the PHP class name. Find it in admin (**Data Objects → Classes**) or SQL:

```sql
SELECT id, name FROM classes WHERE name = 'Products';
```

```bash
docker compose exec php php bin/console generic-data-index:update:index -c 5
```

Replace `5` with the real `id`.

### 5. Asset index

```bash
docker compose exec php php bin/console generic-data-index:update:index -a
```

### 6. Full reset (dev only — deletes and recreates all indices)

```bash
docker compose exec php php bin/console generic-data-index:update:index -r
```

Still run **Messenger consumers** afterward.

### 7. Other index commands

| Command | When to use |
|---------|-------------|
| `generic-data-index:reindex` | Reindex **existing** indices (native reindex) |
| `generic-data-index:deployment:reindex` | After class definition changes — update mappings without deleting indices, then queue affected items |
| `generic-data-index:update:index --update-global-aliases-only` | Fix global index aliases only |

List all:

```bash
docker compose exec php php bin/console list generic-data-index
```

### 8. Configuration

In **`.env`**, **`PIMCORE_OPENSEARCH_DSN`** must point at OpenSearch **from inside Docker** (this project: `opensearch:9200`).

### Typical workflow after adding a new data object class

```bash
docker compose exec php php bin/console generic-data-index:update:index
docker compose up -d supervisord
# wait a few seconds, then open/save objects in Studio
```

---

## Further reading

- [Pimcore studio-example-bundle](https://github.com/pimcore/studio-example-bundle)
- [WebpackEntryPointProvider example](https://github.com/pimcore/studio-example-bundle/blob/main/src/Webpack/WebpackEntryPointProvider.php)
- [Component Registry](https://github.com/pimcore/studio-ui-bundle/blob/2026.x/doc/01_Architecture_Overview/01_SDK_Overview/04_Component_Registry.md)
- Ayra bundle (sidebar + select providers): `bundles/ayra-bundle/doc/STUDIO_PLUGIN.md`
- SharedDrive QR download source: `bundles/SharedDriveBundle/assets/studio/js/src/registerDigitalAssetQrCodeDownloadButton.tsx`
- Generic Data Index / OpenSearch: [doc/GENERIC_DATA_INDEX.md](../../../doc/GENERIC_DATA_INDEX.md)
