<?php

namespace qtismtest\runtime\expressions\operators;

use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\expressions\operators\IntegerDivideProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtismtest\QtiSmTestCase;

class IntegerDivideProcessorTest extends QtiSmTestCase
{
    public function testIntegerDivide()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiInteger(10), new QtiInteger(5)]);
        $processor = new IntegerDivideProcessor($expression, $operands);
        $result = $processor->process();
        $this->assertInstanceOf('qtism\\common\\datatypes\\QtiInteger', $result);
        $this->assertEquals(2, $result->getValue());

        $operands = new OperandsCollection([new QtiInteger(49), new QtiInteger(-5)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this->assertInstanceOf('qtism\\common\\datatypes\\QtiInteger', $result);
        $this->assertEquals(-10, $result->getValue());
    }

    public function testNull()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([null, new QtiInteger(5)]);
        $processor = new IntegerDivideProcessor($expression, $operands);
        $result = $processor->process();
        $this->assertSame(null, $result);
    }

    public function testDivisionByZero()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiInteger(50), new QtiInteger(0)]);
        $processor = new IntegerDivideProcessor($expression, $operands);
        $result = $processor->process();
        $this->assertSame(null, $result);
    }

    public function testWrongCardinality()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new MultipleContainer(BaseType::INTEGER, [new QtiInteger(10)]), new QtiInteger(5)]);
        $processor = new IntegerDivideProcessor($expression, $operands);
        $this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
        $result = $processor->process();
    }

    public function testWrongBaseTypeOne()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiString('ping!'), new QtiInteger(5)]);
        $processor = new IntegerDivideProcessor($expression, $operands);
        $this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
        $result = $processor->process();
    }

    public function testWrongBaseTypeTwo()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiInteger(5), new QtiDuration('P1D')]);
        $processor = new IntegerDivideProcessor($expression, $operands);
        $this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
        $result = $processor->process();
    }

    public function testNotEnoughOperands()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiInteger(5)]);
        $this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
        $processor = new IntegerDivideProcessor($expression, $operands);
    }

    public function testTooMuchOperands()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiInteger(5), new QtiInteger(5), new QtiInteger(5)]);
        $this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
        $processor = new IntegerDivideProcessor($expression, $operands);
    }

    public function createFakeExpression()
    {
        return $this->createComponentFromXml('
			<integerDivide>
				<baseValue baseType="integer">10</baseValue>
				<baseValue baseType="integer">5</baseValue>
			</integerDivide>
		');
    }
}
