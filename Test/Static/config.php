<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$defaults = simplexml_load_file($root . '/etc/config.xml');
$system = simplexml_load_file($root . '/etc/adminhtml/system.xml');
$acl = simplexml_load_file($root . '/etc/acl.xml');

assert($defaults !== false);
assert($system !== false);
assert($acl !== false);
assert((string) $defaults->default->searchai->general->enabled === '0');
assert((string) $defaults->default->searchai->catalog->candidate_limit === '8');
assert((string) $defaults->default->searchai->catalog->include_description === '0');
assert((string) $defaults->default->searchai->security->requests_per_minute === '10');

$systemText = (string) file_get_contents($root . '/etc/adminhtml/system.xml');
assert(substr_count($systemText, 'Magento\\Config\\Model\\Config\\Backend\\Encrypted') === 4);
assert(str_contains($systemText, 'Vera_SearchAI::config'));

$public = (string) file_get_contents($root . '/Model/Config/PublicConfigProvider.php');
assert(!str_contains($public, 'getApiKey'));
assert(!str_contains($public, "'provider'"));
