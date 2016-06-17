<?php
namespace qtismtest\data\storage\xml\marshalling;

use qtismtest\QtiSmTestCase;
use qtism\data\state\ResponseValidityConstraint;
use qtism\data\storage\xml\marshalling\CompactMarshallerFactory;
use \DOMDocument;

class ResponseValidityConstraintMarshallerTest extends QtiSmTestCase {
    
    public function testUnmarshallSimple() {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<responseValidityConstraint responseIdentifier="RESPONSE" minConstraint="0" maxConstraint="1"/>');
        $element = $dom->documentElement;
        $factory = new CompactMarshallerFactory();
        $component = $factory->createMarshaller($element)->unmarshall($element);
        
        $this->assertInstanceOf('qtism\\data\\state\\ResponseValidityConstraint', $component);
        $this->assertEquals('RESPONSE', $component->getResponseIdentifier());
        $this->assertEquals(0, $component->getMinConstraint());
        $this->assertEquals(1, $component->getMaxConstraint());
        $this->assertEquals('', $component->getPatternMask());
    }
    
    /**
     * @depends testUnmarshallSimple
     */
    public function testUnmarshallWithPatternMask() {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<responseValidityConstraint responseIdentifier="RESPONSE" minConstraint="0" maxConstraint="1" patternMask="/.+/ui"/>');
        $element = $dom->documentElement;
        $factory = new CompactMarshallerFactory();
        $component = $factory->createMarshaller($element)->unmarshall($element);
        
        $this->assertEquals('/.+/ui', $component->getPatternMask());
    }
    
    public function testUnmarshallNoResponseIdentifier() {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<responseValidityConstraint minConstraint="0" maxConstraint="1"/>');
        $element = $dom->documentElement;
        $factory = new CompactMarshallerFactory();
        
        $this->setExpectedException(
            '\\qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException',
            "The mandatory 'responseIdentifier' attribute is missing from element 'responseValididtyConstraint'."
        );
        $component = $factory->createMarshaller($element)->unmarshall($element);
    }
    
    public function testUnmarshallNoMinConstraint() {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<responseValidityConstraint responseIdentifier="RESPONSE" maxConstraint="1"/>');
        $element = $dom->documentElement;
        $factory = new CompactMarshallerFactory();
        
        $this->setExpectedException(
            '\\qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException',
            "The mandatory 'minConstraint' attribute is missing from element 'responseValididtyConstraint'."
        );
        $component = $factory->createMarshaller($element)->unmarshall($element);
    }
    
    public function testUnmarshallNoMaxConstraint() {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<responseValidityConstraint responseIdentifier="RESPONSE" minConstraint="0"/>');
        $element = $dom->documentElement;
        $factory = new CompactMarshallerFactory();
        
        $this->setExpectedException(
            '\\qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException',
            "The mandatory 'maxConstraint' attribute is missing from element 'responseValididtyConstraint'."
        );
        $component = $factory->createMarshaller($element)->unmarshall($element);
    }
    
    public function testUnmarshallInvalidMaxConstraintOne() {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<responseValidityConstraint responseIdentifier="RESPONSE" minConstraint="0" maxConstraint="-2"/>');
        $element = $dom->documentElement;
        $factory = new CompactMarshallerFactory();
        
        $this->setExpectedException(
            '\\qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException',
            "An error occured while unmarshalling a 'responseValidityConstraint'. See chained exceptions for more information."
        );
        $component = $factory->createMarshaller($element)->unmarshall($element);
    }
    
    public function testUnmarshallInvalidMaxConstraintTwo() {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<responseValidityConstraint responseIdentifier="RESPONSE" minConstraint="2" maxConstraint="1"/>');
        $element = $dom->documentElement;
        $factory = new CompactMarshallerFactory();
        
        $this->setExpectedException(
            '\\qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException',
            "An error occured while unmarshalling a 'responseValidityConstraint'. See chained exceptions for more information."
        );
        $component = $factory->createMarshaller($element)->unmarshall($element);
    }
    
    public function testMarshallSimple() {
        $component = new ResponseValidityConstraint('RESPONSE', 0, 1, '/.+/ui');
        $factory = new CompactMarshallerFactory();
        
        $element = $factory->createMarshaller($component)->marshall($component);
        $this->assertEquals('RESPONSE', $element->getAttribute('responseIdentifier'));
        $this->assertEquals('0', $element->getAttribute('minConstraint'));
        $this->assertEquals('1', $element->getAttribute('maxConstraint'));
        $this->assertEquals('/.+/ui', $element->getAttribute('patternMask'));
    }
}
