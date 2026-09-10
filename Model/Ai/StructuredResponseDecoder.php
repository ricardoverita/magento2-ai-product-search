<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Ai;

use JsonException;
use Vera\SearchAI\Model\Ai\Exception\ProviderException;
use Vera\SearchAI\Model\Data\ChatResponse;

class StructuredResponseDecoder
{
    public function decode(string $text): ChatResponse
    {
        $text = trim($text);
        if (preg_match('/^\x60{3}(?:json)?\s*(\{.*\})\s*\x60{3}$/is', $text, $matches)) {
            $text = $matches[1];
        }
        try {
            $data = json_decode($text, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new ProviderException('AI provider returned invalid structured output.', 0, $exception);
        }
        if (!is_array($data) || !isset($data['answer'], $data['product_ids'])
            || !is_string($data['answer']) || !is_array($data['product_ids'])) {
            throw new ProviderException('AI provider returned an invalid output shape.');
        }
        $answer = trim(strip_tags($data['answer']));
        if ($answer === '' || mb_strlen($answer) > 4000) {
            throw new ProviderException('AI provider returned an invalid answer.');
        }
        $ids = [];
        foreach (array_slice($data['product_ids'], 0, 20) as $id) {
            if (!is_int($id) || $id <= 0) {
                throw new ProviderException('AI provider returned an invalid product ID.');
            }
            $ids[] = $id;
        }
        return new ChatResponse($answer, array_values(array_unique($ids)));
    }
}
