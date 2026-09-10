<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$javascript = (string) file_get_contents($root . '/view/frontend/web/js/widget.js');
$template = (string) file_get_contents($root . '/view/frontend/templates/widget.phtml');

assert(!str_contains($javascript, 'innerHTML'));
assert(!str_contains($javascript, 'api_key'));
assert(!str_contains($javascript, 'veradesarrollo.com'));
assert(!preg_match('/on(click|submit|keydown)=/i', $template));
assert(str_contains($javascript, 'textContent'));
assert(str_contains($template, 'aria-live="polite"'));
assert(str_contains($template, 'data-mage-init'));
