<?php

namespace qtismtest\runtime\expressions\operators;

use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\expressions\operators\AndProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\ExpressionProcessingException;

class AndProcessorTest extends QtiSmTestCase
{
    public function testNotEnoughOperands()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $this->expectException(ExpressionProcessingException::class);
        $processor = new AndProcessor($expression, $operands);
        $result = $processor->process();
    }

    public function testWrongBaseType()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiPoint(1, 2)]);
        $processor = new AndProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongCardinalityOne()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new RecordContainer(['a' => new QtiString('string!')])]);
        $processor = new AndProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongCardinalityTwo()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new MultipleContainer(BaseType::FLOAT, [new QtiFloat(25.0)])]);
        $processor = new AndProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testNullOperands()
    {
        $expression = $this->createFakeExpression();

        // Even if the cardinality is wrong, the MultipleContainer object will be first considered
        // to be NULL because it is empty.
        $operands = new OperandsCollection([new MultipleContainer(BaseType::FLOAT)]);
        $processor = new AndProcessor($expression, $operands);
        $result = $processor->process();
        $this->assertSame(null, $result);

        // Two NULL values, 'null' && new RecordContainer().
        $operands = new OperandsCollection([new QtiBoolean(true), new QtiBoolean(false), new QtiBoolean(true), null, new RecordContainer()]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this->assertSame(null, $result);
    }

    public function testTrue()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiBoolean(true)]);
        $processor = new AndProcessor($expression, $operands);
        $result = $processor->process();
        $this->assertInstanceOf(QtiBoolean::class, $result);
        $this->assertSame(true, $result->getValue());

        $operands = new OperandsCollection([new QtiBoolean(true), new QtiBoolean(true), new QtiBoolean(true)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this->assertInstanceOf(QtiBoolean::class, $result);
        $this->assertSame(true, $result->getValue());
    }

    public function testFalse()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiBoolean(false)]);
        $processor = new AndProcessor($expression, $operands);
        $result = $processor->process();
        $this->assertInstanceOf(QtiBoolean::class, $result);
        $this->assertSame(false, $result->getValue());

        $operands = new OperandsCollection([new QtiBoolean(false), new QtiBoolean(true), new QtiBoolean(false)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this->assertInstanceOf(QtiBoolean::class, $result);
        $this->assertSame(false, $result->getValue());
    }

    public function createFakeExpression()
    {
        return $this->createComponentFromXml('
			<and>
				<baseValue baseType="boolean">false</baseValue>
			</and>
		');
    }
}
