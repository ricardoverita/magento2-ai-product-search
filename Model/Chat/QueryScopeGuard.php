<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Chat;

final class QueryScopeGuard
{
    private const PROGRAMMING_LANGUAGES =
        '(?:python|javascript|typescript|java|php|sql|ruby|golang|go|'
        . 'c\+\+|c#|bash|linux)';
    private const PROGRAMMING_CONTEXT =
        '(?:codigo|program(?:a|ar|acion)|script|comando(?:s)?|'
        . 'sintaxis|funcion(?:es)?|clase(?:s)?|tutorial|algoritmo|'
        . 'framework|libreria|api)';

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

        if (preg_match(
            '/\b' . self::PROGRAMMING_LANGUAGES . '\b.*\b' . self::PROGRAMMING_CONTEXT
            . '\b|\b' . self::PROGRAMMING_CONTEXT . '\b.*\b' . self::PROGRAMMING_LANGUAGES . '\b/u',
            $normalized
        ) === 1) {
            return true;
        }

        return preg_match(
            '/\b(?:cual es la capital|quien es|cuanto es|resuelve|que tiempo hara|pronostico|'
            . 'clima|noticias|presidente|futbol|receta|sintomas|diagnostico)\b/u',
            $normalized
        ) === 1;
    }

    private function normalize(string $query): string
    {
        $query = mb_strtolower(trim($query), 'UTF-8');
        $query = strtr($query, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        ]);

        return (string) preg_replace('/\s+/u', ' ', $query);
    }
}
