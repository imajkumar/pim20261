# Generic Data Index & OpenSearch: `index_not_found_exception`

If saving a **data object** in Pimcore Studio fails with an error like:

```text
index_not_found_exception
no such index [pimcore_data-object_products]
```

then **OpenSearch is running**, but the **search index for that class** was never created (or was deleted). Pimcore’s **Generic Data Index** bundle writes to OpenSearch when you save; the index name is roughly:

`{index_prefix}_data-object_{className}`

The default prefix is **`pimcore_`**, so a class **Products** becomes **`pimcore_data-object_products`** (name is usually the short class name in lowercase).

---

## Fix (recommended order)

### 1. OpenSearch must be up

With Docker Compose from this project, **`opensearch`** should be running:

```bash
docker compose ps opensearch
```

If it is stopped: `docker compose up -d opensearch` and wait until it is healthy.

### 2. Create / update indices (run inside PHP)

From the **project root**, execute the index update **in the PHP container** (same env as the app):

```bash
docker compose exec php php bin/console generic-data-index:update:index
```

That **creates missing indices**, updates mappings where needed, and **queues** objects for indexing.

### 3. Let the index queue run

Indexing is processed by **Symfony Messenger**. In this skeleton, **supervisord** is supposed to run consumers (see `.docker/supervisord.conf`). Ensure the **`supervisord`** service is up:

```bash
docker compose ps supervisord
docker compose up -d supervisord
```

If you do not use supervisord locally, run a consumer manually (until you stop it):

```bash
docker compose exec php php bin/console messenger:consume pimcore_generic_data_index_queue --time-limit=3600
```

Then **retry saving** the object in Studio.

---

## Stronger reset (dev only)

To **delete and recreate all** search indices and re-queue everything (slower, use when mappings are broken or many classes are missing):

```bash
docker compose exec php php bin/console generic-data-index:update:index -r
```

Still ensure **Messenger consumers** run afterward so the queue is processed.

---

## Only one class (e.g. Products)

`-c` is the **class definition ID** (integer from Pimcore), not the PHP class name. Find it in the admin UI under **Data Objects → Classes → Products** (often visible in the URL or class settings), or from SQL:

```sql
SELECT id, name FROM classes WHERE name = 'Products';
```

Then:

```bash
docker compose exec php php bin/console generic-data-index:update:index -c 5
```

(replace `5` with the real `id`).

---

## Configuration check

OpenSearch DSN for Pimcore is usually in **`.env`** as **`PIMCORE_OPENSEARCH_DSN`**. It must point at the **`opensearch`** host from **inside Docker** (this project’s default uses `opensearch:9200`).

---

## Official documentation

- [Generic Data Index — Index management](https://github.com/pimcore/generic-data-index-bundle/blob/2026.x/doc/02_Configuration/03_Index_Management.md) (commands `generic-data-index:update:index`, `-r`, queue workers)
