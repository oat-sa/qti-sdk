<?php

namespace qtismtest\runtime\expressions\operators;

use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPoint;
use qtism\common\enums\BaseType;
use qtism\data\QtiComponent;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\expressions\operators\DeleteProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\ExpressionProcessingException;

/**
 * Class DeleteProcessorTest
 */
class DeleteProcessorTest extends QtiSmTestCase
{
    public function testMultiple()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiInteger(10);
        $operands[] = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(0), new QtiInteger(10), new QtiInteger(20), new QtiInteger(30)]);
        $processor = new DeleteProcessor($expression, $operands);
        $result = $processor->process();
        $this->assertInstanceOf(MultipleContainer::class, $result);
        $this->assertEquals(3, count($result));
        $this->assertTrue($result->contains(new QtiInteger(0)));
        $this->assertTrue($result->contains(new QtiInteger(20)));
        $this->assertTrue($result->contains(new QtiInteger(30)));
        $this->assertFalse($result->contains(new QtiInteger(10)));

        // Check that ALL the occurences of the first sub-expression are removed.
        $operands->reset();
        $operands[] = new QtiInteger(10);
        $operands[] = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(0), new QtiInteger(10), new QtiInteger(20), new QtiInteger(10), new QtiInteger(10), new QtiInteger(30)]);
        $result = $processor->process();
        $this->assertInstanceOf(MultipleContainer::class, $result);
        $this->assertEquals(3, count($result));
        $this->assertTrue($result->contains(new QtiInteger(0)));
        $this->assertTrue($result->contains(new QtiInteger(20)));
        $this->assertTrue($result->contains(new QtiInteger(30)));
        $this->assertFalse($result->contains(new QtiInteger(10)));
    }

    public function testMultipleNotMatch()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiInteger(60);
        $operands[] = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(0), new QtiInteger(10), new QtiInteger(20), new QtiInteger(30)]);
        $processor = new DeleteProcessor($expression, $operands);
        $result = $processor->process();
        $this->assertTrue($operands[1]->equals($result));
    }

    public function testEverythingRemoved()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiInteger(60);
        $operands[] = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(60), new QtiInteger(60), new QtiInteger(60), new QtiInteger(60)]);
        $processor = new DeleteProcessor($expression, $operands);
        $result = $processor->process();
        $this->assertInstanceOf(MultipleContainer::class, $result);
        $this->assertTrue($result->isNull());
    }

    public function testOrdered()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiPoint(2, 4);
        $operands[] = new OrderedContainer(BaseType::POINT, [new QtiPoint(1, 2), new QtiPoint(2, 4), new QtiPoint(3, 4)]);
        $processor = new DeleteProcessor($expression, $operands);
        $result = $processor->process();
        $this->assertInstanceOf(OrderedContainer::class, $result);
        $this->assertEquals(2, count($result));
        $this->assertTrue($result->contains(new QtiPoint(1, 2)));
        $this->assertTrue($result->contains(new QtiPoint(3, 4)));
        $this->assertFalse($result->contains(new QtiPoint(2, 4)));

        // Check that ALL the occurences of the first sub-expression are removed.
        $operands->reset();
        $operands[] = new QtiPoint(2, 4);
        $operands[] = new OrderedContainer(BaseType::POINT, [new QtiPoint(1, 2), new QtiPoint(2, 4), new QtiPoint(2, 4), new QtiPoint(3, 4)]);
        $result = $processor->process();
        $this->assertInstanceOf(OrderedContainer::class, $result);
        $this->assertEquals(2, count($result));
        $this->assertTrue($result->contains(new QtiPoint(1, 2)));
        $this->assertTrue($result->contains(new QtiPoint(3, 4)));
        $this->assertFalse($result->contains(new QtiPoint(2, 4)));
    }

    public function testNull()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = null;
        $operands[] = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(0), new QtiInteger(10), new QtiInteger(20), new QtiInteger(30)]);
        $processor = new DeleteProcessor($expression, $operands);
        $result = $processor->process();
        $this->assertSame(null, $result);

        $operands->reset();
        $operands[] = new QtiInteger(10);
        $operands[] = new MultipleContainer(BaseType::INTEGER);
        $result = $processor->process();
        $this->assertSame(null, $result);
    }

    public function testDifferentBaseType()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiFloat(10.1);
        $operands[] = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(0), new QtiInteger(10), new QtiInteger(20), new QtiInteger(30)]);
        $processor = new DeleteProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongCardinalityOne()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(0), new QtiInteger(10), new QtiInteger(20), new QtiInteger(30)]);
        $operands[] = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(0), new QtiInteger(10), new QtiInteger(20), new QtiInteger(30)]);
        $processor = new DeleteProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongCardinalityTwo()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiInteger(10);
        $operands[] = new QtiInteger(10);
        $processor = new DeleteProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testNotEnoughOperands()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $this->expectException(ExpressionProcessingException::class);
        $processor = new DeleteProcessor($expression, $operands);
    }

    public function testTooMuchOperands()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiInteger(10);
        $operands[] = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(10)]);
        $operands[] = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(10)]);
        $this->expectException(ExpressionProcessingException::class);
        $processor = new DeleteProcessor($expression, $operands);
    }

    /**
     * @return QtiComponent
     */
    public function createFakeExpression()
    {
        return $this->createComponentFromXml('
			<delete>
				<baseValue baseType="integer">10</baseValue>
				<multiple>
					<baseValue baseType="integer">0</baseValue>
					<baseValue baseType="integer">10</baseValue>
					<baseValue baseType="integer">20</baseValue>
					<baseValue baseType="integer">30</baseValue>
				</multiple>
			</delete>
		');
    }
}
