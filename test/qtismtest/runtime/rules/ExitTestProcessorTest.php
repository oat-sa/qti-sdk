<?php

namespace qtismtest\runtime\rules;

use qtism\runtime\rules\ExitTestProcessor;
use qtism\runtime\rules\RuleProcessingException;
use qtismtest\QtiSmTestCase;

/**
 * Class ExitTestProcessorTest
 */
class ExitTestProcessorTest extends QtiSmTestCase
{
    public function testExitTest(): void
    {
        $rule = $this->createComponentFromXml('<exitTest/>');
        $processor = new ExitTestProcessor($rule);

        try {
            $processor->process();

            // An exception must always be raised!
            $this::assertTrue(false);
        } catch (RuleProcessingException $e) {
            $this::assertEquals(RuleProcessingException::EXIT_TEST, $e->getCode());
        }
    }
}
