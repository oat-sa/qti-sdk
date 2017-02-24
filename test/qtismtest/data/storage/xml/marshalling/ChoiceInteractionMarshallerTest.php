<?php
namespace qtismtest\data\storage\xml\marshalling;

use qtismtest\QtiSmTestCase;
use qtism\data\content\interactions\Prompt;
use qtism\data\content\interactions\Orientation;
use qtism\data\content\interactions\ChoiceInteraction;
use qtism\data\content\TextRun;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\interactions\SimpleChoice;
use qtism\data\content\interactions\SimpleChoiceCollection;
use \DOMDocument;

class ChoiceInteractionMarshallerTest extends QtiSmTestCase {

	public function testMarshall21() {
		
        $choice1 = new SimpleChoice('choice_1');
        $choice1->setContent(new FlowStaticCollection(array(new TextRun('Choice #1'))));
        $choice2 = new SimpleChoice('choice_2');
        $choice2->setContent(new FlowStaticCollection(array(new TextRun('Choice #2'))));
        $choices = new SimpleChoiceCollection(array($choice1, $choice2));
        
        $component = new ChoiceInteraction('RESPONSE', $choices);
        $prompt = new Prompt();
        $prompt->setContent(new FlowStaticCollection(array(new TextRun('Prompt...'))));
        $component->setPrompt($prompt);
        $component->setXmlBase('/home/jerome');
        
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);
        
        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<choiceInteraction responseIdentifier="RESPONSE" xml:base="/home/jerome"><prompt>Prompt...</prompt><simpleChoice identifier="choice_1">Choice #1</simpleChoice><simpleChoice identifier="choice_2">Choice #2</simpleChoice></choiceInteraction>', $dom->saveXML($element));
	}
	
	public function testUnmarshall21() {
        $element = $this->createDOMElement('
            <choiceInteraction responseIdentifier="RESPONSE" xml:base="/home/jerome">
              <prompt>Prompt...</prompt>
              <simpleChoice identifier="choice_1">Choice #1</simpleChoice>
              <simpleChoice identifier="choice_2">Choice #2</simpleChoice>
            </choiceInteraction>
        ');
        
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);
        
        $this->assertInstanceOf('qtism\\data\\content\\interactions\\ChoiceInteraction', $component);
        $this->assertEquals('RESPONSE', $component->getResponseIdentifier());
        $this->assertFalse($component->mustShuffle());
        $this->assertEquals(Orientation::VERTICAL, $component->getOrientation());
        $this->assertTrue($component->hasPrompt());
        $this->assertSame(0, $component->getMaxChoices());
        $this->assertSame(0, $component->getMinChoices());
        $this->assertEquals('/home/jerome', $component->getXmlBase());
        
        $prompt = $component->getPrompt();
        $content = $prompt->getContent();
        $this->assertEquals('Prompt...', $content[0]->getContent());
        
        $simpleChoices = $component->getSimpleChoices();
        $this->assertEquals(2, count($simpleChoices));
	}
    
    /**
     * @depends testUnmarshall21
     */
    public function testUnmarshallNoResponseIdentifier21() {
        $element = $this->createDOMElement('
            <choiceInteraction>
              <prompt>Prompt...</prompt>
              <simpleChoice identifier="choice_1">Choice #1</simpleChoice>
              <simpleChoice identifier="choice_2">Choice #2</simpleChoice>
            </choiceInteraction>
        ');
        
        $this->setExpectedException(
            'qtism\data\storage\xml\marshalling\UnmarshallingException',
            "The mandatory 'responseIdentifier' attribute is missing from the choiceInteraction element."
        );
        
        $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
	}
	
	/**
	 * @depends testUnmarshall21
	 */
	public function testUnmarshallMaxChoicesUnlimited21()
	{
	    $element = $this->createDOMElement('
            <choiceInteraction responseIdentifier="RESPONSE" maxChoices="0">
	          <prompt>Prompt...</prompt>
	          <simpleChoice identifier="choice_1">Choice #1</simpleChoice>
	          <simpleChoice identifier="choice_2">Choice #2</simpleChoice>
	        </choiceInteraction>
        ');
	    
	    $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
	    $component = $marshaller->unmarshall($element);
	    
	    $this->assertInstanceOf('qtism\\data\\content\\interactions\\ChoiceInteraction', $component);
	    $this->assertSame(0, $component->getMaxChoices());
	    $this->assertSame(0, $component->getMinChoices());
	}
	
	/**
	 * @depends testUnmarshall21
	 */
	public function testUnmarshallMinChoicesOnly21()
	{
	    $element = $this->createDOMElement('
            <choiceInteraction responseIdentifier="RESPONSE" minChoices="1">
	          <prompt>Prompt...</prompt>
	          <simpleChoice identifier="choice_1">Choice #1</simpleChoice>
	          <simpleChoice identifier="choice_2">Choice #2</simpleChoice>
	        </choiceInteraction>
        ');
	     
	    $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
	    $component = $marshaller->unmarshall($element);
	    
	    $this->assertInstanceOf('qtism\\data\\content\\interactions\\ChoiceInteraction', $component);
	    $this->assertSame(0, $component->getMaxChoices());
	    $this->assertSame(1, $component->getMinChoices());
	}
	
	public function testMarshallMinChoicesNoOutput20()
	{
	    // Aims at testing that minChoices is not output when QTI 2.0 is in force.
	    $choice1 = new SimpleChoice('choice_1');
	    $choice1->setContent(new FlowStaticCollection(array(new TextRun('Choice #1'))));
	    $choices = new SimpleChoiceCollection(array($choice1));
	    
	    $component = new ChoiceInteraction('RESPONSE', $choices);
	    $component->setMinChoices(1);
	    
	    $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($component);
	    $element = $marshaller->marshall($component);
	    
	    $dom = new DOMDocument('1.0', 'UTF-8');
	    $element = $dom->importNode($element, true);
	    $this->assertEquals('<choiceInteraction responseIdentifier="RESPONSE" shuffle="false" maxChoices="0"><simpleChoice identifier="choice_1">Choice #1</simpleChoice></choiceInteraction>', $dom->saveXML($element));
	}
	
	public function testMarshallOrientationNoOutput20()
	{
	    // Aims at testing that orientation is not output in a QTI 2.0 context.
	    $choice1 = new SimpleChoice('choice_1');
	    $choice1->setContent(new FlowStaticCollection(array(new TextRun('Choice #1'))));
	    $choices = new SimpleChoiceCollection(array($choice1));
	    
	    $component = new ChoiceInteraction('RESPONSE', $choices);
	    // Set non-default value for orientation...
	    $component->setOrientation(Orientation::HORIZONTAL);
	    
	    $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($component);
	    $element = $marshaller->marshall($component);
	    
	    $dom = new DOMDocument('1.0', 'UTF-8');
	    $element = $dom->importNode($element, true);
	    $this->assertEquals('<choiceInteraction responseIdentifier="RESPONSE" shuffle="false" maxChoices="0"><simpleChoice identifier="choice_1">Choice #1</simpleChoice></choiceInteraction>', $dom->saveXML($element));
	}
	
	public function testUnmarshallMinChoicesAvoided20()
	{
	    // Aims at testing that minChoices is not taken into account when unmarshalling
	    // in a QTI 2.0 context.
	    $element = $this->createDOMElement('
            <choiceInteraction responseIdentifier="RESPONSE" minChoices="2" maxChoices="3" shuffle="true">
	          <prompt>Prompt...</prompt>
	          <simpleChoice identifier="choice_1">Choice #1</simpleChoice>
	          <simpleChoice identifier="choice_2">Choice #2</simpleChoice>
	          <simpleChoice identifier="choice_3">Choice #2</simpleChoice>
	        </choiceInteraction>
        ');
	    
	    $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($element);
	    $component = $marshaller->unmarshall($element);
	    
	    $this->assertInstanceOf('qtism\\data\\content\\interactions\\ChoiceInteraction', $component);
	    $this->assertSame(0, $component->getMinChoices());
	}
	
	public function testUnmarshallOrientationAvoided20()
	{
	    // Aims at testing that orientation is not taken into account when unmarshalling
	    // in a QTI 2.0 context.
	    $element = $this->createDOMElement('
            <choiceInteraction responseIdentifier="RESPONSE" maxChoices="0" shuffle="false" orientation="horizontal">
	          <prompt>Prompt...</prompt>
	          <simpleChoice identifier="choice_1">Choice #1</simpleChoice>
	          <simpleChoice identifier="choice_2">Choice #2</simpleChoice>
	          <simpleChoice identifier="choice_3">Choice #2</simpleChoice>
	        </choiceInteraction>
        ');
	     
	    $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($element);
	    $component = $marshaller->unmarshall($element);
	    
	    // value of the orientation attribute in the PHP data model will have to be
	    // the considered default i.e. 'vertical'.
	    $this->assertInstanceOf('qtism\\data\\content\\interactions\\ChoiceInteraction', $component);
	    $this->assertSame(Orientation::VERTICAL, $component->getOrientation());
	}
	
	public function testUnmarshallMandatoryShuffle20()
	{
	    // Aims at testing that shuffle attribute is mandatory in a QTI 2.0 context.
	    // in a QTI 2.0 context.
	    $element = $this->createDOMElement('
            <choiceInteraction responseIdentifier="RESPONSE" maxChoices="0">
	          <prompt>Prompt...</prompt>
	          <simpleChoice identifier="choice_1">Choice #1</simpleChoice>
	          <simpleChoice identifier="choice_2">Choice #2</simpleChoice>
	          <simpleChoice identifier="choice_3">Choice #2</simpleChoice>
	        </choiceInteraction>
        ');
	    
	    $expectedMsg = "The mandatory 'shuffle' attribute is missing from the choiceInteraction element.";
	    $this->setExpectedException('qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException', $expectedMsg);
	    
        $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);
	}
    
    public function testUnmarshallNoMaxChoices()
    {
        $element = $this->createDOMElement('
            <choiceInteraction responseIdentifier="RESPONSE" shuffle="false">
	          <prompt>Prompt...</prompt>
	          <simpleChoice identifier="choice_1">Choice #1</simpleChoice>
	          <simpleChoice identifier="choice_2">Choice #2</simpleChoice>
	          <simpleChoice identifier="choice_3">Choice #2</simpleChoice>
	        </choiceInteraction>
        ');
        
        $this->setExpectedException(
            'qtism\data\storage\xml\marshalling\UnmarshallingException',
            "The mandatory 'maxChoices' attribute is missing from the choiceInteraction element."
        );
        
        $this->getMarshallerFactory('2.0.0')->createMarshaller($element)->unmarshall($element);
    }
    
    public function testUnmarshall30()
    {
        $element = $this->createDOMElement('
            <qti-choice-interaction response-identifier="RESPONSE" xml:base="/home/jerome">
              <qti-prompt>Prompt...</qti-prompt>
              <qti-simple-choice identifier="choice_1"><div>Choice #1</div></qti-simple-choice>
              <qti-simple-choice identifier="choice_2"><div>Choice #2</div></qti-simple-choice>
            </qti-choice-interaction>
        ');
    
        $marshaller = $this->getMarshallerFactory('3.0.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);
    
        $this->assertInstanceOf('qtism\\data\\content\\interactions\\ChoiceInteraction', $component);
        $this->assertEquals('RESPONSE', $component->getResponseIdentifier());
        $this->assertFalse($component->mustShuffle());
        $this->assertEquals(Orientation::VERTICAL, $component->getOrientation());
        $this->assertTrue($component->hasPrompt());
        $this->assertSame(0, $component->getMaxChoices());
        $this->assertSame(0, $component->getMinChoices());
        $this->assertEquals('/home/jerome', $component->getXmlBase());
    
        $prompt = $component->getPrompt();
        $content = $prompt->getContent();
        $this->assertEquals('Prompt...', $content[0]->getContent());
        
        $this->assertCount(2, $component->getSimpleChoices());
        $this->assertInstanceOf('qtism\\data\content\\xhtml\\Text\\Div', $component->getSimpleChoices()[0]->getContent()[0]);
        $this->assertInstanceOf('qtism\\data\content\\TextRun', $component->getSimpleChoices()[0]->getContent()[0]->getContent()[0]);
        $this->assertEquals('Choice #1', $component->getSimpleChoices()[0]->getContent()[0]->getContent()[0]->getContent());
        $this->assertInstanceOf('qtism\\data\content\\xhtml\\Text\\Div', $component->getSimpleChoices()[1]->getContent()[0]);
        $this->assertInstanceOf('qtism\\data\content\\TextRun', $component->getSimpleChoices()[1]->getContent()[0]->getContent()[0]);
        $this->assertEquals('Choice #2', $component->getSimpleChoices()[1]->getContent()[0]->getContent()[0]->getContent());
        
    
        $simpleChoices = $component->getSimpleChoices();
        $this->assertEquals(2, count($simpleChoices));
    }
}
