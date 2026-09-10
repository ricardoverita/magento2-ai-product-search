<?php
declare(strict_types=1);
namespace Vera\SearchAI\Test\Unit\Model\Chat;
use PHPUnit\Framework\TestCase;
use Vera\SearchAI\Model\Chat\CandidateValidator;
use Vera\SearchAI\Model\Data\ChatResponse;
use Vera\SearchAI\Model\Data\ProductCandidate;
final class CandidateValidatorTest extends TestCase
{
    public function testRejectsIdOutsideMagentoCandidates(): void
    {
        $candidate = fn (int $id): ProductCandidate => new ProductCandidate(
            $id, 'S' . $id, 'P' . $id, '', '', [], [], 1.0, '$1', 'USD', true, '/' . $id, '/' . $id . '.jpg'
        );
        $valid = (new CandidateValidator())->validate(
            new ChatResponse('Answer', [20, 999, 10]),
            [$candidate(10), $candidate(20), $candidate(30)]
        );
        self::assertSame([20, 10], array_map(fn (ProductCandidate $item): int => $item->getId(), $valid));
    }
}
