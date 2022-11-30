<?php

declare(strict_types=1);

namespace qtismtest\runtime\expressions\operators;

use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\data\QtiComponent;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\expressions\operators\MemberProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\ExpressionProcessingException;

/**
 * Class MemberProcessorTest
 */
class MemberProcessorTest extends QtiSmTestCase
{
    public function testMultiple(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiFloat(10.1);
        $mult = new MultipleContainer(BaseType::FLOAT, [new QtiFloat(1.1), new QtiFloat(2.1), new QtiFloat(3.1)]);
        $operands[] = $mult;
        $processor = new MemberProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertFalse($result->getValue());

        $mult[] = new QtiFloat(10.1);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());
    }

    public function testOrdered(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiPair('A', 'B');
        $ordered = new OrderedContainer(BaseType::PAIR, [new QtiPair('B', 'C')]);
        $operands[] = $ordered;
        $processor = new MemberProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertFalse($result->getValue());

        $ordered[] = new QtiPair('A', 'B');
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());
    }

    public function testNull(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();

        // second operand is null.
        $operands[] = new QtiInteger(10);
        $operands[] = new OrderedContainer(BaseType::INTEGER);
        $processor = new MemberProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertNull($result);

        // fist operand is null.
        $operands->reset();
        $operands[] = null;
        $operands[] = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(10)]);
        $result = $processor->process();
        $this::assertNull($result);
    }

    public function testDifferentBaseTypeOne(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiString('String1');
        $operands[] = new OrderedContainer(BaseType::IDENTIFIER, [new QtiIdentifier('String2'), new QtiIdentifier('String1'), null]);
        $processor = new MemberProcessor($expression, $operands);

        $this->expectException(ExpressionProcessingException::class);
        $processor->process();
    }

    public function testDifferentBaseTypeTwo(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiPair('A', 'B');
        $operands[] = new MultipleContainer(BaseType::POINT, [new QtiPoint(1, 2)]);
        $processor = new MemberProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongCardinalityOne(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new MultipleContainer(BaseType::POINT, [new QtiPoint(13, 37)]);
        $operands[] = new MultipleContainer(BaseType::POINT, [new QtiPoint(1, 2)]);
        $processor = new MemberProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongCardinalityTwo(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiPoint(13, 37);
        $operands[] = new RecordContainer(['key' => new QtiString('value')]);
        $processor = new MemberProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testNotEnoughOperands(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiPoint(13, 37);
        $this->expectException(ExpressionProcessingException::class);
        $processor = new MemberProcessor($expression, $operands);
    }

    public function testTooMuchOperands(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiPoint(13, 37);
        $operands[] = new MultipleContainer(BaseType::POINT, [new QtiPoint(1, 2)]);
        $operands[] = new MultipleContainer(BaseType::POINT, [new QtiPoint(3, 4)]);
        $this->expectException(ExpressionProcessingException::class);
        $processor = new MemberProcessor($expression, $operands);
    }

    public function testSingleCardinalitySecondOperand(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiPoint(13, 37);
        $operands[] = new QtiPoint(13, 37);
        $processor = new MemberProcessor($expression, $operands);
        $result = $processor->process();

        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());
    }

    /**
     * @return QtiComponent
     * @throws MarshallerNotFoundException
     */
    public function createFakeExpression(): QtiComponent
    {
        return $this->createComponentFromXml('
			<member>
				<baseValue baseType="boolean">true</baseValue>
				<ordered>
					<baseValue baseType="boolean">false</baseValue>
					<baseValue baseType="boolean">true</baseValue>
				</ordered>
			</member>
		');
    }
}
