# Ping SAML SSO — full setup guide

This bundle adds **SAML 2.0 Single Sign-On** with **Ping Identity** (PingFederate, PingOne, or PingOne for Enterprise) to **Pimcore Studio**.

Users click **“Sign in with Ping SSO”** on the Studio login page → Ping authenticates → Pimcore issues a **login token** → Studio opens logged in.

---

## How it works

```text
  Studio login page
       │  click "Sign in with Ping SSO"
       ▼
  GET /sso-login/saml/login
       │  redirect to Ping IdP (HTTP-Redirect)
       ▼
  Ping login (MFA, etc.)
       │  POST SAML Response
       ▼
  POST /sso-login/saml/acs
       │  validate assertion, read NameID / attributes
       │  map to existing Pimcore user (by username)
       │  Authentication::generateTokenByUser()
       ▼
  redirect /pimcore-studio/login?token=…
       │  Studio loginWithToken()
       ▼
  Studio dashboard (authenticated)
```

**Important:** This bundle does **not** auto-provision users. The SAML identity must match an **existing** Pimcore backend user name (configurable attribute mapping).

**Client onboarding (what to send / request from Ping admin):** [CLIENT_SSO_HANDOFF.md](CLIENT_SSO_HANDOFF.md)

---

## Folder map

```text
bundles/SSOLoginBundle/
├── doc/
│   ├── PING_SAML_SSO.md          ← this file
│   └── STUDIO_PLUGIN.md          ← Studio build / plugin wiring
├── src/
│   ├── Controller/                 SAML login, ACS, metadata
│   ├── Security/                 Ping settings + user resolver
│   └── Webpack/                  Studio JS entrypoints
├── assets/studio/                “Sign in with Ping SSO” button
└── config/services.yaml
```

---

## Step 1 — Install the bundle in the project

Root `composer.json` (path repo + require):

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "bundles/SSOLoginBundle",
      "options": { "symlink": true }
    }
  ],
  "require": {
    "ssologin/ssologinbundle": "*"
  }
}
```

```bash
docker compose exec php composer update ssologin/ssologinbundle --no-interaction
```

`config/bundles.php`:

```php
SSOLogin\Bundle\SSOLoginBundle\SSOLoginBundle::class => ['all' => true],
```

`config/routes.yaml`:

```yaml
sso_login_bundle:
    resource: "../bundles/SSOLoginBundle/src/Controller/"
    type: attribute
```

`config/packages/security.yaml` — public SAML routes:

```yaml
access_control:
    - { path: ^/sso-login/saml, roles: PUBLIC_ACCESS }
```

```bash
docker compose exec php php bin/console cache:clear
```

---

## Step 2 — Build Studio assets

```bash
./bundles/SSOLoginBundle/build-studio.sh
docker compose exec php php bin/console assets:install --symlink public
```

Hard-refresh Studio (`Ctrl+Shift+R`). See [STUDIO_PLUGIN.md](STUDIO_PLUGIN.md).

---

## Step 3 — Environment variables

Add to `.env` (never commit real certs/secrets):

```dotenv
###> ssologin/ping-saml ###
# Ping IdP (from Ping admin / SAML metadata XML)
SSO_LOGIN_SAML_IDP_ENTITY_ID=https://idp.example.com
SSO_LOGIN_SAML_IDP_SSO_URL=https://idp.example.com/idp/SSO.saml2
SSO_LOGIN_SAML_IDP_SLO_URL=
SSO_LOGIN_SAML_IDP_X509_CERT="-----BEGIN CERTIFICATE-----
...paste IdP signing cert (single line or PEM)...
-----END CERTIFICATE-----"

# Service Provider (this Pimcore app) — optional; defaults from routes
SSO_LOGIN_SAML_SP_ENTITY_ID=
SSO_LOGIN_SAML_SP_BASE_URL=http://localhost:8082

# Map SAML → Pimcore user name (Pimcore\User::getByName)
# Common Ping attributes: email, mail, sAMAccountName, NameID
SSO_LOGIN_SAML_USERNAME_ATTRIBUTE=email

# Optional SP signing (if Ping requires signed AuthnRequest)
SSO_LOGIN_SAML_SP_X509_CERT=
SSO_LOGIN_SAML_SP_PRIVATE_KEY=

SSO_LOGIN_SAML_DEBUG=0
###< ssologin/ping-saml ###
```

| Variable | Description |
|----------|-------------|
| `SSO_LOGIN_SAML_IDP_ENTITY_ID` | Ping **Issuer / Entity ID** |
| `SSO_LOGIN_SAML_IDP_SSO_URL` | Ping **SSO URL** (Single Sign-On Service) |
| `SSO_LOGIN_SAML_IDP_X509_CERT` | Ping **signing certificate** (PEM, without headers ok) |
| `SSO_LOGIN_SAML_SP_BASE_URL` | Public origin browsers use, e.g. `http://localhost:8082` |
| `SSO_LOGIN_SAML_USERNAME_ATTRIBUTE` | SAML attribute for Pimcore username, or `NameID` |

---

## Step 4 — Configure Ping (IdP)

### URLs to register in Ping

Use your public base URL (`SSO_LOGIN_SAML_SP_BASE_URL`):

| Ping field | Value |
|------------|--------|
| **Entity ID / Audience** | `{base}/sso-login/saml/metadata` or custom `SSO_LOGIN_SAML_SP_ENTITY_ID` |
| **ACS / Assertion Consumer URL** | `{base}/sso-login/saml/acs` |
| **ACS binding** | **HTTP-POST** |
| **NameID format** | Email (recommended) or Persistent |
| **SP metadata URL** (optional) | `{base}/sso-login/saml/metadata` |

Example for local Docker:

```text
Entity ID:     http://localhost:8082/sso-login/saml/metadata
ACS URL:       http://localhost:8082/sso-login/saml/acs
Metadata URL:  http://localhost:8082/sso-login/saml/metadata
```

### PingFederate (high level)

1. **Applications** → create **SAML SP Connection** (or IdP-initiated if needed).
2. Set **ACS URL** and **Entity ID** from table above.
3. Enable **Sign assertions** (typical).
4. **Attribute Contract** → release `email`, `sAMAccountName`, or your chosen username attribute.
5. **Mappings** → map LDAP/AD attribute to SAML attribute name matching `SSO_LOGIN_SAML_USERNAME_ATTRIBUTE`.
6. Download **IdP metadata** or copy Entity ID, SSO URL, and signing certificate into `.env`.

### PingOne

1. **Connections** → **Applications** → Add **SAML Application**.
2. Paste SP metadata from `{base}/sso-login/saml/metadata` **or** enter ACS + Entity ID manually.
3. Copy **IdP metadata** values into `.env`.
4. Under **Attribute Mappings**, ensure `email` (or your username field) is sent.

---

## Step 5 — Pimcore users

Create backend users whose **username** matches the SAML attribute:

| SAML `email` | Pimcore user name |
|--------------|-------------------|
| `jane@company.com` | `jane@company.com` |

Or map `sAMAccountName` → Pimcore user `jdoe` if you set `SSO_LOGIN_SAML_USERNAME_ATTRIBUTE=sAMAccountName`.

Test user must have **Studio access** (perspective / permissions).

---

## Step 6 — Test the flow

1. Open `http://localhost:8082/pimcore-studio/login`
2. Click **Sign in with Ping SSO**
3. Complete Ping login
4. You should land in Studio (token in URL is consumed automatically)

### Troubleshooting

| Symptom | Check |
|---------|--------|
| Button missing | Run `build-studio.sh`, `assets:install`, hard refresh |
| 503 “not configured” | Set `SSO_LOGIN_SAML_IDP_ENTITY_ID`, `IDP_SSO_URL`, `IDP_X509_CERT` |
| Invalid SAML response | Clock skew, wrong IdP cert, ACS URL mismatch in Ping |
| User not found | Username attribute ≠ Pimcore user name; create user in Admin |
| Redirect loop | `SSO_LOGIN_SAML_SP_BASE_URL` must match browser URL |
| Metadata 404 | `cache:clear`, bundle enabled, routes loaded |

Enable debug temporarily:

```dotenv
SSO_LOGIN_SAML_DEBUG=1
```

Check PHP logs / `var/log/` for OneLogin SAML errors.

---

## SAML endpoints (reference)

| Route | Method | Purpose |
|-------|--------|---------|
| `/sso-login/saml/login` | GET | Start SP-initiated SSO |
| `/sso-login/saml/acs` | POST | Assertion Consumer Service (Ping posts here) |
| `/sso-login/saml/metadata` | GET | SP metadata XML for Ping import |

---

## Security notes

- Use **HTTPS** in production for `SSO_LOGIN_SAML_SP_BASE_URL`.
- Keep IdP cert and SP private key in `.env` or secrets manager — not in git.
- SAML routes are **public** (no prior auth); assertions are validated cryptographically.
- Login tokens are short-lived Pimcore recovery tokens; consumed once by Studio API.

---

## Related docs

- [CLIENT_SSO_HANDOFF.md](CLIENT_SSO_HANDOFF.md) — client / Ping admin checklist and email template
- [STUDIO_PLUGIN.md](STUDIO_PLUGIN.md) — Studio plugin build
- [SharedDrive STUDIO_PLUGIN.md](../../SharedDriveBundle/doc/STUDIO_PLUGIN.md) — generic bundle template
- [Ping SAML documentation](https://docs.pingidentity.com/)
- [OneLogin php-saml toolkit](https://github.com/SAML-Toolkits/php-saml)
