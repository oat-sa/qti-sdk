<?php

namespace qtismtest\runtime\expressions\operators;

use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\data\QtiComponent;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\expressions\operators\IsNullProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\ExpressionProcessingException;

/**
 * Class IsNullProcessorTest
 */
class IsNullProcessorTest extends QtiSmTestCase
{
    public function testWithEmptyString()
    {
        $operands = new OperandsCollection();
        $operands[] = new QtiString('');

        $expression = $this->getFakeExpression();
        $processor = new IsNullProcessor($expression, $operands);
        $this->assertTrue($processor->process()->getValue());
    }

    public function testWithNull()
    {
        $operands = new OperandsCollection();
        $operands[] = null;

        $expression = $this->getFakeExpression();
        $processor = new IsNullProcessor($expression, $operands);
        $this->assertTrue($processor->process()->getValue());
    }

    public function testEmptyContainers()
    {
        $operands = new OperandsCollection();
        $operands[] = new MultipleContainer(BaseType::POINT);

        $expression = $this->getFakeExpression();
        $processor = new IsNullProcessor($expression, $operands);
        $this->assertTrue($processor->process()->getValue());

        $operands->reset();
        $operands[] = new OrderedContainer(BaseType::BOOLEAN);
        $this->assertTrue($processor->process()->getValue());

        $operands->reset();
        $operands[] = new RecordContainer();
        $this->assertTrue($processor->process()->getValue());
    }

    public function testNotEmpty()
    {
        $expression = $this->getFakeExpression();
        $operands = new OperandsCollection([new QtiInteger(0)]);

        $processor = new IsNullProcessor($expression, $operands);
        $this->assertFalse($processor->process()->getValue());

        $operands->reset();
        $operands[] = new QtiBoolean(false);
        $this->assertFalse($processor->process()->getValue());

        $operands->reset();
        $operands[] = new QtiInteger(-1);
        $this->assertFalse($processor->process()->getValue());

        $operands->reset();
        $operands[] = new QtiPoint(1, 2);
        $this->assertFalse($processor->process()->getValue());

        $operands->reset();
        $operands[] = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(25)]);
        $this->assertFalse($processor->process()->getValue());

        $operands->reset();
        $operands[] = new OrderedContainer(BaseType::POINT, [new QtiPoint(3, 4), new QtiPoint(5, 6)]);
        $this->assertFalse($processor->process()->getValue());

        $operands->reset();
        $operands[] = new RecordContainer(['a' => new QtiBoolean(true), 'b' => null, 'c' => new QtiPoint(1, 2), 'd' => new QtiInteger(24), 'e' => new QtiFloat(23.3)]);
        $this->assertFalse($processor->process()->getValue());
    }

    public function testLessThanNeededOperands()
    {
        $this->expectException(ExpressionProcessingException::class);

        $operands = new OperandsCollection();
        $expression = $this->getFakeExpression();
        $processor = new IsNullProcessor($expression, $operands);
        $result = $processor->process();
    }

    public function testMoreThanNeededOperands()
    {
        $this->expectException(ExpressionProcessingException::class);

        $operands = new OperandsCollection([new QtiInteger(25), null]);
        $expression = $this->getFakeExpression();
        $processor = new IsNullProcessor($expression, $operands);
        $result = $processor->process();
    }

    /**
     * @return QtiComponent
     */
    private function getFakeExpression()
    {
        return $this->createComponentFromXml('
			<isNull>
				<baseValue baseType="string"></baseValue>
			</isNull>
		');
    }
}
