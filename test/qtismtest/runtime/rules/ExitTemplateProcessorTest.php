<?php

declare(strict_types=1);

namespace qtismtest\runtime\rules;

use qtism\runtime\rules\ExitTemplateProcessor;
use qtism\runtime\rules\RuleProcessingException;
use qtismtest\QtiSmTestCase;

/**
 * Class ExitTemplateProcessorTest
 */
class ExitTemplateProcessorTest extends QtiSmTestCase
{
    public function testExitTest(): void
    {
        $rule = $this->createComponentFromXml('<exitTemplate/>');
        $processor = new ExitTemplateProcessor($rule);

        $this->expectException(RuleProcessingException::class);
        $this->expectExceptionMessage('Termination of Template Processing.');

        $processor->process();
    }
}
