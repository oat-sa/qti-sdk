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

        $this->setExpectedException(
            'qtism\\runtime\\rules\\RuleProcessingException',
            'Termination of Template Processing.',
            RuleProcessingException::EXIT_TEMPLATE
        );

        $processor->process();
    }
}
