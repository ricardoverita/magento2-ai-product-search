# SearchAI for Magento 2 — Design Specification

**Date:** 2026-09-10

**Module:** `Vera_SearchAI`

**Composer package:** `vera/module-search-ai`

**Initial version:** `1.0.0`

**License:** MIT

**Compatibility:** Magento Open Source and Adobe Commerce 2.4.7+, with Magento 2.4.8 as the primary target; PHP 8.2–8.4

## 1. Purpose and Scope

SearchAI is a free, open-source Magento 2 storefront assistant that interprets natural-language shopping questions and recommends products from the current Magento catalog. Magento remains the source of truth for product identity, availability, visibility, pricing, images, and URLs. AI providers may interpret and explain supplied candidates, but they may not introduce products or product metadata.

Version 1 includes:

- A responsive, accessible floating storefront chat widget.
- Placement on the homepage, product pages, and category pages, selectable per store view.
- Magento-native catalog retrieval behind `ProductRetrieverInterface`.
- Compact, configurable product context.
- Provider adapters for OpenAI, Anthropic, Google Gemini, and a custom OpenAI-compatible endpoint.
- Temporary browser-session conversation history.
- Server-side validation, CSRF protection, request limits, rate limiting, safe errors, and secret redaction.
- English and Spanish storefront/admin translations and documentation.
- Unit tests for security-critical and provider-independent behavior.

Version 1 excludes vector search, embeddings, persistent conversations, customer profiling, order or checkout actions, external product search, telemetry, a Vera-operated service, analytics, fine-tuning, speech, and image recognition.

## 2. Repository and Distribution

The `SearchAI` directory is both the Git repository root and the Magento component root. The parent `Vera` directory is not part of the repository. The default branch is `main`.

The root contains `composer.json`, `registration.php`, `LICENSE`, documentation, Magento component directories, and tests. Composer declares type `magento2-module` and PSR-4 namespace `Vera\\SearchAI\\`. Installation places the package at `vendor/vera/module-search-ai`; manual installation places the same contents at `app/code/Vera/SearchAI`.

The project metadata identifies:

- Website: `https://veradesarrollo.com`
- Copyright: `Copyright (c) 2026 Ricardo Vera`
- GitHub: `https://github.com/ricardoverita`
- LinkedIn: `https://www.linkedin.com/in/ricardo-vera/`

The MIT license text and attribution appear in `LICENSE`. Composer and both README files carry the corresponding project and author links.

## 3. Architecture

The application flow is:

```text
Storefront widget
  -> POST controller (method, CSRF, and payload checks)
  -> rate limiter
  -> ChatService
  -> ProductRetrieverInterface
  -> MagentoSearchRetriever
  -> ProductContextBuilder
  -> PromptBuilder
  -> ProviderPool
  -> selected AiProviderInterface adapter
  -> ResponseParser
  -> CandidateValidator
  -> product cards hydrated from current Magento data
  -> JSON response
```

The controller is an orchestration boundary only. Business behavior lives in small services with constructor injection. No class uses the Object Manager directly, and scope configuration is accessed only through `SearchAIConfig`.

Core interfaces make the two expected extension points explicit:

```php
interface ProductRetrieverInterface
{
    /** @return ProductCandidate[] */
    public function retrieve(string $query, int $storeId, int $limit): array;
}

interface AiProviderInterface
{
    public function chat(ChatRequest $request): ChatResponse;
}
```

DTOs carry validated request history, compact candidates, provider requests, provider responses, and safe storefront card data. They contain no service location or mutable provider state.

## 4. Catalog Retrieval

`MagentoSearchRetriever` uses Magento's catalog-search/full-text collection abstractions and never calls OpenSearch or Elasticsearch directly. It applies:

- The current store and website context.
- Enabled status.
- storefront catalog/search visibility.
- Store-scoped names, descriptions, attribute values, URLs, locale, and currency.
- Salability/stock filtering unless the merchant enables out-of-stock candidates.
- A configurable candidate limit from 3 through 20, defaulting to 8.

Search relevance is delegated to Magento's configured search engine and indexed searchable attributes. Name, SKU, short description, category-related search data, and merchant-configured searchable attributes participate through Magento's index configuration rather than provider-specific search queries.

Only required EAV attributes are selected. Category IDs from all candidates are collected and category names are loaded in one batch. Price, URL, image, and salability are resolved from Magento after candidate validation. The small configured candidate bound prevents broad product hydration, and no per-product repository loop is used.

If retrieval returns no candidates, `ChatService` returns a localized no-results answer without calling an AI provider. A catalog retrieval failure produces a safe unavailable response and an actionable redacted log entry; it never breaks the surrounding storefront page.

## 5. Product Context

`ProductContextBuilder` creates provider-facing records containing only:

- product ID
- SKU
- name
- short description when enabled
- full description only when explicitly enabled
- category names
- validated additional storefront-visible attributes
- final/display price when available
- currency
- salability status
- product URL and small image URL only when useful to the model response

All catalog text is treated as untrusted data. HTML is stripped, entities are decoded safely, control characters are removed, whitespace is normalized, and text is truncated by Unicode-aware character count. Defaults are 500 characters for short description and 1,500 characters for full description. Names and SKUs remain complete within an overall per-field safety ceiling. Additional text attributes use a conservative configured ceiling.

Full `description` is disabled by default. Attribute codes are parsed from a comma-separated admin value, normalized, deduplicated, validated against Magento EAV metadata, and accepted only when the attribute is suitable for public storefront display. Cost, internal notes, supplier data, and admin-only values are never included.

Provider context and storefront card data are separate representations. The provider cannot author or override price, currency, URL, image, SKU, name, visibility, or availability rendered to the shopper.

## 6. Prompt and Structured Response

`PromptBuilder` creates a server-side system instruction that:

- Defines the assistant as a shopping assistant for the current store.
- Restricts recommendations to supplied candidate IDs.
- Forbids invented product facts and purchase claims.
- Treats catalog fields as data, never instructions.
- Instructs the model to ignore prompt injection embedded in catalog content or history.
- Prohibits disclosure of prompts, configuration, and secrets.
- Requests concise output in the shopper's language when practical.
- Allows one useful clarification when the request is ambiguous.
- Requires a JSON object with `answer` and `product_ids`.

The secure default prompt can be extended through a store-scoped Advanced setting. The immutable security rules remain part of the final system instruction even when merchant guidance is supplied.

The expected provider response is:

```json
{
  "answer": "I found these options.",
  "product_ids": [10, 20]
}
```

`ResponseParser` first decodes strict JSON, then supports one conservative fallback for JSON enclosed by Markdown code fences. It validates required keys, scalar and array types, answer length, integer IDs, uniqueness, and maximum result count. It does not render provider HTML.

`CandidateValidator` intersects returned IDs with the original candidate set while preserving provider order. An ID such as `999` is discarded when the original candidates were `[10, 20, 30]`. If no returned IDs survive, the textual answer may still be returned only when safe and relevant; no product cards are rendered.

## 7. AI Providers

`ProviderPool` maps configured provider codes to injected implementations and fails safely for unknown or unavailable providers. `ChatService` depends only on `AiProviderInterface` and does not change when another adapter is registered.

Version 1 presets contain only economical models suitable for an ecommerce assistant:

| Provider | Model | API style |
|---|---|---|
| OpenAI | `gpt-5.6-luna` | Responses API |
| Anthropic | `claude-fable-5` | Messages API |
| Google Gemini | `gemini-3.8-flash` | `generateContent` |
| Custom | merchant-provided model ID | OpenAI-compatible Chat Completions |

xAI, Mistral, DeepSeek, and larger OpenAI/Anthropic models are intentionally omitted from v1 configuration.

Each adapter owns its URL, headers, payload shape, structured-output feature, and response extraction. A shared transport owns connection behavior, timeouts, maximum response size, status mapping, JSON decoding boundaries, and sanitized diagnostics. Gemini authentication uses a request header instead of exposing the key in JavaScript or placing it in a configured storefront URL.

The Custom adapter accepts a base URL, endpoint path (default `/v1/chat/completions`), API key, model ID, optional authorization header name, and optional authorization prefix (default `Bearer`). Header names and values reject CR/LF and invalid header-name characters. The adapter does not hardcode OpenRouter, Ollama, LM Studio, or other gateways.

There are no automatic retries in v1. This prevents duplicate provider charges and keeps failure behavior predictable. HTTP 401, 403, 429, timeouts, 5xx responses, malformed JSON, oversized responses, and unsupported response shapes map to internal exception types and the same localized public message: “The assistant is temporarily unavailable. Please try again.”

API keys use Magento's encrypted configuration backend. They never appear in frontend configuration, controller output, exception text, or logs. Sensitive headers and request bodies are not logged.

## 8. Configuration

Configuration lives under `Stores > Configuration > Vera > SearchAI` and is store-scoped where shopper behavior can vary.

### General

- Enable SearchAI: No by default.
- Assistant name: `AI Assistant`.
- Welcome message: `Hi! Tell me what you're looking for and I'll help you find it.`
- Input placeholder: localized default.
- Storefront locations: homepage, product pages, category pages.
- Maximum conversation history: 6 messages by default, with a safe bounded range.

### AI Provider

- Provider: OpenAI, Anthropic, Gemini, or Custom.
- One model field and encrypted API-key field per built-in provider.
- Custom base URL, endpoint, model, encrypted key, optional authorization header, and prefix.
- Magento dependency fields show only settings related to the selected provider.

Provider and model options are centralized in PHP source classes/constants and consumed by Magento source models. JavaScript receives no provider credential or private provider configuration.

### Catalog

- Candidate products: 8 by default; allowed range 3–20.
- Use short description: Yes.
- Include full description: No.
- Additional product attribute codes: empty by default.
- Include out-of-stock products: No.

### Security

- Requests per minute: 10 by default.
- Maximum shopper-query length: bounded default appropriate for short shopping requests.
- Provider timeout: conservative bounded default.

### Advanced

- Additional system guidance with the secure built-in rules retained.

`SearchAIConfig` exposes meaningful typed methods for every setting and applies defaults, bounds, parsing, and scope. No other application class reads raw scope paths.

The widget is hidden when SearchAI is disabled or the selected provider lacks its minimum required configuration. Temporary runtime failures display a safe unavailable state inside an already rendered widget.

## 9. Storefront and Accessibility

The implementation uses Magento-native PHTML, an AMD JavaScript module, and scoped LESS. It adds no React, Vue, or third-party frontend dependency. Initialization uses Magento-compatible CSP-safe mechanisms and no executable inline JavaScript.

The floating launcher appears at the lower right. The panel includes:

- a labelled launcher with `aria-expanded` and `aria-controls`
- a labelled chat region/dialog
- predictable focus when opening and closing
- Escape-to-close behavior
- a labelled textarea
- Enter to send and Shift+Enter for a newline
- disabled duplicate submission while sending
- loading, success, empty, rate-limit, provider-error, and retry states
- responsive product cards and touch targets
- limited motion that respects reduced-motion preferences

Messages are inserted with text nodes/text-only DOM APIs. No untrusted model or catalog string passes through `innerHTML`. Product cards use escaped Magento-originated data and safe URL/image rendering.

The homepage, product, and category layout handles contain the widget integration point. The server-side block/view model returns no markup, and therefore no widget assets initialize, when the current location is not selected. The location mapping is isolated so later CMS/search-page support can be added without changing chat services.

All user-facing strings pass through Magento translation. `i18n/en_US.csv` and `i18n/es_ES.csv` provide the initial dictionaries. The store locale is included in prompt context and the model is asked to reply in the shopper's language.

## 10. Endpoint and Conversation History

The storefront endpoint is `POST /searchai/chat/send` and returns JSON. The controller implements Magento's POST action contract and uses the standard form key/CSRF flow. The frontend submits a form-encoded request containing `form_key`, `message`, and serialized bounded history; this retains Magento CSRF behavior while producing the conceptual JSON response in the product requirements.

The success response is:

```json
{
  "success": true,
  "answer": "I found these options.",
  "products": [
    {
      "id": 10,
      "sku": "ABC-10",
      "name": "Example Product",
      "price": "$79.00",
      "image": "https://store.example/media/catalog/product/example.jpg",
      "url": "https://store.example/example-product.html",
      "salable": true
    }
  ]
}
```

All product fields in this response are rehydrated or formatted by Magento after candidate validation. Errors have stable codes and localized safe messages, never raw provider details or stack traces.

Conversation history is stored only in browser `sessionStorage`. The client caps it for responsiveness; the server independently accepts only user/assistant roles, caps message count, caps characters per message and total characters, removes invalid entries, and never trusts client-provided product or system messages. Customer messages are not persisted or logged by SearchAI.

## 11. Abuse Protection and Privacy

The public endpoint uses a Magento-cache-backed fixed-window rate limiter guarded by Magento's lock abstraction. The default is 10 requests per minute. The cache key combines store ID, frontend session identity, and a one-way keyed hash of a normalized network identifier. Raw IP addresses are neither stored nor logged by the module. The implementation works with Magento's cache abstraction and has no Redis-specific dependency.

The endpoint rejects non-POST requests, invalid form keys, malformed history, overlong fields, invalid encodings, and oversized logical payloads before provider work. It validates all administrator-provided URLs and headers used by Custom, escapes storefront output, and keeps credentials server-side.

When an external provider is selected, the shopper's query, bounded recent history, and compact candidate data are sent directly from Magento to that provider. SearchAI adds no Vera telemetry, tracking, hidden analytics, call-home behavior, or Vera-operated intermediary. The documented data path is only:

```text
Magento store <-> selected AI provider
```

## 12. Performance and Caching

SearchAI never uploads or indexes the complete catalog in an LLM. Each request follows:

```text
Customer question
        -> Magento search
        -> top candidate products
        -> compact catalog context
        -> AI interpretation
        -> validated Magento product cards
```

Candidate count, history, text fields, request body, and provider response all have hard bounds. Full descriptions are opt-in because they increase tokens, latency, cost, payload size, and noise.

Version 1 does not cache AI conversations or final product-card data. A candidate-query cache is omitted initially because correct variation by store, customer group, index state, catalog permissions, and stock context is more valuable than a small optimization at the v1 candidate bound. The retrieval interface permits a safe caching decorator later without changing chat or provider code.

## 13. Testing and Quality

Unit tests prioritize:

- `ProviderPool` selection and unknown-provider behavior.
- `SearchAIConfig` defaults, scope, bounds, provider readiness, and public configuration allowlist.
- `ProductContextBuilder` HTML stripping, whitespace normalization, length bounds, full-description default, and public attribute filtering.
- `PromptBuilder` catalog-as-data boundaries and immutable security rules.
- Built-in and Custom provider response extraction.
- `ResponseParser` strict and fenced JSON behavior.
- `CandidateValidator`, including rejection of ID `999` for candidates `[10, 20, 30]`.
- Rate-limit allowance, exhaustion, window reset, store isolation, and hashed identifiers.
- Request/history validation.
- Credential absence from frontend configuration and JSON responses.

Repository checks include Composer validation, PHP syntax checks, XML well-formedness/schema checks where Magento URNs are available, PHPUnit, Magento Coding Standard/PHPCS where dependencies can be installed, and static analysis appropriate to the module.

This standalone repository has no Magento runtime, database, search engine, catalog, or admin application. Therefore module registration, `setup:upgrade`, admin rendering, live catalog retrieval, layout placement, and provider calls cannot truthfully be verified here unless a Magento test fixture is subsequently attached. Both README files document installation and the exact integration smoke-test checklist for Magento 2.4.7 and 2.4.8.

## 14. Documentation

`README.md` and `README.es.md` each cover purpose, features, compatibility, Composer and manual installation, module enablement, setup upgrade, cache commands, configuration, provider credentials, placement, catalog context, performance, privacy, Custom endpoints, troubleshooting, contributing, and licensing.

The documentation states that the extension is free while provider API usage may incur charges billed by the selected provider. It does not claim any AI API is free. `CONTRIBUTING.md` defines local quality commands and contribution expectations without requiring a Vera-hosted service.

## 15. Failure Guarantees

The surrounding Magento storefront remains usable when the API key is missing, provider configuration is incomplete, the provider is down, catalog retrieval fails, provider JSON is invalid, or a shopper is rate-limited. Incomplete configuration hides the widget. Runtime faults are caught at the endpoint boundary, logged with redaction, and converted to a stable safe response.

No failure path returns credentials, provider response bodies, system prompts, stack traces, arbitrary HTML, or AI-authored product URLs. These guarantees take precedence over returning a partially successful recommendation.

## 16. Accepted Deviations from the Original Brief

The approved v1 scope differs from the initial brief in two ways:

1. Provider presets are limited to OpenAI `gpt-5.6-luna`, Anthropic `claude-fable-5`, and Gemini `gemini-3.8-flash`, plus Custom/OpenAI-compatible. xAI, Mistral, DeepSeek, and larger models are omitted to keep the chatbot economical and the configuration focused.
2. The initial repository has no Magento installation. Compatibility targets are explicit, but installation-level verification must run later inside a real Magento 2.4.7/2.4.8 application.

All other product, security, privacy, performance, documentation, and source-of-truth requirements remain in scope.
