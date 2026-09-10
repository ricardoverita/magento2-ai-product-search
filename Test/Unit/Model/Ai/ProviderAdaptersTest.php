<?php
declare(strict_types=1);
namespace Vera\SearchAI\Test\Unit\Model\Ai;
use PHPUnit\Framework\TestCase;
use Vera\SearchAI\Model\Ai\Http\HttpTransportInterface;
use Vera\SearchAI\Model\Ai\Provider\Anthropic;
use Vera\SearchAI\Model\Ai\Provider\CustomOpenAICompatible;
use Vera\SearchAI\Model\Ai\Provider\Gemini;
use Vera\SearchAI\Model\Ai\Provider\OpenAI;
use Vera\SearchAI\Model\Ai\StructuredResponseDecoder;
use Vera\SearchAI\Model\Config\SearchAIConfig;
use Vera\SearchAI\Model\Data\ChatRequest;
final class ProviderAdaptersTest extends TestCase
{
    public function testExtractsAllProviderResponseShapes(): void
    {
        $request = new ChatRequest('system', [['role' => 'user', 'content' => 'question']], 1);
        $json = '{"answer":"Found","product_ids":[10]}';
        $cases = [
            [OpenAI::class, ['output' => [['content' => [['type' => 'output_text', 'text' => $json]]]]]],
            [Anthropic::class, ['content' => [['type' => 'text', 'text' => $json]]]],
            [Gemini::class, ['candidates' => [['content' => ['parts' => [['text' => $json]]]]]]],
            [CustomOpenAICompatible::class, ['choices' => [['message' => ['content' => $json]]]]],
        ];
        foreach ($cases as [$class, $payload]) {
            $transport = new class($payload) implements HttpTransportInterface {
                public function __construct(private readonly array $payload) {}
                public function post(string $url, array $headers, array $payload, int $timeout, int $maxBytes): array
                {
                    return $this->payload;
                }
            };
            $result = (new $class($this->config(), $transport, new StructuredResponseDecoder()))->chat($request);
            self::assertSame([10], $result->getProductIds());
        }
    }
    private function config(): SearchAIConfig
    {
        return new class extends SearchAIConfig {
            public function __construct() {}
            public function getApiKey(?int $storeId = null): string { return 'secret'; }
            public function getModel(?int $storeId = null): string { return 'model'; }
            public function getProviderTimeout(?int $storeId = null): int { return 10; }
            public function getMaxResponseBytes(?int $storeId = null): int { return 10000; }
            public function getCustomBaseUrl(?int $storeId = null): string { return 'https://example.com'; }
            public function getCustomEndpoint(?int $storeId = null): string { return '/v1/chat/completions'; }
            public function getCustomAuthHeader(?int $storeId = null): string { return 'Authorization'; }
            public function getCustomAuthPrefix(?int $storeId = null): string { return 'Bearer'; }
        };
    }
}
