<?php
declare(strict_types=1);
namespace Vera\SearchAI\Model\Ai\Provider;
use Vera\SearchAI\Model\Ai\Exception\ProviderException;
use Vera\SearchAI\Model\Ai\Http\HttpTransportInterface;
use Vera\SearchAI\Model\Ai\StructuredResponseDecoder;
use Vera\SearchAI\Model\Config\SearchAIConfig;
use Vera\SearchAI\Model\Data\ChatRequest;
abstract class AbstractProvider
{
    public function __construct(
        protected readonly SearchAIConfig $config,
        protected readonly HttpTransportInterface $transport,
        protected readonly StructuredResponseDecoder $decoder
    ) {}
    protected function timeout(ChatRequest $request): int
    {
        return $this->config->getProviderTimeout($request->getStoreId());
    }
    protected function maxBytes(ChatRequest $request): int
    {
        return $this->config->getMaxResponseBytes($request->getStoreId());
    }
    protected function requireValue(string $value): string
    {
        if (trim($value) === '') {
            throw new ProviderException('The selected AI provider is not configured.');
        }
        return $value;
    }
    protected function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'answer' => ['type' => 'string'],
                'product_ids' => ['type' => 'array', 'items' => ['type' => 'integer']],
            ],
            'required' => ['answer', 'product_ids'],
            'additionalProperties' => false,
        ];
    }
    protected function messages(ChatRequest $request): array { return $request->getMessages(); }
}
