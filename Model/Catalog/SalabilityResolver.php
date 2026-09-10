<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Catalog;

use Magento\InventorySalesApi\Api\AreProductsSalableInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;

class SalabilityResolver
{
    public function __construct(
        private readonly StockResolverInterface $stockResolver,
        private readonly AreProductsSalableInterface $areProductsSalable
    ) {
    }

    /** @return array<string, bool> */
    public function resolve(array $skus, string $websiteCode): array
    {
        $skus = array_values(array_unique(array_filter(array_map('strval', $skus))));
        if ($skus === []) {
            return [];
        }

        $stock = $this->stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
        $results = $this->areProductsSalable->execute($skus, (int) $stock->getStockId());
        $salability = [];
        foreach ($results as $result) {
            $salability[(string) $result->getSku()] = (bool) $result->isSalable();
        }
        return $salability;
    }
}
