<?php

namespace qtismtest\runtime\expressions\operators;

use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPoint;
use qtism\common\enums\BaseType;
use qtism\data\QtiComponent;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\RecordContainer;
use qtism\runtime\expressions\operators\FieldValueProcessor;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\ExpressionProcessingException;

/**
 * Class FieldValueProcessorTest
 */
class FieldValueProcessorTest extends QtiSmTestCase
{
    public function testNotEnoughOperands(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $this->expectException(ExpressionProcessingException::class);
        $processor = new FieldValueProcessor($expression, $operands);
    }

    public function testTooMuchOperands(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new RecordContainer();
        $operands[] = new RecordContainer();
        $this->expectException(ExpressionProcessingException::class);
        $processor = new FieldValueProcessor($expression, $operands);
    }

    public function testNullOne(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();

        // unexisting field in record.
        $operands[] = new RecordContainer();
        $processor = new FieldValueProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertNull($result);
    }

    public function testNullTwo(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        // null value as operand.
        $operands[] = null;
        $this->expectException(ExpressionProcessingException::class);
        $processor = new FieldValueProcessor($expression, $operands);
        $result = $processor->process();
    }

    public function testWrongCardinalityOne(): void
    {
        // primitive PHP.
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiInteger(10);
        $processor = new FieldValueProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongCardinalityTwo(): void
    {
        // primitive QTI (Point, Duration, ...)
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new QtiPoint(1, 2);
        $processor = new FieldValueProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongCardinalityThree(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection();
        $operands[] = new MultipleContainer(BaseType::POINT, [new QtiPoint(1, 2)]);

        // Wrong container (Multiple, Ordered)
        $processor = new FieldValueProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testFieldValue(): void
    {
        $expression = $this->createFakeExpression('B');

        $operands = new OperandsCollection();
        $operands[] = new RecordContainer(['A' => new QtiInteger(1), 'B' => new QtiInteger(2), 'C' => new QtiInteger(3)]);
        $processor = new FieldValueProcessor($expression, $operands);

        $result = $processor->process();
        $this::assertEquals(2, $result->getValue());

        $expression = $this->createFakeExpression('D');
        $processor->setExpression($expression);
        $result = $processor->process();
        $this::assertNull($result);
    }

    /**
     * @param string $identifier
     * @return QtiComponent
     * @throws MarshallerNotFoundException
     */
    public function createFakeExpression($identifier = ''): QtiComponent
    {
        // The following XML Component creation
        // underlines the need of a <record> operator... :)
        // -> <multiple> used here just for the example,
        // this is not valid.
        if (empty($identifier)) {
            $identifier = 'identifier1';
        }

        return $this->createComponentFromXml('
			<fieldValue fieldIdentifier="' . $identifier . '">
				<multiple>
					<baseValue baseType="boolean">true</baseValue>
					<baseValue baseType="boolean">false</baseValue>
				</multiple>
			</fieldValue>
		');
    }
}
