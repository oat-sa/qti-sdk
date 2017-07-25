<?php

use qtism\data\rules\Selection;

require_once (dirname(__FILE__) . '/../../../../../QtiSmTestCase.php');

class SelectionMarshallerTest extends QtiSmTestCase {

	public function testMarshall() {

		$select = 2;
		$withReplacement = true;
		
		$component = new Selection($select);
		$component->setWithReplacement($withReplacement);
		
		$marshaller = $this->getMarshallerFactory()->createMarshaller($component);
		$element = $marshaller->marshall($component);
		
		$this->assertInstanceOf('\\DOMElement', $element);
		$this->assertEquals('selection', $element->nodeName);
		$this->assertSame($select . '', $element->getAttribute('select'));
		$this->assertEquals('true', $element->getAttribute('withReplacement'));
	}
    
    public function testMarshallWithExternalData() {

        $select = 2;
        $withReplacement = true;
        $xmlString = '
            <selection xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" select="2" ><som:adaptiveItemSelection xmlns:som="http://www.my-namespace.com"/></selection>
        ';
        
        $component = new Selection($select, $withReplacement, $xmlString);
        
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);
        
        $this->assertInstanceOf('\\DOMElement', $element);
        $this->assertEquals('selection', $element->nodeName);
        $this->assertSame($select . '', $element->getAttribute('select'));
        $this->assertEquals('true', $element->getAttribute('withReplacement'));
        
        $this->assertEquals($element->ownerDocument->saveXML($element), '<selection select="2" withReplacement="true"><som:adaptiveItemSelection xmlns:som="http://www.my-namespace.com"/></selection>');
    }
	
	public function testUnmarshallValid() {
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->loadXML('<selection xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" select="2" withReplacement="true"/>');
		$element = $dom->documentElement;
		
		$marshaller = $this->getMarshallerFactory()->createMarshaller($element);
		$component = $marshaller->unmarshall($element);
		
		$this->assertInstanceOf('qtism\\data\\Rules\\Selection', $component);
		$this->assertEquals($component->getSelect(), 2);
		$this->assertEquals($component->isWithReplacement(), true);
	}
    
    public function testUnmarshallValidTwo() {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<selection xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" select="2"/>');
        $element = $dom->documentElement;
        
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);
        
        $this->assertInstanceOf('qtism\\data\\Rules\\Selection', $component);
        $this->assertEquals($component->getSelect(), 2);
        $this->assertEquals($component->isWithReplacement(), false);
    }

    public function testUnmarshallValidWithExtension() {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('
            <selection xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" select="2" >
                <ais:adaptiveItemSelection xmlns:ais="http://www.taotesting.com/xsd/ais_v1p0p0">
                    <ais:adaptiveEngineRef identifier="engine" href="http://www.my-cat-engine/cat/api/"/>
                    <ais:adaptiveSettingsRef identifier="settings" href="settings.xml"/>
                    <ais:qtiUsagedataRef identifier="usagedata" href="usagedata.xml"/>
                    <ais:qtiMetadataRef identifier="metadata" href="metadata.xml"/>
                </ais:adaptiveItemSelection>
            </selection>
        ');
        $element = $dom->documentElement;
        
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);
        
        $this->assertInstanceOf('qtism\\data\\Rules\\Selection', $component);
        $this->assertEquals($component->getSelect(), 2);
        $this->assertEquals($component->isWithReplacement(), false);
        
        $this->assertEquals(1, $component->getXml()->documentElement->getElementsByTagNameNS('http://www.taotesting.com/xsd/ais_v1p0p0', 'adaptiveItemSelection')->length);
        $this->assertEquals(1, $component->getXml()->documentElement->getElementsByTagNameNS('http://www.taotesting.com/xsd/ais_v1p0p0', 'adaptiveEngineRef')->length);
        $this->assertEquals(1, $component->getXml()->documentElement->getElementsByTagNameNS('http://www.taotesting.com/xsd/ais_v1p0p0', 'qtiUsagedataRef')->length);
        $this->assertEquals(1, $component->getXml()->documentElement->getElementsByTagNameNS('http://www.taotesting.com/xsd/ais_v1p0p0', 'qtiMetadataRef')->length);
    }
	
	public function testUnmarshallInvalid() {
		$dom = new DOMDocument('1.0', 'UTF-8');
		// the mandatory 'select' attribute is missing in the following test.
		$dom->loadXML('<selection xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" withReplacement="true"/>');
		$element = $dom->documentElement;
		
		$marshaller = $this->getMarshallerFactory()->createMarshaller($element);
		
		$this->setExpectedException('qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException');
		$component = $marshaller->unmarshall($element);
	}
}
