<?php

namespace qtismtest\runtime\expressions;

use qtism\runtime\expressions\NullProcessor;
use qtismtest\QtiSmTestCase;

/**
 * Class NullProcessorTest
 */
class NullProcessorTest extends QtiSmTestCase
{
    public function testNullProcessor()
    {
        $nullExpression = $this->createComponentFromXml('<null/>');
        $nullProcessor = new NullProcessor($nullExpression);
        $result = $nullProcessor->process();
        $this::assertTrue($result === null);
    }
}
