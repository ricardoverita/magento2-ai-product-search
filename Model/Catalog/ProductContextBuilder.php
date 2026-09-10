<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Catalog;

use Vera\SearchAI\Model\Config\SearchAIConfig;
use Vera\SearchAI\Model\Data\ProductCandidate;

class ProductContextBuilder
{
    public function __construct(private readonly SearchAIConfig $config)
    {
    }

    public function build(array $candidates, ?int $storeId = null): array
    {
        $context = [];
        foreach ($candidates as $candidate) {
            if (!$candidate instanceof ProductCandidate) {
                continue;
            }
            $item = [
                'id' => $candidate->getId(),
                'sku' => $this->clean($candidate->getSku(), 255),
                'name' => $this->clean($candidate->getName(), 500),
                'categories' => array_map(fn ($value): string => $this->clean((string) $value, 255), $candidate->getCategories()),
                'attributes' => $this->cleanAttributes($candidate->getAttributes()),
                'price' => $candidate->getPrice(),
                'currency' => $candidate->getCurrency(),
                'salable' => $candidate->isSalable(),
            ];
            if ($this->config->shouldUseShortDescription($storeId)) {
                $item['short_description'] = $this->clean($candidate->getShortDescription(), 500);
            }
            if ($this->config->shouldIncludeDescription($storeId)) {
                $item['description'] = $this->clean($candidate->getDescription(), 1500);
            }
            $context[] = $item;
        }
        return $context;
    }

    private function cleanAttributes(array $attributes): array
    {
        $clean = [];
        foreach ($attributes as $code => $value) {
            if (is_scalar($value)) {
                $clean[(string) $code] = $this->clean((string) $value, 300);
            }
        }
        return $clean;
    }

    private function clean(string $value, int $limit): string
    {
        $value = preg_replace('@<(script|style)\b[^>]*>.*?</\1>@is', ' ', $value) ?? '';
        $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/[\p{Cc}\p{Cf}]+/u', ' ', $value) ?? '';
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? '';
        return mb_substr($value, 0, $limit);
    }
}
