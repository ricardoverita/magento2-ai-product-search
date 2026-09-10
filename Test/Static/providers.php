<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
require_once $root . '/Model/Data/ChatResponse.php';
require_once $root . '/Model/Ai/Exception/ProviderException.php';
require_once $root . '/Model/Ai/StructuredResponseDecoder.php';
require_once $root . '/Api/AiProviderInterface.php';
require_once $root . '/Model/Ai/Http/HttpTransportInterface.php';
require_once $root . '/Model/Ai/Provider/AbstractProvider.php';
require_once $root . '/Model/Ai/Provider/OpenAI.php';
require_once $root . '/Model/Ai/Provider/Anthropic.php';
require_once $root . '/Model/Ai/Provider/Gemini.php';
require_once $root . '/Model/Ai/Provider/CustomOpenAICompatible.php';
require_once $root . '/Model/Config/SearchAIConfig.php';
require_once $root . '/Model/Data/ChatRequest.php';

use Vera\SearchAI\Model\Ai\Exception\ProviderException;
use Vera\SearchAI\Model\Ai\StructuredResponseDecoder;
use Vera\SearchAI\Model\Ai\Http\HttpTransportInterface;
use Vera\SearchAI\Model\Ai\Provider\Anthropic;
use Vera\SearchAI\Model\Ai\Provider\CustomOpenAICompatible;
use Vera\SearchAI\Model\Ai\Provider\Gemini;
use Vera\SearchAI\Model\Ai\Provider\OpenAI;
use Vera\SearchAI\Model\Config\SearchAIConfig;
use Vera\SearchAI\Model\Data\ChatRequest;

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

$providerConfig = new class extends SearchAIConfig {
    public function __construct() {}
    public function getApiKey(?int $storeId = null): string { return 'secret'; }
    public function getModel(?int $storeId = null): string { return 'economical-model'; }
    public function getProviderTimeout(?int $storeId = null): int { return 10; }
    public function getMaxResponseBytes(?int $storeId = null): int { return 10000; }
    public function getCustomBaseUrl(?int $storeId = null): string { return 'https://example.com'; }
    public function getCustomEndpoint(?int $storeId = null): string { return '/v1/chat/completions'; }
    public function getCustomAuthHeader(?int $storeId = null): string { return 'Authorization'; }
    public function getCustomAuthPrefix(?int $storeId = null): string { return 'Bearer'; }
};
$request = new ChatRequest('system', [['role' => 'user', 'content' => 'query']], 3);
$json = '{"answer":"Found","product_ids":[10]}';
$shapes = [
    OpenAI::class => ['output' => [['content' => [['type' => 'output_text', 'text' => $json]]]]],
    Anthropic::class => ['content' => [['type' => 'text', 'text' => $json]]],
    Gemini::class => ['candidates' => [['content' => ['parts' => [['text' => $json]]]]]],
    CustomOpenAICompatible::class => ['choices' => [['message' => ['content' => $json]]]],
];
foreach ($shapes as $providerClass => $payload) {
    $transport = new class($payload) implements HttpTransportInterface {
        public function __construct(private readonly array $payload) {}
        public function post(string $url, array $headers, array $payload, int $timeout, int $maxBytes): array
        {
            assert($timeout === 10);
            assert($maxBytes === 10000);
            return $this->payload;
        }
    };
    $parsed = (new $providerClass($providerConfig, $transport, $decoder))->chat($request);
    assert($parsed->getProductIds() === [10]);
}
