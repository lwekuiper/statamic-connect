# Implementation Plan: statamic-connect

## Overview

Unify `lwekuiper/statamic-activecampaign` and `lwekuiper/statamic-hubspot` into a single
`lwekuiper/statamic-connect` addon that supports multiple CRM/marketing integrations
(ActiveCampaign, HubSpot, Klaviyo, Brevo, Salesforce). Each integration is a self-contained
module toggled on/off from a single CP settings page.

---

## Phase 1: Project Scaffold & Base Infrastructure

### 1.1 Create composer.json

```json
{
  "name": "lwekuiper/statamic-connect",
  "description": "Marketing & CRM integrations for Statamic",
  "type": "statamic-addon",
  "license": "proprietary",
  "require": {
    "php": "^8.3",
    "statamic/cms": "^6.0"
  },
  "require-dev": {
    "orchestra/testbench": "^10.0"
  },
  "autoload": {
    "psr-4": {
      "Lwekuiper\\StatamicConnect\\": "src/"
    }
  },
  "autoload-dev": {
    "psr-4": {
      "Lwekuiper\\StatamicConnect\\Tests\\": "tests/"
    }
  },
  "extra": {
    "laravel": {
      "providers": ["Lwekuiper\\StatamicConnect\\ServiceProvider"]
    },
    "statamic": {
      "name": "Connect",
      "description": "Marketing & CRM integrations for Statamic",
      "editions": ["lite", "pro"]
    }
  }
}
```

### 1.2 Create package.json, vite.config.js

Mirror the existing addons' Vite setup. Entry point: `resources/js/addon.js`.

### 1.3 Create phpunit.xml

Standard PHPUnit config pointing to `tests/`.

### 1.4 Create config/connect.php

```php
return [
    'integrations' => [
        'activecampaign' => [
            'api_url' => env('ACTIVECAMPAIGN_API_URL'),
            'api_key' => env('ACTIVECAMPAIGN_API_KEY'),
        ],
        'hubspot' => [
            'access_token' => env('HUBSPOT_ACCESS_TOKEN'),
        ],
        'klaviyo' => [
            'api_key' => env('KLAVIYO_API_KEY'),
        ],
        'brevo' => [
            'api_key' => env('BREVO_API_KEY'),
        ],
        'salesforce' => [
            'instance_url' => env('SALESFORCE_INSTANCE_URL'),
            'client_id' => env('SALESFORCE_CLIENT_ID'),
            'client_secret' => env('SALESFORCE_CLIENT_SECRET'),
        ],
    ],
];
```

**Key:** Config keys namespaced per-integration inside the `integrations` array to avoid
collisions (per CLAUDE.md requirement).

---

## Phase 2: Base Classes & Shared Infrastructure

### 2.1 src/Integrations/BaseIntegration.php

Abstract class that all integrations extend. Provides:

```php
abstract class BaseIntegration
{
    abstract public function subscribe(string $email, array $data): bool;
    abstract public function mapFields(Submission $submission): array;
    abstract public function validateConfig(): bool;

    // Edition gating — per CLAUDE.md, do NOT use Statamic::pro()
    protected function isProEdition(): bool
    {
        // Uses addon edition check, not Statamic::pro()
        return $this->addon()->edition() === 'pro';
    }

    protected function getSite(): string
    {
        if ($this->isProEdition()) {
            return Site::current()->handle();
        }
        return Site::default()->handle();
    }

    protected function hasConsent(Submission $submission, ?string $consentField): bool
    {
        if (! $consentField) {
            return true;
        }
        return (bool) $submission->data()[$consentField] ?? false;
    }
}
```

### 2.2 src/Connectors/BaseConnector.php

Abstract HTTP connector:

```php
abstract class BaseConnector
{
    abstract protected function baseUrl(): string;

    protected function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl());
    }

    protected function handleResponse(Response $response): mixed
    {
        if ($response->failed()) {
            Log::error(static::class . ' API error', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            return null;
        }
        return $response->json();
    }
}
```

### 2.3 src/Data/FormConfig.php

Unified form configuration DTO. Implements Statamic's `Localization` interface for
multi-site support (Pro only). Stores:

- `integration` — which integration this config is for
- `form` — Statamic form handle
- `locale` — site locale
- `email_field` — field containing the email
- `consent_field` — GDPR consent field (required per CLAUDE.md)
- Integration-specific data (list_ids, tag_ids, contact_properties, merge_fields, etc.)

**Composite ID format:** `{integration}::{form}::{locale}`

### 2.4 src/Data/AddonConfig.php

Global addon configuration stored in `resources/connect/config.yaml`:

```yaml
sites:
  en:
    enabled: true
    origin: null
  nl:
    enabled: true
    origin: en
integrations:
  activecampaign:
    enabled: true
  hubspot:
    enabled: true
  klaviyo:
    enabled: false
```

### 2.5 src/Stache/FormConfigStore.php & FormConfigRepository.php

Unified Stache store for all integration form configs.

- **Store handle:** `connect-form-configs` (namespaced to avoid collisions)
- **Storage path:** `resources/connect/{integration}/` (Lite) or
  `resources/connect/{integration}/{locale}/` (Pro)
- **Repository methods:** `all()`, `find()`, `findOrFail()`, `whereIntegration()`,
  `whereForm()`, `whereLocale()`, `save()`, `delete()`

### 2.6 src/Facades/

- `Connect` — main facade for the addon
- `FormConfig` — facade for FormConfigRepository
- `AddonConfig` — facade for AddonConfig data class

---

## Phase 3: ServiceProvider

### 3.1 src/ServiceProvider.php

**Register phase:**
- Singleton: `FormConfigRepository`
- Singleton: `AddonConfig`
- Per enabled integration: register its connector as singleton
  (`connect.{name}.connector`)
- Publish config to `config/statamic/connect.php`

**Boot phase:**
- Register shared fieldtypes (IntegrationMergeFields, StatamicFormFields, ConnectSites)
- Per enabled integration: register its specific fieldtypes (e.g.,
  ActivecampaignList, ActivecampaignTag, HubspotContactProperties)
- Register CP navigation: "Connect" section under Tools, with child items
  per enabled integration, each showing its configured forms
- Register Stache store: `connect-form-configs`
- Register event listeners: `SubmissionCreated` → `DispatchToIntegrations` listener
- Register Vite bundle

**Event listener registration pattern:**

```php
Event::listen(SubmissionCreated::class, DispatchToIntegrations::class);
// Pro edition only:
Event::listen(FormSaved::class, EnsureFormConfigLocalizationsExist::class);
```

The `DispatchToIntegrations` listener iterates enabled integrations and delegates to
each integration's `subscribe()` method.

---

## Phase 4: ActiveCampaign Integration

Port from `lwekuiper/statamic-activecampaign`. Files:

### 4.1 src/Integrations/ActiveCampaign/ActiveCampaignIntegration.php

Extends `BaseIntegration`. Implements:
- `subscribe()` — sync contact, update list status, add tags
- `mapFields()` — map standard fields (email, firstName, lastName, phone) + custom fields
- `validateConfig()` — verify API URL and key are set

### 4.2 src/Connectors/ActiveCampaignConnector.php

Extends `BaseConnector`. Methods:
- `syncContact(string $email, array $data): ?array`
- `updateListStatus(int $contactId, int $listId, int $status): ?array`
- `addTagToContact(int $contactId, int $tagId): ?array`
- `getLists(): array` (cached via Blink)
- `getTags(): array` (cached via Blink)
- `getCustomFields(): array` (cached via Blink)
- `getList(int $id): ?array`
- `getTag(int $id): ?array`

Authentication: API key in header, configurable base URL.

### 4.3 src/Fieldtypes/

- `ActivecampaignList.php` — Relationship fieldtype for AC lists
- `ActivecampaignTag.php` — Relationship fieldtype for AC tags
- `ActivecampaignMergeFields.php` — Fieldtype for merge field mapping

### 4.4 src/Http/Controllers/ActiveCampaign/

- `GetMergeFieldsController.php` — returns standard + custom AC fields
- `GetFormFieldsController.php` — returns Statamic form fields (shared, but
  routed per-integration)

### 4.5 Vue Components

- `resources/js/components/ActiveCampaign/ActiveCampaignMergeFieldsFieldtype.vue`
- (List and Tag fieldtypes use Statamic's built-in relationship fieldtype UI)

### 4.6 Routes (in routes/cp.php)

```
/cp/connect/activecampaign/merge-fields         → GetMergeFieldsController
/cp/connect/activecampaign/form-fields/{form}    → GetFormFieldsController
```

### 4.7 Tests

- `tests/Integrations/ActiveCampaign/ActiveCampaignConnectorTest.php`
- `tests/Integrations/ActiveCampaign/ActiveCampaignSubscribeTest.php`

---

## Phase 5: HubSpot Integration

Port from `lwekuiper/statamic-hubspot`. Files:

### 5.1 src/Integrations/HubSpot/HubSpotIntegration.php

Extends `BaseIntegration`. Implements:
- `subscribe()` — create/update contact via batch upsert
- `mapFields()` — map form fields to HubSpot contact properties, cast all to string
- `validateConfig()` — verify access token is set

### 5.2 src/Connectors/HubSpotConnector.php

Extends `BaseConnector`. Methods:
- `createOrUpdateContact(string $email, array $properties): bool`
- `getContactProperties(): array`
- `getContactProperty(string $name): ?object`

Authentication: Bearer token. Base URL: `https://api.hubapi.com/crm/v3/`.

### 5.3 src/Fieldtypes/

- `HubspotContactProperties.php` — Fieldtype for HubSpot property mapping

### 5.4 src/Http/Controllers/HubSpot/

- `GetContactPropertiesController.php` — returns available HubSpot contact properties
- `GetFormFieldsController.php` — returns Statamic form fields

### 5.5 Vue Components

- `resources/js/components/HubSpot/HubspotContactPropertiesFieldtype.vue`

### 5.6 Routes

```
/cp/connect/hubspot/contact-properties          → GetContactPropertiesController
/cp/connect/hubspot/form-fields/{form}           → GetFormFieldsController
```

### 5.7 Tests

- `tests/Integrations/HubSpot/HubSpotConnectorTest.php`
- `tests/Integrations/HubSpot/HubSpotSubscribeTest.php`

---

## Phase 6: Shared CP Interface

### 6.1 Global Settings Page

Single CP page at `/cp/connect/settings` where you:
- Toggle integrations on/off
- Configure per-site enablement (Pro only)
- Manage site origins

Controllers:
- `src/Http/Controllers/Cp/AddonConfigController.php` — index, edit, update

### 6.2 Form Config Listing & Editing

Shared pages for managing per-form integration configs:

- `resources/js/pages/Index.vue` — lists all configured forms, grouped or filtered
  by integration
- `resources/js/pages/Edit.vue` — edit a form config (blueprint varies by integration)
- `resources/js/pages/Empty.vue` — empty state

Controllers:
- `src/Http/Controllers/Cp/FormConfigController.php` — CRUD for form configs across
  all integrations

### 6.3 Shared Vue Components

- `resources/js/components/publish/PublishForm.vue` — save, validate, dirty-state,
  localization support
- `resources/js/components/listing/ConnectListing.vue` — table listing with
  integration-specific columns
- `resources/js/components/SiteSelector.vue` — site switching dropdown
- `resources/js/components/fieldtypes/StatamicFormFieldsFieldtype.vue` — shared
  form field selector
- `resources/js/components/fieldtypes/ConnectSitesFieldtype.vue` — site toggle table

### 6.4 addon.js Entry Point

Register all Inertia pages and Vue components:

```javascript
Statamic.$inertia.register([
    'Connect_Index',
    'Connect_Empty',
    'Connect_Edit',
    'Connect_Settings',
]);

Statamic.$components.register([
    // Shared
    'connect-listing', 'connect-publish-form', 'connect-site-selector',
    'statamic_form_fields', 'connect_sites',
    // ActiveCampaign
    'activecampaign_merge_fields', 'activecampaign_list', 'activecampaign_tag',
    // HubSpot
    'hubspot_contact_properties',
]);
```

---

## Phase 7: Listeners & Event Handling

### 7.1 src/Listeners/DispatchToIntegrations.php

Central listener for `SubmissionCreated`:

```php
public function handle(SubmissionCreated $event): void
{
    $submission = $event->submission;
    $site = $this->resolveSite($submission);

    foreach ($this->enabledIntegrations() as $integration) {
        $formConfig = FormConfig::find($integration->name(), $submission->form()->handle(), $site);

        if (! $formConfig) continue;
        if (! $integration->hasConsent($submission, $formConfig->consentField())) continue;

        $data = $integration->mapFields($submission, $formConfig);
        $integration->subscribe($formConfig->emailField($submission), $data, $formConfig);
    }
}
```

### 7.2 src/Listeners/EnsureFormConfigLocalizationsExist.php

Listens to `FormSaved` (Pro only). Creates localized form configs across all enabled
sites when a form is saved.

---

## Phase 8: Route Structure

All routes in `routes/cp.php`:

```
/cp/connect
├── GET    /settings                              → AddonConfigController@edit
├── POST   /settings                              → AddonConfigController@update
├── GET    /                                      → FormConfigController@index
├── GET    /{integration}/{formConfig}/edit        → FormConfigController@edit
├── PATCH  /{integration}/{formConfig}             → FormConfigController@update
├── DELETE /{integration}/{formConfig}             → FormConfigController@destroy
│
├── /activecampaign
│   ├── GET /merge-fields                         → AC\GetMergeFieldsController
│   └── GET /form-fields/{form}                   → AC\GetFormFieldsController
│
├── /hubspot
│   ├── GET /contact-properties                   → HS\GetContactPropertiesController
│   └── GET /form-fields/{form}                   → HS\GetFormFieldsController
│
├── /klaviyo                                      (future)
├── /brevo                                        (future)
└── /salesforce                                   (future)
```

---

## Phase 9: Klaviyo, Brevo, Salesforce (Stubs)

For each of Klaviyo, Brevo, and Salesforce, create:

1. `src/Integrations/{Name}/{Name}Integration.php` — extends BaseIntegration with
   `subscribe()`, `mapFields()`, `validateConfig()` stubs
2. `src/Connectors/{Name}Connector.php` — extends BaseConnector with API method stubs
3. Register in ServiceProvider's integration registry

These are placeholder implementations. The full implementation follows the same pattern
as ActiveCampaign and HubSpot — add connector methods, fieldtypes, controllers, Vue
components, and tests as each integration is built out.

---

## Phase 10: Tests

### 10.1 tests/TestCase.php

Base test class extending `AddonTestCase`:
- `setSites()` helper
- `setProEdition()` helper
- `assertEveryItemIsInstanceOf()`

### 10.2 Shared Tests

- `tests/Feature/ViewFormConfigListingTest.php`
- `tests/Feature/EditFormConfigTest.php`
- `tests/Feature/UpdateFormConfigTest.php`
- `tests/Feature/DestroyFormConfigTest.php`
- `tests/Feature/EditionRestrictionTest.php` (Lite vs Pro gating)
- `tests/Stache/FormConfigStoreTest.php`
- `tests/Stache/FormConfigRepositoryTest.php`

### 10.3 Integration-Specific Tests

- `tests/Integrations/ActiveCampaign/` — connector + subscription tests
- `tests/Integrations/HubSpot/` — connector + subscription tests
- `tests/Listeners/DispatchToIntegrationsTest.php`
- `tests/Listeners/EnsureFormConfigLocalizationsExistTest.php`

---

## File Tree Summary

```
statamic-connect/
├── CLAUDE.md
├── composer.json
├── package.json
├── vite.config.js
├── phpunit.xml
├── config/
│   └── connect.php
├── routes/
│   └── cp.php
├── resources/
│   ├── js/
│   │   ├── addon.js
│   │   ├── pages/
│   │   │   ├── Index.vue
│   │   │   ├── Edit.vue
│   │   │   ├── Empty.vue
│   │   │   └── Settings.vue
│   │   └── components/
│   │       ├── publish/PublishForm.vue
│   │       ├── listing/ConnectListing.vue
│   │       ├── SiteSelector.vue
│   │       ├── fieldtypes/
│   │       │   ├── StatamicFormFieldsFieldtype.vue
│   │       │   └── ConnectSitesFieldtype.vue
│   │       ├── ActiveCampaign/
│   │       │   └── ActiveCampaignMergeFieldsFieldtype.vue
│   │       └── HubSpot/
│   │           └── HubspotContactPropertiesFieldtype.vue
│   └── connect/                          ← Stache config storage
│       ├── config.yaml
│       ├── activecampaign/
│       │   └── {locale}/{form}.yaml
│       └── hubspot/
│           └── {locale}/{form}.yaml
├── src/
│   ├── ServiceProvider.php
│   ├── Integrations/
│   │   ├── BaseIntegration.php
│   │   ├── ActiveCampaign/
│   │   │   └── ActiveCampaignIntegration.php
│   │   ├── HubSpot/
│   │   │   └── HubSpotIntegration.php
│   │   ├── Klaviyo/
│   │   │   └── KlaviyoIntegration.php        (stub)
│   │   ├── Brevo/
│   │   │   └── BrevoIntegration.php          (stub)
│   │   └── Salesforce/
│   │       └── SalesforceIntegration.php     (stub)
│   ├── Connectors/
│   │   ├── BaseConnector.php
│   │   ├── ActiveCampaignConnector.php
│   │   ├── HubSpotConnector.php
│   │   ├── KlaviyoConnector.php              (stub)
│   │   ├── BrevoConnector.php                (stub)
│   │   └── SalesforceConnector.php           (stub)
│   ├── Data/
│   │   ├── AddonConfig.php
│   │   ├── FormConfig.php
│   │   └── FormConfigCollection.php
│   ├── Exceptions/
│   │   └── FormConfigNotFoundException.php
│   ├── Facades/
│   │   ├── Connect.php
│   │   ├── FormConfig.php
│   │   └── AddonConfig.php
│   ├── Fieldtypes/
│   │   ├── ConnectSites.php
│   │   ├── StatamicFormFields.php
│   │   ├── ActivecampaignList.php
│   │   ├── ActivecampaignTag.php
│   │   ├── ActivecampaignMergeFields.php
│   │   └── HubspotContactProperties.php
│   ├── Http/
│   │   └── Controllers/
│   │       ├── Cp/
│   │       │   ├── AddonConfigController.php
│   │       │   └── FormConfigController.php
│   │       ├── ActiveCampaign/
│   │       │   ├── GetMergeFieldsController.php
│   │       │   └── GetFormFieldsController.php
│   │       └── HubSpot/
│   │           ├── GetContactPropertiesController.php
│   │           └── GetFormFieldsController.php
│   ├── Listeners/
│   │   ├── DispatchToIntegrations.php
│   │   └── EnsureFormConfigLocalizationsExist.php
│   └── Stache/
│       ├── FormConfigRepository.php
│       └── FormConfigStore.php
└── tests/
    ├── TestCase.php
    ├── Feature/
    │   ├── ViewFormConfigListingTest.php
    │   ├── EditFormConfigTest.php
    │   ├── UpdateFormConfigTest.php
    │   ├── DestroyFormConfigTest.php
    │   └── EditionRestrictionTest.php
    ├── Integrations/
    │   ├── ActiveCampaign/
    │   │   ├── ActiveCampaignConnectorTest.php
    │   │   └── ActiveCampaignSubscribeTest.php
    │   └── HubSpot/
    │       ├── HubSpotConnectorTest.php
    │       └── HubSpotSubscribeTest.php
    ├── Listeners/
    │   ├── DispatchToIntegrationsTest.php
    │   └── EnsureFormConfigLocalizationsExistTest.php
    ├── Stache/
    │   ├── FormConfigStoreTest.php
    │   └── FormConfigRepositoryTest.php
    └── __fixtures__/
        └── resources/connect/
            ├── activecampaign/
            │   └── en/contact_us.yaml
            └── hubspot/
                └── en/newsletter.yaml
```

---

## Key Architecture Decisions

1. **Edition checking:** `$this->isProEdition()` on BaseIntegration, NOT `Statamic::pro()`
2. **Config namespacing:** All integration config under `connect.integrations.{name}`
3. **Single settings page:** One CP page for toggling all integrations and sites
4. **Unified Stache store:** One store `connect-form-configs` with integration prefix in keys
5. **Central event dispatch:** One listener dispatches to all enabled integrations
6. **GDPR consent:** Required field on every integration via BaseIntegration
7. **Vue fieldtype pattern:** Follow Statamic's existing fieldtype component patterns
8. **Storage path:** `resources/connect/{integration}/` for form config YAML files

---

## Scaling to 20+ Integrations

The current architecture (explicit registration in ServiceProvider, single config file,
eager Vue component loading) is appropriate for 5 integrations. If the addon grows to 20+,
the following refactors would be needed:

- **Auto-discovery:** Replace manual registration with directory scanning. Each
  `src/Integrations/{Name}/` would contain a manifest or mini service provider that the
  main ServiceProvider discovers and boots automatically for enabled integrations.
- **Per-integration config files:** Split `config/connect.php` into
  `config/connect/activecampaign.php`, `config/connect/hubspot.php`, etc.
- **Lazy-loaded Vue components:** Use dynamic imports so fieldtype components are only
  loaded when their integration is active, reducing bundle size.
- **Grouped CP navigation:** Categorize integrations (e.g., "Email Marketing", "CRM",
  "E-commerce") instead of a flat list under Connect.

The BaseIntegration contract (`subscribe()`, `mapFields()`, `validateConfig()`) stays
the same — only the registration mechanism changes. This refactor is straightforward to
do later without breaking integration implementations.

---

## Implementation Order

1. Phase 1 — Project scaffold (composer.json, package.json, config, phpunit)
2. Phase 2 — Base classes (BaseIntegration, BaseConnector, FormConfig, AddonConfig, Stache)
3. Phase 3 — ServiceProvider
4. Phase 4 — ActiveCampaign integration (port from existing repo)
5. Phase 5 — HubSpot integration (port from existing repo)
6. Phase 6 — Shared CP interface (Vue pages, shared components)
7. Phase 7 — Listeners
8. Phase 8 — Routes
9. Phase 9 — Klaviyo/Brevo/Salesforce stubs
10. Phase 10 — Tests
