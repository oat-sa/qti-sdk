<?php

namespace qtismtest\runtime\expressions\operators;

use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\expressions\operators\ToleranceMode;
use qtism\data\QtiComponent;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\common\State;
use qtism\runtime\expressions\operators\EqualProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\ExpressionProcessingException;

/**
 * Class EqualProcessorTest
 */
class EqualProcessorTest extends QtiSmTestCase
{
    public function testExact()
    {
        $expression = $this->createFakeExpression(ToleranceMode::EXACT);
        $operands = new OperandsCollection([new QtiInteger(10), new QtiInteger(10)]);
        $processor = new EqualProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());

        $operands = new OperandsCollection([new QtiInteger(0), new QtiInteger(1)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertFalse($result->getValue());

        $operands = new OperandsCollection([new QtiInteger(10), new QtiFloat(10.0)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());

        $operands = new OperandsCollection([new QtiInteger(10), new QtiFloat(10.1)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertFalse($result->getValue());
    }

    public function testRelative()
    {
        // Only one tolerance attribute.
        $expression = $this->createFakeExpression(ToleranceMode::RELATIVE, [90]);
        $operands = new OperandsCollection([new QtiInteger(10), new QtiInteger(10)]);
        $processor = new EqualProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());

        // -- lowerBound = 1; upperBound = 19
        $operands = new OperandsCollection([new QtiInteger(10), new QtiInteger(19)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());

        $operands = new OperandsCollection([new QtiInteger(10), new QtiFloat(19.1)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertFalse($result->getValue());

        $operands = new OperandsCollection([new QtiInteger(10), new QtiInteger(20)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertFalse($result->getValue());

        $operands = new OperandsCollection([new QtiInteger(10), new QtiInteger(0)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertFalse($result->getValue());

        // -- do not include upper bound.
        $expression = $this->createFakeExpression(ToleranceMode::RELATIVE, [90], true, false);
        $processor->setExpression($expression);

        $operands = new OperandsCollection([new QtiInteger(10), new QtiInteger(1)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());

        $operands = new OperandsCollection([new QtiInteger(10), new QtiInteger(19)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertFalse($result->getValue());

        // do not include lower bound.
        $expression = $this->createFakeExpression(ToleranceMode::RELATIVE, [90], false, false);
        $processor->setExpression($expression);

        $operands = new OperandsCollection([new QtiFloat(10.0), new QtiFloat(0.9999)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertFalse($result->getValue());
    }

    public function testAbsolute()
    {
        $expression = $this->createFakeExpression(ToleranceMode::ABSOLUTE, [0.1, 0.2]);
        $operands = new OperandsCollection([new QtiInteger(10), new QtiFloat(9.9)]);
        $processor = new EqualProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());

        $operands = new OperandsCollection([new QtiInteger(10), new QtiFloat(10.2)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());

        $operands = new OperandsCollection([new QtiInteger(10), new QtiFloat(9.8)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertFalse($result->getValue());

        $operands = new OperandsCollection([new QtiInteger(10), new QtiFloat(10.3)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertFalse($result->getValue());
    }

    public function testWithVariableRef()
    {
        $expression = $this->createFakeExpression(ToleranceMode::ABSOLUTE, ['t0', 't1']);
        $operands = new OperandsCollection([new QtiInteger(10), new QtiFloat(9.9)]);
        $processor = new EqualProcessor($expression, $operands);

        $state = new State();
        $state->setVariable(new OutcomeVariable('t0', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(0.1)));
        $state->setVariable(new OutcomeVariable('t1', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(0.1)));
        $processor->setState($state);

        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());

        $operands = new OperandsCollection([new QtiInteger(10), new QtiFloat(9.8)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertFalse($result->getValue());

        // only one t
        $expression = $this->createFakeExpression(ToleranceMode::ABSOLUTE, ['t0']);
        $operands = new OperandsCollection([new QtiInteger(10), new QtiInteger(12)]);
        $processor = new EqualProcessor($expression, $operands);

        $state = new State();
        $state->setVariable(new OutcomeVariable('t0', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(2.0)));
        $processor->setState($state);

        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());

        $operands = new OperandsCollection([new QtiInteger(10), new QtiInteger(13)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertFalse($result->getValue());
    }

    public function testNull()
    {
        $expression = $this->createFakeExpression(ToleranceMode::ABSOLUTE, [0.1, 0.2]);
        $operands = new OperandsCollection([new QtiInteger(10), null]);
        $processor = new EqualProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertNull($result);
    }

    public function testNoVariableRef()
    {
        $expression = $this->createFakeExpression(ToleranceMode::ABSOLUTE, ['t0']);
        $operands = new OperandsCollection([new QtiInteger(10), new QtiFloat(9.9)]);
        $processor = new EqualProcessor($expression, $operands);

        $state = new State();
        $processor->setState($state);
        $this->expectException(ExpressionProcessingException::class);
        $processor->process();
    }

    public function testNoSecondVariableRef()
    {
        $expression = $this->createFakeExpression(ToleranceMode::ABSOLUTE, ['t0', 't1']);
        $operands = new OperandsCollection([new QtiInteger(10), new QtiFloat(9.9)]);
        $processor = new EqualProcessor($expression, $operands);

        $state = new State();
        $state->setVariable(new OutcomeVariable('t0', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(0.1)));
        $processor->setState($state);
        $result = $processor->process();
        $this::assertTrue($result->getValue());

        $operands = new OperandsCollection([new QtiInteger(10), new QtiFloat(9.8)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertFalse($result->getValue());
    }

    public function testWrongBaseType()
    {
        $expression = $this->createFakeExpression(ToleranceMode::ABSOLUTE, [0.1, 0.2]);
        $operands = new OperandsCollection([new QtiInteger(10), new QtiString('String!')]);
        $processor = new EqualProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongCardinality()
    {
        $expression = $this->createFakeExpression(ToleranceMode::ABSOLUTE, [0.1, 0.2]);
        $operands = new OperandsCollection([new RecordContainer(['A' => new QtiInteger(1)]), new QtiInteger(10)]);
        $processor = new EqualProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testNotEnoughOperands()
    {
        $expression = $this->createFakeExpression(ToleranceMode::ABSOLUTE, [0.1, 0.2]);
        $operands = new OperandsCollection([new QtiInteger(10)]);
        $this->expectException(ExpressionProcessingException::class);
        $processor = new EqualProcessor($expression, $operands);
    }

    public function testTooMuchOperands()
    {
        $expression = $this->createFakeExpression(ToleranceMode::ABSOLUTE, [0.1, 0.2]);
        $operands = new OperandsCollection([new QtiInteger(10), new QtiInteger(10), new QtiInteger(10)]);
        $this->expectException(ExpressionProcessingException::class);
        $processor = new EqualProcessor($expression, $operands);
    }

    /**
     * @param $toleranceMode
     * @param array $tolerance
     * @param bool $includeLowerBound
     * @param bool $includeUpperBound
     * @return QtiComponent
     * @throws MarshallerNotFoundException
     */
    public function createFakeExpression($toleranceMode, array $tolerance = [], $includeLowerBound = true, $includeUpperBound = true)
    {
        $tm = ($toleranceMode != ToleranceMode::EXACT) ? ('tolerance="' . implode(' ', $tolerance) . '"') : '';
        $toleranceMode = ToleranceMode::getNameByConstant($toleranceMode);
        $iL = ($includeLowerBound === true) ? 'true' : 'false';
        $iU = ($includeUpperBound === true) ? 'true' : 'false';

        $str = '
			<equal toleranceMode="' . $toleranceMode . '" ' . $tm . ' includeLowerBound="' . $iL . '" includeUpperBound="' . $iU . '">
				<baseValue baseType="integer">10</baseValue>
				<baseValue baseType="integer">10</baseValue>
			</equal>
		';

        return $this->createComponentFromXml($str);
    }
}
