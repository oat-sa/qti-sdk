<?php

namespace qtismtest\data\storage\xml\marshalling;

use qtismtest\QtiSmTestCase;
use qtism\data\content\interactions\InlineChoiceInteraction;
use qtism\data\content\TextRun;
use qtism\data\content\TextOrVariableCollection;
use qtism\data\content\interactions\InlineChoice;
use qtism\data\content\interactions\InlineChoiceCollection;
use DOMDocument;

class InlineChoiceInteractionMarshallerTest extends QtiSmTestCase
{
    public function testMarshall21()
    {
        $inlineChoices = new InlineChoiceCollection();
        
        $choice = new InlineChoice('inlineChoice1');
        $choice->setFixed(true);
        $choice->setContent(new TextOrVariableCollection(array(new TextRun('Option1'))));
        $inlineChoices[] = $choice;
        
        $choice = new InlineChoice('inlineChoice2');
        $choice->setContent(new TextOrVariableCollection(array(new TextRun('Option2'))));
        $inlineChoices[] = $choice;
        
        $choice = new InlineChoice('inlineChoice3');
        $choice->setContent(new TextOrVariableCollection(array(new TextRun('Option3'))));
        $inlineChoices[] = $choice;
        
        $inlineChoiceInteraction = new InlineChoiceInteraction('RESPONSE', $inlineChoices);
        $inlineChoiceInteraction->setShuffle(true);
        $inlineChoiceInteraction->setRequired(true);
        $inlineChoiceInteraction->setXmlBase('/home/jerome');
        
        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($inlineChoiceInteraction)->marshall($inlineChoiceInteraction);
        
        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<inlineChoiceInteraction responseIdentifier="RESPONSE" shuffle="true" required="true" xml:base="/home/jerome"><inlineChoice identifier="inlineChoice1" fixed="true">Option1</inlineChoice><inlineChoice identifier="inlineChoice2">Option2</inlineChoice><inlineChoice identifier="inlineChoice3">Option3</inlineChoice></inlineChoiceInteraction>', $dom->saveXML($element));
    }
    
    public function testMarshall20()
    {
        // check that suffled systematically out and no required attribute.
        $inlineChoices = new InlineChoiceCollection();
         
        $choice = new InlineChoice('inlineChoice1');
        $choice->setFixed(true);
        $choice->setContent(new TextOrVariableCollection(array(new TextRun('Option1'))));
        $inlineChoices[] = $choice;
         
        $inlineChoiceInteraction = new InlineChoiceInteraction('RESPONSE', $inlineChoices);
        $inlineChoiceInteraction->setShuffle(false);
        $inlineChoiceInteraction->setRequired(true);
         
        $element = $this->getMarshallerFactory('2.0.0')->createMarshaller($inlineChoiceInteraction)->marshall($inlineChoiceInteraction);
        
        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<inlineChoiceInteraction responseIdentifier="RESPONSE" shuffle="false"><inlineChoice identifier="inlineChoice1" fixed="true">Option1</inlineChoice></inlineChoiceInteraction>', $dom->saveXML($element));
    }
    
    public function testUnmarshall21()
    {
        $element = $this->createDOMElement('
            <inlineChoiceInteraction responseIdentifier="RESPONSE" shuffle="true" required="true" xml:base="/home/jerome">
                <inlineChoice identifier="inlineChoice1" fixed="true">Option1</inlineChoice>
                <inlineChoice identifier="inlineChoice2">Option2</inlineChoice>
                <inlineChoice identifier="inlineChoice1">Option1</inlineChoice>
            </inlineChoiceInteraction>
        ');
        
        $inlineChoiceInteraction = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf('qtism\\data\\content\\interactions\\InlineChoiceInteraction', $inlineChoiceInteraction);
        $this->assertEquals('RESPONSE', $inlineChoiceInteraction->getResponseIdentifier());
        $this->assertTrue($inlineChoiceInteraction->mustShuffle());
        $this->assertTrue($inlineChoiceInteraction->isRequired());
        $this->assertEquals(3, count($inlineChoiceInteraction->getComponentsByClassName('inlineChoice')));
        $this->assertEquals('/home/jerome', $inlineChoiceInteraction->getXmlBase());
    }
    
    /**
     * @depends testUnmarshall21
     */
    public function testUnmarshall21NoInlineChoices()
    {
        $element = $this->createDOMElement('
            <inlineChoiceInteraction responseIdentifier="RESPONSE" shuffle="true" required="true">
            </inlineChoiceInteraction>
        ');
        
        $this->setExpectedException(
            'qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException',
            "An 'inlineChoiceInteraction' element must contain at least 1 'inlineChoice' elements, none given."
        );
        
        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }
    
    /**
     * @depends testUnmarshall21
     */
    public function testUnmarshall21InvalidResponseIdentifier()
    {
        $element = $this->createDOMElement('
            <inlineChoiceInteraction responseIdentifier="9_RESPONSE" shuffle="true" required="true">
                <inlineChoice identifier="inlineChoice1" fixed="true">Option1</inlineChoice>
                <inlineChoice identifier="inlineChoice2">Option2</inlineChoice>
                <inlineChoice identifier="inlineChoice1">Option1</inlineChoice>
            </inlineChoiceInteraction>
        ');
        
        $this->setExpectedException(
            'qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException',
            "The value of the attribute 'responseIdentifier' for element 'inlineChoiceInteraction' is not a valid identifier."
        );
        
        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }
    
    /**
     * @depends testUnmarshall21
     */
    public function testUnmarshall21NoResponseIdentifier()
    {
        $element = $this->createDOMElement('
            <inlineChoiceInteraction shuffle="true" required="true">
                <inlineChoice identifier="inlineChoice1" fixed="true">Option1</inlineChoice>
                <inlineChoice identifier="inlineChoice2">Option2</inlineChoice>
                <inlineChoice identifier="inlineChoice1">Option1</inlineChoice>
            </inlineChoiceInteraction>
        ');
        
        $this->setExpectedException(
            'qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException',
            "The mandatory 'responseIdentifier' attribute is missing from the 'inlineChoiceInteraction' element."
        );
        
        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }
    
    public function testUnmarshall20()
    {
        // Check required is not taken into account.
        // Check shuffle is always in the output.
        $element = $this->createDOMElement('
            <inlineChoiceInteraction responseIdentifier="RESPONSE" shuffle="true" required="true">
                <inlineChoice identifier="inlineChoice1" fixed="true">Option1</inlineChoice>
                <inlineChoice identifier="inlineChoice2">Option2</inlineChoice>
                <inlineChoice identifier="inlineChoice1">Option1</inlineChoice>
            </inlineChoiceInteraction>
        ');
        
        $inlineChoiceInteraction = $this->getMarshallerFactory('2.0.0')->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf('qtism\\data\\content\\interactions\\InlineChoiceInteraction', $inlineChoiceInteraction);
        $this->assertEquals('RESPONSE', $inlineChoiceInteraction->getResponseIdentifier());
        $this->assertTrue($inlineChoiceInteraction->mustShuffle());
        $this->assertFalse($inlineChoiceInteraction->isRequired());
        $this->assertEquals(3, count($inlineChoiceInteraction->getComponentsByClassName('inlineChoice')));
    }
    
    /**
     * @depends testUnmarshall20
     */
    public function testUnmarshallErrorIfoShuffle20()
    {
        $expectedMsg = "The mandatory 'shuffle' attribute is missing from the 'inlineChoiceInteraction' element.";
        $this->setExpectedException('\\qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException', $expectedMsg);
        
        $element = $this->createDOMElement('
            <inlineChoiceInteraction responseIdentifier="RESPONSE">
                <inlineChoice identifier="inlineChoice1" fixed="true">Option1</inlineChoice>
            </inlineChoiceInteraction>
        ');
         
        $inlineChoiceInteraction = $this->getMarshallerFactory('2.0.0')->createMarshaller($element)->unmarshall($element);
    }
}
