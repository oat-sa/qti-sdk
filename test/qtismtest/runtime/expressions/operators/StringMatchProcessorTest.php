<?php

declare(strict_types=1);

namespace qtismtest\runtime\expressions\operators;

use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\data\QtiComponent;
use qtism\data\storage\xml\marshalling\MarshallerNotFoundException;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\expressions\operators\OperandsCollection;
use qtism\runtime\expressions\operators\StringMatchProcessor;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\ExpressionProcessingException;

/**
 * Class StringMatchProcessorTest
 */
class StringMatchProcessorTest extends QtiSmTestCase
{
    public function testStringMatch(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiString('one'), new QtiString('one')]);
        $processor = new StringMatchProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());

        $operands = new OperandsCollection([new QtiString('one'), new QtiString('oNe')]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertFalse($result->getValue());

        $processor->setExpression($this->createFakeExpression(false));
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());

        // Binary-safe?
        $processor->setExpression($this->createFakeExpression(true));
        $operands = new OperandsCollection([new QtiString('它的工作原理'), new QtiString('它的工作原理')]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertTrue($result->getValue());

        $operands = new OperandsCollection([new QtiString('它的工作原理'), new QtiString('它的原理')]);
        $processor->setOperands($operands);
        $result = $processor->process();
        $this::assertInstanceOf(QtiBoolean::class, $result);
        $this::assertFalse($result->getValue());
    }

    public function testNull(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiString(''), null]);
        $processor = new StringMatchProcessor($expression, $operands);
        $result = $processor->process();
        $this::assertNull($result);
    }

    public function testWrongCardinality(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiString('String!'), new MultipleContainer(BaseType::STRING, [new QtiString('String!')])]);
        $processor = new StringMatchProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testWrongBaseType(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiString('String!'), new QtiInteger(25)]);
        $processor = new StringMatchProcessor($expression, $operands);
        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }

    public function testNotEnoughOperands(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiString('String!')]);
        $this->expectException(ExpressionProcessingException::class);
        $processor = new StringMatchProcessor($expression, $operands);
    }

    public function testTooMuchOperands(): void
    {
        $expression = $this->createFakeExpression();
        $operands = new OperandsCollection([new QtiString('String!'), new QtiString('String!'), new QtiString('String!')]);
        $this->expectException(ExpressionProcessingException::class);
        $processor = new StringMatchProcessor($expression, $operands);
    }

    /**
     * @param bool $caseSensitive
     * @return QtiComponent
     * @throws MarshallerNotFoundException
     */
    public function createFakeExpression($caseSensitive = true): QtiComponent
    {
        $str = ($caseSensitive === true) ? 'true' : 'false';

        return $this->createComponentFromXml('
			<stringMatch caseSensitive="' . $str . '">
				<baseValue baseType="string">This does</baseValue>
				<baseValue baseType="string">not match</baseValue>
			</stringMatch>
		');
    }
}
