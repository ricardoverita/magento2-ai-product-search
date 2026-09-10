<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Config;

final class Path
{
    public const ENABLED = 'searchai/general/enabled';
    public const ASSISTANT_NAME = 'searchai/general/assistant_name';
    public const WELCOME_MESSAGE = 'searchai/general/welcome_message';
    public const INPUT_PLACEHOLDER = 'searchai/general/input_placeholder';
    public const LOCATIONS = 'searchai/general/locations';
    public const MAX_HISTORY = 'searchai/general/max_history';
    public const PROVIDER = 'searchai/ai/provider';
    public const OPENAI_MODEL = 'searchai/ai/openai_model';
    public const OPENAI_API_KEY = 'searchai/ai/openai_api_key';
    public const ANTHROPIC_MODEL = 'searchai/ai/anthropic_model';
    public const ANTHROPIC_API_KEY = 'searchai/ai/anthropic_api_key';
    public const GEMINI_MODEL = 'searchai/ai/gemini_model';
    public const GEMINI_API_KEY = 'searchai/ai/gemini_api_key';
    public const CUSTOM_BASE_URL = 'searchai/ai/custom_base_url';
    public const CUSTOM_ENDPOINT = 'searchai/ai/custom_endpoint';
    public const CUSTOM_MODEL = 'searchai/ai/custom_model';
    public const CUSTOM_API_KEY = 'searchai/ai/custom_api_key';
    public const CUSTOM_AUTH_HEADER = 'searchai/ai/custom_auth_header';
    public const CUSTOM_AUTH_PREFIX = 'searchai/ai/custom_auth_prefix';
    public const CANDIDATE_LIMIT = 'searchai/catalog/candidate_limit';
    public const USE_SHORT_DESCRIPTION = 'searchai/catalog/use_short_description';
    public const INCLUDE_DESCRIPTION = 'searchai/catalog/include_description';
    public const ADDITIONAL_ATTRIBUTES = 'searchai/catalog/additional_attributes';
    public const INCLUDE_OUT_OF_STOCK = 'searchai/catalog/include_out_of_stock';
    public const REQUESTS_PER_MINUTE = 'searchai/security/requests_per_minute';
    public const MAX_QUERY_LENGTH = 'searchai/security/max_query_length';
    public const PROVIDER_TIMEOUT = 'searchai/security/provider_timeout';
    public const MAX_RESPONSE_BYTES = 'searchai/security/max_response_bytes';
    public const SYSTEM_GUIDANCE = 'searchai/advanced/system_guidance';

    private function __construct()
    {
    }
}
