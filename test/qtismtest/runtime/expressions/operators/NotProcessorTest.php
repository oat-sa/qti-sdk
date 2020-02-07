<?php

require_once(dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPoint;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\expressions\operators\NotProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;

class NotProcessorTest extends QtiSmTestCase
{
    public function testNotEnoughOperands()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
        $processor = new NotProcessor($expression, $operands);
    }

    public function testTooMuchOperands()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiBoolean(true), new QtiBoolean(false)]);
        $this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
        $processor = new NotProcessor($expression, $operands);
    }

    public function testWrongCardinality()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new MultipleContainer(BaseType::POINT, [new QtiPoint(1, 2)])]);
        $processor = new NotProcessor($expression, $operands);
        $this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
        $result = $processor->process();
    }

    public function testWrongBaseType()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiInteger(25)]);
        $processor = new NotProcessor($expression, $operands);
        $this->setExpectedException('qtism\\runtime\\expressions\\ExpressionProcessingException');
        $result = $processor->process();
    }

    public function testNull()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([null]);
        $processor = new NotProcessor($expression, $operands);
        $result = $processor->process();
        $this->assertSame(null, $result);
    }

    public function testTrue()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiBoolean(false)]);
        $processor = new NotProcessor($expression, $operands);
        $result = $processor->process();
        $this->assertSame(true, $result->getValue());
    }

    public function testFalse()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiBoolean(true)]);
        $processor = new NotProcessor($expression, $operands);
        $result = $processor->process();
        $this->assertInstanceOf(QtiBoolean::class, $result);
        $this->assertSame(false, $result->getValue());
    }

    public function createFakeExpression()
    {
        return $this->createComponentFromXml('
			<not>
				<baseValue baseType="boolean">false</baseValue>
			</not>
		');
    }
}
