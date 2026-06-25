# Ayra Bundle

Example **Pimcore Studio** plugin: adds a **left sidebar** icon that opens a simple **browser alert**. Same overall pattern as **`pimcore/quill-bundle`** (Rsbuild, Module Federation, `WebpackEntryPointProvider`).

## Default document (homepage)

The skeleton `default/default.html.twig` only shows content in **edit mode**, so `/` looks empty and the Pimcore logo SVG may 404. Ayra registers its own `templates/default/default.html.twig` (via a compiler pass) with a simple landing page and links to **Studio** and **Admin**.

**Full guide:** [doc/STUDIO_PLUGIN.md](doc/STUDIO_PLUGIN.md)  
**India state/city select providers:** [doc/SELECT_OPTIONS_INDIA.md](doc/SELECT_OPTIONS_INDIA.md)  
Also covers **Studio element trees**, **why lock exists**, **how to unlock**, **Docker stuck locks** (clear DB + `user:` hint), and **common `bin/console` commands**: [Studio trees & locking](doc/STUDIO_PLUGIN.md#pimcore-studio-trees-data-objects-assets-documents-locking-and-unlock).

## Folder map (short)

```text
bundles/ayra-bundle/
├── assets/studio/          # npm project — edit plugins.tsx here
├── public/studio/build/    # build output (entrypoints.json) — run npm run build
├── config/services.yaml   # Symfony services (Studio webpack + India select providers)
├── src/                    # PHP bundle + WebpackEntryPointProvider
└── doc/
    ├── STUDIO_PLUGIN.md
    └── SELECT_OPTIONS_INDIA.md
```

## Minimal commands

From the **project** root:

```bash
composer update ayra/ayra-bundle
bin/console cache:clear
bin/console assets:install
```

From **`bundles/ayra-bundle/assets/studio`** (after changing TS/React or first install):

```bash
npm install
npm run build
```

Then open **Pimcore Studio** and use the new sidebar icon.

## Composer package

- **Name:** `ayra/ayra-bundle`
- **Bundle class:** `Ayra\Bundle\AyraBundle\AyraBundle`
