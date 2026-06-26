# SSOLoginBundle

**SAML 2.0 SSO** for Pimcore Studio with **Ping Identity** (PingFederate / PingOne).

## Documentation

| Guide | Description |
|-------|-------------|
| [doc/PING_SAML_SSO.md](doc/PING_SAML_SSO.md) | Technical setup — Ping IdP, `.env`, testing |
| [doc/CLIENT_SSO_HANDOFF.md](doc/CLIENT_SSO_HANDOFF.md) | **Client checklist** — what to send / request for SSO onboarding |
| [doc/STUDIO_PLUGIN.md](doc/STUDIO_PLUGIN.md) | Studio build and login button |

## Quick start

```bash
docker compose exec php composer update ssologin/ssologinbundle --no-interaction
./bundles/SSOLoginBundle/build-studio.sh
docker compose exec php php bin/console cache:clear
docker compose exec php php bin/console assets:install --symlink public
```

Configure `.env` (see PING_SAML_SSO.md), then open Studio login.

## Package

| Item | Value |
|------|--------|
| Composer | `ssologin/ssologinbundle` |
| Bundle class | `SSOLogin\Bundle\SSOLoginBundle\SSOLoginBundle` |
| SAML login URL | `/sso-login/saml/login` |
