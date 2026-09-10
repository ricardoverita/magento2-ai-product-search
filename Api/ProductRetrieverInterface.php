<?php
declare(strict_types=1);

namespace Vera\SearchAI\Api;

interface ProductRetrieverInterface
{
    /** @return \Vera\SearchAI\Model\Data\ProductCandidate[] */
    public function retrieve(string $query, int $storeId, int $limit): array;
}
