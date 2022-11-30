<?php

declare(strict_types=1);

namespace qtismtest\runtime\processing;

use InvalidArgumentException;
use qtism\common\datatypes\QtiInteger;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\State;
use qtism\runtime\common\TemplateVariable;
use qtism\runtime\processing\TemplateProcessingEngine;
use qtismtest\QtiSmTestCase;

/**
 * Class TemplateProcessingEngineTest
 */
class TemplateProcessingEngineTest extends QtiSmTestCase
{
    public function testWrongInput(): void
    {
        $component = $this->createComponentFromXml('
            <outcomeProcessing>
                <exitTest/>
            </outcomeProcessing>
        ');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The TemplateProcessing class only accepts TemplateProcessing objects to be executed.');
        $templateProcessing = new TemplateProcessingEngine($component);
    }

    public function testVeryBasic(): void
    {
        $component = $this->createComponentFromXml('
            <templateProcessing>
                <setTemplateValue identifier="TEMPLATE">
                    <baseValue baseType="integer">1337</baseValue>
                </setTemplateValue>
            </templateProcessing>
        ');

        $state = new State(
            [new TemplateVariable('TEMPLATE', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(1336))]
        );

        $engine = new TemplateProcessingEngine($component, $state);
        $engine->process();

        $this::assertEquals(1337, $state['TEMPLATE']->getValue());
    }

    /**
     * @depends testVeryBasic
     */
    public function testExitTemplate(): void
    {
        $component = $this->createComponentFromXml('
            <templateProcessing>
                <setTemplateValue identifier="TEMPLATE">
                    <baseValue baseType="integer">1336</baseValue>
                </setTemplateValue>        
                <exitTemplate/>
                <setTemplateValue identifier="TEMPLATE">
                    <baseValue baseType="integer">1337</baseValue>
                </setTemplateValue>
            </templateProcessing>
        ');

        $state = new State(
            [new TemplateVariable('TEMPLATE', Cardinality::SINGLE, BaseType::INTEGER)]
        );

        $engine = new TemplateProcessingEngine($component, $state);
        $engine->process();

        $this::assertEquals(1336, $state['TEMPLATE']->getValue());
    }

    /**
     * @depends testVeryBasic
     */
    public function testTemplateConstraintImpossibleWithTemplateVariableOnly(): void
    {
        $component = $this->createComponentFromXml('
            <templateProcessing>
                <setTemplateValue identifier="TEMPLATE">
                    <baseValue baseType="integer">0</baseValue>
                </setTemplateValue>
                <templateConstraint>
                    <gt>
                        <variable identifier="TEMPLATE"/>
                        <baseValue baseType="integer">0</baseValue>
                    </gt>
                </templateConstraint>
            </templateProcessing>
        ');

        $var = new TemplateVariable('TEMPLATE', Cardinality::SINGLE, BaseType::INTEGER);
        $var->setDefaultValue(new QtiInteger(-1));
        $state = new State(
            [$var]
        );

        // The <templateConstraint> will never be satisfied.
        // We should then find the default value in TEMPLATE.
        $engine = new TemplateProcessingEngine($component, $state);
        $engine->process();

        $this::assertEquals(-1, $state['TEMPLATE']->getValue());
    }
}
