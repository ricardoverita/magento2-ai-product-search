<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Chat;

use Vera\SearchAI\Model\Chat\Exception\InvalidRequestException;
use Vera\SearchAI\Model\Config\SearchAIConfig;
use Vera\SearchAI\Model\Data\ConversationMessage;

class RequestValidator
{
    private const MAX_HISTORY_MESSAGE_LENGTH = 2000;
    private const MAX_HISTORY_TOTAL_LENGTH = 8000;

    public function __construct(private readonly SearchAIConfig $config)
    {
    }

    /** @return ConversationMessage[] */
    public function validate(string $message, mixed $history, ?int $storeId = null): array
    {
        $message = trim($message);
        if ($message === '' || mb_strlen($message) > $this->config->getMaxQueryLength($storeId)) {
            throw new InvalidRequestException('Invalid message.');
        }
        if (!is_array($history)) {
            throw new InvalidRequestException('Invalid history.');
        }

        $validated = [];
        $total = 0;
        foreach ($history as $item) {
            if (!is_array($item) || !isset($item['role'], $item['content'])
                || !is_string($item['role']) || !is_string($item['content'])
                || !in_array($item['role'], ['user', 'assistant'], true)
            ) {
                throw new InvalidRequestException('Invalid history.');
            }
            $content = trim($item['content']);
            if ($content === '') {
                continue;
            }
            $content = mb_substr($content, 0, self::MAX_HISTORY_MESSAGE_LENGTH);
            $total += mb_strlen($content);
            if ($total > self::MAX_HISTORY_TOTAL_LENGTH) {
                throw new InvalidRequestException('History is too large.');
            }
            $validated[] = new ConversationMessage($item['role'], $content);
        }

        return array_slice($validated, -$this->config->getMaxHistoryMessages($storeId));
    }
}
