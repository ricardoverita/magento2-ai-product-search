<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Config;

final class ProviderOptions
{
    public const OPENAI = 'openai';
    public const OPENAI_MODEL = 'gpt-5.6-luna';
    public const ANTHROPIC = 'anthropic';
    public const ANTHROPIC_MODEL = 'claude-fable-5';
    public const GEMINI = 'gemini';
    public const GEMINI_MODEL = 'gemini-3.8-flash';
    public const CUSTOM = 'custom';

    public const PROVIDERS = [
        self::OPENAI,
        self::ANTHROPIC,
        self::GEMINI,
        self::CUSTOM,
    ];

    private function __construct()
    {
    }
}
