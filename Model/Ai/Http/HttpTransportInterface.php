<?php
declare(strict_types=1);
namespace Vera\SearchAI\Model\Ai\Http;
interface HttpTransportInterface
{
    public function post(string $url, array $headers, array $payload, int $timeout, int $maxBytes): array;
}
