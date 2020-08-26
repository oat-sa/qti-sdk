<?php

namespace qtismtest\runtime\expressions\operators;

use qtism\common\collections\Container;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\data\expressions\operators\Statistics;
use qtism\data\QtiComponent;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtism\runtime\expressions\operators\OperatorProcessingException;
use qtism\runtime\expressions\operators\StatsOperatorProcessor;
use qtismtest\QtiSmTestCase;

/**
 * Class StatsOperatorProcessorTest
 *
 * @package qtismtest\runtime\expressions\operators
 */
class StatsOperatorProcessorTest extends QtiSmTestCase
{
    /**
     * @dataProvider meanProvider
     *
     * @param Container $container
     * @param float|null $expected
     * @throws MarshallerNotFoundException
     */
    public function testMean(Container $container = null, $expected)
    {
        $expression = $this->createFakeExpression(Statistics::MEAN);
        $operands = new OperandsCollection([$container]);
        $processor = new StatsOperatorProcessor($expression, $operands);
        $this->check($expected, $processor->process());
    }

    /**
     * @dataProvider sampleVarianceProvider
     *
     * @param Container $container
     * @param float|null $expected
     * @throws MarshallerNotFoundException
     */
    public function testSampleVariance(Container $container = null, $expected)
    {
        $expression = $this->createFakeExpression(Statistics::SAMPLE_VARIANCE);
        $operands = new OperandsCollection([$container]);
        $processor = new StatsOperatorProcessor($expression, $operands);
        $this->check($expected, $processor->process());
    }

    /**
     * @dataProvider sampleSDProvider
     *
     * @param Container $container
     * @param float|null $expected
     * @throws MarshallerNotFoundException
     */
    public function testSampleSD(Container $container = null, $expected)
    {
        $expression = $this->createFakeExpression(Statistics::SAMPLE_SD);
        $operands = new OperandsCollection([$container]);
        $processor = new StatsOperatorProcessor($expression, $operands);
        $this->check($expected, $processor->process());
    }

    /**
     * @dataProvider popVarianceProvider
     *
     * @param Container $container
     * @param float|null $expected
     * @throws MarshallerNotFoundException
     */
    public function testPopVariance(Container $container = null, $expected)
    {
        $expression = $this->createFakeExpression(Statistics::POP_VARIANCE);
        $operands = new OperandsCollection([$container]);
        $processor = new StatsOperatorProcessor($expression, $operands);
        $this->check($expected, $processor->process());
    }

    /**
     * @dataProvider popSDProvider
     *
     * @param Container $container
     * @param float|null $expected
     * @throws MarshallerNotFoundException
     */
    public function testPopSD(Container $container = null, $expected)
    {
        $expression = $this->createFakeExpression(Statistics::POP_SD);
        $operands = new OperandsCollection([$container]);
        $processor = new StatsOperatorProcessor($expression, $operands);
        $this->check($expected, $processor->process());
    }

    /**
     * @dataProvider wrongCardinalityProvider
     * @param array $operands
     * @throws MarshallerNotFoundException
     */
    public function testWrongCardinality(array $operands)
    {
        $expression = $this->createFakeExpression(Statistics::MEAN);
        $operands = new OperandsCollection($operands);
        $processor = new StatsOperatorProcessor($expression, $operands);

        try {
            $result = $processor->process();
            $this->assertTrue(false); // cannot happen.
        } catch (OperatorProcessingException $e) {
            $this->assertTrue(true); // exception thrown, good!
            $this->assertEquals(OperatorProcessingException::WRONG_CARDINALITY, $e->getCode());
        }
    }

    /**
     * @dataProvider wrongBaseTypeProvider
     *
     * @param array $operands
     * @throws MarshallerNotFoundException
     */
    public function testWrongBaseType(array $operands)
    {
        $expression = $this->createFakeExpression(Statistics::MEAN);
        $operands = new OperandsCollection($operands);
        $processor = new StatsOperatorProcessor($expression, $operands);

        try {
            $result = $processor->process();
            $this->assertTrue(false); // cannot happen.
        } catch (OperatorProcessingException $e) {
            $this->assertTrue(true); // exception thrown, good!
            $this->assertEquals(OperatorProcessingException::WRONG_BASETYPE, $e->getCode());
        }
    }

    public function testNotEnoughOperands()
    {
        $expression = $this->createFakeExpression(Statistics::MEAN);
        $operands = new OperandsCollection();
        $this->expectException(OperatorProcessingException::class);
        $processor = new StatsOperatorProcessor($expression, $operands);
    }

    public function testTooMuchOperands()
    {
        $expression = $this->createFakeExpression(Statistics::MEAN);
        $operands = new OperandsCollection([new OrderedContainer(BaseType::INTEGER, [new QtiInteger(10)]), new MultipleContainer(BaseType::FLOAT, [new QtiFloat(10.0)])]);
        $this->expectException(OperatorProcessingException::class);
        $processor = new StatsOperatorProcessor($expression, $operands);
    }

    /**
     * @param $expected
     * @param $value
     */
    protected function check($expected, $value)
    {
        if (is_null($expected)) {
            $this->assertTrue($value === null);
        } else {
            $this->assertInstanceOf(QtiFloat::class, $value);
            $this->assertSame(round($expected, 3), round($value->getValue(), 3));
        }
    }

    /**
     * @return array
     */
    public function meanProvider()
    {
        return [
            [new OrderedContainer(BaseType::FLOAT, [new QtiFloat(10.0), new QtiFloat(20.0), new QtiFloat(30.0)]), 20.0],
            [new MultipleContainer(BaseType::INTEGER, [new QtiInteger(0)]), 0.0],
            [new MultipleContainer(BaseType::FLOAT, [new QtiFloat(10.0), null, new QtiFloat(23.3)]), null], // contains a null value
            [null, null],
        ];
    }

    /**
     * @return array
     */
    public function sampleVarianceProvider()
    {
        return [
            [new OrderedContainer(BaseType::FLOAT, [new QtiFloat(10.0)]), null], // fails because containerSize <= 1
            [new MultipleContainer(BaseType::INTEGER, [new QtiInteger(600), new QtiInteger(470), new QtiInteger(170), new QtiInteger(430), new QtiInteger(300)]), 27130],
            [new MultipleContainer(BaseType::FLOAT, [new QtiFloat(10.0), null, new QtiFloat(23.3)]), null], // contains a null value
            [null, null],
        ];
    }

    /**
     * @return array
     */
    public function sampleSDProvider()
    {
        return [
            [new OrderedContainer(BaseType::INTEGER, [new QtiInteger(10)]), null], // containerSize <= 1
            [new OrderedContainer(BaseType::INTEGER, [new QtiInteger(600), new QtiInteger(470), new QtiInteger(170), new QtiInteger(430), new QtiInteger(300)]), 164.712],
            [new MultipleContainer(BaseType::FLOAT, [new QtiFloat(10.0), null, new QtiFloat(23.3)]), null], // contains a null value
            [null, null],
        ];
    }

    /**
     * @return array
     */
    public function popVarianceProvider()
    {
        return [
            [new OrderedContainer(BaseType::INTEGER, [new QtiInteger(10)]), 0], // containerSize <= 1 but applied on a population -> OK.
            [new MultipleContainer(BaseType::INTEGER, [new QtiInteger(600), new QtiInteger(470), new QtiInteger(170), new QtiInteger(430), new QtiInteger(300)]), 21704],
            [new MultipleContainer(BaseType::FLOAT, [new QtiFloat(10.0), null, new QtiFloat(23.33333)]), null], // contains a null value
        ];
    }

    /**
     * @return array
     */
    public function popSDProvider()
    {
        return [
            [new OrderedContainer(BaseType::INTEGER, [new QtiInteger(10)]), 0], // containerSize <= 1 but applied on population
            [new OrderedContainer(BaseType::FLOAT, [new QtiFloat(600.0), new QtiFloat(470.0), new QtiFloat(170.0), new QtiFloat(430.0), new QtiFloat(300.0)]), 147.323],
            [new MultipleContainer(BaseType::FLOAT, [new QtiFloat(10.0), null, new QtiFloat(23.33333)]), null], // contains a null value
        ];
    }

    /**
     * @return array
     */
    public function wrongCardinalityProvider()
    {
        return [
            [[new QtiFloat(25.3)]],
            [[new QtiInteger(-10)]],
            [[new RecordContainer(['A' => new QtiInteger(1)])]],
        ];
    }

    /**
     * @return array
     */
    public function wrongBaseTypeProvider()
    {
        return [
            [[new MultipleContainer(BaseType::POINT, [new QtiPoint(1, 2)])]],
            [[new OrderedContainer(BaseType::STRING, [new QtiString('String!')])]],
        ];
    }

    /**
     * @param $name
     * @return QtiComponent
     * @throws MarshallerNotFoundException
     */
    public function createFakeExpression($name)
    {
        $name = Statistics::getNameByConstant($name);

        return $this->createComponentFromXml('
			<statsOperator name="' . $name . '">
				<multiple>
					<baseValue baseType="integer">10</baseValue>
					<baseValue baseType="integer">20</baseValue>
					<baseValue baseType="integer">30</baseValue>
				</multiple>
			</statsOperator>
		');
    }
}
