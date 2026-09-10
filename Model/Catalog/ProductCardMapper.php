<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Catalog;

use Vera\SearchAI\Model\Data\ProductCandidate;
use Vera\SearchAI\Model\Data\ProductCard;

class ProductCardMapper
{
    public function map(ProductCandidate $candidate): ProductCard
    {
        return new ProductCard(
            $candidate->getId(),
            $candidate->getSku(),
            $candidate->getName(),
            $candidate->getFormattedPrice(),
            $candidate->getCurrency(),
            $candidate->isSalable(),
            $candidate->getUrl(),
            $candidate->getImage()
        );
    }
}
