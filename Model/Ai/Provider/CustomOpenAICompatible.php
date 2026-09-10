<?php
declare(strict_types=1);
namespace Vera\SearchAI\Model\Ai\Provider;
use Vera\SearchAI\Api\AiProviderInterface;
use Vera\SearchAI\Model\Ai\Exception\ProviderConfigurationException;
use Vera\SearchAI\Model\Ai\Exception\ProviderException;
use Vera\SearchAI\Model\Data\ChatRequest;
use Vera\SearchAI\Model\Data\ChatResponse;
class CustomOpenAICompatible extends AbstractProvider implements AiProviderInterface
{
    public function chat(ChatRequest $request): ChatResponse
    {
        $headers = [];
        $key = $this->config->getApiKey($request->getStoreId());
        if ($key !== '') {
            $name = $this->config->getCustomAuthHeader($request->getStoreId());
            $prefix = $this->config->getCustomAuthPrefix($request->getStoreId());
            if (!preg_match('/^[A-Za-z0-9-]+$/', $name) || preg_match('/[\r\n]/', $prefix . $key)) {
                throw new ProviderConfigurationException('Invalid custom authorization header.');
            }
            $headers[$name] = trim($prefix . ' ' . $key);
        }
        $data = $this->transport->post(
            $this->buildUrl($request->getStoreId()),
            $headers,
            [
                'model' => $this->requireValue($this->config->getModel($request->getStoreId())),
                'messages' => array_merge(
                    [['role' => 'system', 'content' => $request->getSystemPrompt()]],
                    $this->messages($request)
                ),
                'max_tokens' => 1000,
            ],
            $this->timeout($request),
            $this->maxBytes($request)
        );
        $text = $data['choices'][0]['message']['content'] ?? null;
        if (!is_string($text) || $text === '') {
            throw new ProviderException('Custom provider returned no output text.');
        }
        return $this->decoder->decode($text);
    }
    private function buildUrl(?int $storeId): string
    {
        $base = $this->requireValue($this->config->getCustomBaseUrl($storeId));
        $parts = parse_url($base);
        if (!is_array($parts)
            || !in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)
            || isset($parts['user'], $parts['pass'], $parts['fragment'])
            || empty($parts['host'])) {
            throw new ProviderConfigurationException('Invalid custom provider base URL.');
        }
        $endpoint = $this->config->getCustomEndpoint($storeId);
        if (preg_match('/[\r\n?#]/', $endpoint)) {
            throw new ProviderConfigurationException('Invalid custom provider endpoint.');
        }
        return rtrim($base, '/') . '/' . ltrim($endpoint, '/');
    }
}
