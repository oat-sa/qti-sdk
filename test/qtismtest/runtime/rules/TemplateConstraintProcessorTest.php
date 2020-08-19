<?php

namespace qtismtest\runtime\rules;

use qtism\runtime\rules\RuleProcessingException;
use qtism\runtime\rules\TemplateConstraintProcessor;
use qtismtest\QtiSmTestCase;

class TemplateConstraintProcessorTest extends QtiSmTestCase
{
    public function testTemplateConstraintNullResult()
    {
        $rule = $this->createComponentFromXml('
		    <templateConstraint>
		        <null/>
		    </templateConstraint>
		');
        $processor = new TemplateConstraintProcessor($rule);

        $this->setExpectedException(
            RuleProcessingException::class,
            'Unsatisfied Template Constraint.',
            RuleProcessingException::TEMPLATE_CONSTRAINT_UNSATISFIED
        );

        $processor->process();
    }

    public function testTemplateConstraintFalseResult()
    {
        $rule = $this->createComponentFromXml('
	        <templateConstraint>
                <baseValue baseType="boolean">false</baseValue>
	        </templateConstraint>
	    ');

        $processor = new TemplateConstraintProcessor($rule);

        $this->setExpectedException(
            RuleProcessingException::class,
            'Unsatisfied Template Constraint.',
            RuleProcessingException::TEMPLATE_CONSTRAINT_UNSATISFIED
        );

        $processor->process();
    }

    public function testTemplateConstraintEmptyStringResult()
    {
        $rule = $this->createComponentFromXml('
	        <templateConstraint>
                <baseValue baseType="string"></baseValue>
	        </templateConstraint>
	    ');

        $processor = new TemplateConstraintProcessor($rule);

        $this->setExpectedException(
            RuleProcessingException::class,
            'Unsatisfied Template Constraint.',
            RuleProcessingException::TEMPLATE_CONSTRAINT_UNSATISFIED
        );

        $processor->process();
    }

    public function testTemplateConstraintEmptyContainerResult()
    {
        $rule = $this->createComponentFromXml('
	        <templateConstraint>
                <multiple/>
	        </templateConstraint>
	    ');

        $processor = new TemplateConstraintProcessor($rule);

        $this->setExpectedException(
            RuleProcessingException::class,
            'Unsatisfied Template Constraint.',
            RuleProcessingException::TEMPLATE_CONSTRAINT_UNSATISFIED
        );

        $processor->process();
    }

    public function testTemplateConstraintSatisfied()
    {
        $rule = $this->createComponentFromXml('
	        <templateConstraint>
                <baseValue baseType="boolean">true</baseValue>
	        </templateConstraint>
	    ');

        $processor = new TemplateConstraintProcessor($rule);
        $processor->process();

        // Nothing should happen, because the templateConstraint is satisfied.
        $this->assertTrue(true, 'The template constraint should have been satisfied.');
    }
}
