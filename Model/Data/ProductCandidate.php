<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Data;

final class ProductCandidate
{
    public function __construct(
        private readonly int $id,
        private readonly string $sku,
        private readonly string $name,
        private readonly string $shortDescription,
        private readonly string $description,
        private readonly array $categories,
        private readonly array $attributes,
        private readonly float $price,
        private readonly string $formattedPrice,
        private readonly string $currency,
        private readonly bool $salable,
        private readonly string $url,
        private readonly string $image
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getFormattedPrice(): string
    {
        return $this->formattedPrice;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function isSalable(): bool
    {
        return $this->salable;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getImage(): string
    {
        return $this->image;
    }
}
