<?php

namespace qtismtest\runtime\expressions\operators;

use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\expressions\operators\ContainerSizeProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\ExpressionProcessingException;

class ContainerSizeProcessorTest extends QtiSmTestCase
{
    public function testNotEnoughOperands()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $this->setExpectedException(ExpressionProcessingException::class);
        $processor = new ContainerSizeProcessor($expression, $operands);
    }

    public function testTooMuchOperands()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(25)]);
        $operands[] = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(26)]);
        $this->setExpectedException(ExpressionProcessingException::class);
        $processor = new ContainerSizeProcessor($expression, $operands);
    }

    public function testNull()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([null]);
        $processor = new ContainerSizeProcessor($expression, $operands);
        $result = $processor->process();
        $this->assertInstanceOf(QtiInteger::class, $result);
        $this->assertSame(0, $result->getValue());

        $operands = new OperandsCollection([new MultipleContainer(BaseType::INTEGER)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this->assertInstanceOf(QtiInteger::class, $result);
        $this->assertSame(0, $result->getValue());
    }

    public function testWrongCardinalityOne()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiInteger(25)]);
        $processor = new ContainerSizeProcessor($expression, $operands);
        $this->setExpectedException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongCardinalityTwo()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new RecordContainer(['1' => new QtiFloat(1.0), '2' => new QtiInteger(2)])]);
        $processor = new ContainerSizeProcessor($expression, $operands);
        $this->setExpectedException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testSize()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new MultipleContainer(BaseType::STRING, [new QtiString('String!')]);
        $processor = new ContainerSizeProcessor($expression, $operands);
        $result = $processor->process();
        $this->assertEquals(1, $result->getValue());

        $operands->reset();
        $operands[] = new MultipleContainer(BaseType::POINT, [new QtiPoint(1, 2), new QtiPoint(2, 3), new QtiPoint(3, 4)]);
        $result = $processor->process();
        $this->assertInstanceOf(QtiInteger::class, $result);
        $this->assertEquals(3, $result->getValue());
    }

    public function createFakeExpression()
    {
        return $this->createComponentFromXml('
			<containerSize>
				<multiple>
					<baseValue baseType="integer">1</baseValue>
				<baseValue baseType="integer">2</baseValue>
				<baseValue baseType="integer">3</baseValue>
				</multiple>
			</containerSize>
		');
    }
}
