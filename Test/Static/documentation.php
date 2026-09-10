<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
foreach (['README.md', 'README.es.md'] as $file) {
    $text = (string) file_get_contents($root . '/' . $file);
    foreach ([
        'Vera_SearchAI',
        'vera/module-search-ai',
        'Magento 2.4.7',
        'PHP 8.2',
        'composer require vera/module-search-ai',
        'module:enable Vera_SearchAI',
        'setup:upgrade',
        'gpt-5.6-luna',
        'claude-fable-5',
        'gemini-3.8-flash',
        'Custom',
        'telemetry',
        'MIT',
        'https://veradesarrollo.com',
        'https://github.com/ricardoverita',
    ] as $required) {
        assert(str_contains($text, $required), $file . ' misses ' . $required);
    }
}

assert(is_file($root . '/i18n/en_US.csv'));
assert(is_file($root . '/i18n/es_ES.csv'));
assert(is_file($root . '/CONTRIBUTING.md'));
