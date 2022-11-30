<?php

declare(strict_types=1);

namespace qtismtest\runtime\common;

use InvalidArgumentException;
use qtism\runtime\common\VariableIdentifier;
use qtismtest\QtiSmTestCase;

/**
 * Class VariableIdentifierTest
 */
class VariableIdentifierTest extends QtiSmTestCase
{
    /**
     * @dataProvider invalidIdentifierProvider
     *
     * @param string $identifier
     */
    public function testInvalidIdentifier($identifier): void
    {
        $this->expectException(InvalidArgumentException::class);
        $v = new VariableIdentifier($identifier);
    }

    /**
     * @dataProvider simpleIdentifiersProvider
     *
     * @param string $identifier
     */
    public function testSimpleIdentifiers($identifier): void
    {
        $v = new VariableIdentifier($identifier);

        $this::assertEquals($identifier, $v->getIdentifier());
        $this::assertEquals($identifier, $v->getVariableName());
        $this::assertFalse($v->hasPrefix());
        $this::assertFalse($v->hasSequenceNumber());
    }

    /**
     * @dataProvider prefixedIdentifiersProvider
     *
     * @param string $identifier
     * @param string $expectedPrefix
     * @param string $expectedVariableName
     */
    public function testPrefixedIdentifiers($identifier, $expectedPrefix, $expectedVariableName): void
    {
        $v = new VariableIdentifier($identifier);

        $this::assertEquals($identifier, $v->getIdentifier());
        $this::assertTrue($v->hasPrefix());
        $this::assertFalse($v->hasSequenceNumber());
        $this::assertEquals($expectedPrefix, $v->getPrefix());
        $this::assertEquals($expectedVariableName, $v->getVariableName());
    }

    /**
     * @dataProvider sequencedIdentifiersProvider
     *
     * @param string $identifier
     * @param string $expectedPrefix
     * @param string $expectedSequence
     * @param string $expectedVariableName
     */
    public function testSequencedIdentifiers($identifier, $expectedPrefix, $expectedSequence, $expectedVariableName): void
    {
        $v = new VariableIdentifier($identifier);

        $this::assertEquals($identifier, $v->getIdentifier());
        $this::assertTrue($v->hasPrefix());
        $this::assertTrue($v->hasSequenceNumber());
        $this::assertEquals($expectedPrefix, $v->getPrefix());
        $this::assertEquals($expectedVariableName, $v->getVariableName());
        $this::assertEquals($expectedSequence, $v->getSequenceNumber());
    }

    public function testInvalidSequenceNumberOne(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The identifier 'Q01.bla.SCORE' is not a valid QTI Variable Name Identifier.");
        new VariableIdentifier('Q01.bla.SCORE');
    }

    public function testInvalidSequenceNumberTwo(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The identifier 'Q01.0.SCORE' is not a valid QTI Variable Name Identifier.");
        new VariableIdentifier('Q01.0.SCORE');
    }

    public function testInvalidVariableName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The identifier 'Q01.0. ' is not a valid QTI Variable Name Identifier.");
        new VariableIdentifier('Q01.0. ');
    }

    public function testInvalidPrefix(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The identifier ' .SCORE' is not a valid QTI Variable Name Identifier.");
        new VariableIdentifier(' .SCORE');
    }

    /**
     * @return array
     */
    public function invalidIdentifierProvider(): array
    {
        return [
            ['Q*01'],
            ['_Q01'],
            [''],
            [1337],
            ['Q01.A.SCORE'],
            ['Qxx.12.'],
            ['Q-2.'],
            ['934.9.SCORE'],
            ['Q01.1.SCORE.MAX'],
            ['Q 01'],
            ['Q01 . SCORE'],
            ['Q01._SCORE'],
            ['Q01.-1.SCORE'],
            ['Q01..SCORE'],
            ['Q01.'],
            ['.1.SCORE'],
            ['1.SCORE'],
        ];
    }

    /**
     * @return array
     */
    public function simpleIdentifiersProvider(): array
    {
        return [
            ['Q01'],
            ['Q_01'],
            ['MAXSCORE3'],
        ];
    }

    /**
     * @return array
     */
    public function prefixedIdentifiersProvider(): array
    {
        return [
            ['Q01.SCORE', 'Q01', 'SCORE'],
            ['Q_01.SCORE', 'Q_01', 'SCORE'],
            ['Question.MAX', 'Question', 'MAX'],
        ];
    }

    /**
     * @return array
     */
    public function sequencedIdentifiersProvider(): array
    {
        return [
            ['Q01.1.SCORE', 'Q01', 1, 'SCORE'],
            ['Q_01.245.MAX', 'Q_01', 245, 'MAX'],
        ];
    }
}
