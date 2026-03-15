# statamic-connect

## Project Overview
A Statamic addon bundling marketing & CRM integrations (ActiveCampaign,
HubSpot, Klaviyo, Brevo, Salesforce). Each integration is a self-contained
module, toggled on/off in the Statamic CP.

## Architecture
- Each integration lives in src/Integrations/{Name}/
- All integrations extend BaseIntegration
- Multi-site config is a Pro-only feature, gated via Statamic editions
- CP Vue components live in resources/js/components/{Name}/
- Config published to config/statamic/connect.php

## Key Commands
- composer test — run PHPUnit
- npm run dev — compile Vue components
- php artisan vendor:publish --tag=connect-config

## Code Standards
- PHP 8.3, Laravel 12, Statamic 6
- Each integration must implement: subscribe(), mapFields(), validateConfig()
- Multi-site support always gated behind Pro edition check
- GDPR consent field support required on every integration
- Follow existing ActiveCampaign integration as the reference implementation

## What Claude Gets Wrong
- Do not use Statamic::pro() for edition checks, use $this->isProEdition()
- Config keys must be namespaced per-integration to avoid collisions
- Vue components must use Statamic's existing fieldtype patterns
