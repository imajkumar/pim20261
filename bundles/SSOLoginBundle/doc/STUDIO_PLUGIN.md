# SSOLoginBundle — Studio plugin

Adds **“Sign in with Ping SSO”** to the Pimcore Studio login form.

---

## Quick build

```bash
./bundles/SSOLoginBundle/build-studio.sh
docker compose exec php php bin/console assets:install --symlink public
```

Hard-refresh Studio (`Ctrl+Shift+R`).

---

## Plugin registration

`assets/studio/js/src/plugins.tsx` registers `PingSsoLoginButton` on the `form.login` slot (same pattern as Ayra Google login).

Button navigates to:

```text
/sso-login/saml/login
```

---

## Naming (must match)

| Setting | Value |
|---------|--------|
| Module Federation `name` | `SSOLoginBundle` |
| `plugins.tsx` `name` | `SSOLoginBundle` |
| `rsbuild` `uniqueName` | `SSOLoginBundle` |
| Public asset path | `/bundles/ssologin/studio/build/<uuid>/` |
| Dev server port | `3035` |

---

## Typecheck

```bash
cd bundles/SSOLoginBundle/assets/studio && npm run check-types
```

---

## Full SAML setup

See [PING_SAML_SSO.md](PING_SAML_SSO.md).
