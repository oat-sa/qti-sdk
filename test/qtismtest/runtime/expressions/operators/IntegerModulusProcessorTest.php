<?php

declare(strict_types=1);

namespace qtismtest\runtime\expressions\operators;

use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\data\QtiComponent;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\expressions\operators\IntegerModulusProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\ExpressionProcessingException;

/**
 * Class IntegerModulusProcessorTest
 */
class IntegerModulusProcessorTest extends QtiSmTestCase
{
    public function testIntegerModulus(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiInteger(10), new QtiInteger(5)]);
        $processor = new IntegerModulusProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiInteger::class, $result);
        $this::assertEquals(0, $result->getValue());

        $operands = new OperandsCollection([new QtiInteger(49), new QtiInteger(-5)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiInteger::class, $result);
        $this::assertEquals(4, $result->getValue());

        $operands = new OperandsCollection([new QtiInteger(36), new QtiInteger(7)]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiInteger::class, $result);
        $this::assertEquals(1, $result->getValue());
    }

    public function testNull(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([null, new QtiInteger(5)]);
        $processor = new IntegerModulusProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertNull($result);
    }

    public function testModulusByZero(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiInteger(50), new QtiInteger(0)]);
        $processor = new IntegerModulusProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertNull($result);
    }

    public function testWrongCardinality(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new MultipleContainer(BaseType::INTEGER, [new QtiInteger(10)]), new QtiInteger(5)]);
        $processor = new IntegerModulusProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongBaseTypeOne(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiString('ping!'), new QtiInteger(5)]);
        $processor = new IntegerModulusProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongBaseTypeTwo(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiInteger(5), new QtiDuration('P1D')]);
        $processor = new IntegerModulusProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testNotEnoughOperands(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiInteger(5)]);
        $this->expectException(ExpressionProcessingException::class);
        $processor = new IntegerModulusProcessor($expression, $operands);
    }

    public function testTooMuchOperands(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiInteger(5), new QtiInteger(5), new QtiInteger(5)]);
        $this->expectException(ExpressionProcessingException::class);
        $processor = new IntegerModulusProcessor($expression, $operands);
    }

    /**
     * @return QtiComponent
     * @throws MarshallerNotFoundException
     */
    public function createFakeExpression(): QtiComponent
    {
        return $this->createComponentFromXml('
			<integerModulus>
				<baseValue baseType="integer">36</baseValue>
				<baseValue baseType="integer">7</baseValue>
			</integerModulus>
		');
    }
}
