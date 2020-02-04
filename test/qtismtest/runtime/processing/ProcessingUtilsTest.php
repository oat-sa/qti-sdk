<?php

namespace qtismtest\runtime\processing;

use qtismtest\QtiSmTestCase;
use qtism\data\processing\TemplateProcessing;
use qtism\runtime\processing\Utils;

class ProcessingUtilsTest extends QtiSmTestCase
{
    
    /**
     * @dataProvider impactedVariablesProvider
     *
     * @param TemplateProcessing $templateProcessing
     * @param array $expectedIdentifiers
     */
    public function testImpactedVariables(TemplateProcessing $templateProcessing, array $expectedIdentifiers)
    {
        $this->assertEquals(
            $expectedIdentifiers,
            Utils::templateProcessingImpactedVariables($templateProcessing)
        );
    }
    
    public function impactedVariablesProvider()
    {
        $data = array();
        
        $templateProcessing = $this->createComponentFromXml('
            <templateProcessing>
                <exitTemplate/>
            </templateProcessing>
        ');
        $identifiers = array();
        $data[] = array($templateProcessing, $identifiers);
        
        $templateProcessing = $this->createComponentFromXml('
            <templateProcessing>
                <setTemplateValue identifier="TPL">
                    <baseValue baseType="boolean">true</baseValue>        
                </setTemplateValue>
                <setCorrectResponse identifier="RESP">
                    <baseValue baseType="boolean">true</baseValue>        
                </setCorrectResponse>
                <setDefaultValue identifier="VAR">
                    <baseValue baseType="boolean">true</baseValue>        
                </setDefaultValue>
            </templateProcessing>
        ');
        $identifiers = array('TPL', 'RESP', 'VAR');
        $data[] = array($templateProcessing, $identifiers);
        
        $templateProcessing = $this->createComponentFromXml('
            <templateProcessing>
                <templateCondition>
                    <templateIf>
                        <baseValue baseType="boolean">true</baseValue>
                        <setTemplateValue identifier="TPL">
                            <baseValue baseType="boolean">true</baseValue>        
                        </setTemplateValue>
                        <setCorrectResponse identifier="RESP">
                            <baseValue baseType="boolean">true</baseValue>        
                        </setCorrectResponse>
                        <setDefaultValue identifier="VAR">
                            <baseValue baseType="boolean">true</baseValue>        
                        </setDefaultValue>    
                    </templateIf>
                    <templateElse>
                        <setTemplateValue identifier="TPL">
                            <baseValue baseType="boolean">false</baseValue>        
                        </setTemplateValue>
                        <setCorrectResponse identifier="RESP">
                            <baseValue baseType="boolean">false</baseValue>        
                        </setCorrectResponse>
                        <setDefaultValue identifier="VAR">
                            <baseValue baseType="boolean">false</baseValue>        
                        </setDefaultValue>    
                    </templateElse>
                </templateCondition>
            </templateProcessing>
        ');
        $identifiers = array('TPL', 'RESP', 'VAR');
        $data[] = array($templateProcessing, $identifiers);
        
        return $data;
    }
}
