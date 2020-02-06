<?php

namespace qtismtest\runtime\common;

use qtism\common\collections\Container;
use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiDatatype;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
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

    /**
     * @dataProvider isNullDataProvider
     *
     * @param QtiDatatype $value
     * @param boolean $expected
     */
    public function testIsNull(QtiDatatype $value = null, $expected)
    {
        $this->assertSame($expected, Utils::isNull($value));
    }

    /**
     * @dataProvider equalsProvider
     *
     * @param QtiDatatype $a
     * @param QtiDatatype $b
     * @param boolean $expected
     */
    public function testEquals(QtiDatatype $a = null, QtiDatatype $b = null, $expected)
    {
        $this->assertSame($expected, Utils::equals($a, $b));
    }

    /**
     * @dataProvider throwTypingErrorProvider
     */
    public function testThrowTypingError($value, $expectedMsg)
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            $expectedMsg
        );

        Utils::throwTypingError($value);
    }

    /**
     * @dataProvider floatArrayToIntegerProvider
     */
    public function testFloatArrayToInteger($floatArray, $integerArray)
    {
        $this->assertEquals($integerArray, Utils::floatArrayToInteger($floatArray));
    }

    /**
     * @dataProvider integerArrayToFloatProvider
     */
    public function testIntegerArrayToFloat($integerArray, $floatArray)
    {
        $this->assertEquals($floatArray, Utils::integerArrayToFloat($integerArray));
    }

    public function throwTypingErrorProvider()
    {
        return [
            [99.9, "A value is not compliant with the QTI runtime model datatypes: boolean, integer, float, double, string, Duration, Pair, DirectedPair, Point . 'double' given."],
            ['blah', "A value is not compliant with the QTI runtime model datatypes: boolean, integer, float, double, string, Duration, Pair, DirectedPair, Point . 'string' given."],
            [new stdClass(), "A value is not compliant with the QTI runtime model datatypes: boolean, integer, float, double, string, Duration, Pair, DirectedPair, Point . 'stdClass' given."],
        ];
    }

    public function floatArrayToIntegerProvider()
    {
        return [
            [[10.2, 0.0], [10, 0]],
        ];
    }

    public function integerArrayToFloatProvider()
    {
        return [
            [[10, 0], [10., 0.]],
        ];
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
        $returnValue[] = [new StdClass(), false];
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

    public function isNullDataProvider()
    {
        return [
            [new QtiBoolean(true), false],
            [new MultipleContainer(BaseType::INTEGER, [new QtiInteger(10), new QtiInteger(20)]), false],
            [new QtiString('G-string!'), false],
            [null, true],
            [new QtiString(''), true],
            [new MultipleContainer(BaseType::INTEGER), true],
            [new OrderedContainer(BaseType::INTEGER), true],
            [new RecordContainer(), true],
        ];
    }

    public function equalsProvider()
    {
        return [
            [new QtiBoolean(true), null, false],
            [null, null, true],
            [new MultipleContainer(BaseType::INTEGER, [new QtiInteger(10)]), new MultipleContainer(BaseType::INTEGER, [new QtiInteger(10)]), true],
            [new MultipleContainer(BaseType::INTEGER, [new QtiInteger(10)]), new MultipleContainer(BaseType::INTEGER, [new QtiInteger(100)]), false],
        ];
    }
}
