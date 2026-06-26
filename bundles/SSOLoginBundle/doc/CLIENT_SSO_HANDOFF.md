# Ping SAML SSO — client handoff checklist

Use this document when onboarding a **client / Ping administrator** for Pimcore Studio SSO.

- **Technical setup (your team):** [PING_SAML_SSO.md](PING_SAML_SSO.md)
- **Studio build:** [STUDIO_PLUGIN.md](STUDIO_PLUGIN.md)

---

## Overview

| Role | Responsibility |
|------|----------------|
| **You (Pimcore team)** | Deploy SSOLoginBundle, share SP URLs/metadata, configure `.env`, create test Pimcore users |
| **Client (Ping / IT)** | Register Pimcore as SAML SP in Ping, share IdP metadata, release username attribute |
| **Client (Pimcore admin)** | Ensure backend users exist and have Studio access |

**Important:** SSOLoginBundle does **not** auto-create Pimcore users. The SAML identity must match an **existing** Pimcore username.

---

## Part A — Share with the client (Service Provider details)

Send these values to the client’s **Ping / IdP administrator**. Replace `{base}` with the public URL users open in the browser.

| Item | Value |
|------|--------|
| **Application name** | Pimcore Studio *(or client-specific name)* |
| **Protocol** | SAML 2.0 |
| **SP Entity ID** | `{base}/sso-login/saml/metadata` |
| **ACS URL** (Assertion Consumer Service) | `{base}/sso-login/saml/acs` |
| **ACS binding** | **HTTP-POST** |
| **SP metadata URL** | `{base}/sso-login/saml/metadata` |
| **SSO initiation** | SP-initiated — user clicks **Sign in with Ping SSO** on Studio login |

### Environment examples

| Environment | `{base}` example |
|-------------|------------------|
| Local dev | `http://localhost:8082` |
| Staging | `https://pimcore-staging.client.com` |
| Production | `https://pimcore.client.com` |

### Filled example (staging)

```text
SP Entity ID:    https://pimcore-staging.client.com/sso-login/saml/metadata
ACS URL:         https://pimcore-staging.client.com/sso-login/saml/acs
ACS binding:     HTTP-POST
SP metadata URL: https://pimcore-staging.client.com/sso-login/saml/metadata
```

### Optional: attach SP metadata XML

After the bundle is deployed, open in a browser:

```text
{base}/sso-login/saml/metadata
```

Save the XML and send it to the client for **import into Ping** (PingFederate / PingOne).

---

## Part B — Request from the client (IdP details)

Ask the client to provide **one** of:

1. **IdP metadata XML** *(preferred)*, or  
2. The fields below separately:

| # | Ping / IdP field | Maps to `.env` |
|---|------------------|----------------|
| 1 | **IdP Entity ID / Issuer** | `SSO_LOGIN_SAML_IDP_ENTITY_ID` |
| 2 | **SSO URL** (Single Sign-On Service) | `SSO_LOGIN_SAML_IDP_SSO_URL` |
| 3 | **IdP signing certificate** (X.509 PEM) | `SSO_LOGIN_SAML_IDP_X509_CERT` |
| 4 | **SLO URL** *(optional, single logout)* | `SSO_LOGIN_SAML_IDP_SLO_URL` |

### User / attribute mapping

| # | Question for client | Notes |
|---|---------------------|--------|
| 5 | Which SAML attribute is the **login username**? | e.g. `email`, `mail`, `sAMAccountName`, `NameID` → `SSO_LOGIN_SAML_USERNAME_ATTRIBUTE` |
| 6 | **Sample test user** value | e.g. `jane@company.com` — must match a Pimcore user name |
| 7 | User provisioning model | We require **pre-existing** Pimcore users; confirm if they expect auto-provision *(not supported by default)* |

### Security / Ping policy

| # | Question | Why it matters |
|---|----------|----------------|
| 8 | Must the SP **sign AuthnRequest**? | If yes → need `SSO_LOGIN_SAML_SP_X509_CERT` + `SSO_LOGIN_SAML_SP_PRIVATE_KEY` |
| 9 | Are SAML **assertions encrypted**? | May require extra SP key configuration |
| 10 | **NameID format** Ping will send | Email format recommended |
| 11 | Network restrictions on ACS URL? | VPN, IP allowlist, firewall rules |
| 12 | Separate Ping apps per environment? | Dev / staging / prod often need separate Entity IDs and certs |
| 13 | **Production base URL** (HTTPS) | Must match `SSO_LOGIN_SAML_SP_BASE_URL` exactly |

---

## Part C — Internal checklist (your team)

Before UAT / go-live:

| Step | Done |
|------|------|
| SSOLoginBundle enabled in `config/bundles.php` | ☐ |
| Routes + `security.yaml` public access for `/sso-login/saml` | ☐ |
| Studio assets built (`build-studio.sh`) + `assets:install` | ☐ |
| `.env` filled from Part B | ☐ |
| `SSO_LOGIN_SAML_SP_BASE_URL` = exact browser origin | ☐ |
| `SSO_LOGIN_SAML_USERNAME_ATTRIBUTE` agreed with client | ☐ |
| Test Pimcore user created (username = SAML attribute value) | ☐ |
| Test user has Studio perspective / permissions | ☐ |
| Ping ACS URL matches `{base}/sso-login/saml/acs` | ☐ |
| Production uses **HTTPS** | ☐ |

---

## Part D — `.env` template (after client responds)

```dotenv
###> ssologin/ping-saml ###
SSO_LOGIN_SAML_IDP_ENTITY_ID=
SSO_LOGIN_SAML_IDP_SSO_URL=
SSO_LOGIN_SAML_IDP_SLO_URL=
SSO_LOGIN_SAML_IDP_X509_CERT=

SSO_LOGIN_SAML_SP_BASE_URL=https://pimcore-staging.client.com
SSO_LOGIN_SAML_SP_ENTITY_ID=
SSO_LOGIN_SAML_USERNAME_ATTRIBUTE=email

# Only if Ping requires signed AuthnRequest:
SSO_LOGIN_SAML_SP_X509_CERT=
SSO_LOGIN_SAML_SP_PRIVATE_KEY=

SSO_LOGIN_SAML_DEBUG=0
###< ssologin/ping-saml ###
```

---

## Part E — Email template (copy to client)

```text
Subject: Pimcore Studio – Ping SAML SSO – configuration details

Hi,

We are integrating Pimcore Studio with your Ping Identity (SAML 2.0) environment.
Please register Pimcore as a SAML Service Provider using the details below.

SERVICE PROVIDER (Pimcore)
--------------------------
SP Entity ID:     {base}/sso-login/saml/metadata
ACS URL:          {base}/sso-login/saml/acs
ACS binding:      HTTP-POST
SP metadata URL:  {base}/sso-login/saml/metadata

Alternatively, we can provide the SP metadata XML file for import.

INFORMATION WE NEED FROM YOU
----------------------------
Please provide either:
  • IdP metadata XML, or
  • IdP Entity ID, SSO URL, and IdP signing certificate (X.509 PEM)

Also please confirm:
  1. Which SAML attribute should map to the Pimcore login username
     (e.g. email, mail, sAMAccountName, or NameID)?
  2. A test user identity we can use for UAT (e.g. email address).
  3. Whether the Service Provider must sign SAML AuthnRequests.
  4. The production base URL (must be HTTPS).
  5. Whether dev, staging, and production use separate Ping applications.

IMPORTANT
---------
Pimcore backend users must already exist before SSO login.
The SAML username must match the Pimcore user name exactly.
We do not auto-create Pimcore accounts via SSO in the current setup.

Thank you,
[Your name]
```

---

## Part F — After client responds

1. Map their IdP metadata → `.env` (Part D).
2. Create / verify Pimcore users for each SSO identity.
3. Run test flow:
   - Open `{base}/pimcore-studio/login`
   - Click **Sign in with Ping SSO**
   - Complete Ping login → should land in Studio
4. If errors, see troubleshooting in [PING_SAML_SSO.md](PING_SAML_SSO.md).

| Symptom | Likely cause |
|---------|----------------|
| User not found | SAML attribute ≠ Pimcore username |
| Invalid SAML response | Wrong IdP cert, ACS URL mismatch, clock skew |
| 503 not configured | Missing IdP env vars |
| Redirect issues | `SSO_LOGIN_SAML_SP_BASE_URL` ≠ browser URL |

---

## Quick reference — who sends what

```text
  YOU  ──►  Client:  SP Entity ID, ACS URL, metadata URL/XML
  Client  ──►  YOU:  IdP metadata (or Entity ID + SSO URL + cert)
  Client  ──►  YOU:  Username attribute name + test user
  YOU  ──►  Pimcore:  Create matching backend user + Studio access
```
