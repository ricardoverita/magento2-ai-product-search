<?php
declare(strict_types=1);
namespace Vera\SearchAI\Model\Ai\Provider;
use Vera\SearchAI\Api\AiProviderInterface;
use Vera\SearchAI\Model\Ai\Exception\ProviderException;
use Vera\SearchAI\Model\Data\ChatRequest;
use Vera\SearchAI\Model\Data\ChatResponse;
class Gemini extends AbstractProvider implements AiProviderInterface
{
    public function chat(ChatRequest $request): ChatResponse
    {
        $contents = [];
        foreach ($this->messages($request) as $message) {
            $contents[] = [
                'role' => ($message['role'] ?? '') === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => (string) ($message['content'] ?? '')]],
            ];
        }
        $model = rawurlencode($this->requireValue($this->config->getModel($request->getStoreId())));
        $data = $this->transport->post(
            'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent',
            ['x-goog-api-key' => $this->requireValue($this->config->getApiKey($request->getStoreId()))],
            [
                'system_instruction' => ['parts' => [['text' => $request->getSystemPrompt()]]],
                'contents' => $contents,
                'generationConfig' => [
                    'maxOutputTokens' => 1000,
                    'responseMimeType' => 'application/json',
                    'responseSchema' => $this->schema(),
                ],
            ],
            $this->timeout($request),
            $this->maxBytes($request)
        );
        $text = '';
        foreach (($data['candidates'][0]['content']['parts'] ?? []) as $part) {
            if (is_string($part['text'] ?? null)) {
                $text .= $part['text'];
            }
        }
        if ($text === '') {
            throw new ProviderException('Gemini returned no output text.');
        }
        return $this->decoder->decode($text);
    }
}
