<?php
namespace qtismtest\runtime\processing;

use qtismtest\QtiSmTestCase;
use qtism\runtime\common\TemplateVariable;
use qtism\runtime\processing\TemplateProcessingEngine;
use qtism\common\datatypes\Integer;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\State;

class TemplateProcessingEngineTest extends QtiSmTestCase {
	
    public function testWrongInput() {
        $component = $this->createComponentFromXml('
            <outcomeProcessing>
                <exitTest/>
            </outcomeProcessing>
        ');
        $this->setExpectedException(
            '\\InvalidArgumentException', 
            'The TemplateProcessing class only accepts TemplateProcessing objects to be executed.');
        $templateProcessing = new TemplateProcessingEngine($component);
    }
    
    public function testVeryBasic() {
        $component = $this->createComponentFromXml('
            <templateProcessing>
                <setTemplateValue identifier="TEMPLATE">
                    <baseValue baseType="integer">1337</baseValue>
                </setTemplateValue>
            </templateProcessing>
        ');
        
        $state = new State(
            array(new TemplateVariable('TEMPLATE', Cardinality::SINGLE, BaseType::INTEGER, new Integer(1336)))                
        );
        
        $engine = new TemplateProcessingEngine($component, $state);
        $engine->process();
        
        $this->assertEquals(1337, $state['TEMPLATE']->getValue());
    }
    
    /**
     * @depends testVeryBasic
     */
    public function testExitTemplate() {
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
            array(new TemplateVariable('TEMPLATE', Cardinality::SINGLE, BaseType::INTEGER))
        );
        
        $engine = new TemplateProcessingEngine($component, $state);
        $engine->process();
        
        $this->assertEquals(1336, $state['TEMPLATE']->getValue());
    }
}