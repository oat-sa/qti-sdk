<?php

namespace qtismtest\runtime\common;

use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\Container;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\common\Utils;
use qtismtest\QtiSmTestCase;
use stdClass;

class RuntimeUtilsTest extends QtiSmTestCase
{
    /**
     * @dataProvider inferBaseTypeProvider
     */
    public function testInferBaseType($value, $expectedBaseType)
    {
        $this->assertTrue(Utils::inferBaseType($value) === $expectedBaseType);
    }

    /**
     * @dataProvider inferCardinalityProvider
     */
    public function testInferCardinality($value, $expectedCardinality)
    {
        $this->assertTrue(Utils::inferCardinality($value) === $expectedCardinality);
    }

    /**
     * @dataProvider isValidVariableIdentifierProvider
     *
     * @param string $string
     * @param boolean $expected
     */
    public function testIsValidVariableIdentifier($string, $expected)
    {
        $this->assertSame($expected, Utils::isValidVariableIdentifier($string));
    }

    public function inferBaseTypeProvider()
    {
        $returnValue = [];

        $returnValue[] = [new RecordContainer(), false];
        $returnValue[] = [new RecordContainer(['a' => new QtiInteger(1), 'b' => new QtiInteger(2)]), false];
        $returnValue[] = [null, false];
        $returnValue[] = [new QtiString(''), BaseType::STRING];
        $returnValue[] = [new QtiString('String!'), BaseType::STRING];
        $returnValue[] = [new QtiBoolean(false), BaseType::BOOLEAN];
        $returnValue[] = [new QtiInteger(0), BaseType::INTEGER];
        $returnValue[] = [new QtiFloat(0.0), BaseType::FLOAT];
        $returnValue[] = [new MultipleContainer(BaseType::DURATION), BaseType::DURATION];
        $returnValue[] = [new OrderedContainer(BaseType::BOOLEAN), BaseType::BOOLEAN];
        $returnValue[] = [new QtiDuration('P1D'), BaseType::DURATION];
        $returnValue[] = [new QtiPoint(1, 1), BaseType::POINT];
        $returnValue[] = [new QtiPair('A', 'B'), BaseType::PAIR];
        $returnValue[] = [new QtiDirectedPair('A', 'B'), BaseType::DIRECTED_PAIR];
        $returnValue[] = [new stdClass(), false];
        $returnValue[] = [new Container(), false];

        return $returnValue;
    }

    public function inferCardinalityProvider()
    {
        $returnValue = [];

        $returnValue[] = [new RecordContainer(), Cardinality::RECORD];
        $returnValue[] = [new MultipleContainer(BaseType::INTEGER), Cardinality::MULTIPLE];
        $returnValue[] = [new OrderedContainer(BaseType::DURATION), Cardinality::ORDERED];
        $returnValue[] = [new stdClass(), false];
        $returnValue[] = [null, false];
        $returnValue[] = [new QtiString(''), Cardinality::SINGLE];
        $returnValue[] = [new QtiString('String!'), Cardinality::SINGLE];
        $returnValue[] = [new QtiInteger(0), Cardinality::SINGLE];
        $returnValue[] = [new QtiFloat(0.0), Cardinality::SINGLE];
        $returnValue[] = [new QtiBoolean(false), Cardinality::SINGLE];
        $returnValue[] = [new QtiPoint(1, 1), Cardinality::SINGLE];
        $returnValue[] = [new QtiPair('A', 'B'), Cardinality::SINGLE];
        $returnValue[] = [new QtiDirectedPair('A', 'B'), Cardinality::SINGLE];
        $returnValue[] = [new QtiDuration('P1D'), Cardinality::SINGLE];

        return $returnValue;
    }

    public function isValidVariableIdentifierProvider()
    {
        return [
            ['Q01', true],
            ['Q_01', true],
            ['Q-01', true],
            ['Q*01', false],
            ['q01', true],
            ['_Q01', false],
            ['', false],
            [1337, false],
            ['Q01.1', true],
            ['Q01.1.SCORE', true],
            ['Q01.999.SCORE', true],
            ['Q01.A.SCORE', false],
            ['Qxx.12.', false],
            ['Q-2.', false],
            ['934.9.SCORE', false],
            ['A34.10.S-C-O', true],
            ['999', false],
            ['Q01.1.SCORE.MAX', false],
            ['Q 01', false],
            ['Q01 . SCORE', false],
            ['Q_01.SCORE', true],
            ['Q01.0.SCORE', false], // non positive sequence number -> false
            ['Q01.09.SCORE', false] // prefixing sequence by zero not allowed.
        ];
    }
}
