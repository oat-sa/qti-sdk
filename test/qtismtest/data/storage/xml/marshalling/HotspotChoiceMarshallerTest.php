<?php

namespace qtismtest\data\storage\xml\marshalling;

use qtismtest\QtiSmTestCase;
use qtism\data\ShowHide;
use qtism\data\content\interactions\HotspotChoice;
use qtism\common\datatypes\QtiCoords;
use qtism\common\datatypes\QtiShape;
use DOMDocument;

class HotspotChoiceMarshallerTest extends QtiSmTestCase
{

    public function testMarshall()
    {
        $shape = QtiShape::CIRCLE;
        $coords = new QtiCoords($shape, array(0, 0, 5));
        $hotspotLabel = "This is a circle.";
        $hotspotChoice = new HotspotChoice('hotspotchoice1', $shape, $coords, 'my-hotspotchoice');
        $hotspotChoice->setFixed(true);
        $hotspotChoice->setTemplateIdentifier('mytpl1');
        $hotspotChoice->setShowHide(ShowHide::HIDE);
        $hotspotChoice->setHotspotLabel($hotspotLabel);
        
        $element = $this->getMarshallerFactory('2.1.0')->createMarshaller($hotspotChoice)->marshall($hotspotChoice);
        
        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<hotspotChoice identifier="hotspotchoice1" shape="circle" coords="0,0,5" fixed="true" templateIdentifier="mytpl1" showHide="hide" hotspotLabel="This is a circle." id="my-hotspotchoice"/>', $dom->saveXML($element));
    }
    
    public function testUnmarshall()
    {
        $element = $this->createDOMElement('
	        <hotspotChoice identifier="hotspotchoice1" shape="circle" coords="0,0,5" fixed="true" templateIdentifier="mytpl1" showHide="hide" hotspotLabel="This is a circle." id="my-hotspotchoice"/>
	    ');
        
        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf('qtism\\data\\content\\interactions\\HotspotChoice', $component);
        $this->assertInstanceOf('qtism\\data\\content\\interactions\\Hotspot', $component);
        $this->assertInstanceOf('qtism\\data\\content\\interactions\\Choice', $component);
        
        $this->assertEquals('hotspotchoice1', $component->getIdentifier());
        $this->assertEquals(QtiShape::CIRCLE, $component->getShape());
        $this->assertEquals('0,0,5', $component->getCoords()->__toString());
        $this->assertTrue($component->isFixed());
        $this->assertEquals('mytpl1', $component->getTemplateIdentifier());
        $this->assertTrue($component->hasTemplateIdentifier());
        $this->assertEquals(ShowHide::HIDE, $component->getShowHide());
        $this->assertEquals('my-hotspotchoice', $component->getId());
        $this->assertEquals('This is a circle.', $component->getHotspotLabel());
        $this->assertTrue($component->hasHotspotLabel());
    }
    
    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallFloatCoords()
    {
        // Example taken from a TAO migration issue. Coordinates contain "string-float" values.
        $element = $this->createDOMElement('
	        <hotspotChoice identifier="r_50" fixed="false" shape="circle" coords="128, 222  , 18.36"/>
	    ');
        
        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf('qtism\\data\\content\\interactions\\HotspotChoice', $component);
        $this->assertEquals('r_50', $component->getIdentifier());
        $this->assertFalse($component->isFixed());
        $this->assertEquals(QtiShape::CIRCLE, $component->getShape());
        $this->assertTrue($component->getCoords()->equals(new QtiCoords(QtiShape::CIRCLE, array(128, 222, 18))));
    }
    
    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallUnknownShape()
    {
        $element = $this->createDOMElement('
	        <hotspotChoice identifier="r_50" fixed="false" shape="unknown" coords="128,222,343"/>
	    ');
        
        $this->setExpectedException(
            'qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException',
            "The value of the mandatory attribute 'shape' is not a value from the 'shape' enumeration"
        );
        
        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }
    
    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallCoordsDoNotSatisfyShape()
    {
        $element = $this->createDOMElement('
	        <hotspotChoice identifier="r_50" fixed="false" shape="circle" coords="128,222,343,20,50"/>
	    ');
        
        $this->setExpectedException(
            'qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException',
            "The coordinates 'coords' of element 'hotspotChoice' could not be converted."
        );
        
        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }
    
    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallWrongShowHideValue()
    {
        $element = $this->createDOMElement('
	        <hotspotChoice identifier="r_50" fixed="false" shape="circle" coords="128,222,343" showHide="bla"/>
	    ');
        
        $this->setExpectedException(
            'qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException',
            "The value of the 'showHide' attribute of element 'hotspotChoice' is not a value from the 'showHide' enumeration."
        );
        
        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }
    
    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallMissingCoords()
    {
        $element = $this->createDOMElement('
	        <hotspotChoice identifier="r_50" fixed="false" shape="circle"/>
	    ');
        
        $this->setExpectedException(
            'qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException',
            "The mandatory attribute 'coords' is missing from element 'hotspotChoice'."
        );
        
        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }
    
    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallMissingShape()
    {
        $element = $this->createDOMElement('
	        <hotspotChoice identifier="r_50" fixed="false"/>
	    ');
        
        $this->setExpectedException(
            'qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException',
            "The mandatory attribute 'shape' is missing from element 'hotspotChoice'."
        );
        
        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }
    
    /**
     * @depends testUnmarshall
     */
    public function testUnmarshallMissingIdentifier()
    {
        $element = $this->createDOMElement('
	        <hotspotChoice/>
	    ');
        
        $this->setExpectedException(
            'qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException',
            "The mandatory attribute 'identifier' is missing from element 'hotspotChoice'."
        );
        
        $component = $this->getMarshallerFactory('2.1.0')->createMarshaller($element)->unmarshall($element);
    }
}
