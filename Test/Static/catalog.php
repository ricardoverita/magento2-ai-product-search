<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$retriever = (string) file_get_contents($root . '/Model/Catalog/MagentoSearchRetriever.php');
$salability = (string) file_get_contents($root . '/Model/Catalog/SalabilityResolver.php');
$categories = (string) file_get_contents($root . '/Model/Catalog/CategoryNameResolver.php');

assert(str_contains($retriever, 'Fulltext\\CollectionFactory'));
assert(str_contains($retriever, 'addSearchFilter'));
assert(str_contains($retriever, 'setVisibility'));
assert(!str_contains($retriever, 'ProductRepositoryInterface'));
assert(str_contains($salability, 'AreProductsSalableInterface'));
assert(str_contains($categories, "addFieldToFilter('entity_id', ['in' => \$ids])"));
