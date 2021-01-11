<?php

namespace qtismtest\runtime\expressions\operators;

use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPair;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\expressions\operators\RoundingMode;
use qtism\data\QtiComponent;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\common\State;
use qtism\runtime\expressions\operators\EqualRoundedProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\ExpressionProcessingException;

/**
 * Class EqualRoundedProcessorTest
 */
class EqualRoundedProcessorTest extends QtiSmTestCase
{
    public function testSignificantFigures()
    {
        $expression = $this->createFakeExpression(RoundingMode::SIGNIFICANT_FIGURES, 3);
        $operands = new OperandsCollection([new QtiFloat(3.175), new QtiFloat(3.183)]);
        $processor = new EqualRoundedProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertTrue($result->getValue());

        $operands = new OperandsCollection([new QtiFloat(3.175), new QtiFloat(3.1749)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertFalse($result->getValue());
    }

    public function testDecimalPlaces()
    {
        $expression = $this->createFakeExpression(RoundingMode::DECIMAL_PLACES, 2);
        $operands = new OperandsCollection([new QtiFloat(1.68572), new QtiFloat(1.69)]);
        $processor = new EqualRoundedProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertTrue($result->getValue());

        $operands = new OperandsCollection([new QtiFloat(1.68572), new QtiFloat(1.68432)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertFalse($result->getValue());
    }

    public function testNull()
    {
        $expression = $this->createFakeExpression(RoundingMode::DECIMAL_PLACES, 2);
        $operands = new OperandsCollection([new QtiFloat(1.68572), null]);
        $processor = new EqualRoundedProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertNull($result);
    }

    public function testVariableRef()
    {
        $expression = $this->createFakeExpression(RoundingMode::SIGNIFICANT_FIGURES, 'var1');
        $operands = new OperandsCollection([new QtiFloat(3.175), new QtiFloat(3.183)]);
        $processor = new EqualRoundedProcessor($expression, $operands);

        $state = new State();
        $state->setVariable(new OutcomeVariable('var1', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(3)));
        $processor->setState($state);

        $result = $processor->process();
        $this::assertTrue($result->getValue());
    }

    public function testUnknownVariableRef()
    {
        $expression = $this->createFakeExpression(RoundingMode::SIGNIFICANT_FIGURES, 'var1');
        $operands = new OperandsCollection([new QtiFloat(3.175), new QtiFloat(3.183)]);
        $processor = new EqualRoundedProcessor($expression, $operands);

        $state = new State();
        $state->setVariable(new OutcomeVariable('varX', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(3)));
        $processor->setState($state);

        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
        $this::assertTrue($result->getValue());
    }

    public function testWrongBaseType()
    {
        $expression = $this->createFakeExpression(RoundingMode::DECIMAL_PLACES, 2);
        $operands = new OperandsCollection([new QtiPair('A', 'B'), new QtiInteger(3)]);
        $processor = new EqualRoundedProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongCardinality()
    {
        $expression = $this->createFakeExpression(RoundingMode::DECIMAL_PLACES, 2);
        $operands = new OperandsCollection([new QtiInteger(10), new RecordContainer(['A' => new QtiInteger(1337)])]);
        $processor = new EqualRoundedProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testNotEnoughOperands()
    {
        $expression = $this->createFakeExpression(RoundingMode::DECIMAL_PLACES, 2);
        $operands = new OperandsCollection([new QtiInteger(10)]);
        $this->expectException(ExpressionProcessingException::class);
        $processor = new EqualRoundedProcessor($expression, $operands);
    }

    public function testTooMuchOperands()
    {
        $expression = $this->createFakeExpression(RoundingMode::DECIMAL_PLACES, 2);
        $operands = new OperandsCollection([new QtiInteger(10), new QtiInteger(10), new QtiInteger(10)]);
        $this->expectException(ExpressionProcessingException::class);
        $processor = new EqualRoundedProcessor($expression, $operands);
    }

    /**
     * @param $roundingMode
     * @param $figures
     * @return QtiComponent
     * @throws MarshallerNotFoundException
     */
    public function createFakeExpression($roundingMode, $figures)
    {
        $roundingMode = RoundingMode::getNameByConstant($roundingMode);

        return $this->createComponentFromXml('
			<equalRounded roundingMode="' . $roundingMode . '" figures="' . $figures . '">
				<baseValue baseType="float">102.155</baseValue>
				<baseValue baseType="float">1065.155</baseValue>
			</equalRounded>
		');
    }
}
