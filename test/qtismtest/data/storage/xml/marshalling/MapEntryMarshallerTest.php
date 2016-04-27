<?php
namespace qtismtest\data\storage\xml\marshalling;

use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\Marshaller;
use qtism\data\state\MapEntry;
use qtism\common\enums\BaseType;
use \DOMDocument;

class MapEntryMarshallerTest extends QtiSmTestCase {

	public function testMarshall21() {

		$component = new MapEntry(1337, 1.377, true);
		
		$marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component, array(BaseType::INTEGER));
		$element = $marshaller->marshall($component);
		
		$this->assertInstanceOf('\\DOMElement', $element);
		$this->assertEquals('mapEntry', $element->nodeName);
		$this->assertEquals('1337', $element->getAttribute('mapKey'));
		$this->assertEquals('1.377', $element->getAttribute('mappedValue'));
		$this->assertEquals('true', $element->getAttribute('caseSensitive'));
	}
	
	public function testUnmarshall21() {
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->loadXML('<mapEntry mapKey="1337" mappedValue="1.377" caseSensitive="true"/>');
		$element = $dom->documentElement;
		
		$marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element, array(BaseType::INTEGER));
		$component = $marshaller->unmarshall($element);
		
		$this->assertInstanceOf('qtism\\data\\state\\MapEntry', $component);
		$this->assertInternalType('integer', $component->getMapKey());
		$this->assertEquals(1337, $component->getMapKey());
		$this->assertInternalType('float', $component->getMappedValue());
		$this->assertEquals(1.377, $component->getMappedValue());
		$this->assertInternalType('boolean', $component->isCaseSensitive());
		$this->assertEquals(true, $component->isCaseSensitive());
	}
    
    public function testUnmarshall21EmptyMapKeyForString() {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<mapEntry mapKey="" mappedValue="-1.0"/>');
        $element = $dom->documentElement;
        
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element, array(BaseType::STRING));
        $component = $marshaller->unmarshall($element);
        
        $this->assertInstanceOf('qtism\\data\\state\MapEntry', $component);
        $this->assertEquals('', $component->getMapKey());
        $this->assertEquals(-1.0, $component->getMappedValue());
    }
    
    public function testUnmarshall21EmptyMapKeyForInteger() {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<mapEntry mapKey="" mappedValue="-1.0"/>');
        $element = $dom->documentElement;
        
        $this->setExpectedException(
            'qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException',
            "The value '' of the 'mapKey' attribute could not be converted to a 'integer' value."
        );
        
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element, array(BaseType::INTEGER));
        $component = $marshaller->unmarshall($element);
    }
    
    public function testUnmarshall21EmptyMapKeyForIdentifier() {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<mapEntry mapKey="" mappedValue="-1.0"/>');
        $element = $dom->documentElement;
        
        $this->setExpectedException(
            'qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException',
            "The value '' of the 'mapKey' attribute could not be converted to a 'identifier' value."
        );
        
        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element, array(BaseType::IDENTIFIER));
        $component = $marshaller->unmarshall($element);
    }
	
	public function testMarshall20() {
	    // No caseSensitive attribute in QTI 2.0. Check that no caseSensitive attribute goes out.
	    $component = new MapEntry(1337, 1.377, true);
		
		$marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($component, array(BaseType::INTEGER));
		$element = $marshaller->marshall($component);
		
		$this->assertInstanceOf('\\DOMElement', $element);
		$this->assertEquals('mapEntry', $element->nodeName);
		$this->assertEquals('1337', $element->getAttribute('mapKey'));
		$this->assertEquals('1.377', $element->getAttribute('mappedValue'));
		
		// Should return an empty string.
		$this->assertSame('', $element->getAttribute('caseSensitive'));
	}
	
	public function testUnmarshall20() {
	    // Make sure the caseSensitive attribute is not taken into account.
	    $dom = new DOMDocument('1.0', 'UTF-8');
	    $dom->loadXML('<mapEntry mapKey="1337" mappedValue="1.377" caseSensitive="false"/>');
	    $element = $dom->documentElement;
	    
	    $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($element, array(BaseType::INTEGER));
	    $component = $marshaller->unmarshall($element);
	    
	    $this->assertInstanceOf('qtism\\data\\state\\MapEntry', $component);
	    $this->assertInternalType('integer', $component->getMapKey());
	    $this->assertEquals(1337, $component->getMapKey());
	    $this->assertInternalType('float', $component->getMappedValue());
	    $this->assertEquals(1.377, $component->getMappedValue());
	    
	    // Because default behaviour of the PHP model is true for caseSensitive,
	    // make sure its not false.
	    $this->assertInternalType('boolean', $component->isCaseSensitive());
	    $this->assertEquals(true, $component->isCaseSensitive());
	}
}
