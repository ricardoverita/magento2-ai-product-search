<?php
declare(strict_types=1);

$composer = json_decode(
    (string) file_get_contents(__DIR__ . '/../../composer.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);

assert($composer['name'] === 'vera/module-search-ai');
assert($composer['type'] === 'magento2-module');
assert($composer['license'] === 'MIT');
assert($composer['require']['php'] === '^8.2 || ^8.3 || ^8.4');
assert($composer['autoload']['psr-4']['Vera\\SearchAI\\'] === '');
assert($composer['homepage'] === 'https://veradesarrollo.com');
