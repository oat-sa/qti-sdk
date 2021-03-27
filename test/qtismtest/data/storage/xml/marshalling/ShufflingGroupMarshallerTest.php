<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\collections\IdentifierCollection;
use qtism\data\state\ShufflingGroup;
use qtism\data\storage\xml\marshalling\Compact21MarshallerFactory;
use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\UnmarshallingException;

/**
 * Class ShufflingGroupMarshallerTest
 */
class ShufflingGroupMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $component = new ShufflingGroup(new IdentifierCollection(['id1', 'id2', 'id3']));
        $component->setFixedIdentifiers(new IdentifierCollection(['id2']));
        $factory = new Compact21MarshallerFactory();
        $marshaller = $factory->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('id1 id2 id3', $element->getAttribute('identifiers'));
        $this::assertEquals('id2', $element->getAttribute('fixedIdentifiers'));
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<shufflingGroup identifiers="id1 id2 id3" fixedIdentifiers="id2"/>');
        $element = $dom->documentElement;

        $factory = new Compact21MarshallerFactory();
        $marshaller = $factory->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(ShufflingGroup::class, $component);
        $this::assertEquals(['id1', 'id2', 'id3'], $component->getIdentifiers()->getArrayCopy());
        $this::assertEquals(['id2'], $component->getFixedIdentifiers()->getArrayCopy());
    }

    public function testUnmarshallMissingIdentifiersAttribute()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML('<shufflingGroup fixedIdentifiers="id2"/>');
        $element = $dom->documentElement;

        $factory = new Compact21MarshallerFactory();

        $this->expectException(UnmarshallingException::class);
        $this->expectExceptionMessage("The mandatory attribute 'identifiers' is missing from element 'shufflingGroup'.");

        $factory->createMarshaller($element)->unmarshall($element);
    }
}
