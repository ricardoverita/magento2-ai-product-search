<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$rateLimiter = (string) file_get_contents($root . '/Model/Chat/RateLimiter.php');
$fingerprint = (string) file_get_contents($root . '/Model/Chat/NetworkFingerprint.php');

assert(str_contains($rateLimiter, 'LockManagerInterface'));
assert(str_contains($rateLimiter, 'hash('));
assert(str_contains($rateLimiter, 'finally'));
assert(str_contains($fingerprint, "hash_hmac('sha256'"));
assert(!str_contains($rateLimiter, 'getRemoteAddress'));
