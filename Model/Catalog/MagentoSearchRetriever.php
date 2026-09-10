<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Catalog;

use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\CollectionFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Vera\SearchAI\Api\ProductRetrieverInterface;
use Vera\SearchAI\Model\Config\SearchAIConfig;
use Vera\SearchAI\Model\Data\ProductCandidate;

class MagentoSearchRetriever implements ProductRetrieverInterface
{
    public function __construct(
        private readonly CollectionFactory $collectionFactory,
        private readonly Visibility $productVisibility,
        private readonly StoreManagerInterface $storeManager,
        private readonly SearchAIConfig $config,
        private readonly PublicAttributeValidator $attributeValidator,
        private readonly CategoryNameResolver $categoryNameResolver,
        private readonly SalabilityResolver $salabilityResolver,
        private readonly ImageFactory $imageFactory,
        private readonly PriceCurrencyInterface $priceCurrency
    ) {
    }

    public function retrieve(string $query, int $storeId, int $limit): array
    {
        $store = $this->storeManager->getStore($storeId);
        $website = $store->getWebsite();
        $additional = $this->attributeValidator->filter($this->config->getAdditionalAttributes($storeId));
        $attributes = array_values(array_unique(array_merge(
            ['sku', 'name', 'short_description', 'description', 'image', 'small_image', 'price'],
            $additional
        )));
        $includeOutOfStock = $this->config->shouldIncludeOutOfStock($storeId);
        $fetchLimit = $includeOutOfStock ? $limit : min(40, $limit * 3);

        $collection = $this->collectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addStoreFilter($storeId);
        $collection->addSearchFilter($query);
        $collection->addAttributeToSelect($attributes);
        $collection->addAttributeToFilter('status', Status::STATUS_ENABLED);
        $collection->setVisibility($this->productVisibility->getVisibleInSearchIds());
        $collection->setPageSize($fetchLimit);

        $products = [];
        $skus = [];
        $categoryIds = [];
        foreach ($collection as $product) {
            if (!$product instanceof Product) {
                continue;
            }
            $products[] = $product;
            $skus[] = (string) $product->getSku();
            $categoryIds = array_merge($categoryIds, $product->getCategoryIds());
        }

        $salability = $this->salabilityResolver->resolve($skus, (string) $website->getCode());
        $categoryNames = $this->categoryNameResolver->resolve($categoryIds, $storeId);
        $currency = (string) $store->getCurrentCurrencyCode();
        $candidates = [];

        foreach ($products as $product) {
            $sku = (string) $product->getSku();
            $isSalable = $salability[$sku] ?? false;
            if (!$includeOutOfStock && !$isSalable) {
                continue;
            }

            $attributeValues = [];
            foreach ($additional as $code) {
                $value = $product->getAttributeText($code);
                if (is_array($value)) {
                    $value = implode(', ', array_map('strval', $value));
                } elseif (!is_scalar($value)) {
                    $value = $product->getData($code);
                }
                if (is_scalar($value) && trim((string) $value) !== '') {
                    $attributeValues[$code] = (string) $value;
                }
            }

            $categories = [];
            foreach ($product->getCategoryIds() as $categoryId) {
                if (isset($categoryNames[(int) $categoryId])) {
                    $categories[] = $categoryNames[(int) $categoryId];
                }
            }

            $price = (float) $product->getFinalPrice();
            $image = (string) $this->imageFactory->create()
                ->init($product, 'product_thumbnail_image')
                ->getUrl();

            $candidates[] = new ProductCandidate(
                (int) $product->getId(),
                $sku,
                (string) $product->getName(),
                (string) $product->getData('short_description'),
                (string) $product->getData('description'),
                $categories,
                $attributeValues,
                $price,
                (string) $this->priceCurrency->convertAndFormat($price, false, 2, $store),
                $currency,
                $isSalable,
                (string) $product->getProductUrl(),
                $image
            );

            if (count($candidates) >= $limit) {
                break;
            }
        }

        return $candidates;
    }
}
