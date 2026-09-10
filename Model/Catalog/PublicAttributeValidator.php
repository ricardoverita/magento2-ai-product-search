<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Catalog;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config as EavConfig;
use Throwable;

class PublicAttributeValidator
{
    private const BLOCKED = ['cost', 'supplier', 'supplier_id', 'internal_notes'];

    public function __construct(private readonly EavConfig $eavConfig)
    {
    }

    public function filter(array $codes): array
    {
        $valid = [];
        foreach ($codes as $code) {
            if (in_array($code, self::BLOCKED, true)) {
                continue;
            }
            try {
                $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $code);
                if ($attribute->getId() && ($attribute->getIsVisibleOnFront() || $attribute->getIsSearchable())) {
                    $valid[] = $code;
                }
            } catch (Throwable) {
                continue;
            }
        }
        return array_values(array_unique($valid));
    }
}
