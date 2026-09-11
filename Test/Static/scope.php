<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
require_once $root . '/Model/Chat/QueryScopeGuard.php';

use Vera\SearchAI\Model\Chat\QueryScopeGuard;

$guard = new QueryScopeGuard();

$chatService = (string) file_get_contents($root . '/Model/Chat/ChatService.php');
$promptBuilder = (string) file_get_contents($root . '/Model/Chat/PromptBuilder.php');
assert(str_contains($chatService, 'QueryScopeGuard'));
assert(str_contains($chatService, 'isOutOfScope'));
assert(str_contains($chatService, 'I can only help you find products in this store.'));
$scopePosition = strpos($chatService, '$this->scopeGuard->isOutOfScope');
$retrieverPosition = strpos($chatService, '$this->productRetriever->retrieve');
$providerPosition = strpos($chatService, '->chat($request)');
assert($scopePosition !== false && $retrieverPosition !== false && $scopePosition < $retrieverPosition);
assert($scopePosition !== false && $providerPosition !== false && $scopePosition < $providerPosition);
assert(str_contains($promptBuilder, 'If the request is not about finding products'));

foreach ([
    'Dame una lista de comandos de Python',
    'Explícame cómo declarar una función en JavaScript',
    '¿Cuál es la capital de Francia?',
    '¿Qué tiempo hará mañana?',
    'Show me a list of Python commands',
    'Explain how to declare a function in JavaScript',
    'What is the capital of France?',
    'What will the weather be tomorrow?',
] as $query) {
    assert($guard->isOutOfScope($query), $query . ' should be out of scope');
}

foreach ([
    'Busco zapatillas negras para correr',
    '¿Tienen una camisa de algodón?',
    'Necesito una laptop para trabajo',
    'Busco una camiseta de fútbol',
    'Busco ropa para clima frío',
    'Busco un libro sobre el presidente de Francia',
    'I need a football shirt',
    'I need a book about the president of France',
] as $query) {
    assert(!$guard->isOutOfScope($query), $query . ' should remain a product query');
}
