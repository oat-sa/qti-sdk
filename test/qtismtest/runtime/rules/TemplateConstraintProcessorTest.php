<?php

namespace qtismtest\runtime\rules;

use qtism\runtime\rules\RuleProcessingException;
use qtism\runtime\rules\TemplateConstraintProcessor;
use qtismtest\QtiSmTestCase;

/**
 * Class TemplateConstraintProcessorTest
 */
class TemplateConstraintProcessorTest extends QtiSmTestCase
{
    public function testTemplateConstraintNullResult(): void
    {
        $rule = $this->createComponentFromXml('
		    <templateConstraint>
		        <null/>
		    </templateConstraint>
		');
        $processor = new TemplateConstraintProcessor($rule);

        $this->expectException(RuleProcessingException::class);
        $this->expectExceptionMessage('Unsatisfied Template Constraint.');

        $processor->process();
    }

    public function testTemplateConstraintFalseResult(): void
    {
        $rule = $this->createComponentFromXml('
	        <templateConstraint>
                <baseValue baseType="boolean">false</baseValue>
	        </templateConstraint>
	    ');

        $processor = new TemplateConstraintProcessor($rule);

        $this->expectException(RuleProcessingException::class);
        $this->expectExceptionMessage('Unsatisfied Template Constraint.');

        $processor->process();
    }

    public function testTemplateConstraintEmptyStringResult(): void
    {
        $rule = $this->createComponentFromXml('
	        <templateConstraint>
                <baseValue baseType="string"></baseValue>
	        </templateConstraint>
	    ');

        $processor = new TemplateConstraintProcessor($rule);

        $this->expectException(RuleProcessingException::class);
        $this->expectExceptionMessage('Unsatisfied Template Constraint.');

        $processor->process();
    }

    public function testTemplateConstraintEmptyContainerResult(): void
    {
        $rule = $this->createComponentFromXml('
	        <templateConstraint>
                <multiple/>
	        </templateConstraint>
	    ');

        $processor = new TemplateConstraintProcessor($rule);

        $this->expectException(RuleProcessingException::class);
        $this->expectExceptionMessage('Unsatisfied Template Constraint.');

        $processor->process();
    }

    public function testTemplateConstraintSatisfied(): void
    {
        $rule = $this->createComponentFromXml('
	        <templateConstraint>
                <baseValue baseType="boolean">true</baseValue>
	        </templateConstraint>
	    ');

        $processor = new TemplateConstraintProcessor($rule);
        $processor->process();

        // Nothing should happen, because the templateConstraint is satisfied.
        $this::assertTrue(true, 'The template constraint should have been satisfied.');
    }
}
