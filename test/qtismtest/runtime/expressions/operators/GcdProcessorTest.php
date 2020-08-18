<?php

namespace qtismtest\runtime\expressions\operators;

use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\expressions\operators\GcdProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\operators\OperatorProcessingException;

class GcdProcessorTest extends QtiSmTestCase
{
    /**
     * @dataProvider gcdProvider
     *
     * @param array $operands
     * @param integer $expected
     */
    public function testGcd(array $operands, $expected)
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection($operands);
        $processor = new GcdProcessor($expression, $operands);
        $this->assertSame($expected, $processor->process()->getValue());
    }

    public function testNotEnoughOperands()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $this->setExpectedException(OperatorProcessingException::class);
        $processor = new GcdProcessor($expression, $operands);
    }

    public function testWrongBaseType()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new MultipleContainer(BaseType::STRING, [new QtiString('String!')]), new QtiInteger(10)]);
        $processor = new GcdProcessor($expression, $operands);
        $this->setExpectedException(OperatorProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongCardinality()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiInteger(10), new QtiInteger(20), new RecordContainer(['A' => new QtiInteger(10)]), new QtiInteger(30)]);
        $processor = new GcdProcessor($expression, $operands);
        $this->setExpectedException(OperatorProcessingException::class);
        $result = $processor->process();
    }

    /**
     * @dataProvider gcdWithNullValuesProvider
     *
     * @param array $operands
     */
    public function testGcdWithNullValues(array $operands)
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection($operands);
        $processor = new GcdProcessor($expression, $operands);
        $this->assertSame(null, $processor->process());
    }

    public function gcdProvider()
    {
        return [
            [[new QtiInteger(45), new QtiInteger(60), new QtiInteger(330)], 15],
            [[new QtiInteger(0), new QtiInteger(45), new QtiInteger(60), new QtiInteger(0), new QtiInteger(330), new QtiInteger(15), new QtiInteger(0)], 15], // gcd (0, 45, 60, 330, 15, 0)
            [[new QtiInteger(0)], 0],
            [[new QtiInteger(0), new QtiInteger(0), new QtiInteger(0)], 0],
            [[new MultipleContainer(BaseType::INTEGER, [new QtiInteger(45), new QtiInteger(60), new QtiInteger(330)])], 15], // gcd(45, 60, 330)
            [[new QtiInteger(0), new OrderedContainer(BaseType::INTEGER, [new QtiInteger(0)])], 0], // gcd(0, 0, 0)
            [[new MultipleContainer(BaseType::INTEGER, [new QtiInteger(45), new QtiInteger(60), new QtiInteger(0), new QtiInteger(330)])], 15], // gcd(45, 60, 0, 330)
            [[new MultipleContainer(BaseType::INTEGER, [new QtiInteger(45)]), new OrderedContainer(BaseType::INTEGER, [new QtiInteger(60)]), new MultipleContainer(BaseType::INTEGER, [new QtiInteger(330)])], 15],
            [[new QtiInteger(45)], 45],
            [[new QtiInteger(0), new QtiInteger(45)], 45],
            [[new QtiInteger(45), new QtiInteger(0)], 45],
            [[new QtiInteger(0), new QtiInteger(45), new QtiInteger(0)], 45],
        ];
    }

    public function gcdWithNullValuesProvider()
    {
        return [
            [[new QtiInteger(45), null, new QtiInteger(330)]],
            [[new QtiString(''), new QtiInteger(550), new QtiInteger(330)]],
            [[new QtiInteger(230), new OrderedContainer(BaseType::INTEGER), new QtiInteger(25), new QtiInteger(33)]],
            [[new OrderedContainer(BaseType::INTEGER, [null])]],
            [[new OrderedContainer(BaseType::INTEGER, [null, null, null])]],
            [[new OrderedContainer(BaseType::INTEGER, [new QtiInteger(25), new QtiInteger(30)]), new QtiInteger(200), new MultipleContainer(BaseType::INTEGER, [new QtiInteger(25), null, new QtiInteger(30)])]],
        ];
    }

    public function createFakeExpression()
    {
        return $this->createComponentFromXml('
			<gcd>
				<baseValue baseType="integer">40</baseValue>
				<baseValue baseType="integer">60</baseValue>
				<baseValue baseType="integer">330</baseValue>
			</gcd>
		');
    }
}
