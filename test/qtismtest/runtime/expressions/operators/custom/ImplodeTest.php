<?php

namespace qtismtest\runtime\expressions\operators\custom;

use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\data\QtiComponent;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\expressions\ExpressionProcessingException;
use qtism\runtime\expressions\operators\custom\Implode;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtism\runtime\expressions\operators\OperatorProcessingException;
use qtismtest\QtiSmTestCase;

/**
 * Class ImplodeProcessorTest
 */
class ImplodeProcessorTest extends QtiSmTestCase
{
    public function testNotEnoughOperandsOne()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $this->expectException(ExpressionProcessingException::class);
        $this->expectExceptionMessage("The 'qtism.runtime.expressions.operators.custom.Implode' custom operator takes 2 sub-expressions as parameters, 0 given.");
        $this->expectExceptionCode(OperatorProcessingException::NOT_ENOUGH_OPERANDS);
        $processor = new Implode($expression, $operands);
        $result = $processor->process();
    }

    public function testNotEnoughOperandsTwo()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiString('Hello-World!')]);
        $this->expectException(ExpressionProcessingException::class);
        $this->expectExceptionMessage("The 'qtism.runtime.expressions.operators.custom.Implode' custom operator takes 2 sub-expressions as parameters, 1 given.");
        $this->expectExceptionCode(OperatorProcessingException::NOT_ENOUGH_OPERANDS);
        $processor = new Implode($expression, $operands);
        $result = $processor->process();
    }

    public function testWrongBaseType()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiInteger(2), new QtiPoint(0, 0)]);
        $processor = new Implode($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $this->expectExceptionMessage("The 'qtism.runtime.expressions.operators.custom.Implode' custom operator only accepts operands with a string baseType.");
        $result = $processor->process();
    }

    public function testWrongCardinalityOne()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new MultipleContainer(BaseType::STRING, [new QtiString('String!')]), new QtiString('Hello World!')]);
        $processor = new Implode($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $this->expectExceptionMessage("The 'qtism.runtime.expressions.operators.custom.Implode' custom operator only accepts a first operand with single cardinality.");
        $result = $processor->process();
    }

    public function testWrongCardinalityTwo()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiString('-'), new QtiString('Hello-World!')]);
        $processor = new Implode($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $this->expectExceptionMessage("The 'qtism.runtime.expressions.operators.custom.Implode' custom operator only accepts a second operand with multiple or ordered cardinality.");
        $result = $processor->process();
    }

    public function testNullOperands()
    {
        $expression = $this->createFakeExpression();

        $operands = new OperandsCollection([new QtiString(''), null]);
        $processor = new Implode($expression, $operands);
        $result = $processor->process();
        $this::assertSame(null, $result);
    }

    public function testImplodeOne()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiString('-'), new MultipleContainer(BaseType::STRING, [new QtiString('Hello'), new QtiString('World')])]);
        $processor = new Implode($expression, $operands);
        $result = $processor->process();

        $this::assertInstanceOf(QtiString::class, $result);
        $this::assertEquals('Hello-World', $result->getValue());
    }

    /**
     * @return QtiComponent
     * @throws MarshallerNotFoundException
     */
    public function createFakeExpression()
    {
        return $this->createComponentFromXml('
			<customOperator class="qtism.runtime.expressions.operators.custom.Implode">
		        <baseValue baseType="string">-</baseValue>
				<multiple>
		            <baseValue baseType="string">Hello</baseValue>
		            <baseValue baseType="string">World</baseValue>
		        </multiple>
			</customOperator>
		');
    }
}
