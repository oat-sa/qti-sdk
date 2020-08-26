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
use qtism\runtime\expressions\operators\TruncateProcessor;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\ExpressionProcessingException;

/**
 * Class TruncateProcessorTest
 */
class TruncateProcessorTest extends QtiSmTestCase
{
    public function testRound()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiFloat(6.8);
        $processor = new TruncateProcessor($expression, $operands);

        $result = $processor->process();
        $this->assertInstanceOf(QtiInteger::class, $result);
        $this->assertEquals(6, $result->getValue());

        $operands->reset();
        $operands[] = new QtiFloat(6.5);
        $result = $processor->process();
        $this->assertInstanceOf(QtiInteger::class, $result);
        $this->assertEquals(6, $result->getValue());

        $operands->reset();
        $operands[] = new QtiFloat(6.49);
        $result = $processor->process();
        $this->assertInstanceOf(QtiInteger::class, $result);
        $this->assertEquals(6, $result->getValue());

        $operands->reset();
        $operands[] = new QtiFloat(-6.5);
        $result = $processor->process();
        $this->assertInstanceOf(QtiInteger::class, $result);
        $this->assertEquals(-6, $result->getValue());

        $operands->reset();
        $operands[] = new QtiFloat(-6.8);
        $result = $processor->process();
        $this->assertInstanceOf(QtiInteger::class, $result);
        $this->assertEquals(-6, $result->getValue());

        $operands->reset();
        $operands[] = new QtiFloat(-6.49);
        $result = $processor->process();
        $this->assertInstanceOf(QtiInteger::class, $result);
        $this->assertEquals(-6, $result->getValue());

        $operands->reset();
        $operands[] = new QtiInteger(0);
        $result = $processor->process();
        $this->assertInstanceOf(QtiInteger::class, $result);
        $this->assertEquals(0, $result->getValue());

        $operands->reset();
        $operands[] = new QtiFloat(-0.0);
        $result = $processor->process();
        $this->assertInstanceOf(QtiInteger::class, $result);
        $this->assertEquals(0, $result->getValue());

        $operands->reset();
        $operands[] = new QtiFloat(-0.5);
        $result = $processor->process();
        $this->assertInstanceOf(QtiInteger::class, $result);
        $this->assertEquals(0, $result->getValue());

        $operands->reset();
        $operands[] = new QtiFloat(-0.4);
        $result = $processor->process();
        $this->assertInstanceOf(QtiInteger::class, $result);
        $this->assertEquals(0, $result->getValue());

        $operands->reset();
        $operands[] = new QtiFloat(-0.6);
        $result = $processor->process();
        $this->assertInstanceOf(QtiInteger::class, $result);
        $this->assertEquals(0, $result->getValue());

        $operands->reset();
        $operands[] = new QtiFloat(NAN);
        $result = $processor->process();
        $this->assertSame(null, $result);

        $operands->reset();
        $operands[] = new QtiFloat(-INF);
        $result = $processor->process();
        $this->assertInstanceOf(QtiFloat::class, $result);
        $this->assertEquals(-INF, $result->getValue());

        $operands->reset();
        $operands[] = new QtiFloat(INF);
        $result = $processor->process();
        $this->assertInstanceOf(QtiFloat::class, $result);
        $this->assertEquals(INF, $result->getValue());
    }

    public function testNull()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = null;
        $processor = new TruncateProcessor($expression, $operands);
        $result = $processor->process();
        $this->assertSame(null, $result);
    }

    public function testWrongCardinality()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new OrderedContainer(BaseType::FLOAT, [new QtiFloat(1.1), new QtiFloat(2.2)]);
        $processor = new TruncateProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongBaseTypeOne()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiBoolean(true);
        $processor = new TruncateProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongBaseTypeTwo()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiDuration('P1D');
        $processor = new TruncateProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testNotEnoughOperands()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $this->expectException(ExpressionProcessingException::class);
        $processor = new TruncateProcessor($expression, $operands);
    }

    public function testTooMuchOperands()
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiInteger(10);
        $operands[] = new QtiFloat(1.1);
        $this->expectException(ExpressionProcessingException::class);
        $processor = new TruncateProcessor($expression, $operands);
    }

    /**
     * @return QtiComponent
     * @throws MarshallerNotFoundException
     */
    public function createFakeExpression()
    {
        return $this->createComponentFromXml('
			<truncate>
				<baseValue baseType="float">6.49</baseValue>
			</truncate>
		');
    }

    /**
     * @return array
     */
    public function provider()
    {
        return [
            [97.2, 97],
            [97.5, 97],
            [97.9, 97],
            [98.0, 98],
        ];
    }

    /**
     * @dataProvider provider
     * @param float $val
     * @param integer $expected
     * @throws MarshallerNotFoundException
     */
    public function testForProvider($val, $expected)
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiFloat($val);
        $processor = new TruncateProcessor($expression, $operands);

        $result = $processor->process();
        $this->assertInstanceOf(QtiInteger::class, $result);
        $this->assertEquals($expected, $result->getValue());
    }
}
