<?php
declare(strict_types=1);
namespace Vera\SearchAI\Api;
use Vera\SearchAI\Model\Data\ChatRequest;
use Vera\SearchAI\Model\Data\ChatResponse;
interface AiProviderInterface
{
    public function chat(ChatRequest $request): ChatResponse;
}
