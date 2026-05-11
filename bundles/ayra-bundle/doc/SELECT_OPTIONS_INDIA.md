# India State & City select options (Ayra bundle)

This bundle ships two **dynamic option providers** for Pimcore **Select** fields:

| Provider class | Purpose |
|----------------|---------|
| `Ayra\Bundle\AyraBundle\DataObject\IndiaStateSelectOptionsProvider` | All **states and union territories of India** (static list). |
| `Ayra\Bundle\AyraBundle\DataObject\IndiaCitySelectOptionsProvider` | **Cities** that depend on the selected **state** (reads another field on the same object). |

Data lives in **`src/DataObject/IndiaGeoData.php`**. City lists are **representative** (major towns); extend `IndiaGeoData::CITIES_BY_STATE` if you need every municipality.

---

## 1. Field names on your class

Your generated class uses lowercase field names **`state`** and **`city`** (see `var/classes/DataObject/Products.php`). The examples below assume:

- Select field **`state`** — uses the **state** provider.
- Select field **`city`** — uses the **city** provider.

If your field names differ, set **Options provider data** on the **city** field to the **exact** state field name (for example `region` if that field holds the state value).

---

## 2. Configure the **State** select (Pimcore Studio)

1. Open **Data Objects → Classes →** your class (e.g. **Products**).
2. Open the **State** select field.
3. Under options / dynamic options:
   - **Options source / provider type:** **Class** (or “Options provider class”, depending on UI wording). In definitions this is `class` / `TYPE_CLASS`.
   - **Options provider class:**  
     `Ayra\Bundle\AyraBundle\DataObject\IndiaStateSelectOptionsProvider`
4. Save the class definition and **deploy** / save layout as usual.

---

## 3. Configure the **City** select (important: leading `@`)

Pimcore resolves option providers either as **`new ClassName()`** (no Symfony container) or as **`@service_id`** from the container. The city provider **must** use the **`@` form** so Symfony injects **`RequestStack`**. That allows reading **unsaved** `state` from the Studio **`POST /pimcore-studio/api/data-objects/select-options`** JSON body (`changedData`). Without `@`, cities follow only the **last saved** state from the database (wrong list while you edit).

1. Open the **City** select field on the same class.
2. Set:
   - **Options provider type:** **Class**
   - **Options provider class** (exact string, including the leading `@`):  
     `@Ayra\Bundle\AyraBundle\DataObject\IndiaCitySelectOptionsProvider`
   - **Options provider data:**  
     `state`  
     (Name of the field that holds the selected state **value**, e.g. `bihar`, not the visible label.)

3. Save the class.

Studio already calls **`POST /pimcore-studio/api/data-objects/select-options`** via RTK Query (`dataObjectGetSelectOptions`). You do **not** need a custom RTK slice in your bundle. Pimcore merges **`changedData`** into the object **before** `getOptions()` runs; this provider also reads the same JSON (including **nested** `changedData` shapes) and falls back to **`request_stack` from the container** when Pimcore instantiates the class with `new …` (no `@`), so cities still track the selected state in typical Studio setups.

---

## 4. Clear cache

After changing class definitions or PHP providers:

```bash
bin/console cache:clear
```

In Docker:

```bash
docker compose exec php php bin/console cache:clear
```

---

## 5. How it works (technical)

- Pimcore calls `getOptions(array $context, Data $fieldDefinition)` on the provider.
- **`$context['object']`** is the persisted object (DB state).
- For **city**, the provider first tries **`changedData.<stateField>`** from the current **select-options** HTTP request (when `RequestStack` is injected via `@…`). That matches the value you just picked in the form **before Save**.
- If nothing is in `changedData`, it falls back to **`$object->getState()`** (or the getter for whatever you put in **Options provider data**).
- State **values** are stable slugs (e.g. `bihar`, `maharashtra`). City **values** are slugs per city.

---

## 6. Symfony services

Both providers are **public** services in **`config/services.yaml`**. The city service is wired with **`$requestStack: '@request_stack'`**.

- **State** provider: you may use the plain class name **`Ayra\Bundle\AyraBundle\DataObject\IndiaStateSelectOptionsProvider`** (no constructor dependencies).
- **City** provider: use **`@Ayra\Bundle\AyraBundle\DataObject\IndiaCitySelectOptionsProvider`** in the class definition as described above.

---

## 7. Related files

| File | Role |
|------|------|
| `src/DataObject/IndiaGeoData.php` | State list + `CITIES_BY_STATE` map + `isCityValueAllowedForState` |
| `src/DataObject/IndiaStateSelectOptionsProvider.php` | State select provider |
| `src/DataObject/IndiaCitySelectOptionsProvider.php` | City select provider (reads `changedData` from request when wired with `@`) |
| `src/EventSubscriber/DataObject/ProductsIndiaGeoStateCitySubscriber.php` | Aligns **city** with **state** on save: clears when state empty; sets **first** city for the state when city missing or invalid (fixes Studio auto-save sending only `state`). |
| `assets/studio/js/src/indiaDependentSelectFetchPatch.ts` | Studio **`fetch`** patch: injects **`changedData.state`** for city **`select-options`** calls |
| `assets/studio/js/src/indiaStateLabelToValue.ts` | State **label → value** map for DOM fallback (keep in sync with **`IndiaGeoData::STATES`**) |

---

## 8. Stale city after changing state (auto-save)

Studio **PUT** updates often send **only** the fields you touched, e.g. `editableData: { "state": "goa" }`. The previous **city** may be wrong for the new state, or **city** may be empty.

**`ProductsIndiaGeoStateCitySubscriber`** on **`PRE_ADD`** / **`PRE_UPDATE`**:

- If **state** is empty → **city** is cleared.
- If **state** is set and **city** is empty **or** not allowed for that state (see `IndiaGeoData::isCityValueAllowedForState`) → **city** is set to **`IndiaGeoData::firstCityValueForState`** (first city in `CITIES_BY_STATE` for that region), so auto-save does not leave **`city: null`** once a state is chosen (e.g. Goa → **Panaji** by default; change it in the dropdown if you prefer another Goa city).
- If **city** is already valid for the **state** → unchanged.

If your class name is not **`Products`**, copy the subscriber and adjust `PRODUCTS_CLASS_NAME` (or generalize with a parameter).

### Studio UI: correct city **options** without reloading the page

The edit layout still embeds options from the **last saved** object. Pimcore’s `POST …/select-options` request does not always send **`changedData.state`** until auto-save has run, so the city dropdown can briefly show the wrong region (e.g. Goa cities while **State** shows Delhi).

The Ayra **Studio plugin** wraps **`fetch`** (`assets/studio/js/src/indiaDependentSelectFetchPatch.ts`):

1. For **`fieldName: "city"`**, it sets **`changedData.state`** using **what you see on screen first** (label → slug via **`indiaStateLabelToValue.ts`**), then **sessionStorage**, then the request body. That avoids a **stale** saved state (e.g. Gujarat) overriding **Bihar** you just picked before auto-save.

Rebuild after edits: `cd bundles/ayra-bundle/assets/studio && npm run build` (the **`postbuild`** script copies assets into **`public/bundles/ayra/studio/build/`**).

If Studio shows **translated** state labels, extend the label→slug map or rely on sessionStorage after the first successful save.

---

## 9. Troubleshooting

| Symptom | Likely cause |
|---------|----------------|
| After switching **State**, API still returns old **city** (e.g. Goa city with Bihar state) | Deploy **`ProductsIndiaGeoStateCitySubscriber`** and run **`cache:clear`**. Invalid cities are replaced by the **first** city for the new state. |
| City list stays on the **old** state after you change **State** in the form | Class definition still had a plain FQCN (Pimcore used `new` without DI). Use **`@…`**, run **`cache:clear`**, and restart PHP workers if needed so `ClassResolver`’s static cache is cleared. The Ayra provider also resolves **`request_stack` from the container** as a fallback when `new` is used. |
| City list is always “Select state first” | **Options provider data** does not match your state field name, or `changedData` does not include that key. |
| City dropdown shows cities from the **wrong** state until page reload | Rebuild the Ayra Studio bundle (`npm run build` in `assets/studio`). Ensure **`select-options`** receives **`changedData.state`** — the fetch patch + PHP provider handle this after deploy. |
