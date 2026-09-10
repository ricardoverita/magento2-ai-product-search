<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Chat;

use Vera\SearchAI\Model\Data\ChatResponse;
use Vera\SearchAI\Model\Data\ProductCandidate;

class CandidateValidator
{
    /** @return ProductCandidate[] */
    public function validate(ChatResponse $response, array $candidates): array
    {
        $allowed = [];
        foreach ($candidates as $candidate) {
            if ($candidate instanceof ProductCandidate) {
                $allowed[$candidate->getId()] = $candidate;
            }
        }

        $valid = [];
        foreach (array_unique($response->getProductIds()) as $id) {
            if (is_int($id) && isset($allowed[$id])) {
                $valid[] = $allowed[$id];
            }
        }
        return $valid;
    }
}
