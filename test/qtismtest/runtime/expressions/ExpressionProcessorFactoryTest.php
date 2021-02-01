<?php

namespace qtismtest\runtime\expressions;

use qtism\runtime\expressions\ExpressionProcessorFactory;
use qtismtest\QtiSmTestCase;
use qtism\runtime\expressions\BaseValueProcessor;
use RuntimeException;

/**
 * Class ExpressionProcessorFactoryTest
 */
class ExpressionProcessorFactoryTest extends QtiSmTestCase
{
    public function testCreateProcessor()
    {
        $expression = $this->createComponentFromXml('<baseValue baseType="integer">1337</baseValue>');

        $factory = new ExpressionProcessorFactory();
        $processor = $factory->createProcessor($expression);
        $this::assertInstanceOf(BaseValueProcessor::class, $processor);
        $this::assertEquals('baseValue', $processor->getExpression()->getQtiClassName());
    }

    public function testCreateProcessorNoProcessor()
    {
        $expression = $this->createComponentFromXml('
			<sum>
				<baseValue baseType="integer">1</baseValue>
				<baseValue baseType="integer">1</baseValue>
			</sum>');

        $factory = new ExpressionProcessorFactory();
        $this->expectException(RuntimeException::class);
        $processor = $factory->createProcessor($expression);
    }
}
