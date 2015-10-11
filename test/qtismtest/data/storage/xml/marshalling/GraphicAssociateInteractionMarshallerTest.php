<?php
namespace qtismtest\data\storage\xml\marshalling;

use qtismtest\QtiSmTestCase;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\interactions\GraphicAssociateInteraction;
use qtism\data\content\interactions\AssociableHotspotCollection;
use qtism\common\datatypes\QtiCoords;
use qtism\common\datatypes\QtiShape;
use qtism\data\content\interactions\AssociableHotspot;
use qtism\data\content\xhtml\Object;
use qtism\data\content\TextRun;
use qtism\data\content\InlineStaticCollection;
use qtism\data\content\interactions\Prompt;
use \DOMDocument;

class GraphicAssociateInteractionMarshallerTest extends QtiSmTestCase {

	public function testMarshall21() {
        
	    $prompt = new Prompt();
	    $prompt->setContent(new FlowStaticCollection(array(new TextRun('Prompt...'))));
	    
	    $object = new Object('myimg.png', 'image/png');
	    
	    $choice1 = new AssociableHotspot('choice1', 2, QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, array(0, 0, 15)));
	    $choice1->setMatchMin(1);
	    $choice2 = new AssociableHotspot('choice2', 1, QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, array(2, 2, 15)));
	    $choice3 = new AssociableHotspot('choice3', 1, QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, array(4, 4, 15)));
	    $choices = new AssociableHotspotCollection(array($choice1, $choice2, $choice3));
	    
	    $graphicAssociateInteraction = new GraphicAssociateInteraction('RESPONSE', $object, $choices, 'prout');
	    $graphicAssociateInteraction->setPrompt($prompt);
	    $graphicAssociateInteraction->setMaxAssociations(3);
	    $graphicAssociateInteraction->setMinAssociations(2);
	    
        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($graphicAssociateInteraction)->marshall($graphicAssociateInteraction);
        
        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        
        $this->assertEquals('<graphicAssociateInteraction id="prout" responseIdentifier="RESPONSE" minAssociations="2" maxAssociations="3"><prompt>Prompt...</prompt><object data="myimg.png" type="image/png"/><associableHotspot identifier="choice1" shape="circle" coords="0,0,15" matchMin="1" matchMax="2"/><associableHotspot identifier="choice2" shape="circle" coords="2,2,15" matchMax="1"/><associableHotspot identifier="choice3" shape="circle" coords="4,4,15" matchMax="1"/></graphicAssociateInteraction>', $dom->saveXML($element));
	}
	
	/**
	 * @depends testMarshall21
	 */
	public function testMarshall20() {
	    // Make sure that maxAssociations is always in the output but never minAssociations.
	    $object = new Object('myimg.png', 'image/png');
	     
	    $choice1 = new AssociableHotspot('choice1', 2, QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, array(0, 0, 15)));
	    $choice1->setMatchMin(1);
	    $choice2 = new AssociableHotspot('choice2', 1, QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, array(2, 2, 15)));
	    $choice3 = new AssociableHotspot('choice3', 1, QtiShape::CIRCLE, new QtiCoords(QtiShape::CIRCLE, array(4, 4, 15)));
	    $choices = new AssociableHotspotCollection(array($choice1, $choice2, $choice3));
	     
	    $graphicAssociateInteraction = new GraphicAssociateInteraction('RESPONSE', $object, $choices);
	    $graphicAssociateInteraction->setMaxAssociations(3);
	    $graphicAssociateInteraction->setMinAssociations(2);
	     
	    $element = $this->getMarshallerFactory('2.0.0')->createMarshaller($graphicAssociateInteraction)->marshall($graphicAssociateInteraction);
	    
	    $dom = new DOMDocument('1.0', 'UTF-8');
	    $element = $dom->importNode($element, true);
	    
	    $this->assertEquals('<graphicAssociateInteraction responseIdentifier="RESPONSE" maxAssociations="3"><object data="myimg.png" type="image/png"/><associableHotspot identifier="choice1" shape="circle" coords="0,0,15" matchMax="2"/><associableHotspot identifier="choice2" shape="circle" coords="2,2,15" matchMax="1"/><associableHotspot identifier="choice3" shape="circle" coords="4,4,15" matchMax="1"/></graphicAssociateInteraction>', $dom->saveXML($element));
	}
	
	public function testUnmarshall21() {
        $element = $this->createDOMElement('
            <graphicAssociateInteraction responseIdentifier="RESPONSE" id="prout" minAssociations="2" maxAssociations="3">
              <prompt>Prompt...</prompt>
              <object data="myimg.png" type="image/png"/>
              <associableHotspot identifier="choice1" shape="circle" coords="0,0,15" matchMin="1" matchMax="2"/>
              <associableHotspot identifier="choice2" shape="circle" coords="2,2,15" matchMax="1"/>
              <associableHotspot identifier="choice3" shape="circle" coords="4,4,15" matchMax="1"/>
            </graphicAssociateInteraction>
        ');
        
        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf('qtism\\data\\content\\interactions\\GraphicAssociateInteraction', $component);
        $this->assertEquals('RESPONSE', $component->getResponseIdentifier());
        $this->assertEquals('prout', $component->getId());
        $this->assertSame(2, $component->getMinAssociations());
        $this->assertSame(3, $component->getMaxAssociations());
        
        $this->assertTrue($component->hasPrompt());
        $promptContent = $component->getPrompt()->getContent();
        $this->assertEquals('Prompt...', $promptContent[0]->getContent());
        
        $object = $component->getObject();
        $this->assertEquals('myimg.png', $object->getData());
        $this->assertEquals('image/png', $object->getType());
        
        $choices = $component->getAssociableHotspots();
        $this->assertEquals(3, count($choices));
        
        $this->assertEquals('choice1', $choices[0]->getIdentifier());
        $this->assertEquals(2, $choices[0]->getMatchMax());
        $this->assertEquals(1, $choices[0]->getMatchMin());
        $this->assertEquals(QtiShape::CIRCLE, $choices[0]->getShape());
        $this->assertTrue($choices[0]->getCoords()->equals(new QtiCoords(QtiShape::CIRCLE, array(0, 0, 15))));
        
        $this->assertEquals('choice2', $choices[1]->getIdentifier());
        $this->assertEquals(1, $choices[1]->getMatchMax());
        $this->assertEquals(0, $choices[1]->getMatchMin());
        $this->assertEquals(QtiShape::CIRCLE, $choices[1]->getShape());
        $this->assertTrue($choices[1]->getCoords()->equals(new QtiCoords(QtiShape::CIRCLE, array(2, 2, 15))));
        
        $this->assertEquals('choice3', $choices[2]->getIdentifier());
        $this->assertEquals(1, $choices[2]->getMatchMax());
        $this->assertEquals(0, $choices[2]->getMatchMin());
        $this->assertEquals(QtiShape::CIRCLE, $choices[2]->getShape());
        $this->assertTrue($choices[2]->getCoords()->equals(new QtiCoords(QtiShape::CIRCLE, array(4, 4, 15))));
	}
	
	public function testUnmarshall20() {
	    // Make sure minAssociations is not taken into account
	    // in a QTI 2.0 context.
	    $element = $this->createDOMElement('
            <graphicAssociateInteraction responseIdentifier="RESPONSE" minAssociations="2" maxAssociations="3">
              <object data="myimg.png" type="image/png"/>
              <associableHotspot identifier="choice1" shape="circle" coords="0,0,15" matchMin="1" matchMax="2"/>
              <associableHotspot identifier="choice2" shape="circle" coords="2,2,15" matchMax="1"/>
              <associableHotspot identifier="choice3" shape="circle" coords="4,4,15" matchMax="1"/>
            </graphicAssociateInteraction>
        ');
	    
	    $component = $this->getMarshallerFactory('2.0.0')->createMarshaller($element)->unmarshall($element);

	    $this->assertSame(0, $component->getMinAssociations());
	    $this->assertSame(3, $component->getMaxAssociations());
	}
}