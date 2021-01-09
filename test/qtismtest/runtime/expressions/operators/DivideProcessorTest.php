<?php

namespace qtismtest\runtime\expressions\operators;

use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiString;
use qtism\data\QtiComponent;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\expressions\operators\DivideProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\ExpressionProcessingException;

/**
 * Class DivideProcessorTest
 */
class DivideProcessorTest extends QtiSmTestCase
{
    public function testDivide()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiInteger(1), new QtiInteger(1)]);
        $processor = new DivideProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(1, $result->getValue());

        $operands = new OperandsCollection([new QtiInteger(0), new QtiInteger(2)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(0, $result->getValue());

        $operands = new OperandsCollection([new QtiInteger(-30), new QtiInteger(5)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(-6, $result->getValue());

        $operands = new OperandsCollection([new QtiInteger(30), new QtiInteger(5)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(6, $result->getValue());

        $operands = new OperandsCollection([new QtiInteger(1), new QtiFloat(0.5)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(2, $result->getValue());
    }

    public function testDivisionByZero()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiInteger(1), new QtiInteger(0)]);
        $processor = new DivideProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertSame(null, $result);
    }

    public function testDivisionByInfinite()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiInteger(10), new QtiFloat(INF)]);
        $processor = new DivideProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(0, $result->getValue());

        $operands = new OperandsCollection([new QtiInteger(-1), new QtiFloat(INF)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(-0, $result->getValue());
    }

    public function testInfiniteDividedByInfinite()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiFloat(INF), new QtiFloat(INF)]);
        $processor = new DivideProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertSame(null, $result);
    }

    public function testWrongBaseTypeOne()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiString('string!'), new QtiBoolean(true)]);
        $processor = new DivideProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongBaseTypeTwo()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiPoint(1, 2), new QtiBoolean(true)]);
        $processor = new DivideProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongCardinality()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new RecordContainer(['A' => new QtiInteger(1)]), new QtiInteger(10)]);
        $processor = new DivideProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testNotEnoughOperands()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $this->expectException(ExpressionProcessingException::class);
        $processor = new DivideProcessor($expression, $operands);
    }

    public function testTooMuchOperands()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiInteger(10), new QtiInteger(11), new QtiInteger(12)]);
        $this->expectException(ExpressionProcessingException::class);
        $processor = new DivideProcessor($expression, $operands);
    }

    /**
     * @return QtiComponent
     */
    public function createFakeExpression()
    {
        return $this->createComponentFromXml('
			<divide>
				<baseValue baseType="integer">10</baseValue>
				<baseValue baseType="integer">2</baseValue>
			</divide>
		');
    }
}
