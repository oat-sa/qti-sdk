<?php

namespace qtismtest\runtime\expressions;

use qtism\common\datatypes\QtiFloat;
use qtism\runtime\expressions\MathConstantProcessor;
use qtismtest\QtiSmTestCase;

class MathConstantProcessorTest extends QtiSmTestCase
{
    public function testSimple()
    {
        $mathConstantExpr = $this->createComponentFromXml('<mathConstant name="e"/>');
        $mathConstantProcessor = new MathConstantProcessor($mathConstantExpr);

        $result = $mathConstantProcessor->process();
        $this->assertInstanceOf(QtiFloat::class, $result);
        $this->assertEquals(M_E, $result->getValue());

        $mathConstantExpr = $this->createComponentFromXml('<mathConstant name="pi"/>');
        $mathConstantProcessor->setExpression($mathConstantExpr);
        $result = $mathConstantProcessor->process();
        $this->assertInstanceOf(QtiFloat::class, $result);
        $this->assertEquals(M_PI, $result->getValue());
    }
}
