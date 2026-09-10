# Contributing to SearchAI

Thank you for improving SearchAI for Magento 2.

## Development expectations

Target Magento 2.4.7+ and PHP 8.2–8.4. Keep classes focused, use constructor dependency injection, preserve store scope, and do not use ObjectManager directly. New behavior requires tests. Never include real provider keys, customer messages, catalog exports, or credentials in fixtures.

Magento is the product source of truth. Provider adapters may return answer text and candidate IDs only. Provider-specific HTTP payloads stay under Model/Ai/Provider. Catalog implementations stay behind ProductRetrieverInterface.

## Checks

Run in a Magento development installation:

~~~bash
vendor/bin/phpunit -c app/code/Vera/SearchAI/phpunit.xml.dist
vendor/bin/phpcs --standard=app/code/Vera/SearchAI/phpcs.xml.dist app/code/Vera/SearchAI
find app/code/Vera/SearchAI -name '*.php' -print0 | xargs -0 -n1 php -l
node --check app/code/Vera/SearchAI/view/frontend/web/js/widget.js
~~~

Test both enabled and disabled placements, multi-store scope, invalid provider responses, missing credentials, rate limits, and candidate-ID validation.

## Pull requests

Explain the behavior and security/privacy impact, list verification commands and outputs, and update README.md plus README.es.md when public configuration changes.

Contributions are accepted under the MIT License. Project information: [VeraDesarrollo.com](https://veradesarrollo.com), [GitHub](https://github.com/ricardoverita), and [Ricardo Vera on LinkedIn](https://www.linkedin.com/in/ricardo-vera/).
