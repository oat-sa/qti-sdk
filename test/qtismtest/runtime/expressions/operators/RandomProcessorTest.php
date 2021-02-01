<?php

namespace qtismtest\runtime\expressions\operators;

use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\data\QtiComponent;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtism\runtime\expressions\operators\RandomProcessor;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\ExpressionProcessingException;

/**
 * Class RandomProcessorTest
 */
class RandomProcessorTest extends QtiSmTestCase
{
    public function testPrimitiveMultiple()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new MultipleContainer(BaseType::FLOAT, [new QtiFloat(1.0), new QtiFloat(2.0), new QtiFloat(3.0)]);
        $processor = new RandomProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertGreaterThanOrEqual(1.0, $result->getValue());
        $this::assertLessThanOrEqual(3.0, $result->getValue());
    }

    public function testPrimitiveOrdered()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new OrderedContainer(BaseType::STRING, [new QtiString('s1'), new QtiString('s2'), new QtiString('s3')]);
        $processor = new RandomProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiString::class, $result);
        $this::assertTrue($result->equals(new QtiString('s1')) || $result->equals(new QtiString('s2')) || $result->equals(new QtiString('s3')));
    }

    public function testComplexMultiple()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new MultipleContainer(BaseType::DURATION, [new QtiDuration('P1D'), new QtiDuration('P2D'), new QtiDuration('P3D')]);
        $processor = new RandomProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiDuration::class, $result);
        $this::assertGreaterThanOrEqual(1, $result->getDays());
        $this::assertLessThanOrEqual(3, $result->getDays());
    }

    public function testComplexOrdered()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new OrderedContainer(BaseType::POINT, [new QtiPoint(1, 1), new QtiPoint(2, 2), new QtiPoint(3, 3)]);
        $processor = new RandomProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiPoint::class, $result);
        $this::assertGreaterThanOrEqual(1, $result->getX());
        $this::assertLessThanOrEqual(3, $result->getY());
    }

    public function testOnlyOneInContainer()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new OrderedContainer(BaseType::POINT, [new QtiPoint(22, 33)]);
        $processor = new RandomProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiPoint::class, $result);
        $this::assertEquals(22, $result->getX());
        $this::assertEquals(33, $result->getY());
    }

    public function testNull()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new MultipleContainer(BaseType::POINT);
        $processor = new RandomProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertNull($result);
    }

    public function testWrongCardinalityOne()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiInteger(10);
        $processor = new RandomProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongCardinalityTwo()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new RecordContainer(['A' => new QtiInteger(1)]);
        $processor = new RandomProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testNotEnoughOperands()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $this->expectException(ExpressionProcessingException::class);
        $processor = new RandomProcessor($expression, $operands);
        $result = $processor->process();
    }

    public function testTooMuchOperands()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new MultipleContainer(BaseType::PAIR);
        $operands[] = new MultipleContainer(BaseType::PAIR);
        $this->expectException(ExpressionProcessingException::class);
        $processor = new RandomProcessor($expression, $operands);
        $result = $processor->process();
    }

    /**
     * @return QtiComponent
     * @throws MarshallerNotFoundException
     */
    public function createFakeExpression()
    {
        return $this->createComponentFromXml('
			<random>
				<multiple>
					<baseValue baseType="boolean">true</baseValue>
					<baseValue baseType="boolean">false</baseValue>
				</multiple>
			</random>
		');
    }
}
