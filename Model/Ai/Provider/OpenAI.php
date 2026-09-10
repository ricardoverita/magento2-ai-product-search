<?php
declare(strict_types=1);
namespace Vera\SearchAI\Model\Ai\Provider;
use Vera\SearchAI\Api\AiProviderInterface;
use Vera\SearchAI\Model\Ai\Exception\ProviderException;
use Vera\SearchAI\Model\Data\ChatRequest;
use Vera\SearchAI\Model\Data\ChatResponse;
class OpenAI extends AbstractProvider implements AiProviderInterface
{
    public function chat(ChatRequest $request): ChatResponse
    {
        $data = $this->transport->post(
            'https://api.openai.com/v1/responses',
            ['Authorization' => 'Bearer ' . $this->requireValue($this->config->getApiKey($request->getStoreId()))],
            [
                'model' => $this->requireValue($this->config->getModel($request->getStoreId())),
                'instructions' => $request->getSystemPrompt(),
                'input' => $this->messages($request),
                'max_output_tokens' => 1000,
                'text' => ['format' => [
                    'type' => 'json_schema',
                    'name' => 'product_recommendations',
                    'strict' => true,
                    'schema' => $this->schema(),
                ]],
            ],
            $this->timeout($request),
            $this->maxBytes($request)
        );
        $text = is_string($data['output_text'] ?? null) ? $data['output_text'] : '';
        if ($text === '') {
            foreach (($data['output'] ?? []) as $output) {
                foreach (($output['content'] ?? []) as $content) {
                    if (($content['type'] ?? '') === 'output_text' && is_string($content['text'] ?? null)) {
                        $text .= $content['text'];
                    }
                }
            }
        }
        if ($text === '') {
            throw new ProviderException('OpenAI returned no output text.');
        }
        return $this->decoder->decode($text);
    }
}
