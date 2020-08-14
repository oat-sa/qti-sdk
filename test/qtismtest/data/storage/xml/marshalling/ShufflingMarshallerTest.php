<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use qtism\common\collections\IdentifierCollection;
use qtism\data\state\Shuffling;
use qtism\data\state\ShufflingGroup;
use qtism\data\state\ShufflingGroupCollection;
use qtism\data\storage\xml\marshalling\Compact21MarshallerFactory;
use qtismtest\QtiSmTestCase;

class ShufflingMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $shufflingGroup1 = new ShufflingGroup(new IdentifierCollection(['id1', 'id2', 'id3']));
        $shufflingGroup2 = new ShufflingGroup(new IdentifierCollection(['id4', 'id5', 'id6']));
        $shuffling = new Shuffling('RESPONSE', new ShufflingGroupCollection([$shufflingGroup1, $shufflingGroup2]));

        $factory = new Compact21MarshallerFactory();
        $element = $factory->createMarshaller($shuffling)->marshall($shuffling);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);

        $this->assertEquals('<shuffling responseIdentifier="RESPONSE"><shufflingGroup identifiers="id1 id2 id3"/><shufflingGroup identifiers="id4 id5 id6"/></shuffling>', $dom->saveXML($element));
    }

    public function testUnmarshall()
    {
        $element = $this->createDOMElement('
	        <shuffling responseIdentifier="RESPONSE"><shufflingGroup identifiers="id1 id2 id3"/><shufflingGroup identifiers="id4 id5 id6"/></shuffling>                
	    ');

        $factory = new Compact21MarshallerFactory();
        $component = $factory->createMarshaller($element)->unmarshall($element);

        $this->assertInstanceOf('\\qtism\\data\\state\\Shuffling', $component);
        $this->assertEquals('RESPONSE', $component->getResponseIdentifier());

        $groups = $component->getShufflingGroups();
        $this->assertEquals(2, count($groups));

        $this->assertEquals(['id1', 'id2', 'id3'], $groups[0]->getIdentifiers()->getArrayCopy());
        $this->assertEquals(['id4', 'id5', 'id6'], $groups[1]->getIdentifiers()->getArrayCopy());
    }
}
