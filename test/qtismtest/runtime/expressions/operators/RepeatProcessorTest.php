<?php

namespace qtismtest\runtime\expressions\operators;

use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiString;
use qtism\common\datatypes\QtiUri;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\QtiComponent;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\State;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtism\runtime\expressions\operators\RepeatProcessor;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\ExpressionProcessingException;
use qtism\runtime\expressions\operators\OperatorProcessingException;

/**
 * Class RepeatProcessorTest
 */
class RepeatProcessorTest extends QtiSmTestCase
{
    public function testRepeatScalarOnly()
    {
        $initialVal = [new QtiInteger(1), new QtiInteger(2), new QtiInteger(3)];
        $expression = $this->createFakeExpression(1);
        $operands = new OperandsCollection($initialVal);
        $processor = new RepeatProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertTrue($result->equals(new OrderedContainer(BaseType::INTEGER, $initialVal)));

        $expression = $this->createFakeExpression(2);
        $processor->setExpression($expression);
        $result = $processor->process();
        $this::assertTrue($result->equals(new OrderedContainer(BaseType::INTEGER, array_merge($initialVal, $initialVal))));
    }

    public function testRepeatVariableRef()
    {
        $initialVal = [new QtiInteger(1), new QtiInteger(2), new QtiInteger(3)];
        $expression = $this->createFakeExpression('repeat');
        $operands = new OperandsCollection($initialVal);
        $processor = new RepeatProcessor($expression, $operands);
        $processor->setState(new State([new OutcomeVariable('repeat', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(2))]));

        $result = $processor->process();
        $this::assertTrue($result->equals(new OrderedContainer(BaseType::INTEGER, array_merge($initialVal, $initialVal))));
    }

    public function testRepeatVariableRefNullRef()
    {
        $initialVal = [new QtiInteger(1), new QtiInteger(2), new QtiInteger(3)];
        $expression = $this->createFakeExpression('repeat');
        $operands = new OperandsCollection($initialVal);
        $processor = new RepeatProcessor($expression, $operands);

        $this->expectException(OperatorProcessingException::class);
        $this->expectExceptionMessage("The variable with name 'repeat' could not be resolved.");

        $processor->process();
    }

    public function testRepeatVariableRefNonIntegerRef()
    {
        $initialVal = [new QtiInteger(1), new QtiInteger(2), new QtiInteger(3)];
        $expression = $this->createFakeExpression('repeat');
        $operands = new OperandsCollection($initialVal);
        $processor = new RepeatProcessor($expression, $operands);
        $processor->setState(new State([new OutcomeVariable('repeat', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(2.))]));

        $this->expectException(OperatorProcessingException::class);
        $this->expectExceptionMessage("The variable with name 'repeat' is not an integer value.");

        $processor->process();
    }

    public function testOrderedOnly()
    {
        $expression = $this->createFakeExpression(2);
        $ordered1 = new OrderedContainer(BaseType::INTEGER, [new QtiInteger(1), new QtiInteger(2), new QtiInteger(3)]);
        $ordered2 = new OrderedContainer(BaseType::INTEGER, [new QtiInteger(4)]);
        $operands = new OperandsCollection([$ordered1, $ordered2]);
        $processor = new RepeatProcessor($expression, $operands);
        $result = $processor->process();

        $comparison = new OrderedContainer(BaseType::INTEGER, [new QtiInteger(1), new QtiInteger(2), new QtiInteger(3), new QtiInteger(4), new QtiInteger(1), new QtiInteger(2), new QtiInteger(3), new QtiInteger(4)]);
        $this::assertTrue($comparison->equals($result));
    }

    public function testMixed()
    {
        $expression = $this->createFakeExpression(2);
        $operands = new OperandsCollection();
        $operands[] = new QtiPoint(0, 0);
        $operands[] = new OrderedContainer(BaseType::POINT, [new QtiPoint(1, 2), new QtiPoint(2, 3), new QtiPoint(3, 4)]);
        $operands[] = new QtiPoint(10, 10);
        $operands[] = new OrderedContainer(BaseType::POINT, [new QtiPoint(4, 5)]);

        $processor = new RepeatProcessor($expression, $operands);
        $result = $processor->process();

        $comparison = new OrderedContainer(
            BaseType::POINT,
            [new QtiPoint(0, 0), new QtiPoint(1, 2), new QtiPoint(2, 3), new QtiPoint(3, 4), new QtiPoint(10, 10), new QtiPoint(4, 5), new QtiPoint(0, 0), new QtiPoint(1, 2), new QtiPoint(2, 3), new QtiPoint(3, 4), new QtiPoint(10, 10), new QtiPoint(4, 5)]
        );
        $this::assertTrue($comparison->equals($result));
    }

    public function testNull()
    {
        // If all sub-expressions are NULL, the result is NULL.
        $expression = $this->createFakeExpression(1);
        $operands = new OperandsCollection([null, new OrderedContainer(BaseType::INTEGER)]);
        $processor = new RepeatProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertSame(null, $result);

        // Any sub-expressions evaluating to NULL are ignored.
        $operands = new OperandsCollection([null, new QtiString('String1'), new OrderedContainer(BaseType::STRING, [new QtiString('String2'), null]), new QtiString('String3')]);
        $processor->setOperands($operands);
        $result = $processor->process();

        $comparison = new OrderedContainer(BaseType::STRING, [new QtiString('String1'), new QtiString('String2'), null, new QtiString('String3')]);
        $this::assertTrue($result->equals($comparison));
    }

    public function testWrongBaseTypeOne()
    {
        $expression = $this->createFakeExpression(1);
        $operands = new OperandsCollection();
        $operands[] = null;
        $operands[] = new OrderedContainer(BaseType::IDENTIFIER, [new QtiIdentifier('id1'), new QtiIdentifier('id2')]);
        $operands[] = new OrderedContainer(BaseType::URI, [new QtiUri('id3'), new QtiUri('id4')]);
        $operands[] = new QtiUri('http://www.taotesting.com');
        $operands[] = new OrderedContainer(BaseType::STRING);

        $processor = new RepeatProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongCardinality()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new MultipleContainer(BaseType::INTEGER, [new QtiInteger(10)])]);
        $processor = new RepeatProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongBaseTypeTwo()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new OrderedContainer(BaseType::INTEGER, [new QtiInteger(10)]), new QtiFloat(10.3)]);
        $processor = new RepeatProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testNotEnoughOperands()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $this->expectException(ExpressionProcessingException::class);
        $processor = new RepeatProcessor($expression, $operands);
    }

    /**
     * @param int $numberRepeats
     * @return QtiComponent
     * @throws MarshallerNotFoundException
     */
    public function createFakeExpression($numberRepeats = 1)
    {
        return $this->createComponentFromXml('
			<repeat numberRepeats="' . $numberRepeats . '">
				<baseValue baseType="integer">120</baseValue>
			</repeat>
		');
    }
}
