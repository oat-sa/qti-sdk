<?php

namespace qtismtest\runtime\expressions\operators;

use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\data\QtiComponent;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\expressions\operators\AndProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\ExpressionProcessingException;

/**
 * Class AndProcessorTest
 */
class AndProcessorTest extends QtiSmTestCase
{
    public function testNotEnoughOperands(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $this->expectException(ExpressionProcessingException::class);
        $processor = new AndProcessor($expression, $operands);
        $result = $processor->process();
    }

    public function testWrongBaseType(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiPoint(1, 2)]);
        $processor = new AndProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongCardinalityOne(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new RecordContainer(['a' => new QtiString('string!')])]);
        $processor = new AndProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongCardinalityTwo(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new MultipleContainer(BaseType::FLOAT, [new QtiFloat(25.0)])]);
        $processor = new AndProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testNullOperands(): void
    {
        $expression = $this->createFakeExpression();

        // Even if the cardinality is wrong, the MultipleContainer object will be first considered
        // to be NULL because it is empty.
        $operands = new OperandsCollection([new MultipleContainer(BaseType::FLOAT)]);
        $processor = new AndProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertNull($result);

        // Two NULL values, 'null' && new RecordContainer().
        $operands = new OperandsCollection([new QtiBoolean(true), new QtiBoolean(false), new QtiBoolean(true), null, new RecordContainer()]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertNull($result);
    }

    public function testTrue(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiBoolean(true)]);
        $processor = new AndProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());

        $operands = new OperandsCollection([new QtiBoolean(true), new QtiBoolean(true), new QtiBoolean(true)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());
    }

    public function testFalse(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiBoolean(false)]);
        $processor = new AndProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertFalse($result->getValue());

        $operands = new OperandsCollection([new QtiBoolean(false), new QtiBoolean(true), new QtiBoolean(false)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertFalse($result->getValue());
    }

    /**
     * @return QtiComponent
     * @throws MarshallerNotFoundException
     */
    public function createFakeExpression(): QtiComponent
    {
        return $this->createComponentFromXml('
			<and>
				<baseValue baseType="boolean">false</baseValue>
			</and>
		');
    }
}
