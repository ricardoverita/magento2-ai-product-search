<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Data;

final class ConversationMessage
{
    public function __construct(
        private readonly string $role,
        private readonly string $content
    ) {
        if (!in_array($role, ['user', 'assistant'], true)) {
            throw new \InvalidArgumentException('Invalid conversation role.');
        }
    }

    public function getRole(): string { return $this->role; }
    public function getContent(): string { return $this->content; }

    public function toArray(): array
    {
        return ['role' => $this->role, 'content' => $this->content];
    }
}
