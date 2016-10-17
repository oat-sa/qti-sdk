<?php
namespace qtismtest\data\storage\xml\marshalling;

use qtismtest\QtiSmTestCase;
use qtism\data\ShowHide;
use qtism\data\content\TextRun;
use qtism\data\content\InlineStaticCollection;
use qtism\data\content\TemplateInline;
use \DOMDocument;

class TemplateInlineMarshallerTest extends QtiSmTestCase {

	public function testMarshall() {
	    
	    $templateInline = new TemplateInline('tpl1', 'inline1');
	    $templateInline->setContent(new InlineStaticCollection(array(new TextRun('Inline ...'))));
	    
	    $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($templateInline)->marshall($templateInline);
	    
	    $dom = new DOMDocument('1.0', 'UTF-8');
	    $element = $dom->importNode($element, true);
	    $this->assertEquals('<templateInline templateIdentifier="tpl1" identifier="inline1" showHide="show">Inline ...</templateInline>', $dom->saveXML($element));
	}
	
	public function testUnmarshall() {
	    $element = $this->createDOMElement('
	        <templateInline templateIdentifier="tpl1" identifier="inline1" showHide="show">Inline ...</templateInline>
	    ');
	    
	    $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
	    $this->assertInstanceOf('qtism\\data\\content\\TemplateInline', $component);
	    $this->assertEquals('tpl1', $component->getTemplateIdentifier());
	    $this->assertEquals('inline1', $component->getIdentifier());
	    $this->assertEquals(ShowHide::SHOW, $component->getShowHide());
	    
	    $content = $component->getContent();
	    $this->assertEquals(1, count($content));
	    $this->assertEquals('Inline ...', $content[0]->getContent());
	}
    
    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallInvalidContent() {
	    $element = $this->createDOMElement('
	        <templateInline templateIdentifier="tpl1" identifier="inline1" showHide="show"><div>Block Content</div></templateInline>
	    ');
        
        $this->setExpectedException(
            'qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException',
            "The content of the 'templateInline' is invalid. It must only contain 'block' elements."
        );
	    
	    $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
	}
}
