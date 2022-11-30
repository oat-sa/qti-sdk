<?php

declare(strict_types=1);

namespace qtismtest\runtime\rules;

use qtism\runtime\rules\ExitResponseProcessor;
use qtism\runtime\rules\RuleProcessingException;
use qtismtest\QtiSmTestCase;

/**
 * Class ExitResponseProcessorTest
 */
class ExitResponseProcessorTest extends QtiSmTestCase
{
    public function testExitResponse(): void
    {
        $rule = $this->createComponentFromXml('<exitResponse/>');
        $processor = new ExitResponseProcessor($rule);

        try {
            $processor->process();

            // An exception must always be raised!
            $this::assertTrue(false);
        } catch (RuleProcessingException $e) {
            $this::assertEquals(RuleProcessingException::EXIT_RESPONSE, $e->getCode());
        }
    }
}
