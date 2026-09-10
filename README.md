# SearchAI for Magento 2

SearchAI is a free, open-source product-discovery chatbot for Magento Open Source and Adobe Commerce. Customers ask natural-language questions; Magento retrieves a small candidate set; an AI provider explains or recommends only those products; Magento validates the IDs and renders current product cards.

Created by Ricardo Vera — [VeraDesarrollo.com](https://veradesarrollo.com).

## Features

- Magento-native catalog search without direct OpenSearch or Elasticsearch calls.
- OpenAI gpt-5.6-luna, Anthropic claude-fable-5, Google Gemini gemini-3.8-flash, and Custom/OpenAI-compatible endpoints.
- Store-scoped configuration, locale, currency, URLs, product text, and credentials.
- Homepage, product-page, and category-page placement.
- Accessible responsive widget with keyboard support and session-only history.
- Compact, sanitized product context; full description is off by default.
- Candidate-ID validation: the model cannot create product cards for unknown products.
- Encrypted credentials, CSRF validation, bounded payloads, privacy-conscious rate limiting, and safe errors.
- English and Spanish translations.
- No Vera telemetry, analytics, SaaS backend, or call-home behavior.

## Requirements

- Magento Open Source or Adobe Commerce 2.4.7 or newer; Magento 2.4.8 is the primary target.
- PHP 8.2, 8.3, or 8.4.
- A working Magento catalog-search engine and Magento Inventory/MSI.
- HTTPS is strongly recommended.
- An API key for a built-in external provider, or a reachable Custom gateway.

## Composer installation

Once the package is available from your configured Composer repository:

~~~bash
composer require vera/module-search-ai
bin/magento module:enable Vera_SearchAI
bin/magento setup:upgrade
bin/magento cache:clean config layout block_html full_page
~~~

Use normal production deployment commands for dependency injection and static content when the store runs production mode.

## Manual installation

Copy this repository contents to app/code/Vera/SearchAI inside the Magento application, then run:

~~~bash
bin/magento module:enable Vera_SearchAI
bin/magento setup:upgrade
bin/magento cache:clean config layout block_html full_page
~~~

Do not copy the parent Vera development directory. composer.json, registration.php, etc, Model, view, and the other component files belong directly in app/code/Vera/SearchAI.

## Configuration

Open Stores > Configuration > Vera > SearchAI. Configuration supports default, website, and store-view scopes.

### General

Enable SearchAI, set the assistant title, welcome text and placeholder, select Homepage/Product pages/Category pages, and choose the temporary history size. History defaults to six messages and stays in browser sessionStorage.

The widget is hidden when disabled or when the selected provider lacks its minimum configuration.

### AI Provider

- OpenAI uses the Responses API and gpt-5.6-luna.
- Anthropic uses the Messages API and claude-fable-5.
- Google Gemini uses generateContent and gemini-3.8-flash.
- Custom uses an OpenAI-compatible Chat Completions endpoint.

API keys are encrypted with Magento configuration encryption and never sent to JavaScript. AI provider usage may cost money. Charges are billed by the provider selected by the merchant, not by Vera or SearchAI. The module itself is free.

For Custom, configure the HTTP/HTTPS base URL, endpoint path, model ID, optional API key, authorization header, and prefix. The default path is /v1/chat/completions and the default header is Authorization with Bearer prefix. This supports gateways such as OpenRouter and compatible local/self-hosted software; compatibility depends on the gateway implementing the expected response shape.

### Catalog

Candidate Products defaults to 8 and accepts 3–20. Short description is enabled. Include Full Description in AI Context is disabled. Full descriptions are capped at 1,500 characters when enabled; short descriptions are capped at 500.

Additional attributes are comma-separated codes such as brand,color,size,material. Only attributes found by Magento and marked searchable or visible on the storefront are included. Cost and known internal fields are blocked. Out-of-stock products are excluded by default.

### Security and Advanced

Requests per Minute defaults to 10. Query length, provider timeout, and response size are bounded. Additional system guidance may be entered, but cannot replace the built-in rules that restrict recommendations to Magento candidates.

## How performance stays bounded

SearchAI does not upload or index the complete catalog in an LLM:

~~~text
Customer question
  -> Magento search
  -> top candidate products
  -> compact catalog context
  -> AI interpretation
  -> validated Magento product cards
~~~

Using short descriptions instead of full HTML reduces tokens, latency, request size, provider cost, and noise. Product retrieval is limited and bulk salability/category operations avoid per-product repository loads.

## Privacy and security

When an external provider is enabled, the customer query, bounded recent history, and compact candidate-product data are sent from the Magento store to that provider. Review the provider terms and privacy policy for your jurisdiction.

SearchAI stores no conversation database and does not log customer messages by default. It sends no telemetry to Vera, contains no hidden analytics, and calls no Vera server. The data path is Magento store <-> selected AI provider.

The public POST endpoint uses Magento form-key validation. Output is text-only; model HTML is not rendered. Price, image, URL, name, SKU, currency, visibility, and stock displayed in cards originate from Magento. Rate-limit cache keys use hashed identity data and do not store raw IP addresses.

## Troubleshooting

- Widget missing: verify Enable SearchAI, provider readiness, store scope, selected page location, then clean config/layout/full-page caches.
- Temporary unavailable: verify the encrypted key, model, provider endpoint, outbound HTTPS, timeout, and provider account limits. Magento logs contain only redacted technical categories.
- No results: confirm products are enabled, searchable, visible in search, assigned to the current website/store, indexed, and salable.
- Custom provider errors: verify the base URL, path, model, authorization header/prefix, and OpenAI-compatible choices[0].message.content response.
- Styling/JavaScript changes: redeploy static content and clear browser/full-page caches.

## Testing

Inside a Magento 2.4.7/2.4.8 development installation with dependencies available:

~~~bash
vendor/bin/phpunit -c app/code/Vera/SearchAI/phpunit.xml.dist
vendor/bin/phpcs --standard=app/code/Vera/SearchAI/phpcs.xml.dist app/code/Vera/SearchAI
find app/code/Vera/SearchAI -name '*.php' -print0 | xargs -0 -n1 php -l
~~~

Then verify module:status Vera_SearchAI, setup:upgrade, Admin configuration, each enabled/disabled layout location, a real search, each configured provider, invalid JSON, missing keys, rate limiting, and invented product IDs.

This standalone repository does not include Magento, a database, a search engine, provider credentials, or Commerce Marketplace authentication. Live installation and integration checks must therefore run in a Magento application.

## Contributing

See CONTRIBUTING.md. Keep provider-specific behavior in adapters, catalog retrieval behind ProductRetrieverInterface, and all product-card truth in Magento. Security or privacy regressions require tests.

## License and contact

MIT License. Copyright (c) 2026 Ricardo Vera.

- Website: [VeraDesarrollo.com](https://veradesarrollo.com)
- GitHub: [ricardoverita](https://github.com/ricardoverita)
- LinkedIn: [Ricardo Vera](https://www.linkedin.com/in/ricardo-vera/)
