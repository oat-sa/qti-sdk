<?php

namespace qtismtest\data\storage\xml\marshalling;

use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\CompactMarshallerFactory;
use qtism\data\state\ShufflingGroup;
use qtism\common\collections\IdentifierCollection;
use DOMDocument;

class ShufflingGroupMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        
        $component = new ShufflingGroup(new IdentifierCollection(array('id1', 'id2', 'id3')));
        $component->setFixedIdentifiers(new IdentifierCollection(array('id2')));
        $factory = new CompactMarshallerFactory();
        $marshaller = $factory->createMarshaller($component);
        $element = $marshaller->marshall($component);
        
        $this->assertInstanceOf('\\DOMElement', $element);
        $this->assertEquals('id1 id2 id3', $element->getAttribute('identifiers'));
        $this->assertEquals('id2', $element->getAttribute('fixedIdentifiers'));
    }
    
    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<shufflingGroup identifiers="id1 id2 id3" fixedIdentifiers="id2"/>');
        $element = $dom->documentElement;
        
        $factory = new CompactMarshallerFactory();
        $marshaller = $factory->createMarshaller($element);
        $component = $marshaller->unmarshall($element);
        
        $this->assertInstanceOf('qtism\\data\\state\\ShufflingGroup', $component);
        $this->assertEquals(array('id1', 'id2', 'id3'), $component->getIdentifiers()->getArrayCopy());
        $this->assertEquals(array('id2'), $component->getFixedIdentifiers()->getArrayCopy());
    }
    
    public function testUnmarshallMissingIdentifiersAttribute()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<shufflingGroup fixedIdentifiers="id2"/>');
        $element = $dom->documentElement;
        
        $factory = new CompactMarshallerFactory();
        
        $this->setExpectedException(
            'qtism\\data\\storage\\xml\\marshalling\\UnmarshallingException',
            "The mandatory attribute 'identifiers' is missing from element 'shufflingGroup'."
        );
        
        $factory->createMarshaller($element)->unmarshall($element);
    }
}
