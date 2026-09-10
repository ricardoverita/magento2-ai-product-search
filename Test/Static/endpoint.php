<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$controller = (string) file_get_contents($root . '/Controller/Chat/Send.php');
$service = (string) file_get_contents($root . '/Model/Chat/ChatService.php');

assert(str_contains($controller, 'HttpPostActionInterface'));
assert(str_contains($controller, 'CsrfAwareActionInterface'));
assert(str_contains($controller, 'JSON_THROW_ON_ERROR'));
assert(!str_contains($controller, 'getTrace'));
assert(!str_contains($controller, 'getMessage()'));
assert(str_contains($service, 'ProductRetrieverInterface'));
assert(str_contains($service, 'CandidateValidator'));
