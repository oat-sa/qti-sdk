<?php

declare(strict_types=1);

namespace qtismtest\data\expressions\operators;

use PHPUnit\Framework\TestCase;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\MatchOperator;

/**
 * This class provides test for the backward compatibility Match operator in
 * PHP 7.*.
 * In PHP 8.*, match is a reserved word, the Match operator class has been
 * renamed MacthOperator but compact tests contain generated PHP code which
 * contains references to the Match class. This class makes sure these compact
 * tests still run in PHP 7.*. When run on PHP 8.0, the compact tests have to
 * be updated either by re-publishing the test or by running a script to update
 * the generated PHP code.
 */
class MatchTest extends TestCase
{
    public function testClassCreation(): void
    {
        if (version_compare(PHP_VERSION, '8.0.0', '<')) {
            $expression = $this->createMock(ExpressionCollection::class);
            $matchOperator = new \qtism\data\expressions\operators\Match($expression);
            $this::assertInstanceOf(MatchOperator::class, $matchOperator);
        } else {
            $this::assertTrue(true);
        }
    }
}
