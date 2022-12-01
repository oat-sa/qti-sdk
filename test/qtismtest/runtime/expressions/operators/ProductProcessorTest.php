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
use qtism\runtime\expressions\operators\ProductProcessor;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\ExpressionProcessingException;
use qtism\runtime\common\Processable;
use RuntimeException;

/**
 * Class ProductProcessorTest
 */
class ProductProcessorTest extends QtiSmTestCase
{
    public function testSimple(): void
    {
        $product = $this->createFakeProductComponent();

        $operands = new OperandsCollection([new QtiInteger(1), new QtiInteger(1)]);
        $productProcessor = new ProductProcessor($product, $operands);
        $result = $productProcessor->process();

        $this::assertInstanceOf(Processable::class, $productProcessor);
        $this::assertInstanceOf(QtiInteger::class, $result);
        $this::assertEquals(1, $result->getValue());
    }

    public function testNary(): void
    {
        $product = $this->createFakeProductComponent();

        $operands = new OperandsCollection([new QtiInteger(24), new QtiInteger(-4), new QtiInteger(1)]);
        $productProcessor = new ProductProcessor($product, $operands);
        $result = $productProcessor->process();

        $this::assertInstanceOf(QtiInteger::class, $result);
        $this::assertEquals(-96, $result->getValue());
    }

    public function testComplex(): void
    {
        $product = $this->createFakeProductComponent();

        $operands = new OperandsCollection([new QtiInteger(-1), new QtiInteger(1)]);
        $operands[] = new MultipleContainer(BaseType::FLOAT, [new QtiFloat(2.1), new QtiFloat(4.3)]);
        $operands[] = new OrderedContainer(BaseType::INTEGER, [new QtiInteger(10), new QtiInteger(15)]);
        $productProcessor = new ProductProcessor($product, $operands);
        $result = $productProcessor->process();

        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertEquals(-1354.5, $result->getValue());
    }

    public function testInvalidOperandsOne(): void
    {
        $product = $this->createFakeProductComponent();

        $this->expectException(RuntimeException::class);

        $operands = new OperandsCollection([new QtiBoolean(true), new QtiInteger(14), new QtiInteger(10)]);
        $productProcessor = new ProductProcessor($product, $operands);
        $result = $productProcessor->process();
    }

    public function testInvalidOperandsTwo(): void
    {
        $product = $this->createFakeProductComponent();
        $operands = new OperandsCollection();
        $operands[] = new MultipleContainer(BaseType::BOOLEAN, [new QtiBoolean(true), new QtiBoolean(false)]);
        $productProcessor = new ProductProcessor($product, $operands);

        $this->expectException(RuntimeException::class);
        $result = $productProcessor->process();
    }

    public function testNullInvolved(): void
    {
        $product = $this->createFakeProductComponent();
        $operands = new OperandsCollection([new QtiInteger(10), new QtiInteger(10), null]);
        $productProcessor = new ProductProcessor($product, $operands);
        $result = $productProcessor->process();
        $this::assertNull($result);
    }

    public function testNotEnoughOperands(): void
    {
        $product = $this->createFakeProductComponent();
        $operands = new OperandsCollection();
        $this->expectException(ExpressionProcessingException::class);
        $productProcessor = new ProductProcessor($product, $operands);
    }

    /**
     * @return QtiComponent
     * @throws MarshallerNotFoundException
     */
    private function createFakeProductComponent(): QtiComponent
    {
        return $this->createComponentFromXml('
			<product xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1">
				<baseValue baseType="integer">1</baseValue>
				<baseValue baseType="integer">3</baseValue>
			</product>
		');
    }
}
