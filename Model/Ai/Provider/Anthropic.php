<?php
declare(strict_types=1);
namespace Vera\SearchAI\Model\Ai\Provider;
use Vera\SearchAI\Api\AiProviderInterface;
use Vera\SearchAI\Model\Ai\Exception\ProviderException;
use Vera\SearchAI\Model\Data\ChatRequest;
use Vera\SearchAI\Model\Data\ChatResponse;
class Anthropic extends AbstractProvider implements AiProviderInterface
{
    public function chat(ChatRequest $request): ChatResponse
    {
        $data = $this->transport->post(
            'https://api.anthropic.com/v1/messages',
            ['x-api-key' => $this->requireValue($this->config->getApiKey($request->getStoreId())), 'anthropic-version' => '2023-06-01'],
            [
                'model' => $this->requireValue($this->config->getModel($request->getStoreId())),
                'system' => $request->getSystemPrompt(),
                'messages' => $this->messages($request),
                'max_tokens' => 1000,
            ],
            $this->timeout($request),
            $this->maxBytes($request)
        );
        $text = '';
        foreach (($data['content'] ?? []) as $content) {
            if (($content['type'] ?? '') === 'text' && is_string($content['text'] ?? null)) {
                $text .= $content['text'];
            }
        }
        if ($text === '') {
            throw new ProviderException('Anthropic returned no output text.');
        }
        return $this->decoder->decode($text);
    }
}
