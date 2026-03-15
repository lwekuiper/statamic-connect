# statamic-connect

## Project Overview
A Statamic addon bundling marketing & CRM integrations (Mailchimp,
ActiveCampaign, HubSpot, Klaviyo, Brevo, Salesforce). Each integration is a
self-contained module, toggled on/off in the Statamic CP.

## Architecture
- Each integration lives in src/Integrations/{Name}/
- All integrations extend BaseIntegration
- Multi-site config is a Pro-only feature, gated via Statamic editions
- CP Vue components live in resources/js/components/{Name}/
- Config published to config/statamic/connect.php

## Integration Priorities
- Phase 1 (Launch): Mailchimp, ActiveCampaign, HubSpot, Klaviyo, Brevo, Salesforce
- Phase 2: ConvertKit (Kit), MailerLite
- Phase 3: Campaign Monitor, Mailcoach, Drip, Constant Contact, Bento

## Competitive Landscape
Multi-site support is the primary differentiator for this addon.

| Platform | Existing Addon | Multi-site? |
|---|---|---|
| Mailchimp | statamic-rad-pack/mailchimp | No |
| ActiveCampaign | lwekuiper/activecampaign | Yes (Pro) |
| HubSpot | lwekuiper/hubspot | Yes (Pro) |
| Brevo | siterig/sendinblue | No |
| Salesforce | stokoe/forms-to-wherever | No |
| MailerLite | siterig/mailerlite | No |
| Campaign Monitor | rad-pack/campaign-monitor | No |
| Mailcoach | spatie/mailcoach | No |
| ConvertKit | stokoe/forms-to-wherever | No |
| Klaviyo | — | No addon |
| Drip | — | No addon |
| Constant Contact | — | No addon |
| Bento | bentonow/statamic | No |

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
