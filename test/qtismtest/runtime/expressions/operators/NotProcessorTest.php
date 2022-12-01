<?php

namespace qtismtest\runtime\expressions\operators;

use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPoint;
use qtism\common\enums\BaseType;
use qtism\data\QtiComponent;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\expressions\operators\NotProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\ExpressionProcessingException;

/**
 * Class NotProcessorTest
 */
class NotProcessorTest extends QtiSmTestCase
{
    public function testNotEnoughOperands(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $this->expectException(ExpressionProcessingException::class);
        $processor = new NotProcessor($expression, $operands);
    }

    public function testTooMuchOperands(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiBoolean(true), new QtiBoolean(false)]);
        $this->expectException(ExpressionProcessingException::class);
        $processor = new NotProcessor($expression, $operands);
    }

    public function testWrongCardinality(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new MultipleContainer(BaseType::POINT, [new QtiPoint(1, 2)])]);
        $processor = new NotProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongBaseType(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiInteger(25)]);
        $processor = new NotProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testNull(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([null]);
        $processor = new NotProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertNull($result);
    }

    public function testTrue(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiBoolean(false)]);
        $processor = new NotProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertTrue($result->getValue());
    }

    public function testFalse(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiBoolean(true)]);
        $processor = new NotProcessor($expression, $operands);
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
			<not>
				<baseValue baseType="boolean">false</baseValue>
			</not>
		');
    }
}
