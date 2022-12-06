<?php

namespace qtismtest\runtime\expressions\operators;

use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\data\QtiComponent;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\expressions\operators\LcmProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\operators\OperatorProcessingException;

/**
 * Class LcmProcessorTest
 */
class LcmProcessorTest extends QtiSmTestCase
{
    /**
     * @dataProvider lcmProvider
     * @param array $operands
     * @param int $expected
     * @throws MarshallerNotFoundException
     */
    public function testLcm(array $operands, $expected): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection($operands);
        $processor = new LcmProcessor($expression, $operands);
        $this::assertSame($expected, $processor->process()->getValue());
    }

    public function testNotEnoughOperands(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $this->expectException(OperatorProcessingException::class);
        $processor = new LcmProcessor($expression, $operands);
    }

    public function testWrongBaseType(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new MultipleContainer(BaseType::STRING, [new QtiString('String!')]), new QtiInteger(10)]);
        $processor = new LcmProcessor($expression, $operands);
        $this->expectException(OperatorProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongCardinality(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiInteger(10), new QtiInteger(20), new RecordContainer(['A' => new QtiInteger(10)]), new QtiInteger(30)]);
        $processor = new LcmProcessor($expression, $operands);
        $this->expectException(OperatorProcessingException::class);
        $result = $processor->process();
    }

    /**
     * @dataProvider lcmWithNullValuesProvider
     * @param array $operands
     * @throws MarshallerNotFoundException
     */
    public function testGcdWithNullValues(array $operands): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection($operands);
        $processor = new LcmProcessor($expression, $operands);
        $this::assertNull($processor->process());
    }

    /**
     * @return array
     */
    public function lcmProvider(): array
    {
        return [
            [[new QtiInteger(0)], 0],
            [[new QtiInteger(0), new MultipleContainer(BaseType::INTEGER, [new QtiInteger(0)])], 0],
            [[new QtiInteger(0), new QtiInteger(0)], 0],
            [[new QtiInteger(330), new QtiInteger(0)], 0],
            [[new QtiInteger(0), new QtiInteger(330)], 0],
            [[new QtiInteger(330), new QtiInteger(0), new QtiInteger(15)], 0],
            [[new QtiInteger(330), new QtiInteger(65), new QtiInteger(15)], 4290],
            [[new QtiInteger(-10), new QtiInteger(-5)], 10],
            [[new QtiInteger(330)], 330],
            [[new QtiInteger(330), new MultipleContainer(BaseType::INTEGER, [new QtiInteger(65)]), new QtiInteger(15)], 4290],
            [[new OrderedContainer(BaseType::INTEGER, [new QtiInteger(330)]), new MultipleContainer(BaseType::INTEGER, [new QtiInteger(65)]), new MultipleContainer(BaseType::INTEGER, [new QtiInteger(15)])], 4290],
            [[new OrderedContainer(BaseType::INTEGER, [new QtiInteger(330), new QtiInteger(65)]), new MultipleContainer(BaseType::INTEGER, [new QtiInteger(65)])], 4290],
        ];
    }

    /**
     * @return array
     */
    public function lcmWithNullValuesProvider(): array
    {
        return [
            [[null]],
            [[null, new QtiInteger(10)]],
            [[new QtiInteger(10), null]],
            [[new QtiInteger(10), null, new QtiInteger(10)]],
            [[new QtiInteger(10), new MultipleContainer(BaseType::INTEGER)]],
            [[new OrderedContainer(BaseType::INTEGER, [new QtiInteger(10), null])]],
        ];
    }

    /**
     * @return QtiComponent
     * @throws MarshallerNotFoundException
     */
    public function createFakeExpression(): QtiComponent
    {
        return $this->createComponentFromXml('
			<lcm>
				<baseValue baseType="integer">330</baseValue>
				<baseValue baseType="integer">65</baseValue>
				<baseValue baseType="integer">15</baseValue>
			</lcm>
		');
    }
}
