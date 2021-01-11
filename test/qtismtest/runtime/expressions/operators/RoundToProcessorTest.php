<?php

namespace qtismtest\runtime\expressions\operators;

use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\State;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtism\runtime\expressions\operators\RoundToProcessor;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\ExpressionProcessingException;

/**
 * Class RoundToProcessorTest
 */
class RoundToProcessorTest extends QtiSmTestCase
{
    public function testSignificantFigures()
    {
        $expr = $this->createComponentFromXml('
			<roundTo figures="3">
				<baseValue baseType="float">1239451</baseValue>
			</roundTo>
		');

        $operands = new OperandsCollection();
        $operands[] = new QtiInteger(1239451);
        $processor = new RoundToProcessor($expr, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(round(1240000), round($result->getValue()));

        $operands[0] = new QtiFloat(12.1257);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(round(12.1, 1), round($result->getValue(), 1));

        $operands[0] = new QtiFloat(0.0681);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(round(0.0681, 4), round($result->getValue(), 4));

        $operands[0] = new QtiInteger(5);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(5, $result->getValue());

        $operands[0] = new QtiInteger(0);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(0, $result->getValue());

        $operands[0] = new QtiFloat(-12.1257);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(round(-12.1, 1), round($result->getValue(), 1));
    }

    public function testFiguresFromRef()
    {
        $expr = $this->createComponentFromXml('
			<roundTo figures="nfigures">
				<baseValue baseType="float">1239451</baseValue>
			</roundTo>
		');

        $operands = new OperandsCollection();
        $operands[] = new QtiInteger(1239451);
        $processor = new RoundToProcessor($expr, $operands);
        $processor->setState(
            new State(
                [
                    new OutcomeVariable('nfigures', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(3)),
                ]
            )
        );
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(round(1240000), round($result->getValue()));
    }

    public function testFiguresFromRefNoRef()
    {
        $this->expectException(ExpressionProcessingException::class);

        $expr = $this->createComponentFromXml('
			<roundTo figures="nfigures">
				<baseValue baseType="float">1239451</baseValue>
			</roundTo>
		');

        $operands = new OperandsCollection();
        $operands[] = new QtiInteger(1239451);
        $processor = new RoundToProcessor($expr, $operands);
        $result = $processor->process();
    }

    public function testFiguresFromRefNonIntegerRef()
    {
        $this->expectException(ExpressionProcessingException::class);

        $expr = $this->createComponentFromXml('
			<roundTo figures="nfigures">
				<baseValue baseType="float">1239451</baseValue>
			</roundTo>
		');

        $operands = new OperandsCollection();
        $operands[] = new QtiInteger(1239451);
        $processor = new RoundToProcessor($expr, $operands);
        $processor->setState(
            new State(
                [
                    new OutcomeVariable('nfigures', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(3.333)),
                ]
            )
        );
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(round(1240000), round($result->getValue()));
    }

    public function testFiguresFromRefNegativeWhenSignificantFigureInUse()
    {
        $this->expectException(ExpressionProcessingException::class);

        $expr = $this->createComponentFromXml('
			<roundTo figures="nfigures" roundingMode="significantFigures">
				<baseValue baseType="float">1239451</baseValue>
			</roundTo>
		');

        $operands = new OperandsCollection();
        $operands[] = new QtiInteger(1239451);
        $processor = new RoundToProcessor($expr, $operands);
        $processor->setState(
            new State(
                [
                    new OutcomeVariable('nfigures', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(-3)),
                ]
            )
        );
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(round(1240000), round($result->getValue()));
    }

    public function testDecimalPlaces()
    {
        $expr = $this->createComponentFromXml('
			<roundTo figures="0" roundingMode="decimalPlaces">
				<baseValue baseType="float">3.4</baseValue>
			</roundTo>
		');

        $operands = new OperandsCollection();
        $operands[] = new QtiFloat(3.4);
        $processor = new RoundToProcessor($expr, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(3, $result->getValue());

        $operands[0] = new QtiFloat(3.5);
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(4, $result->getValue());

        $operands[0] = new QtiFloat(3.6);
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(4, $result->getValue());

        $operands[0] = new QtiFloat(4.0);
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(4, $result->getValue());

        $expr->setFigures(2); // We now go for 2 figures...
        $operands[0] = new QtiFloat(1.95583);
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(1.96, $result->getValue());

        $operands[0] = new QtiFloat(5.045);
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(5.05, $result->getValue());

        $expr->setFigures(2);
        $operands[0] = new QtiFloat(5.055);
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(5.06, $result->getValue());
    }

    public function testNoOperands()
    {
        $this->expectException(ExpressionProcessingException::class);

        $expr = $this->createComponentFromXml('
			<roundTo figures="0" roundingMode="decimalPlaces">
				<baseValue baseType="float">3.4</baseValue>
			</roundTo>
		');
        $operands = new OperandsCollection();
        $processor = new RoundToProcessor($expr, $operands);
        $result = $processor->process();
    }

    public function testOperandsContainNull()
    {
        $expr = $this->createComponentFromXml('
			<roundTo figures="0" roundingMode="decimalPlaces">
				<baseValue baseType="float">3.4</baseValue>
			</roundTo>
		');
        $operands = new OperandsCollection([null]);
        $processor = new RoundToProcessor($expr, $operands);
        $result = $processor->process();

        $this::assertNull($result);
    }

    public function testTooMuchOperands()
    {
        $this->expectException(ExpressionProcessingException::class);

        $expr = $this->createComponentFromXml('
			<roundTo figures="0" roundingMode="decimalPlaces">
				<baseValue baseType="float">3.4</baseValue>
			</roundTo>
		');
        $operands = new OperandsCollection([new QtiInteger(4), new QtiInteger(4)]);
        $processor = new RoundToProcessor($expr, $operands);
        $result = $processor->process();
    }

    public function testWrongBaseType()
    {
        $this->expectException(ExpressionProcessingException::class);

        $expr = $this->createComponentFromXml('
			<roundTo figures="0" roundingMode="decimalPlaces">
				<baseValue baseType="float">3.4</baseValue>
			</roundTo>
		');
        $operands = new OperandsCollection([new QtiBoolean(true)]);
        $processor = new RoundToProcessor($expr, $operands);
        $result = $processor->process();
    }

    public function testWrongCardinality()
    {
        $this->expectException(ExpressionProcessingException::class);

        $expr = $this->createComponentFromXml('
			<roundTo figures="0" roundingMode="decimalPlaces">
				<baseValue baseType="float">3.4</baseValue>
			</roundTo>
		');
        $operands = new OperandsCollection([new MultipleContainer(BaseType::INTEGER, [new QtiInteger(20), new QtiInteger(30), new QtiInteger(40)])]);
        $processor = new RoundToProcessor($expr, $operands);
        $result = $processor->process();
    }

    public function testWrongFiguresOne()
    {
        $this->expectException(ExpressionProcessingException::class);

        $expr = $this->createComponentFromXml('
			<roundTo figures="0" roundingMode="significantFigures">
				<baseValue baseType="float">3.4</baseValue>
			</roundTo>
		');

        $operands = new OperandsCollection([new QtiFloat(3.4)]);
        $processor = new RoundToProcessor($expr, $operands);
        $result = $processor->process();
    }

    public function testWrongFiguresTwo()
    {
        $this->expectException(ExpressionProcessingException::class);

        $expr = $this->createComponentFromXml('
			<roundTo figures="-1" roundingMode="decimalPlaces">
				<baseValue baseType="float">3.4</baseValue>
			</roundTo>
		');
        $operands = new OperandsCollection([new QtiFloat(3.4)]);
        $processor = new RoundToProcessor($expr, $operands);
        $result = $processor->process();
    }

    public function testNan()
    {
        $expr = $this->createComponentFromXml('
			<roundTo figures="0" roundingMode="decimalPlaces">
				<baseValue baseType="float">3.4</baseValue>
			</roundTo>
		');
        $operands = new OperandsCollection([new QtiFloat(NAN)]);
        $processor = new RoundToProcessor($expr, $operands);
        $result = $processor->process();
        $this::assertNull($result);
    }

    public function testInfinity()
    {
        $expr = $this->createComponentFromXml('
			<roundTo figures="0" roundingMode="decimalPlaces">
				<baseValue baseType="float">3.4</baseValue>
			</roundTo>
		');
        $operands = new OperandsCollection([new QtiFloat(INF)]);
        $processor = new RoundToProcessor($expr, $operands);
        $result = $processor->process();
        $this::assertTrue(is_infinite($result->getValue()));
        $this::assertSame(INF, $result->getValue());

        $processor->setOperands(new OperandsCollection([new QtiFloat(-INF)]));
        $result = $processor->process();
        $this::assertTrue(is_infinite($result->getValue()));
        $this::assertSame(-INF, $result->getValue());
    }
}
