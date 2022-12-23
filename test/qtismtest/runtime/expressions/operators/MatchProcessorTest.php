<?php

namespace qtismtest\runtime\expressions\operators;

use qtism\common\datatypes\files\FileSystemFileManager;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiIntOrIdentifier;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\data\QtiComponent;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\expressions\operators\MatchProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\ExpressionProcessingException;

/**
 * Class MatchProcessorTest
 */
class MatchProcessorTest extends QtiSmTestCase
{
    public function testScalar(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiInteger(10), new QtiInteger(10)]);
        $processor = new MatchProcessor($expression, $operands);
        $this::assertTrue($processor->process()->getValue());

        $operands = new OperandsCollection([new QtiInteger(10), new QtiInteger(11)]);
        $processor->setOperands($operands);
        $this::assertNotTrue($processor->process()->getValue());
    }

    public function testContainer(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(5), new QtiInteger(4), new QtiInteger(3), new QtiInteger(2), new QtiInteger(1)]);
        $operands[] = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(1), new QtiInteger(2), new QtiInteger(3), new QtiInteger(4), new QtiInteger(5)]);
        $processor = new MatchProcessor($expression, $operands);

        $this::assertTrue($processor->process()->getValue());

        $operands = new OperandsCollection();
        $operands[] = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(5), new QtiInteger(4), new QtiInteger(3), new QtiInteger(2), new QtiInteger(1)]);
        $operands[] = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(1), new QtiInteger(6), new QtiInteger(7), new QtiInteger(8), new QtiInteger(5)]);
        $processor->setOperands($operands);
        $this::assertNotTrue($processor->process()->getValue());
    }

    public function testFile(): void
    {
        $fManager = new FileSystemFileManager();
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();

        $file1 = $fManager->createFromData('Some text', 'text/plain');
        $file2 = $fManager->createFromData('Some text', 'text/plain');

        $operands[] = $file1;
        $operands[] = $file2;
        $processor = new MatchProcessor($expression, $operands);

        $this::assertTrue($processor->process()->getValue());
        $fManager->delete($file1);
        $fManager->delete($file2);

        $operands->reset();
        $file1 = $fManager->createFromData('Some text', 'text/plain');
        $file2 = $fManager->createFromData('Other text', 'text/plain');
        $operands[] = $file1;
        $operands[] = $file2;

        $this::assertFalse($processor->process()->getValue());
        $fManager->delete($file1);
        $fManager->delete($file2);
    }

    public function testWrongBaseType(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('txt1'), new QtiIdentifier('txt2')]);
        $operands[] = new MultipleContainer(BaseType::STRING, [new QtiString('txt1'), new QtiString('txt2')]);
        $processor = new MatchProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $processor->process();
    }

    public function testWrongBaseTypeCompliance(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new MultipleContainer(BaseType::INT_OR_IDENTIFIER, [new QtiIntOrIdentifier('txt1'), new QtiIntOrIdentifier('txt2')]);
        $operands[] = new MultipleContainer(BaseType::STRING, [new QtiString('txt1'), new QtiString('txt2')]);
        $processor = new MatchProcessor($expression, $operands);

        // Unfortunately, INT_OR_IDENTIFIER cannot be considered as compliant with STRING.
        $this->expectException(ExpressionProcessingException::class);
        $processor->process();
    }

    public function testDifferentBaseTypesScalar(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiInteger(15);
        $operands[] = new QtiString('String!');
        $processor = new MatchProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testDifferentBaseTypesContainer(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(10), new QtiInteger(20), new QtiInteger(30), new QtiInteger(40)]);
        $operands[] = new MultipleContainer(BaseType::FLOAT, [new QtiFloat(10.0), new QtiFloat(20.0), new QtiFloat(30.0), new QtiFloat(40.0)]);
        $processor = new MatchProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testDifferentBaseTypesMixed(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiString('String!');
        $operands[] = new OrderedContainer(BaseType::FLOAT, [new QtiFloat(10.0), new QtiFloat(20.0)]);
        $processor = new MatchProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testDifferentCardinalitiesOne(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiString('String!');
        $operands[] = new MultipleContainer(BaseType::STRING, [new QtiString('String!')]);
        $processor = new MatchProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testDifferentCardinalitiesTwo(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new OrderedContainer(BaseType::STRING, [new QtiString('String!')]);
        $operands[] = new MultipleContainer(BaseType::STRING, [new QtiString('String!')]);
        $processor = new MatchProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testDifferentCardinalitiesThree(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new OrderedContainer(BaseType::STRING, [new QtiString('String!')]);
        $operands[] = new RecordContainer(['entry1' => new QtiString('String!')]);
        $processor = new MatchProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testNotEnoughOperands(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiInteger(15)]);
        $this->expectException(ExpressionProcessingException::class);
        $processor = new MatchProcessor($expression, $operands);
    }

    public function testTooMuchOperands(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiInteger(25), new QtiInteger(25), new QtiInteger(25)]);
        $this->expectException(ExpressionProcessingException::class);
        $processor = new MatchProcessor($expression, $operands);
    }

    public function testNullScalar(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiFloat(15.0), null]);
        $processor = new MatchProcessor($expression, $operands);
        $this::assertNull($processor->process());
    }

    public function testNullContainer(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new MultipleContainer(BaseType::INTEGER, [new QtiInteger(10), new QtiInteger(20)]);
        $operands[] = new MultipleContainer(BaseType::INTEGER);
        $processor = new MatchProcessor($expression, $operands);
        $this::assertNull($processor->process());
    }

    /**
     * @return QtiComponent
     * @throws MarshallerNotFoundException
     */
    private function createFakeExpression(): QtiComponent
    {
        return $this->createComponentFromXml('
			<match>
				<baseValue baseType="integer">10</baseValue>
				<baseValue baseType="integer">11</baseValue>
			</match>
		');
    }
}
