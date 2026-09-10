<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Catalog;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

class CategoryNameResolver
{
    public function __construct(private readonly CollectionFactory $collectionFactory)
    {
    }

    /** @return array<int, string> */
    public function resolve(array $ids, int $storeId): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if ($ids === []) {
            return [];
        }

        $collection = $this->collectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addAttributeToSelect('name');
        $collection->addFieldToFilter('entity_id', ['in' => $ids]);

        $names = [];
        foreach ($collection as $category) {
            $names[(int) $category->getId()] = (string) $category->getName();
        }
        return $names;
    }
}
