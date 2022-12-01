<?php

namespace qtismtest\runtime\expressions\operators;

use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\data\QtiComponent;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\expressions\operators\ContainsProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\ExpressionProcessingException;

/**
 * Class ContainsProcessorTest
 */
class ContainsProcessorTest extends QtiSmTestCase
{
    public function testPrimitiveOrderedTrailing(): void
    {
        $expression = $this->createFakeExpression();

        // For ordered containers [A,B,C] contains [B,C]
        $operands = new OperandsCollection();
        $operands[] = new OrderedContainer(BaseType::STRING, [new QtiString('A'), new QtiString('B'), new QtiString('C')]);
        $operands[] = new OrderedContainer(BaseType::STRING, [new QtiString('B'), new QtiString('C')]);
        $processor = new ContainsProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());

        // [A,B,C] does not contain [C,B]
        $operands->reset();
        $operands[] = new OrderedContainer(BaseType::STRING, [new QtiString('A'), new QtiString('B'), new QtiString('C')]);
        $operands[] = new OrderedContainer(BaseType::STRING, [new QtiString('C'), new QtiString('B')]);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertFalse($result->getValue());

        // [A,B,C] does not contain [E,F]
        $operands->reset();
        $operands[] = new OrderedContainer(BaseType::STRING, [new QtiString('A'), new QtiString('B'), new QtiString('C')]);
        $operands[] = new OrderedContainer(BaseType::STRING, [new QtiString('E'), new QtiString('F')]);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertFalse($result->getValue());
    }

    public function testPrimitiveOrderedLeading(): void
    {
        $expression = $this->createFakeExpression();

        // For ordered containers [A,B,C] contains [A,B]
        $operands = new OperandsCollection();
        $operands[] = new OrderedContainer(BaseType::STRING, [new QtiString('A'), new QtiString('B'), new QtiString('C')]);
        $operands[] = new OrderedContainer(BaseType::STRING, [new QtiString('A'), new QtiString('B')]);
        $processor = new ContainsProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());

        // [A,B,C] does not contain [B,A]
        $operands->reset();
        $operands[] = new OrderedContainer(BaseType::STRING, [new QtiString('A'), new QtiString('B'), new QtiString('C')]);
        $operands[] = new OrderedContainer(BaseType::STRING, [new QtiString('B'), new QtiString('A')]);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertFalse($result->getValue());
    }

    public function testPrimitiveOrderedInBetween(): void
    {
        $expression = $this->createFakeExpression();

        // For ordered containers [A,B,C,D] contains [B]
        $operands = new OperandsCollection();
        $operands[] = new OrderedContainer(BaseType::STRING, [new QtiString('A'), new QtiString('B'), new QtiString('C'), new QtiString('D')]);
        $operands[] = new OrderedContainer(BaseType::STRING, [new QtiString('B')]);
        $processor = new ContainsProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());

        // [A,B,C,D] does not contain [E]
        $operands->reset();
        $operands[] = new OrderedContainer(BaseType::STRING, [new QtiString('A'), new QtiString('B'), new QtiString('C'), new QtiString('D')]);
        $operands[] = new OrderedContainer(BaseType::STRING, [new QtiString('E')]);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertFalse($result->getValue());

        // [A,B,C,D] contains [B,C]
        $operands->reset();
        $operands[] = new OrderedContainer(BaseType::STRING, [new QtiString('A'), new QtiString('B'), new QtiString('C'), new QtiString('D')]);
        $operands[] = new OrderedContainer(BaseType::STRING, [new QtiString('B'), new QtiString('C')]);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());
    }

    public function testPrimitiveMultipleTrailing(): void
    {
        $expression = $this->createFakeExpression();

        // For multiple containers [A,B,C] contains [B,C]
        $operands = new OperandsCollection();
        $operands[] = new MultipleContainer(BaseType::STRING, [new QtiString('A'), new QtiString('B'), new QtiString('C')]);
        $operands[] = new MultipleContainer(BaseType::STRING, [new QtiString('B'), new QtiString('C')]);
        $processor = new ContainsProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());

        // [A,B,C] contains [C,B]
        $operands->reset();
        $operands[] = new MultipleContainer(BaseType::STRING, [new QtiString('A'), new QtiString('B'), new QtiString('C')]);
        $operands[] = new MultipleContainer(BaseType::STRING, [new QtiString('C'), new QtiString('B')]);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());

        // [A,B,C] does not contain [E,F]
        $operands->reset();
        $operands[] = new MultipleContainer(BaseType::STRING, [new QtiString('A'), new QtiString('B'), new QtiString('C')]);
        $operands[] = new MultipleContainer(BaseType::STRING, [new QtiString('E'), new QtiString('F')]);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertFalse($result->getValue());
    }

    public function testPrimitiveMultipleLeading(): void
    {
        $expression = $this->createFakeExpression();

        // For ordered containers [A,B,C] contains [A,B]
        $operands = new OperandsCollection();
        $operands[] = new MultipleContainer(BaseType::STRING, [new QtiString('A'), new QtiString('B'), new QtiString('C')]);
        $operands[] = new MultipleContainer(BaseType::STRING, [new QtiString('A'), new QtiString('B')]);
        $processor = new ContainsProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());

        // [A,B,C] contains [B,A]
        $operands->reset();
        $operands[] = new MultipleContainer(BaseType::STRING, [new QtiString('A'), new QtiString('B'), new QtiString('C')]);
        $operands[] = new MultipleContainer(BaseType::STRING, [new QtiString('B'), new QtiString('A')]);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());
    }

    public function testPrimitiveMultipleInBetween(): void
    {
        $expression = $this->createFakeExpression();

        // For ordered containers [A,B,C,D] contains [B]
        $operands = new OperandsCollection();
        $operands[] = new MultipleContainer(BaseType::STRING, [new QtiString('A'), new QtiString('B'), new QtiString('C'), new QtiString('D')]);
        $operands[] = new MultipleContainer(BaseType::STRING, [new QtiString('B')]);
        $processor = new ContainsProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());

        // [A,B,C,D] does not contain [E]
        $operands->reset();
        $operands[] = new MultipleContainer(BaseType::STRING, [new QtiString('A'), new QtiString('B'), new QtiString('C'), new QtiString('D')]);
        $operands[] = new MultipleContainer(BaseType::STRING, [new QtiString('E')]);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertFalse($result->getValue());

        // [A,B,C,D] contains [A,D]
        $operands->reset();
        $operands[] = new MultipleContainer(BaseType::STRING, [new QtiString('A'), new QtiString('B'), new QtiString('C'), new QtiString('D')]);
        $operands[] = new MultipleContainer(BaseType::STRING, [new QtiString('A'), new QtiString('D')]);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());
    }

    public function testComplexOrderedTrailing(): void
    {
        $expression = $this->createFakeExpression();

        // For ordered containers [A,B,C] contains [B,C]
        $operands = new OperandsCollection();
        $operands[] = new OrderedContainer(BaseType::POINT, [new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6)]);
        $operands[] = new OrderedContainer(BaseType::POINT, [new QtiPoint(3, 4), new QtiPoint(5, 6)]);
        $processor = new ContainsProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());

        // [A,B,C] does not contain [C,B]
        $operands->reset();
        $operands[] = new OrderedContainer(BaseType::POINT, [new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6)]);
        $operands[] = new OrderedContainer(BaseType::POINT, [new QtiPoint(5, 6), new QtiPoint(3, 4)]);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertFalse($result->getValue());

        // [A,B,C] does not contain [E,F]
        $operands->reset();
        $operands[] = new OrderedContainer(BaseType::POINT, [new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6)]);
        $operands[] = new OrderedContainer(BaseType::POINT, [new QtiPoint(7, 8), new QtiPoint(9, 10)]);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertFalse($result->getValue());
    }

    public function testComplexOrderedLeading(): void
    {
        $expression = $this->createFakeExpression();

        // For ordered containers [A,B,C] contains [A,B]
        $operands = new OperandsCollection();
        $operands[] = new OrderedContainer(BaseType::POINT, [new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6)]);
        $operands[] = new OrderedContainer(BaseType::POINT, [new QtiPoint(1, 2), new QtiPoint(3, 4)]);
        $processor = new ContainsProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());

        // [A,B,C] does not contain [B,A]
        $operands->reset();
        $operands[] = new OrderedContainer(BaseType::POINT, [new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6)]);
        $operands[] = new OrderedContainer(BaseType::POINT, [new QtiPoint(3, 4), new QtiPoint(1, 2)]);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertFalse($result->getValue());
    }

    public function testComplexOrderedInBetween(): void
    {
        $expression = $this->createFakeExpression();

        // For ordered containers [A,B,C,D] contains [B]
        $operands = new OperandsCollection();
        $operands[] = new OrderedContainer(BaseType::POINT, [new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6), new QtiPoint(7, 8)]);
        $operands[] = new OrderedContainer(BaseType::POINT, [new QtiPoint(3, 4)]);
        $processor = new ContainsProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());

        // [A,B,C,D] does not contain [E]
        $operands->reset();
        $operands[] = new OrderedContainer(BaseType::POINT, [new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6), new QtiPoint(7, 8)]);
        $operands[] = new OrderedContainer(BaseType::POINT, [new QtiPoint(9, 10)]);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertFalse($result->getValue());

        // [A,B,C,D] contains [B,C]
        $operands->reset();
        $operands[] = new OrderedContainer(BaseType::POINT, [new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6), new QtiPoint(7, 8)]);
        $operands[] = new OrderedContainer(BaseType::POINT, [new QtiPoint(3, 4), new QtiPoint(5, 6)]);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());
    }

    public function testComplexMultipleTrailing(): void
    {
        $expression = $this->createFakeExpression();

        // For multiple containers [A,B,C] contains [B,C]
        $operands = new OperandsCollection();
        $operands[] = new MultipleContainer(BaseType::POINT, [new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6)]);
        $operands[] = new MultipleContainer(BaseType::POINT, [new QtiPoint(3, 4), new QtiPoint(5, 6)]);
        $processor = new ContainsProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());

        // [A,B,C] contains [C,B]
        $operands->reset();
        $operands[] = new MultipleContainer(BaseType::POINT, [new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6)]);
        $operands[] = new MultipleContainer(BaseType::POINT, [new QtiPoint(5, 6), new QtiPoint(3, 4)]);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());

        // [A,B,C] does not contain [E,F]
        $operands->reset();
        $operands[] = new MultipleContainer(BaseType::POINT, [new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6)]);
        $operands[] = new MultipleContainer(BaseType::POINT, [new QtiPoint(9, 10), new QtiPoint(11, 12)]);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertFalse($result->getValue());
    }

    public function testComplexMultipleLeading(): void
    {
        $expression = $this->createFakeExpression();

        // For ordered containers [A,B,C] contains [A,B]
        $operands = new OperandsCollection();
        $operands[] = new MultipleContainer(BaseType::POINT, [new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6)]);
        $operands[] = new MultipleContainer(BaseType::POINT, [new QtiPoint(1, 2), new QtiPoint(3, 4)]);
        $processor = new ContainsProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());

        // [A,B,C] contains [B,A]
        $operands->reset();
        $operands[] = new MultipleContainer(BaseType::POINT, [new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6)]);
        $operands[] = new MultipleContainer(BaseType::POINT, [new QtiPoint(3, 4), new QtiPoint(1, 2)]);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());

        // [A,B,C] does not contain [B,D]
        $operands->reset();
        $operands[] = new MultipleContainer(BaseType::POINT, [new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6)]);
        $operands[] = new MultipleContainer(BaseType::POINT, [new QtiPoint(3, 4), new QtiPoint(7, 8)]);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertFalse($result->getValue());
    }

    public function testComplexMultipleInBetween(): void
    {
        $expression = $this->createFakeExpression();

        // For ordered containers [A,B,C,D] contains [B]
        $operands = new OperandsCollection();
        $operands[] = new MultipleContainer(BaseType::POINT, [new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6), new QtiPoint(7, 8)]);
        $operands[] = new MultipleContainer(BaseType::POINT, [new QtiPoint(3, 4)]);
        $processor = new ContainsProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());

        // [A,B,C,D] does not contain [E]
        $operands->reset();
        $operands[] = new MultipleContainer(BaseType::POINT, [new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6), new QtiPoint(7, 8)]);
        $operands[] = new MultipleContainer(BaseType::POINT, [new QtiPoint(9, 10)]);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertFalse($result->getValue());

        // [A,B,C,D] contains [A,D]
        $operands->reset();
        $operands[] = new MultipleContainer(BaseType::POINT, [new QtiPoint(1, 2), new QtiPoint(3, 4), new QtiPoint(5, 6), new QtiPoint(7, 8)]);
        $operands[] = new MultipleContainer(BaseType::POINT, [new QtiPoint(1, 2), new QtiPoint(7, 8)]);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());
    }

    public function testMultipleOccurences(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection(
            [
                new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('no'), new QtiIdentifier('yes'), new QtiIdentifier('yes'), new QtiIdentifier('no')]),
                new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('yes'), new QtiIdentifier('yes')]),
            ]
        );
        $processor = new ContainsProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertTrue($result->getValue());
    }

    public function testNull(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([null, new MultipleContainer(BaseType::INTEGER, [new QtiInteger(25)])]);
        $processor = new ContainsProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertNull($result);

        $operands = new OperandsCollection([new MultipleContainer(BaseType::INTEGER), new MultipleContainer(BaseType::INTEGER, [new QtiInteger(25)])]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertNull($result);
    }

    public function testNotSameBaseTypeOne(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new MultipleContainer(BaseType::STRING, [new QtiString('identifier3'), new QtiString('identifier4'), null, new QtiString('identifier2')]);
        $operands[] = new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('identifier3'), new QtiIdentifier('identifier2')]);
        $processor = new ContainsProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $processor->process();
    }

    public function testNotSameBaseTypeTwo(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(25)]);
        $operands[] = new MultipleContainer(BaseType::FLOAT, [new QtiFloat(25.0)]);
        $processor = new ContainsProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testNotSameCardinality(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(25)]);
        $operands[] = new OrderedContainer(BaseType::INTEGER, [new QtiInteger(25)]);
        $processor = new ContainsProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongCardinality(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new OrderedContainer(BaseType::INTEGER, [new QtiInteger(25)]);
        $operands[] = new RecordContainer(['1' => new QtiInteger(1), '2' => new QtiInteger(2)]);
        $processor = new ContainsProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testNotEnoughOperands(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new MultipleContainer(BaseType::POINT, [new QtiPoint(1, 2)])]);
        $this->expectException(ExpressionProcessingException::class);
        $processor = new ContainsProcessor($expression, $operands);
    }

    public function testTooMuchOperands(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new OrderedContainer(BaseType::INTEGER, [new QtiInteger(25)]);
        $operands[] = new OrderedContainer(BaseType::INTEGER, [new QtiInteger(25)]);
        $operands[] = new OrderedContainer(BaseType::INTEGER, [new QtiInteger(25)]);
        $this->expectException(ExpressionProcessingException::class);
        $processor = new ContainsProcessor($expression, $operands);
    }

    /**
     * @return QtiComponent
     * @throws MarshallerNotFoundException
     */
    public function createFakeExpression(): QtiComponent
    {
        return $this->createComponentFromXml('
			<contains>
				<multiple>
					<baseValue baseType="string">A</baseValue>
					<baseValue baseType="string">B</baseValue>
					<baseValue baseType="string">C</baseValue>
				</multiple>
				<multiple>
					<baseValue baseType="string">B</baseValue>
					<baseValue baseType="string">C</baseValue>
				</multiple>
			</contains>
		');
    }
}
