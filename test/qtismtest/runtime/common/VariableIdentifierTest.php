<?php

namespace qtismtest\runtime\common;

use qtism\runtime\common\VariableIdentifier;
use qtismtest\QtiSmTestCase;

class VariableIdentifierTest extends QtiSmTestCase
{
    /**
     * @dataProvider invalidIdentifierProvider
     *
     * @param string $identifier
     */
    public function testInvalidIdentifier($identifier)
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $v = new VariableIdentifier($identifier);
    }

    /**
     * @dataProvider simpleIdentifiersProvider
     *
     * @param string $identifier
     */
    public function testSimpleIdentifiers($identifier)
    {
        $v = new VariableIdentifier($identifier);

        $this->assertEquals($identifier, $v->getIdentifier());
        $this->assertEquals($identifier, $v->getVariableName());
        $this->assertFalse($v->hasPrefix());
        $this->assertFalse($v->hasSequenceNumber());
    }

    /**
     * @dataProvider prefixedIdentifiersProvider
     *
     * @param string $identifier
     * @param string $expectedPrefix
     * @param string $expectedVariableName
     */
    public function testPrefixedIdentifiers($identifier, $expectedPrefix, $expectedVariableName)
    {
        $v = new VariableIdentifier($identifier);

        $this->assertEquals($identifier, $v->getIdentifier());
        $this->assertTrue($v->hasPrefix());
        $this->assertFalse($v->hasSequenceNumber());
        $this->assertEquals($expectedPrefix, $v->getPrefix());
        $this->assertEquals($expectedVariableName, $v->getVariableName());
    }

    /**
     * @dataProvider sequencedIdentifiersProvider
     *
     * @param string $identifier
     * @param string $expectedPrefix
     * @param string $expectedSequence
     * @param string $expectedVariableName
     */
    public function testSequencedIdentifiers($identifier, $expectedPrefix, $expectedSequence, $expectedVariableName)
    {
        $v = new VariableIdentifier($identifier);

        $this->assertEquals($identifier, $v->getIdentifier());
        $this->assertTrue($v->hasPrefix());
        $this->assertTrue($v->hasSequenceNumber());
        $this->assertEquals($expectedPrefix, $v->getPrefix());
        $this->assertEquals($expectedVariableName, $v->getVariableName());
        $this->assertEquals($expectedSequence, $v->getSequenceNumber());
    }

    public function testInvalidSequenceNumberOne()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The identifier 'Q01.bla.SCORE' is not a valid QTI Variable Name Identifier."
        );
        new VariableIdentifier('Q01.bla.SCORE');
    }

    public function testInvalidSequenceNumberTwo()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The identifier 'Q01.0.SCORE' is not a valid QTI Variable Name Identifier."
        );
        new VariableIdentifier('Q01.0.SCORE');
    }

    public function testInvalidVariableName()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The identifier 'Q01.0. ' is not a valid QTI Variable Name Identifier."
        );
        new VariableIdentifier('Q01.0. ');
    }

    public function testInvalidPrefix()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            "The identifier ' .SCORE' is not a valid QTI Variable Name Identifier."
        );
        new VariableIdentifier(' .SCORE');
    }

    public function invalidIdentifierProvider()
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

    public function simpleIdentifiersProvider()
    {
        return [
            ['Q01'],
            ['Q_01'],
            ['MAXSCORE3'],
        ];
    }

    public function prefixedIdentifiersProvider()
    {
        return [
            ['Q01.SCORE', 'Q01', 'SCORE'],
            ['Q_01.SCORE', 'Q_01', 'SCORE'],
            ['Question.MAX', 'Question', 'MAX'],
        ];
    }

    public function sequencedIdentifiersProvider()
    {
        return [
            ['Q01.1.SCORE', 'Q01', 1, 'SCORE'],
            ['Q_01.245.MAX', 'Q_01', 245, 'MAX'],
        ];
    }
}
