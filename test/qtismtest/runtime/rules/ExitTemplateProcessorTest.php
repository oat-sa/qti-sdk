<?php

namespace qtismtest\runtime\rules;

use qtism\runtime\rules\ExitTemplateProcessor;
use qtism\runtime\rules\RuleProcessingException;
use qtismtest\QtiSmTestCase;

class ExitTemplateProcessorTest extends QtiSmTestCase
{
    public function testExitTest()
    {
        $rule = $this->createComponentFromXml('<exitTemplate/>');
        $processor = new ExitTemplateProcessor($rule);

        $this->expectException(RuleProcessingException::class);
        $this->expectExceptionMessage('Termination of Template Processing.');

        $processor->process();
    }
}
