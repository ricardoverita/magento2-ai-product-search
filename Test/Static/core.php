<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
foreach ([
    'Model/Config/SearchAIConfig.php',
    'Model/Data/ConversationMessage.php',
    'Model/Data/ProductCandidate.php',
    'Model/Data/ChatResponse.php',
    'Model/Chat/CandidateValidator.php',
    'Model/Catalog/ProductContextBuilder.php',
] as $file) {
    require_once $root . '/' . $file;
}

use Vera\SearchAI\Model\Catalog\ProductContextBuilder;
use Vera\SearchAI\Model\Chat\CandidateValidator;
use Vera\SearchAI\Model\Config\SearchAIConfig;
use Vera\SearchAI\Model\Data\ChatResponse;
use Vera\SearchAI\Model\Data\ProductCandidate;

$config = new class extends SearchAIConfig {
    public function __construct() {}
    public function shouldUseShortDescription(?int $storeId = null): bool { return true; }
    public function shouldIncludeDescription(?int $storeId = null): bool { return false; }
};

$candidate = new ProductCandidate(
    10,
    'SKU-10',
    'Black Shoe',
    '<p>Hello&nbsp; world<script>bad()</script></p>' . str_repeat('x', 600),
    '<p>Hidden full description</p>',
    ['Shoes'],
    ['color' => 'Black'],
    79.0,
    '$79.00',
    'USD',
    true,
    'https://example.com/shoe',
    'https://example.com/shoe.jpg'
);

$context = (new ProductContextBuilder($config))->build([$candidate]);
assert(!str_contains($context[0]['short_description'], '<'));
assert(!str_contains($context[0]['short_description'], 'bad()'));
assert(mb_strlen($context[0]['short_description']) <= 500);
assert(!array_key_exists('description', $context[0]));

$candidates = [
    $candidate,
    new ProductCandidate(20, 'SKU-20', 'Runner', '', '', [], [], 50.0, '$50.00', 'USD', true, '/20', '/20.jpg'),
    new ProductCandidate(30, 'SKU-30', 'Trainer', '', '', [], [], 60.0, '$60.00', 'USD', true, '/30', '/30.jpg'),
];
$validated = (new CandidateValidator())->validate(new ChatResponse('Options', [20, 999, 10, 20]), $candidates);
assert(array_map(static fn (ProductCandidate $item): int => $item->getId(), $validated) === [20, 10]);
