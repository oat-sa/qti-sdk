<?php

declare(strict_types=1);

namespace qtismtest\runtime\expressions\operators;

use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\data\QtiComponent;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\expressions\operators\GcdProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\operators\OperatorProcessingException;

/**
 * Class GcdProcessorTest
 */
class GcdProcessorTest extends QtiSmTestCase
{
    /**
     * @dataProvider gcdProvider
     *
     * @param array $operands
     * @param int $expected
     * @throws MarshallerNotFoundException
     */
    public function testGcd(array $operands, $expected): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection($operands);
        $processor = new GcdProcessor($expression, $operands);
        $this::assertSame($expected, $processor->process()->getValue());
    }

    public function testNotEnoughOperands(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $this->expectException(OperatorProcessingException::class);
        $processor = new GcdProcessor($expression, $operands);
    }

    public function testWrongBaseType(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new MultipleContainer(BaseType::STRING, [new QtiString('String!')]), new QtiInteger(10)]);
        $processor = new GcdProcessor($expression, $operands);
        $this->expectException(OperatorProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongCardinality(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiInteger(10), new QtiInteger(20), new RecordContainer(['A' => new QtiInteger(10)]), new QtiInteger(30)]);
        $processor = new GcdProcessor($expression, $operands);
        $this->expectException(OperatorProcessingException::class);
        $result = $processor->process();
    }

    /**
     * @dataProvider gcdWithNullValuesProvider
     * @param array $operands
     * @throws MarshallerNotFoundException
     */
    public function testGcdWithNullValues(array $operands): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection($operands);
        $processor = new GcdProcessor($expression, $operands);
        $this::assertNull($processor->process());
    }

    /**
     * @return array
     */
    public function gcdProvider(): array
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

    /**
     * @return array
     */
    public function gcdWithNullValuesProvider(): array
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

    /**
     * @return QtiComponent
     * @throws MarshallerNotFoundException
     */
    public function createFakeExpression(): QtiComponent
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
