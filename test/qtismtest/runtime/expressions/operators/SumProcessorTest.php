<?php

namespace qtismtest\runtime\expressions\operators;

use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\common\enums\BaseType;
use qtism\data\QtiComponent;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtism\runtime\expressions\operators\SumProcessor;
use qtismtest\QtiSmTestCase;
use qtism\runtime\common\Processable;
use RuntimeException;

/**
 * Class SumProcessorTest
 */
class SumProcessorTest extends QtiSmTestCase
{
    public function testSimple()
    {
        $sum = $this->createFakeSumComponent();

        $operands = new OperandsCollection([new QtiInteger(1), new QtiInteger(1)]);
        $sumProcessor = new SumProcessor($sum, $operands);
        $result = $sumProcessor->process();

        $this::assertInstanceOf(Processable::class, $sumProcessor);
        $this::assertInstanceOf(QtiInteger::class, $result);
        $this::assertEquals(2, $result->getValue());
    }

    public function testNary()
    {
        $sum = $this->createFakeSumComponent();

        $operands = new OperandsCollection([new QtiInteger(24), new QtiInteger(-4), new QtiInteger(0)]);
        $sumProcessor = new SumProcessor($sum, $operands);
        $result = $sumProcessor->process();

        $this::assertInstanceOf(QtiInteger::class, $result);
        $this::assertEquals(20, $result->getValue());
    }

    public function testComplex()
    {
        $sum = $this->createFakeSumComponent();

        $operands = new OperandsCollection([new QtiInteger(-1), new QtiInteger(1)]);
        $operands[] = new MultipleContainer(BaseType::FLOAT, [new QtiFloat(2.1), new QtiFloat(4.3)]);
        $operands[] = new OrderedContainer(BaseType::INTEGER, [new QtiInteger(10), new QtiInteger(15)]);
        $sumProcessor = new SumProcessor($sum, $operands);
        $result = $sumProcessor->process();

        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(31.4, $result->getValue());
    }

    public function testZero()
    {
        $sum = $this->createFakeSumComponent();

        $operands = new OperandsCollection([new QtiInteger(0), new QtiFloat(6.0)]);
        $sumProcessor = new SumProcessor($sum, $operands);
        $result = $sumProcessor->process();

        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(6.0, $result->getValue());
    }

    public function testInvalidOperandsOne()
    {
        $sum = $this->createFakeSumComponent();

        $this->expectException(RuntimeException::class);

        $operands = new OperandsCollection([new QtiBoolean(true), new QtiInteger(14), new QtiInteger(10)]);
        $sumProcessor = new SumProcessor($sum, $operands);
        $result = $sumProcessor->process();
    }

    public function testInvalidOperandsTwo()
    {
        $sum = $this->createFakeSumComponent();
        $operands = new OperandsCollection();
        $operands[] = new MultipleContainer(BaseType::BOOLEAN, [new QtiBoolean(true), new QtiBoolean(false)]);
        $sumProcessor = new SumProcessor($sum, $operands);

        $this->expectException(RuntimeException::class);
        $result = $sumProcessor->process();
    }

    public function testNullInvolved()
    {
        $sum = $this->createFakeSumComponent();
        $operands = new OperandsCollection([new QtiInteger(10), new QtiInteger(10), null]);
        $sumProcessor = new SumProcessor($sum, $operands);
        $result = $sumProcessor->process();
        $this::assertNull($result);
    }

    /**
     * @return QtiComponent
     * @throws MarshallerNotFoundException
     */
    private function createFakeSumComponent()
    {
        return $this->createComponentFromXml('
			<sum xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1">
				<baseValue baseType="integer">1</baseValue>
				<baseValue baseType="integer">3</baseValue>
			</sum>
		');
    }
}
