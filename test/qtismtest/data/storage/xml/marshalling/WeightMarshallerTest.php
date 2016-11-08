<?php
namespace qtismtest\data\storage\xml\marshalling;

use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\Marshaller;
use qtism\data\state\Weight;
use \DOMDocument;

class WeightMarshallerTest extends QtiSmTestCase
{
	public function testMarshall()
    {
		$identifier = 'myWeight1';
		$value = 3.45;
		
		$component = new Weight($identifier, $value);
		$marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
		$element = $marshaller->marshall($component);
		
		$this->assertInstanceOf('\\DOMElement', $element);
		$this->assertEquals('weight', $element->nodeName);
		$this->assertEquals($identifier, $element->getAttribute('identifier'));
		$this->assertEquals($value . '', $element->getAttribute('value'));
	}
	
	public function testUnmarshall() 
    {
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->loadXML('<weight xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="myWeight1" value="3.45"/>');
		$element = $dom->documentElement;
		
		$marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
		$component = $marshaller->unmarshall($element);
		
		$this->assertInstanceOf('qtism\\data\\state\\Weight', $component);
		$this->assertEquals($component->getIdentifier(), 'myWeight1');
		$this->assertEquals($component->getValue(), 3.45);
	}
    
    public function testUnmarshallWrongIdentifier()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
		$dom->loadXML('<weight xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="999" value="3.45"/>');
		$element = $dom->documentElement;
		
		$marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        
        $this->setExpectedException(
            'qtism\data\storage\xml\marshalling\UnmarshallingException',
            "The value of 'identifier' from element 'weight' is not a valid QTI Identifier."
        );
        
		$marshaller->unmarshall($element);
    }
    
    public function testUnmarshallNonFloatValue()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
		$dom->loadXML('<weight xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="my-identifier" value="lll"/>');
		$element = $dom->documentElement;
		
		$marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        
        $this->setExpectedException(
            'qtism\data\storage\xml\marshalling\UnmarshallingException',
            "The value of attribute 'value' from element 'weight' cannot be converted into a float."
        );
        
		$marshaller->unmarshall($element);
    }
    
    public function testUnmarshallNoValue()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
		$dom->loadXML('<weight xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="my-identifier"/>');
		$element = $dom->documentElement;
		
		$marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        
        $this->setExpectedException(
            'qtism\data\storage\xml\marshalling\UnmarshallingException',
            "The mandatory attribute 'value' is missing from element 'weight'."
        );
        
		$marshaller->unmarshall($element);
    }
    
    public function testUnmarshallMissingIdentifier()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
		$dom->loadXML('<weight xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" value="1.1"/>');
		$element = $dom->documentElement;
		
		$marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        
        $this->setExpectedException(
            'qtism\data\storage\xml\marshalling\UnmarshallingException',
            "The mandatory attribute 'identifier' is missing from element 'weight'."
        );
        
		$marshaller->unmarshall($element);
    }
}
