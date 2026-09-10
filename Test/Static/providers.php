<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
require_once $root . '/Model/Data/ChatResponse.php';
require_once $root . '/Model/Ai/Exception/ProviderException.php';
require_once $root . '/Model/Ai/StructuredResponseDecoder.php';

use Vera\SearchAI\Model\Ai\Exception\ProviderException;
use Vera\SearchAI\Model\Ai\StructuredResponseDecoder;

$decoder = new StructuredResponseDecoder();
$response = $decoder->decode("```json\n{\"answer\":\"Options\",\"product_ids\":[10,20,10]}\n```");
assert($response->getAnswer() === 'Options');
assert($response->getProductIds() === [10, 20]);

try {
    $decoder->decode('{"answer":[],"product_ids":"10"}');
    assert(false, 'Malformed response was accepted.');
} catch (ProviderException) {
}

foreach (['OpenAI.php', 'Anthropic.php', 'Gemini.php', 'CustomOpenAICompatible.php'] as $provider) {
    assert(is_file($root . '/Model/Ai/Provider/' . $provider));
}
