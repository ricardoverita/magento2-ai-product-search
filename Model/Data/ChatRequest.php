<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Data;

final class ChatRequest
{
    public function __construct(
        private readonly string $systemPrompt,
        private readonly array $messages,
        private readonly ?int $storeId = null
    ) {
    }

    public function getSystemPrompt(): string
    {
        return $this->systemPrompt;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getStoreId(): ?int
    {
        return $this->storeId;
    }
}
