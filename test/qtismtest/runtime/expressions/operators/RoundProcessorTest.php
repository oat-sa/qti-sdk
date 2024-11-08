<?php

namespace qtismtest\runtime\expressions\operators;

use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\common\enums\BaseType;
use qtism\data\QtiComponent;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtism\runtime\expressions\operators\RoundProcessor;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\ExpressionProcessingException;

/**
 * Class RoundProcessorTest
 */
class RoundProcessorTest extends QtiSmTestCase
{
    public function testRound(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiFloat(6.8);
        $processor = new RoundProcessor($expression, $operands);

        $result = $processor->process();
        $this::assertInstanceOf(QtiInteger::class, $result);
        $this::assertEquals(7, $result->getValue());

        $operands->reset();
        $operands[] = new QtiFloat(6.5);
        $result = $processor->process();
        $this::assertInstanceOf(QtiInteger::class, $result);
        $this::assertEquals(7, $result->getValue());

        $operands->reset();
        $operands[] = new QtiFloat(6.49);
        $result = $processor->process();
        $this::assertInstanceOf(QtiInteger::class, $result);
        $this::assertEquals(6, $result->getValue());

        $operands->reset();
        $operands[] = new QtiFloat(6.5);
        $result = $processor->process();
        $this::assertInstanceOf(QtiInteger::class, $result);
        $this::assertEquals(7, $result->getValue());

        $operands->reset();
        $operands[] = new QtiFloat(-6.5);
        $result = $processor->process();
        $this::assertInstanceOf(QtiInteger::class, $result);
        $this::assertEquals(-6, $result->getValue());

        $operands->reset();
        $operands[] = new QtiFloat(-6.51);
        $result = $processor->process();
        $this::assertInstanceOf(QtiInteger::class, $result);
        $this::assertEquals(-7, $result->getValue());

        $operands->reset();
        $operands[] = new QtiFloat(-6.49);
        $result = $processor->process();
        $this::assertInstanceOf(QtiInteger::class, $result);
        $this::assertEquals(-6, $result->getValue());

        $operands->reset();
        $operands[] = new QtiInteger(0);
        $result = $processor->process();
        $this::assertInstanceOf(QtiInteger::class, $result);
        $this::assertEquals(0, $result->getValue());

        $operands->reset();
        $operands[] = new QtiFloat(-0.0);
        $result = $processor->process();
        $this::assertInstanceOf(QtiInteger::class, $result);
        $this::assertEquals(0, $result->getValue());

        $operands->reset();
        $operands[] = new QtiFloat(-0.5);
        $result = $processor->process();
        $this::assertInstanceOf(QtiInteger::class, $result);
        $this::assertEquals(0, $result->getValue());
    }

    public function testNull(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = null;
        $processor = new RoundProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertNull($result);
    }

    public function testWrongCardinality(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new OrderedContainer(BaseType::FLOAT, [new QtiFloat(1.1), new QtiFloat(2.2)]);
        $processor = new RoundProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongBaseTypeOne(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiBoolean(true);
        $processor = new RoundProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongBaseTypeTwo(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiDuration('P1D');
        $processor = new RoundProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testNotEnoughOperands(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $this->expectException(ExpressionProcessingException::class);
        $processor = new RoundProcessor($expression, $operands);
    }

    public function testTooMuchOperands(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiInteger(10);
        $operands[] = new QtiFloat(1.1);
        $this->expectException(ExpressionProcessingException::class);
        $processor = new RoundProcessor($expression, $operands);
    }

    /**
     * @return QtiComponent
     * @throws MarshallerNotFoundException
     */
    public function createFakeExpression(): QtiComponent
    {
        return $this->createComponentFromXml('
			<round>
				<baseValue baseType="float">6.49</baseValue>
			</round>
		');
    }
}
