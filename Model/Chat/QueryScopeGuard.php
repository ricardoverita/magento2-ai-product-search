<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Chat;

final class QueryScopeGuard
{
    private const PROGRAMMING_LANGUAGES =
        '(?:python|javascript|typescript|java|php|sql|ruby|golang|go|'
        . 'c\+\+|c#|bash|linux)';
    private const PROGRAMMING_CONTEXT =
        '(?:codigo|code|program(?:a|ar|acion|ming)?|script|comando(?:s)?|'
        . 'command(?:s)?|sintaxis|syntax|funcion(?:es)?|function(?:s)?|'
        . 'clase(?:s)?|class(?:es)?|tutorial|algoritmo|algorithm|'
        . 'framework|libreria|librar(?:y|ies)|api)';

    /**
     * Detects clearly non-shopping requests without making another AI call.
     * Product names that happen to contain a language name remain allowed unless
     * the customer also asks for programming-related help.
     */
    public function isOutOfScope(string $query): bool
    {
        $normalized = $this->normalize($query);
        if ($normalized === '') {
            return false;
        }

        $language = '(?<![a-z0-9])' . self::PROGRAMMING_LANGUAGES . '(?![a-z0-9])';
        $context = '\b' . self::PROGRAMMING_CONTEXT . '\b';
        $programmingPattern = '/' . $language . '.*' . $context . '|'
            . $context . '.*' . $language . '/u';
        if (preg_match($programmingPattern, $normalized) === 1) {
            return true;
        }

        return preg_match(
            '/^(?:cual es la capital|quien es(?: el| la)?|cuanto es|resuelve|que tiempo hara|'
            . 'como estara el clima|pronostico|noticias|what is the capital|who is(?: the| a)?|'
            . 'how much is|solve|what(?: will)? the weather|weather forecast|latest news|'
            . 'dame una receta|give me a recipe|sintomas de|symptoms of|diagnostico de|diagnosis of)\b/u',
            $normalized
        ) === 1;
    }

    private function normalize(string $query): string
    {
        $query = mb_strtolower(trim($query), 'UTF-8');
        $query = strtr($query, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        ]);
        $query = (string) preg_replace('/^[^a-z0-9]+/u', '', $query);

        return (string) preg_replace('/\s+/u', ' ', $query);
    }
}
