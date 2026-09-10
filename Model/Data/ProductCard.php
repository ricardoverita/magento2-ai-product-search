<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Data;

final class ProductCard
{
    public function __construct(
        private readonly int $id,
        private readonly string $sku,
        private readonly string $name,
        private readonly string $formattedPrice,
        private readonly string $currency,
        private readonly bool $salable,
        private readonly string $url,
        private readonly string $image
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'price' => $this->formattedPrice,
            'currency' => $this->currency,
            'salable' => $this->salable,
            'url' => $this->url,
            'image' => $this->image,
        ];
    }
}
