<?php

namespace qtismtest\runtime\expressions\operators\custom;

use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\data\QtiComponent;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\expressions\ExpressionProcessingException;
use qtism\runtime\expressions\operators\custom\Explode;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtism\runtime\expressions\operators\OperatorProcessingException;
use qtismtest\QtiSmTestCase;

/**
 * Class ExplodeProcessorTest
 */
class ExplodeProcessorTest extends QtiSmTestCase
{
    public function testNotEnoughOperandsOne()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $this->expectException(ExpressionProcessingException::class);
        $this->expectExceptionMessage("The 'qtism.runtime.expressions.operators.custom.Explode' custom operator takes 2 sub-expressions as parameters, 0 given.");
        $this->expectExceptionCode(OperatorProcessingException::NOT_ENOUGH_OPERANDS);
        $processor = new Explode($expression, $operands);
        $result = $processor->process();
    }

    public function testNotEnoughOperandsTwo()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiString('Hello-World!')]);
        $this->expectException(ExpressionProcessingException::class);
        $this->expectExceptionMessage("The 'qtism.runtime.expressions.operators.custom.Explode' custom operator takes 2 sub-expressions as parameters, 1 given.");
        $this->expectExceptionCode(OperatorProcessingException::NOT_ENOUGH_OPERANDS);
        $processor = new Explode($expression, $operands);
        $result = $processor->process();
    }

    public function testWrongBaseType()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiInteger(2), new QtiPoint(0, 0)]);
        $processor = new Explode($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $this->expectExceptionMessage("The 'qtism.runtime.expressions.operators.custom.Explode' custom operator only accepts operands with a string baseType.");
        $result = $processor->process();
    }

    public function testWrongCardinality()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new RecordContainer(['a' => new QtiString('String!')]), new QtiString('Hey!')]);
        $processor = new Explode($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $this->expectExceptionMessage("The 'qtism.runtime.expressions.operators.custom.Explode' custom operator only accepts operands with single cardinality.");
        $result = $processor->process();
    }

    public function testNullOperands()
    {
        $expression = $this->createFakeExpression();

        // Edge case, empty multiple container, considered as null.
        $operands = new OperandsCollection([new MultipleContainer(BaseType::FLOAT), new MultipleContainer(BaseType::FLOAT)]);
        $processor = new Explode($expression, $operands);
        $result = $processor->process();
        $this::assertNull($result);
    }

    public function testExplodeOne()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiString('-'), new QtiString('Hello-World-This-Is-Me')]);
        $processor = new Explode($expression, $operands);
        $result = $processor->process();

        $this::assertInstanceOf(OrderedContainer::class, $result);
        $this::assertCount(5, $result);
        $this::assertEquals(['Hello', 'World', 'This', 'Is', 'Me'], $result->getArrayCopy());
    }

    public function testExplodeTwo()
    {
        // Specific case, the delimiter is not found in the original string.
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiString('-'), new QtiString('Hello World!')]);
        $processor = new Explode($expression, $operands);
        $result = $processor->process();

        $this::assertInstanceOf(OrderedContainer::class, $result);
        $this::assertCount(1, $result);
        $this::assertEquals(['Hello World!'], $result->getArrayCopy());
    }

    public function testExplodeThree()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiString(' '), new QtiString('Hello World!')]);
        $processor = new Explode($expression, $operands);
        $result = $processor->process();

        $this::assertInstanceOf(OrderedContainer::class, $result);
        $this::assertCount(2, $result);
        $this::assertEquals(['Hello', 'World!'], $result->getArrayCopy());
    }

    /**
     * @return QtiComponent
     * @throws MarshallerNotFoundException
     */
    public function createFakeExpression()
    {
        return $this->createComponentFromXml('
			<customOperator class="qtism.runtime.expressions.operators.custom.Explode">
		        <baseValue baseType="string"> </baseValue>
				<baseValue baseType="string">Hello World!</baseValue>
			</customOperator>
		');
    }
}
