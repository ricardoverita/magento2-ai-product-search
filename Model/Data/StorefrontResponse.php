<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Data;

final class StorefrontResponse
{
    public function __construct(
        private readonly string $answer,
        private readonly array $products
    ) {
    }

    public function getAnswer(): string
    {
        return $this->answer;
    }

    public function getProducts(): array
    {
        return $this->products;
    }

    public function toArray(): array
    {
        return [
            'success' => true,
            'answer' => $this->answer,
            'products' => array_map(
                static fn (ProductCard $card): array => $card->toArray(),
                $this->products
            ),
        ];
    }
}
