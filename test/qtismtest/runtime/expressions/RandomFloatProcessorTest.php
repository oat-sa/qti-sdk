<?php

namespace qtismtest\runtime\expressions;

use qtism\common\datatypes\QtiFloat;
use qtism\runtime\expressions\RandomFloatProcessor;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\ExpressionProcessingException;

/**
 * Class RandomFloatProcessorTest
 */
class RandomFloatProcessorTest extends QtiSmTestCase
{
    public function testSimple()
    {
        $expression = $this->createComponentFromXml('<randomFloat max="100.34"/>');
        $processor = new RandomFloatProcessor($expression);
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertLessThanOrEqual(100.34, $result->getValue());
        $this::assertGreaterThanOrEqual(0, $result->getValue());

        $expression = $this->createComponentFromXml('<randomFloat min="-2000" max="-1000"/>');
        $processor->setExpression($expression);
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertGreaterThanOrEqual(-2000, $result->getValue());
        $this::assertLessThanOrEqual(-1000, $result->getValue());

        $expression = $this->createComponentFromXml('<randomFloat min="100" max="2430.6666"/>');
        $processor->setExpression($expression);
        $result = $processor->process();
        $this::assertInstanceOf(QtiFloat::class, $result);
        $this::assertGreaterThanOrEqual(100, $result->getValue());
        $this::assertLessThanOrEqual(2430.6666, $result->getValue());
    }

    public function testMinGreaterThanMax()
    {
        $expression = $this->createComponentFromXml('<randomFloat min="133.2" max="25.3"/>');
        $processor = new RandomFloatProcessor($expression);
        $processor->setExpression($expression);

        $this->expectException(ExpressionProcessingException::class);
        $result = $processor->process();
    }
}
