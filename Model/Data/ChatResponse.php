<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Data;

final class ChatResponse
{
    public function __construct(
        private readonly string $answer,
        private readonly array $productIds
    ) {
    }

    public function getAnswer(): string
    {
        return $this->answer;
    }

    public function getProductIds(): array
    {
        return $this->productIds;
    }
}
