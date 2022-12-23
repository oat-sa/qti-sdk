<?php

namespace qtismtest\runtime\expressions;

use qtism\common\datatypes\QtiPoint;
use qtism\runtime\expressions\BaseValueProcessor;
use qtismtest\QtiSmTestCase;

/**
 * Class BaseValueProcessorTest
 */
class BaseValueProcessorTest extends QtiSmTestCase
{
    public function testBaseValue(): void
    {
        $baseValue = $this->createComponentFromXml('<baseValue baseType="boolean">true</baseValue>');
        $baseValueProcessor = new BaseValueProcessor($baseValue);
        $this::assertTrue($baseValueProcessor->process()->getValue());

        $baseValue = $this->createComponentFromXml('<baseValue baseType="point">150 130</baseValue>');
        $baseValueProcessor->setExpression($baseValue);
        $this::assertTrue($baseValueProcessor->process()->equals(new QtiPoint(150, 130)));
    }
}
