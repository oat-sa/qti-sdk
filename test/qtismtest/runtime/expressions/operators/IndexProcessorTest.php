<?php

namespace qtismtest\runtime\expressions\operators;

use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPoint;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\QtiComponent;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\State;
use qtism\runtime\expressions\operators\IndexProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\ExpressionProcessingException;

/**
 * Class IndexProcessorTest
 */
class IndexProcessorTest extends QtiSmTestCase
{
    public function testIndexNumeric(): void
    {
        // first trial at the trail of the collection.
        $expression = $this->createFakeExpression(1);
        $operands = new OperandsCollection();
        $operands[] = new OrderedContainer(BaseType::INTEGER, [new QtiInteger(1), new QtiInteger(2), new QtiInteger(3), new QtiInteger(4), new QtiInteger(5)]);
        $processor = new IndexProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiInteger::class, $result);
        $this::assertEquals(1, $result->getValue());

        // in the middle...
        $expression = $this->createFakeExpression(3);
        $processor->setExpression($expression);
        $result = $processor->process();
        $this::assertInstanceOf(QtiInteger::class, $result);
        $this::assertEquals(3, $result->getValue());

        // in the end...
        $expression = $this->createFakeExpression(5);
        $processor->setExpression($expression);
        $result = $processor->process();
        $this::assertInstanceOf(QtiInteger::class, $result);
        $this::assertEquals(5, $result->getValue());
    }

    public function testIndexVariableReference(): void
    {
        $expression = $this->createFakeExpression('variable1');
        $operands = new OperandsCollection();
        $operands[] = new OrderedContainer(BaseType::INTEGER, [new QtiInteger(1), new QtiInteger(2), new QtiInteger(3), new QtiInteger(4), new QtiInteger(5)]);
        $processor = new IndexProcessor($expression, $operands);

        $state = new State();
        $state->setVariable(new OutcomeVariable('variable1', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(3)));
        $processor->setState($state);

        $result = $processor->process();
        $this::assertInstanceOf(QtiInteger::class, $result);
        $this::assertEquals(3, $result->getValue());
    }

    public function testIndexVariableReferenceNotFound(): void
    {
        $expression = $this->createFakeExpression('variable1');
        $operands = new OperandsCollection();
        $operands[] = new OrderedContainer(BaseType::INTEGER, [new QtiInteger(1), new QtiInteger(2), new QtiInteger(3), new QtiInteger(4), new QtiInteger(5)]);
        $processor = new IndexProcessor($expression, $operands);

        $state = new State();
        $state->setVariable(new OutcomeVariable('variableXXX', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(3)));
        $processor->setState($state);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testVariableReferenceNotInteger(): void
    {
        $expression = $this->createFakeExpression('variable1');
        $operands = new OperandsCollection();
        $operands[] = new OrderedContainer(BaseType::INTEGER, [new QtiInteger(1), new QtiInteger(2), new QtiInteger(3), new QtiInteger(4), new QtiInteger(5)]);
        $processor = new IndexProcessor($expression, $operands);

        $state = new State();
        $state->setVariable(new OutcomeVariable('variable1', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(1, 2)));
        $processor->setState($state);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testOutOfRangeOne(): void
    {
        // 1. non-zero integer
        $expression = $this->createFakeExpression(-3);
        $operands = new OperandsCollection();
        $operands[] = new OrderedContainer(BaseType::INTEGER, [new QtiInteger(1), new QtiInteger(2), new QtiInteger(3), new QtiInteger(4), new QtiInteger(5)]);
        $processor = new IndexProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testOutOfRangeTwo(): void
    {
        // 2. out of range
        $expression = $this->createFakeExpression(1000);
        $operands = new OperandsCollection();
        $operands[] = new OrderedContainer(BaseType::INTEGER, [new QtiInteger(1), new QtiInteger(2), new QtiInteger(3), new QtiInteger(4), new QtiInteger(5)]);
        $processor = new IndexProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertNull($result);
    }

    public function testWrongCardinality(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(1), new QtiInteger(2), new QtiInteger(3), new QtiInteger(4), new QtiInteger(5)]);
        $processor = new IndexProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testNull(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new OrderedContainer(BaseType::FLOAT);
        $processor = new IndexProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertNull($result);
    }

    public function testNotEnoughOperands(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $this->expectException(ExpressionProcessingException::class);
        $processor = new IndexProcessor($expression, $operands);
    }

    public function testTooMuchOperands(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new OrderedContainer(BaseType::INTEGER, [new QtiInteger(1), new QtiInteger(2), new QtiInteger(3), new QtiInteger(4), new QtiInteger(5)]);
        $operands[] = new OrderedContainer(BaseType::INTEGER, [new QtiInteger(1), new QtiInteger(2), new QtiInteger(3), new QtiInteger(4), new QtiInteger(5)]);
        $this->expectException(ExpressionProcessingException::class);
        $processor = new IndexProcessor($expression, $operands);
    }

    /**
     * @param int $n
     * @return QtiComponent
     * @throws MarshallerNotFoundException
     */
    public function createFakeExpression($n = -1): QtiComponent
    {
        if ($n === -1) {
            $n = 3;
        }

        return $this->createComponentFromXml('
			<index n="' . $n . '">
				<ordered>
					<baseValue baseType="integer">1</baseValue>
					<baseValue baseType="integer">2</baseValue>
					<baseValue baseType="integer">3</baseValue>
					<baseValue baseType="integer">4</baseValue>
					<baseValue baseType="integer">5</baseValue>
				</ordered>
			</index>
		');
    }
}
