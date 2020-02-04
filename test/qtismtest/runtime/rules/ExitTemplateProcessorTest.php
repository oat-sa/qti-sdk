<?php

namespace qtismtest\runtime\rules;

use qtismtest\QtiSmTestCase;
use qtism\runtime\rules\ExitTemplateProcessor;
use qtism\runtime\rules\RuleProcessingException;

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
