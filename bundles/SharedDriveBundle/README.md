# SharedDrive Bundle

Pimcore Studio plugin: **QR code image field** download button for DigitalAsset (and document image editables named `qrcode`).

**Full guide (copy this bundle or create a new one):** [doc/STUDIO_PLUGIN.md](doc/STUDIO_PLUGIN.md)

## Quick commands

From project root:

```bash
composer update shareddrive/shareddrivebundle
docker compose exec php php bin/console cache:clear
docker compose exec php php bin/console assets:install --symlink public
```

Build Studio assets:

```bash
./bundles/SharedDriveBundle/build-studio.sh
```

Hard-refresh Pimcore Studio (`Ctrl+Shift+R`).

### OpenSearch / data object index (save errors)

If Studio shows `index_not_found_exception` when saving objects:

```bash
docker compose exec php php bin/console generic-data-index:update:index
docker compose up -d supervisord
```

See [doc/GENERIC_DATA_INDEX.md](../../doc/GENERIC_DATA_INDEX.md) and [doc/STUDIO_PLUGIN.md](doc/STUDIO_PLUGIN.md#generic-data-index--opensearch-important-commands).

## Package

| Item | Value |
|------|--------|
| Composer package | `shareddrive/shareddrivebundle` |
| Bundle class | `SharedDrive\Bundle\SharedDriveBundle\SharedDriveBundle` |
| Public URL prefix | `/bundles/shareddrive/` |
| Module Federation name | `SharedDriveBundle` (must match `plugins.tsx` `name`) |
